<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\CharityClaim;
use App\Models\CharityClaimNote;
use App\Models\Invoice;
use Illuminate\Http\Request;

class CharityClaimController extends Controller
{
    /**
     * Display a listing of charity claims
     */
    public function index(Request $request)
    {
        $this->authorize('claims.view');

        if (auth()->user()->hasRole('insurance_clerk') && $request->get('tab', 'charity') === 'charity') {
            return redirect()->route('charity-claims.index', array_merge($request->query(), ['tab' => 'insurance']));
        }

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
                            ->orWhere('name_ar_first', 'like', "%{$search}%")
                            ->orWhere('name_ar_father', 'like', "%{$search}%")
                            ->orWhere('name_ar_family', 'like', "%{$search}%")
                            ->orWhere('file_number', 'like', "%{$search}%");
                    });
            });
        }

        $claims = $query->latest()->paginate(20, ['*'], 'charity_page');
        $charityEntities = \App\Models\CharityEntity::orderByRaw('COALESCE(name_ar, name)')->get();

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
        $this->authorize('claims.view');

        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with(['patient', 'items.service'])->findOrFail($request->invoice_id);

            // Check if invoice already has a claim
            if ($invoice->hasCharityClaim()) {
                return back()->withErrors(['invoice_id' => app()->getLocale() === 'ar'
                    ? 'هذه الفاتورة لديها مطالبة بالفعل'
                    : 'This invoice already has a claim']);
            }

            // Check if patient is charity type using invoice's payment_type
            if ($invoice->payment_type !== 'charity') {
                return back()->withErrors(['invoice_id' => app()->getLocale() === 'ar'
                    ? 'هذه الفاتورة ليست مطالبة جمعية خيرية (ربما تم تسجيلها كـ كاش لعدم وجود اعتماد)'
                    : 'This invoice is not a charity claim (maybe it was recorded as cash due to missing approval)']);
            }
        }

        return view('charity-claims.create', compact('invoice'));
    }

    /**
     * Store a newly created charity claim
     */
    public function store(Request $request)
    {
        $this->authorize('claims.view');

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::with('patient')->findOrFail($request->invoice_id);

        // Validate charity invoice
        if ($invoice->payment_type !== 'charity') {
            return back()->withErrors(['invoice_id' => app()->getLocale() === 'ar'
                ? 'هذه الفاتورة ليست مطالبة جمعية خيرية (ربما تم تسجيلها كـ كاش لعدم وجود اعتماد)'
                : 'This invoice is not a charity claim (maybe it was recorded as cash due to missing approval)']);
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
        $this->authorize('claims.view');

        $charityClaim->load(['invoice.patient', 'invoice.items.service', 'charityEntity', 'sentByUser']);

        $claimNotes = $charityClaim->notes()->with('createdByUser')->orderBy('created_at', 'desc')->get();

        return view('charity-claims.show', compact('charityClaim', 'claimNotes'));
    }

    /**
     * إضافة ملاحظة للمطالبة (بدون تغيير الحالة).
     */
    public function addNote(Request $request, CharityClaim $charityClaim)
    {
        $this->authorize('claims.view');

        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        CharityClaimNote::create([
            'charity_claim_id' => $charityClaim->id,
            'body' => $request->body,
            'created_by' => auth()->id(),
        ]);

        ActivityLogger::log('Charity Claim Note Added', 'CharityClaim', $charityClaim->id, 'Note added', null, ['body_length' => strlen($request->body)]);

        return redirect()->route('charity-claims.show', $charityClaim)->withFragment('notes')->with('success', app()->getLocale() === 'ar' ? 'تمت إضافة الملاحظة' : 'Note added');
    }

    /**
     * Preview claim as it will appear when sent to the charity (same layout as official document).
     */
    public function preview(CharityClaim $charityClaim)
    {
        $this->authorize('claims.view');

        $charityClaim->load(['invoice.patient', 'invoice.items.service', 'charityEntity']);

        return view('charity-claims.preview', compact('charityClaim'));
    }

    /**
     * Send claim to charity entity
     */
    public function send(CharityClaim $charityClaim)
    {
        $this->authorize('claims.view');

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
        $this->authorize('claims.view');

        $request->validate([
            'status' => 'required|in:under_review,approved,rejected,paid',
            'approved_amount' => 'nullable|numeric|min:0',
            'entity_response_notes' => 'nullable|string|max:1000',
            'approval_document' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        // Handle Approval Document upload if status is approved or under_review
        if ($request->hasFile('approval_document') && $charityClaim->invoice?->patient) {
            $charityClaim->invoice->patient->addMedia($request->file('approval_document'))
                ->toMediaCollection('charity-approvals');
        }

        $oldStatus = $charityClaim->status;

        if ($request->filled('entity_response_notes')) {
            CharityClaimNote::create([
                'charity_claim_id' => $charityClaim->id,
                'body' => $request->entity_response_notes,
                'created_by' => auth()->id(),
            ]);
        }

        switch ($request->status) {
            case 'under_review':
                $charityClaim->markAsUnderReview($request->entity_response_notes);
                break;
            case 'approved':
                $charityClaim->markAsApproved($request->approved_amount, $request->entity_response_notes);
                break;
            case 'rejected':
                $charityClaim->markAsRejected($request->entity_response_notes);
                break;
            case 'paid':
                $charityClaim->markAsPaid();

                // Notify Accountants of the new revenue
                $accountants = \App\Models\User::role('accountant')->get();
                if ($accountants->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send($accountants, new \App\Notifications\SystemNotification([
                        'title' => app()->getLocale() === 'ar' ? 'تم تحصيل مطالبة جمعية' : 'Charity Claim Paid',
                        'message' => (app()->getLocale() === 'ar' ? "تم سداد مطالبة بمبلغ " : "Claim paid with amount: ") . number_format($charityClaim->approved_amount ?: $charityClaim->invoice->remaining_amount, 2) . (app()->getLocale() === 'ar' ? " للمريض: " : " for patient: ") . $charityClaim->invoice->patient->fullArabicName(),
                        'action_url' => route('charity-claims.show', $charityClaim),
                        'type' => 'success',
                    ]));
                }
                break;
        }

        ActivityLogger::log('Charity Claim Status Updated', 'CharityClaim', $charityClaim->id, 'Status changed',
            ['old_status' => $oldStatus], ['new_status' => $request->status]);

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديث حالة المطالبة' : 'Claim status updated');
    }
}
