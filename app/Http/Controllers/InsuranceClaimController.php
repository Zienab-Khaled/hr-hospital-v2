<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\InsuranceClaim;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;

class InsuranceClaimController extends Controller
{
    /**
     * Show the form for creating a new insurance claim
     */
    public function create()
    {
        $this->authorize('invoices.create');

        return view('insurance-claims.create');
    }

    /**
     * AJAX: Search for insurance patients
     */
    public function searchPatients(Request $request)
    {
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
        $this->authorize('invoices.create');

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
        $items = $invoice->items()
            ->where('status', 'pending')
            ->with('service')
            ->get();

        return response()->json($items);
    }
}
