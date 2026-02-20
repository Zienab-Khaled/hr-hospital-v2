<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\CharityClaim;
use App\Models\Invoice;
use Illuminate\Http\Request;

class CharityClaimController extends Controller
{
    /**
     * Display a listing of charity claims
     */
    public function index(Request $request)
    {
        $this->authorize('invoices.view');

        $query = CharityClaim::with(['invoice.patient', 'charityEntity', 'sentByUser']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by charity entity
        if ($request->filled('charity_entity_id')) {
            $query->where('charity_entity_id', $request->charity_entity_id);
        }

        // Search by invoice number or patient name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('invoice', function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%")
                            ->orWhere('file_number', 'like', "%{$search}%");
                    });
            });
        }

        $claims = $query->latest()->paginate(20, ['*'], 'charity_page');
        $charityEntities = \App\Models\CharityEntity::orderBy('name')->get();

        // Insurance claims tab
        $insuranceClaims = \App\Models\InsuranceClaim::with(['invoice.patient', 'insuranceCompany', 'sentByUser'])
            ->latest()
            ->paginate(20, ['*'], 'insurance_page');

        return view('charity-claims.index', compact('claims', 'charityEntities', 'insuranceClaims'));
    }

    /**
     * Show the form for creating a new charity claim
     */
    public function create(Request $request)
    {
        $this->authorize('invoices.create');

        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with(['patient', 'items.service'])->findOrFail($request->invoice_id);

            // Check if invoice already has a claim
            if ($invoice->hasCharityClaim()) {
                return back()->withErrors(['invoice_id' => app()->getLocale() === 'ar'
                    ? 'هذه الفاتورة لديها مطالبة بالفعل'
                    : 'This invoice already has a claim']);
            }

            // Check if patient is charity type
            if ($invoice->patient->payment_type !== 'charity') {
                return back()->withErrors(['invoice_id' => app()->getLocale() === 'ar'
                    ? 'هذا المريض ليس من نوع جمعية خيرية'
                    : 'This patient is not a charity type']);
            }
        }

        return view('charity-claims.create', compact('invoice'));
    }

    /**
     * Store a newly created charity claim
     */
    public function store(Request $request)
    {
        $this->authorize('invoices.create');

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::with('patient')->findOrFail($request->invoice_id);

        // Validate charity patient
        if ($invoice->patient->payment_type !== 'charity') {
            return back()->withErrors(['invoice_id' => app()->getLocale() === 'ar'
                ? 'هذا المريض ليس من نوع جمعية خيرية'
                : 'This patient is not a charity type']);
        }

        // Check if claim already exists
        if ($invoice->hasCharityClaim()) {
            return back()->withErrors(['invoice_id' => app()->getLocale() === 'ar'
                ? 'هذه الفاتورة لديها مطالبة بالفعل'
                : 'This invoice already has a claim']);
        }

        $claim = CharityClaim::create([
            'invoice_id' => $invoice->id,
            'charity_entity_id' => $invoice->patient->charity_entity_id,
            'status' => 'draft',
            'notes' => $request->notes,
        ]);

        ActivityLogger::log('Charity Claim Created', 'CharityClaim', $claim->id, 'Claim created for invoice', null, $claim->toArray());

        return redirect()->route('charity-claims.show', $claim)
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المطالبة بنجاح' : 'Claim created successfully');
    }

    /**
     * Display the specified charity claim
     */
    public function show(CharityClaim $charityClaim)
    {
        $this->authorize('invoices.view');

        $charityClaim->load(['invoice.patient', 'invoice.items.service', 'charityEntity', 'sentByUser']);

        return view('charity-claims.show', compact('charityClaim'));
    }

    /**
     * Send claim to charity entity
     */
    public function send(CharityClaim $charityClaim)
    {
        $this->authorize('invoices.create');

        if (!$charityClaim->canBeSent()) {
            return back()->withErrors(['status' => app()->getLocale() === 'ar'
                ? 'لا يمكن إرسال هذه المطالبة'
                : 'This claim cannot be sent']);
        }

        $charityClaim->markAsSent();

        ActivityLogger::log('Charity Claim Sent', 'CharityClaim', $charityClaim->id, 'Claim sent to charity entity',
            ['old_status' => 'draft'], ['new_status' => 'sent']);

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم إرسال المطالبة للجمعية' : 'Claim sent to charity entity');
    }

    /**
     * Update claim status
     */
    public function updateStatus(Request $request, CharityClaim $charityClaim)
    {
        $this->authorize('invoices.create');

        $request->validate([
            'status' => 'required|in:under_review,approved,rejected,paid',
            'approved_amount' => 'nullable|numeric|min:0',
            'entity_response_notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $charityClaim->status;

        switch ($request->status) {
            case 'under_review':
                $charityClaim->markAsUnderReview();
                break;
            case 'approved':
                $charityClaim->markAsApproved($request->approved_amount);
                break;
            case 'rejected':
                $charityClaim->markAsRejected($request->entity_response_notes);
                break;
            case 'paid':
                $charityClaim->markAsPaid();
                break;
        }

        ActivityLogger::log('Charity Claim Status Updated', 'CharityClaim', $charityClaim->id, 'Status changed',
            ['old_status' => $oldStatus], ['new_status' => $request->status]);

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث حالة المطالبة' : 'Claim status updated');
    }
}
