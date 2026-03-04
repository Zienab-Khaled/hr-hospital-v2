<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\InsuranceClaim;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceClaimController extends Controller
{
    /**
     * Show the form for creating a new insurance claim
     * يقبل invoice_id و patient_id في الـ query لتعبيئة النموذج من صفحة تقارير التأمين
     */
    public function create(Request $request)
    {
        $this->authorize('claims.view');

        $prefill = null;
        if ($request->filled('invoice_id') && $request->filled('patient_id')) {
            $invoice = Invoice::with('patient')->find($request->invoice_id);
            if ($invoice && $invoice->patient_id == $request->patient_id && $invoice->patient->payment_type === 'insurance') {
                $prefill = [
                    'patient_id' => $invoice->patient_id,
                    'patient_name' => $invoice->patient->name_ar ?? $invoice->patient->name,
                    'patient_file_number' => $invoice->patient->file_number ?? '',
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
                    'total_amount' => (float) $invoice->total_amount,
                ];
            }
        }

        return view('insurance-claims.create', compact('prefill'));
    }

    /**
     * AJAX: Search for insurance patients
     */
    public function searchPatients(Request $request)
    {
        $this->authorize('claims.view');
        $q = $request->get('q');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $patients = Patient::where('payment_type', 'insurance')
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('name_ar', 'like', "%{$q}%")
                    ->orWhere('file_number', 'like', "%{$q}%")
                    ->orWhere('identity_value', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'name_ar', 'file_number']);

        return response()->json($patients);
    }

    /**
     * Store a newly created insurance claim
     */
    public function store(Request $request)
    {
        $this->authorize('claims.view');

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'invoice_id' => 'required|exists:invoices,id',
            'status' => 'required|in:draft,sent,under_review,approved,rejected,paid',
            'arqos_file' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'notes' => 'nullable|string|max:1000',
        ]);

        $patient = Patient::findOrFail($request->patient_id);
        $invoice = Invoice::findOrFail($request->invoice_id);

        $claim = InsuranceClaim::create([
            'invoice_id' => $invoice->id,
            'insurance_company_id' => $patient->insurance_company_id,
            'status' => $request->status,
            'sent_date' => $request->status !== 'draft' ? now() : null,
            'sent_by' => $request->status !== 'draft' ? auth()->id() : null,
            'notes' => $request->notes,
        ]);

        if ($request->hasFile('arqos_file')) {
            $claim->addMediaFromRequest('arqos_file')->toMediaCollection('arqos_file');
        }

        ActivityLogger::log('Insurance Claim Created', 'InsuranceClaim', $claim->id, 'Claim created for invoice', null, $claim->toArray());

        return redirect()->route('charity-claims.index', ['tab' => 'insurance'])
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء مطالبة التأمين بنجاح' : 'Insurance claim created successfully');
    }

    /**
     * AJAX: Get invoices for a specific patient
     */
    public function getInvoices(Patient $patient)
    {
        $this->authorize('claims.view');
        $invoices = $patient->invoices()
            ->withCount(['items as pending_items_count' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->latest()
            ->get(['id', 'invoice_number', 'invoice_date', 'total_amount']);

        return response()->json($invoices);
    }

    /**
     * AJAX: Get pending items for a specific invoice
     */
    public function getItems(Invoice $invoice)
    {
        $this->authorize('claims.view');
        $items = $invoice->items()
            ->where('status', 'pending')
            ->with('service')
            ->get();

        return response()->json($items);
    }

    /**
     * Display the specified insurance claim
     */
    public function show(InsuranceClaim $insuranceClaim)
    {
        $this->authorize('claims.view');
        $insuranceClaim->load(['invoice.patient', 'invoice.items.service', 'insuranceCompany', 'sentByUser']);
        return view('insurance-claims.show', compact('insuranceClaim'));
    }

    /**
     * Update claim status
     */
    public function updateStatus(Request $request, InsuranceClaim $insuranceClaim)
    {
        $this->authorize('claims.view');

        $request->validate([
            'status' => 'required|in:under_review,approved,rejected,paid',
            'approved_amount' => 'nullable|numeric|min:0',
            'company_response_notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $insuranceClaim->status;

        DB::beginTransaction();
        try {
            if ($request->status === 'paid') {
                $insuranceClaim->markAsPaid();

                // Notify Accountants
                $accountants = \App\Models\User::role('accountant')->get();
                if ($accountants->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send($accountants, new \App\Notifications\SystemNotification([
                        'title' => app()->getLocale() === 'ar' ? 'تم تحصيل مطالبة تأمين' : 'Insurance Claim Paid',
                        'message' => (app()->getLocale() === 'ar' ? "تم سداد مطالبة بمبلغ " : "Claim paid with amount: ") . number_format($insuranceClaim->approved_amount ?: $insuranceClaim->invoice->remaining_amount, 2) . (app()->getLocale() === 'ar' ? " للمريض: " : " for patient: ") . ($insuranceClaim->invoice->patient->name_ar ?? $insuranceClaim->invoice->patient->name),
                        'action_url' => route('insurance-claims.show', $insuranceClaim),
                        'type' => 'success',
                    ]));
                }
            } else {
                $updateData = ['status' => $request->status];
                if ($request->filled('approved_amount')) {
                    $updateData['approved_amount'] = $request->approved_amount;
                }
                if ($request->filled('company_response_notes')) {
                    $updateData['company_response_notes'] = $request->company_response_notes;
                }
                $insuranceClaim->update($updateData);
            }

            DB::commit();

            ActivityLogger::log('Insurance Claim Status Updated', 'InsuranceClaim', $insuranceClaim->id, 'Status changed',
                ['old_status' => $oldStatus], ['new_status' => $request->status]);

            return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث حالة المطالبة' : 'Claim status updated');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating claim: ' . $e->getMessage()]);
        }
    }
}
