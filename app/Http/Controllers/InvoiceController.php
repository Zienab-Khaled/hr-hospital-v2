<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePartySend;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\Service;
use App\Models\Visit;
use App\Models\Approval;
use App\Models\Attachment;
use App\Models\InsuranceCompany;
use App\Models\CharityEntity;
use App\Mail\ApprovalRequestMail;
use App\Mail\InvoiceToPartyMail;
use App\Helpers\ActivityLogger;
use App\Helpers\CurrencyHelper;
use App\Helpers\InvoiceAmountHelper;
use App\Models\User;
use App\Support\RoleNav;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;
use Mpdf\Mpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    protected function authorizeInvoiceEdit(): void
    {
        if (! RoleNav::canEditInvoices(auth()->user())) {
            abort(403, app()->getLocale() === 'ar'
                ? 'المحاسب وأمين الصندوق لا يمكنهم تعديل الفاتورة.'
                : 'Accountants and cashiers cannot edit invoices.');
        }
    }

    public function create(Request $request)
    {
        $this->authorize('invoices.create');
        abort_unless(\App\Support\RoleNav::canCreateInvoiceWithServices(auth()->user()), 403);

        $patient = null;
        $visit = null;
        $patientId = $request->get('patient_id') ?? old('patient_id');
        $visitId = $request->get('visit_id') ?? old('visit_id');
        if ($patientId) {
            $patient = Patient::with(['insuranceCompany', 'charityEntity'])->find($patientId);
            if ($patient && $visitId) {
                $visit = Visit::where('patient_id', $patient->id)->find($visitId);
            }
        }

        $patients = Patient::where('is_active', true)->orderBy('name');
        if (auth()->user()->hasRole('insurance_clerk')) {
            $patients = $patients->where('payment_type', 'insurance');
        }
        $patients = $patients->get();
        $insuranceCompanies = InsuranceCompany::orderBy('name')->get();
        $charityEntities = CharityEntity::orderByRaw('COALESCE(name_ar, name)')->get();

        return view('invoices.create', compact('patients', 'patient', 'visit', 'insuranceCompanies', 'charityEntities'));
    }

    /** بحث المرضى للفاتورة: اسم / رقم هوية / رقم فيزا / رقم ملف / هاتف */
    public function searchPatients(Request $request)
    {
        $this->authorize('invoices.create');

        $q = $request->get('q', '');
        $q = trim($q);
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $patients = Patient::where('is_active', true)
            ->when(auth()->user()->hasRole('insurance_clerk'), fn ($q) => $q->where('payment_type', 'insurance'))
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('name_ar', 'like', "%{$q}%")
                    ->orWhere('name_ar_first', 'like', "%{$q}%")
                    ->orWhere('name_ar_father', 'like', "%{$q}%")
                    ->orWhere('name_ar_family', 'like', "%{$q}%")
                    ->orWhere('file_number', 'like', "%{$q}%")
                    ->orWhere('identity_value', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('sponsor_name', 'like', "%{$q}%")
                    ->orWhere('sponsor_phone', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'name_ar' => $p->fullArabicName(),
                    'file_number' => $p->file_number,
                    'identity_value' => $p->identity_value,
                    'phone' => $p->phone,
                    'payment_type' => $p->payment_type,
                ];
            });

        return response()->json($patients);
    }

    /** بحث الخدمات بالاسم أو الكود (لصفحة إنشاء الفاتورة بدون تحميل الكل). */
    public function searchServices(Request $request)
    {
        $this->authorize('invoices.create');

        $q = $request->get('q', '');
        $q = trim($q);
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $services = Service::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('name_ar', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'name_ar' => $s->name_ar,
                    'code' => $s->code,
                    'default_price' => (float) $s->default_price,
                    'is_multi_session' => (bool) $s->is_multi_session,
                    'session_count' => (int) ($s->session_count ?: 1),
                ];
            });

        return response()->json($services);
    }

    public function store(Request $request)
    {
        $this->authorize('invoices.create');
        abort_unless(\App\Support\RoleNav::canCreateInvoiceWithServices(auth()->user()), 403);

        Log::info('Invoice store request', [
            'has_patient_id' => $request->has('patient_id'),
            'patient_id' => $request->input('patient_id'),
            'services_count' => is_array($request->input('services')) ? count($request->input('services')) : 0,
            'services_keys' => is_array($request->input('services')) ? array_keys($request->input('services')) : [],
        ]);

        try {
            $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'referral_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|numeric|min:1',
            'services.*.unit_price' => 'required|numeric|min:0',
            'services.*.total_price' => 'required|numeric|min:0',
            'services.*.description' => 'nullable|string',
            'services.*.insurance_coverage_type' => 'nullable|string|in:percentage,fixed',
            'services.*.insurance_coverage_value' => 'nullable|numeric|min:0',
            // Optional patient updates from invoice form
            'patient_name' => 'nullable|string|max:255',
            'patient_name_ar' => 'nullable|string|max:255',
            'patient_file_number' => 'nullable|string|max:100',
            'patient_identity_type' => 'nullable|string|max:50',
            'patient_identity_value' => 'nullable|string|max:100',
            'patient_phone' => 'nullable|string|max:50',
            'patient_date_of_birth' => 'nullable|date|before_or_equal:today|after:1900-01-01',
            'patient_gender' => 'nullable|string|max:20',
            'patient_country_of_origin' => 'nullable|string|max:100',
            'patient_sponsor_name' => 'nullable|string|max:255',
            'patient_sponsor_phone' => 'nullable|string|max:50',
            'patient_payment_type' => 'nullable|string|in:' . implode(',', Patient::paymentTypeKeys()),
            'patient_insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'patient_charity_entity_id' => 'nullable|exists:charity_entities,id',
            'print_media_ids' => 'nullable|array',
            'print_media_ids.*' => 'integer',
            'medical_reports' => 'nullable|array',
            'medical_reports.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'visit_id' => 'nullable|exists:visits,id',
            // علاج أهلي: مجاني على المريض + أحقية علاج أم لا (يظهر فقط لمرضى الجمعية في النموذج)
            'charity_treatment_invoice_mode' => 'nullable|string|in:,eligibility,no_eligibility',
            // Collection Fields
            'collection_amount' => 'nullable|numeric|min:0',
            'collection_method' => 'nullable|string|in:cash,card,bank_transfer,cheque',
            'collection_reference' => 'nullable|string|max:100',
            'ministry_receipt_number' => 'nullable|string|max:50',
            'physical_receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'collector_screenshot' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $patient = Patient::findOrFail($validated['patient_id']);

            // Update patient if form sent editable fields (including payment type)
            $identityType = $validated['patient_identity_type'] ?? null;
            $gender = $validated['patient_gender'] ?? null;
            $paymentType = $validated['patient_payment_type'] ?? null;
            $patientUpdates = array_filter([
                'name' => $validated['patient_name'] ?? null,
                'file_number' => $validated['patient_file_number'] ?? null,
                'identity_type' => $identityType && array_key_exists($identityType, Patient::identityTypeOptions()) ? $identityType : null,
                'identity_value' => $validated['patient_identity_value'] ?? null,
                'phone' => $validated['patient_phone'] ?? null,
                'date_of_birth' => ! empty($validated['patient_date_of_birth'] ?? null) ? $validated['patient_date_of_birth'] : null,
                'gender' => in_array($gender, ['male', 'female'], true) ? $gender : null,
                'country_of_origin' => $validated['patient_country_of_origin'] ?? null,
                'sponsor_name' => $validated['patient_sponsor_name'] ?? null,
                'sponsor_phone' => $validated['patient_sponsor_phone'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');
            if (array_key_exists('patient_name_ar', $validated) && is_string($validated['patient_name_ar']) && trim($validated['patient_name_ar']) !== '') {
                $patientUpdates['name_ar'] = trim($validated['patient_name_ar']);
                $patientUpdates['name_ar_first'] = null;
                $patientUpdates['name_ar_father'] = null;
                $patientUpdates['name_ar_family'] = null;
            }
            if (in_array($paymentType, Patient::paymentTypeKeys(), true)) {
                $patientUpdates['payment_type'] = $paymentType;
                $patientUpdates['insurance_company_id'] = $paymentType === 'insurance' ? ($validated['patient_insurance_company_id'] ?? null) : null;
                $patientUpdates['charity_entity_id'] = $paymentType === 'charity' ? ($validated['patient_charity_entity_id'] ?? null) : null;
            }
            if (! empty($patientUpdates)) {
                $patient->update($patientUpdates);
            }
            $patient->refresh();

            $finalPaymentType = $patient->payment_type;
            $charityMode = trim((string) ($validated['charity_treatment_invoice_mode'] ?? ''));
            if ($charityMode !== '' && $finalPaymentType !== 'charity') {
                throw ValidationException::withMessages([
                    'charity_treatment_invoice_mode' => app()->getLocale() === 'ar'
                        ? 'خيار «علاج أهلي / أحقية» يخص مرضى الجمعية فقط.'
                        : 'Charity treatment / eligibility options apply only to charity patients.',
                ]);
            }
            $forceCharityPatientFree = $finalPaymentType === 'charity' && in_array($charityMode, ['eligibility', 'no_eligibility'], true);
            $isTreatmentEligibilityFree = Patient::isTreatmentEligibility($finalPaymentType);
            $invoiceType = 'regular';
            if ($forceCharityPatientFree) {
                $invoiceType = $charityMode === 'eligibility' ? 'eligibility' : 'charity_treatment_free';
            } elseif ($isTreatmentEligibilityFree) {
                $invoiceType = 'eligibility';
            }

            // Use existing visit (e.g. from "create visit" flow) or create one
            $visitId = isset($validated['visit_id']) ? (int) $validated['visit_id'] : null;
            $visit = null;
            if ($visitId) {
                $visit = Visit::where('patient_id', $patient->id)->find($visitId);
            }
            if (!$visit) {
                $visit = Visit::create([
                    'patient_id' => $patient->id,
                    'visit_date' => $validated['invoice_date'],
                    'notes' => 'Invoice created',
                    'referral_number' => $validated['referral_number'] ?? null,
                    'registered_by' => auth()->user()?->getKey(),
                ]);
            }

            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(Invoice::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total amount
            $totalAmount = $isTreatmentEligibilityFree
                ? 0.0
                : collect($validated['services'])->sum('total_price');

            // Only allow print_media_ids that belong to this patient's documents/medical-reports
            $patientMediaIds = $patient->getMedia('documents')->merge($patient->getMedia('medical-reports'))->pluck('id')->all();
            $printMediaIds = isset($validated['print_media_ids']) ? array_values(array_intersect(array_map('intval', $validated['print_media_ids']), $patientMediaIds)) : null;

            // Create invoice
            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                'visit_id' => $visit->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['invoice_date'],
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'remaining_amount' => $totalAmount,
                'deposit_amount' => 0,
                'status' => $isTreatmentEligibilityFree ? 'paid' : 'pending',
                'notes' => $validated['notes'] ?: ($isTreatmentEligibilityFree
                    ? (app()->getLocale() === 'ar' ? 'أحقية العلاج' : 'Treatment Eligibility')
                    : null),
                'print_media_ids' => $printMediaIds ?: null,
                'payment_type' => $finalPaymentType,
                'invoice_type' => $invoiceType,
                'audit_status' => 'under_review',
            ]);

            // Create invoice items (quantity: ensure integer for DB; optional insurance coverage per line)
            foreach ($validated['services'] as $serviceData) {
                if ($forceCharityPatientFree || $isTreatmentEligibilityFree) {
                    $coverageType = 'percentage';
                    $coverageValue = 100.0;
                } else {
                    $coverageType = isset($serviceData['insurance_coverage_type']) && in_array($serviceData['insurance_coverage_type'], ['percentage', 'fixed'], true)
                        ? $serviceData['insurance_coverage_type'] : null;
                    $coverageValue = isset($serviceData['insurance_coverage_value']) && $serviceData['insurance_coverage_value'] !== ''
                        ? (float) $serviceData['insurance_coverage_value'] : null;
                }
                $lineTotal = $isTreatmentEligibilityFree ? 0.0 : (float) $serviceData['total_price'];
                $lineUnit = $isTreatmentEligibilityFree ? 0.0 : (float) $serviceData['unit_price'];
                $invoice->items()->create([
                    'service_id' => (int) $serviceData['service_id'],
                    'quantity' => (int) round((float) $serviceData['quantity']),
                    'unit_price' => $lineUnit,
                    'total_price' => $lineTotal,
                    'description' => isset($serviceData['description']) && $serviceData['description'] !== '' ? (string) $serviceData['description'] : null,
                    'insurance_coverage_type' => $coverageType,
                    'insurance_coverage_value' => $coverageValue,
                ]);
            }

            InvoiceAmountHelper::syncInvoiceTotalsFromItems($invoice);
            if ($isTreatmentEligibilityFree) {
                InvoiceAmountHelper::applyTreatmentEligibilityZeroInvoice($invoice->refresh());
            }
            $invoice->refresh();

            // Attach medical report(s) if uploaded
            if ($request->hasFile('medical_reports')) {
                foreach ($request->file('medical_reports') as $file) {
                    $path = $file->store('invoice-reports', 'public');
                    $invoice->attachments()->create([
                        'document_type' => 'medical_report',
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_by' => auth()->user()?->getKey(),
                    ]);
                }
            }

            // NEW: Handle Financial Collection & Digital q-1 Documents
            if ($isTreatmentEligibilityFree && ! empty($validated['collection_method']) && ($validated['collection_amount'] ?? 0) > 0) {
                throw ValidationException::withMessages([
                    'collection_amount' => app()->getLocale() === 'ar'
                        ? 'مرضى أحقية العلاج لا يدفعون — لا يمكن تسجيل تحصيل نقدي.'
                        : 'Treatment eligibility patients do not pay — cash collection is not allowed.',
                ]);
            }
            if (!empty($validated['collection_method']) && ($validated['collection_amount'] ?? 0) > 0) {
                $collectionAmt = (float) $validated['collection_amount'];
                if (($validated['collection_method'] ?? '') === 'cash' && ($invoice->payment_type ?? '') === 'cash') {
                    $collectionAmt = InvoiceAmountHelper::roundCashRiyalToWhole($collectionAmt);
                }
                if ($collectionAmt > (float) $invoice->total_amount + 0.009) {
                    throw ValidationException::withMessages([
                        'collection_amount' => app()->getLocale() === 'ar'
                            ? 'مبلغ التحصيل يتجاوز إجمالي الفاتورة (بعد تقريب النقد إن وُجد).'
                            : 'Collection amount exceeds invoice total (after any cash rounding).',
                    ]);
                }

                $invoice->load('items.service');

                // 1. Create Payment record
                $payment = \App\Models\Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_type' => $invoice->payment_type,
                    'amount' => $collectionAmt,
                    'received_date' => now(),
                    'received_by' => auth()->user()->id,
                    'approved_by' => auth()->user()->id, // Auto-approve immediate collection
                    'approved_at' => now(),
                    'reference_no' => $validated['collection_reference'] ?? null,
                    'status' => 'approved',
                    'audit_status' => 'matched',
                    'notes' => $validated['notes'] ?? 'Immediate collection upon creation',
                ]);

                // Prepare selected items data for receipt snapshot (Digital q-1) — من البنود بعد المزامنة/التقريب
                $selectedItemsData = [];
                foreach ($invoice->items as $itemRow) {
                    $lineName = $itemRow->service?->name_ar
                        ?? $itemRow->service?->name
                        ?? (trim((string) ($itemRow->description ?? '')) !== '' ? trim((string) $itemRow->description) : null)
                        ?? '—';
                    $selectedItemsData[] = [
                        'id' => $itemRow->id,
                        'code' => $itemRow->service?->code ?? '—',
                        'name' => $lineName,
                        'qty' => (int) $itemRow->quantity,
                        'unit_price' => (float) $itemRow->unit_price,
                        'total' => (float) $itemRow->total_price,
                    ];
                }

                // 2. Create Payment Receipt (Digital q-1)
                $receipt = \App\Models\PaymentReceipt::create([
                    'payment_id' => $payment->id,
                    'patient_id' => $invoice->patient_id,
                    'ministry_receipt_number' => $validated['ministry_receipt_number'] ?? null,
                    'amount' => $collectionAmt,
                    'payment_method' => $validated['collection_method'],
                    'reference_number' => $validated['collection_reference'] ?? null,
                    'invoice_snapshot_total' => $invoice->total_amount,
                    'invoice_snapshot_paid' => $invoice->paid_amount + $collectionAmt,
                    'invoice_snapshot_remaining' => $invoice->total_amount - ($invoice->paid_amount + $collectionAmt),
                    'collected_by' => auth()->user()->id,
                    'collected_at' => now(),
                    'notes' => $validated['notes'],
                    'selected_items' => $selectedItemsData,
                ]);

                \App\Models\PaymentReceiptSplit::create([
                    'payment_receipt_id' => $receipt->id,
                    'payment_method' => $validated['collection_method'],
                    'amount' => $collectionAmt,
                    'reference_number' => $validated['collection_reference'] ?? null,
                    'sort_order' => 0,
                ]);

                // 3. Attach Proof Documents (Spatie Media Library)
                if ($request->hasFile('physical_receipt')) {
                    $receipt->addMediaFromRequest('physical_receipt')->toMediaCollection('physical_receipt');
                }
                if ($request->hasFile('collector_screenshot')) {
                    $receipt->addMediaFromRequest('collector_screenshot')->toMediaCollection('collector_screenshot');
                }

                // 4. Update Invoice Totals and Status
                $newPaidAmount = $invoice->paid_amount + $collectionAmt;
                $newRemainingAmount = $invoice->total_amount - $newPaidAmount;

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'remaining_amount' => $newRemainingAmount,
                    'status' => $newRemainingAmount <= 0 ? 'paid' : 'pending',
                    'audit_status' => 'under_review',
                    'debt_status' => $newRemainingAmount <= 0 ? 'paid' : $invoice->debt_status,
                ]);

                $invoice->refresh(); // Sync the model state
            }

            // Create approval request for insurance/charity patients (email sent after HTTP response — avoids SMTP hang/timeouts blocking the request)
            $deferredApprovalMail = null;
            if (in_array($patient->payment_type, ['insurance', 'charity'])) {
                $approval = Approval::create([
                    'invoice_id' => $invoice->id,
                    'patient_id' => $patient->id,
                    'approval_type' => $patient->payment_type,
                    'insurance_company_id' => $patient->insurance_company_id,
                    'charity_entity_id' => $patient->charity_entity_id,
                    'requested_amount' => (float) $invoice->total_amount,
                    'status' => 'pending',
                    'requested_by' => auth()->user()?->getKey(),
                ]);

                $recipientEmail = null;
                if ($patient->payment_type === 'insurance' && $patient->insuranceCompany) {
                    $recipientEmail = $patient->insuranceCompany->email;
                } elseif ($patient->payment_type === 'charity' && $patient->charityEntity) {
                    $recipientEmail = $patient->charityEntity->email;
                }

                if ($recipientEmail) {
                    $deferredApprovalMail = ['approval_id' => $approval->id, 'to' => $recipientEmail];
                }
            }

            DB::commit();

            if ($deferredApprovalMail !== null) {
                $approvalId = $deferredApprovalMail['approval_id'];
                $mailTo = $deferredApprovalMail['to'];
                dispatch(function () use ($approvalId, $mailTo) {
                    $approval = Approval::query()->find($approvalId);
                    if (!$approval || $mailTo === '') {
                        return;
                    }
                    try {
                        Mail::to($mailTo)->send(new ApprovalRequestMail($approval));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send approval email (after response): ' . $e->getMessage(), [
                            'approval_id' => $approvalId,
                            'exception' => $e,
                        ]);
                    }
                })->afterResponse();
            }

            $message = in_array($patient->payment_type, ['insurance', 'charity'])
                ? __('Invoice created and approval request sent successfully.')
                : __('Invoice created successfully.');

            ActivityLogger::log('Invoice Created', 'Invoice', $invoice->id, 'Invoice created with ' . count($validated['services']) . ' items', null, $invoice->toArray());

            // System Notification for Managers and Accountants
            $notifyUsers = User::role(['manager', 'admin', 'accountant'])->get();
            if ($notifyUsers->isNotEmpty()) {
                Notification::send($notifyUsers, new SystemNotification([
                    'title' => app()->getLocale() === 'ar' ? 'فاتورة جديدة' : 'New Invoice Created',
                    'message' => (app()->getLocale() === 'ar' ? "تم إصدار فاتورة بمبلغ " : "Invoice created with amount: ") . CurrencyHelper::formatAmountDecimal($invoice->total_amount) . (app()->getLocale() === 'ar' ? " للمريض: " : " for patient: ") . $patient->fullArabicName(),
                    'action_url' => route('invoices.show', $invoice),
                    'type' => 'success',
                ]));
            }

            return redirect()->route('invoices.show', $invoice)->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice store exception: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Error creating invoice: ' . $e->getMessage()])->withInput();
        }
        } catch (ValidationException $e) {
            Log::warning('Invoice store validation failed', ['errors' => $e->errors()]);
            throw $e;
        }
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('invoices.view');
        if (auth()->user()->hasRole('insurance_clerk') && $invoice->patient && $invoice->patient->payment_type !== 'insurance') {
            abort(403);
        }

        $invoice->load([
            'patient.insuranceCompany',
            'patient.charityEntity',
            'items.service',
            'items.completedByUser',
            'payments.receipt.splits',
            'visit.registeredBy',
            'partySends'
        ]);

        $printMedia = collect();
        if ($invoice->patient && !empty($invoice->print_media_ids)) {
            $ids = array_map('intval', (array) $invoice->print_media_ids);
            $printMedia = $invoice->patient->getMedia('documents')
                ->merge($invoice->patient->getMedia('medical-reports'))
                ->whereIn('id', $ids)->values();
        }

        return view('invoices.show', compact('invoice', 'printMedia'));
    }

    /** طباعة محضر تعهد مرتبط بالفاتورة */
    public function printCommitmentForm(Invoice $invoice)
    {
        $this->authorize('invoices.view');
        $invoice->load(['patient.insuranceCompany', 'patient.charityEntity', 'items.service']);
        $settings = \App\Models\Setting::first();
        $manager = \App\Models\User::getManagerForSignature();

        // Update tracking flag
        $invoice->update(['printed_commitment_at' => now()]);

        // Log the action
        ActivityLogger::log('Print Commitment', 'Invoice', $invoice->id, 'Commitment form printed for invoice: ' . $invoice->invoice_number, null, null);

        return view('invoices.print-commitment', compact('invoice', 'settings', 'manager'));
    }

    /** طباعة محضر إقرار بعدم التوقيع مرتبط بالفاتورة */
    public function printNonCommitmentForm(Invoice $invoice)
    {
        $this->authorize('invoices.view');
        $invoice->load(['patient.insuranceCompany', 'patient.charityEntity', 'items.service']);
        $settings = \App\Models\Setting::first();
        $manager = \App\Models\User::getManagerForSignature();

        // Update tracking flag
        $invoice->update(['printed_non_commitment_at' => now()]);

        // Log the action
        ActivityLogger::log('Print Non-Commitment', 'Invoice', $invoice->id, 'Non-commitment form printed for invoice: ' . $invoice->invoice_number, null, null);

        return view('invoices.print-non-commitment', compact('invoice', 'settings', 'manager'));
    }

    /** طباعة الفاتورة التفصيلية */
    public function printInvoice(Invoice $invoice)
    {
        $this->authorize('invoices.view');
        $invoice->load(['patient.insuranceCompany', 'patient.charityEntity', 'items.service', 'visit.department']);

        $visit = $invoice->visit;
        if ($visit) {
            $visit->load(['patient.insuranceCompany', 'department', 'shift']);
            if (! $visit->department) {
                $invoice->patient?->loadMissing('department');
                if ($invoice->patient?->department) {
                    $visit->setRelation('department', $invoice->patient->department);
                }
            }
        } else {
            $invoice->patient?->load(['insuranceCompany', 'department']);
            $visit = new Visit([
                'patient_id' => $invoice->patient_id,
                'department_id' => $invoice->patient?->department_id,
                'visit_date' => $invoice->invoice_date ?? now(),
            ]);
            $visit->setRelation('patient', $invoice->patient);
            $visit->setRelation('department', $invoice->patient?->department);
        }

        $services = $invoice->items->map(function ($item) {
            $name = $item->description
                ?: (app()->getLocale() === 'ar' ? ($item->service?->name_ar ?? $item->service?->name) : ($item->service?->name ?? $item->service?->name_ar))
                ?: '—';

            return [
                'code' => $item->service?->code ?? '',
                'name' => $name,
                'qty' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total_price,
                'insurance_coverage_type' => $item->insurance_coverage_type ?? '',
                'insurance_coverage_value' => $item->insurance_coverage_value ?? 0,
            ];
        })->values()->all();

        $manager = User::getManagerForSignature();
        $printTitle = 'detailed_invoice';

        ActivityLogger::log('Print Invoice', 'Invoice', $invoice->id, 'Detailed invoice printed: '.$invoice->invoice_number, null, null);

        return view('visits.price-inquiry-print', compact('visit', 'services', 'manager', 'printTitle', 'invoice'));
    }

    /** إرسال الفاتورة لشركة التأمين / الجمعية الخيرية */
    public function sendToParty(Invoice $invoice)
    {
        $this->authorize('invoices.view');
        $invoice->load(['patient.insuranceCompany', 'patient.charityEntity', 'items.service']);
        return view('invoices.send-to-party', compact('invoice'));
    }

    /** إرسال بريد احترافي مع مرفق عرض السعر وزرّي تأكيد/رفض */
    public function sendToPartySubmit(Request $request, Invoice $invoice)
    {
        $this->authorize('invoices.view');
        $invoice->load(['patient.insuranceCompany', 'patient.charityEntity', 'visit', 'items.service']);

        $request->validate(['recipient_email' => 'required|email']);

        $patient = $invoice->patient;
        if (! $patient || ! in_array($patient->payment_type, ['insurance', 'charity'])) {
            return back()->withErrors(['recipient_email' => __('Invoice is not linked to insurance or charity patient.')]);
        }

        $party = $patient->payment_type === 'insurance' ? $patient->insuranceCompany : $patient->charityEntity;
        if (! $party) {
            return back()->withErrors(['recipient_email' => __('Insurance/charity entity not found.')]);
        }

        $recipientName = app()->getLocale() === 'ar' && ! empty($party->name_ar) ? $party->name_ar : $party->name;
        $token = InvoicePartySend::generateToken();

        $partySend = InvoicePartySend::create([
            'invoice_id' => $invoice->id,
            'recipient_type' => $patient->payment_type,
            'recipient_entity_id' => $party->id,
            'recipient_email' => $request->input('recipient_email'),
            'recipient_name' => $recipientName,
            'token' => $token,
            'sent_at' => now(),
            'sent_by' => auth()->user()?->getKey(),
        ]);

        $settings = [
            'hospital_name' => Setting::get('hospital_name', ''),
            'hospital_name_en' => Setting::get('hospital_name_en', ''),
            'health_cluster_name' => Setting::get('health_cluster_name', ''),
            'health_cluster_name_en' => Setting::get('health_cluster_name_en', ''),
            'manager_name' => Setting::get('manager_name', ''),
            'logo' => Setting::get('logo', ''),
            'bank_name' => Setting::get('bank_name', ''),
            'account_number' => Setting::get('account_number', ''),
            'iban_number' => Setting::get('iban_number', ''),
            'manager_signature' => Setting::get('manager_signature', ''),
            'department_manager_name' => Setting::get('department_manager_name', ''),
            'department_manager_signature' => Setting::get('department_manager_signature', ''),
        ];

        $pdfDir = 'invoice-party-pdfs';
        $pdfFilename = $token . '.pdf';
        $pdfRelativePath = $pdfDir . '/' . $pdfFilename;

        try {
            $attachmentPaths = $invoice->getAttachmentPathsForPdf();
            $html = view('invoices.price-offer-pdf', [
                'invoice' => $invoice,
                'recipientName' => $recipientName,
                'settings' => $settings,
                'attachmentPaths' => $attachmentPaths,
            ])->render();

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font_size' => 11,
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 16,
                'margin_bottom' => 16,
            ]);
            $mpdf->SetDirectionality('rtl');
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');
            Storage::disk('local')->put($pdfRelativePath, $pdfContent);

            Mail::to($partySend->recipient_email)->send(new InvoiceToPartyMail($partySend, $pdfRelativePath));

            $invoice->update([
                'status' => $patient->payment_type === 'insurance' ? 'sent_to_insurance' : 'sent_to_charity',
            ]);

            // Auto-create CharityClaim when sending to charity
            if ($patient->payment_type === 'charity' && $patient->charity_entity_id) {
                \App\Models\CharityClaim::updateOrCreate(
                    ['invoice_id' => $invoice->id],
                    [
                        'charity_entity_id' => $patient->charity_entity_id,
                        'status'            => 'sent',
                        'sent_date'         => today(),
                        'sent_by'           => auth()->user()?->id,
                    ]
                );
            }

            ActivityLogger::log('Invoice Sent', 'Invoice', $invoice->id, 'Invoice sent to ' . $partySend->recipient_name, null, $partySend->toArray());

            Storage::disk('local')->delete($pdfRelativePath);
        } catch (\Throwable $e) {
            Log::error('Invoice send to party failed: ' . $e->getMessage(), ['invoice_id' => $invoice->id]);
            if (Storage::disk('local')->exists($pdfRelativePath)) {
                Storage::disk('local')->delete($pdfRelativePath);
            }
            return back()->withErrors(['recipient_email' => __('Failed to send email. Please try again.')]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', __('Professional email with price offer has been sent. The recipient can confirm or reject with written response.'));
    }

    /**
     * صفحة تعديل الإيميل ثم الإرسال للجمعية (موضوع، نص، مدة العلاج، مرفقات).
     */
    public function composeCharityEmail(Invoice $invoice)
    {
        $this->authorize('invoices.view');
        $invoice->load(['patient.charityEntity', 'items.service']);

        $patient = $invoice->patient;
        if (! $patient || $patient->payment_type !== 'charity' || ! $patient->charityEntity) {
            return redirect()->route('invoices.show', $invoice)->withErrors([
                'error' => app()->getLocale() === 'ar' ? 'الفاتورة غير مرتبطة بمريض جمعية.' : 'Invoice is not linked to a charity patient.',
            ]);
        }
        if (! $patient->charityEntity->email) {
            return redirect()->route('invoices.show', $invoice)->withErrors([
                'error' => app()->getLocale() === 'ar' ? 'لا يوجد بريد إلكتروني مسجل للجمعية.' : 'No email registered for the charity.',
            ]);
        }

        $bankName = Setting::get('bank_name', 'البنك');
        $accountNumber = Setting::get('account_number', '');
        $ibanNumber = Setting::get('iban_number', '');
        $patientName = $patient->fullArabicName() ?: '—';
        $patientIdentity = $patient->identity_value ?: $patient->file_number ?? '—';
        $defaultIntro = 'تجدون أدناه عرض سعر للخدمات العلاجية المطلوبة للمريضة / ' . $patientName . ' رقم الجواز (' . $patientIdentity . ') ونفيد سعادتكم بانه تم ارفاق التقرير الطبي وفي حال السداد نأمل تحويل المبلغ على الحساب في ' . $bankName . ' (' . $accountNumber . ') رقم الأيبان ' . $ibanNumber;
        $defaultSubject = (app()->getLocale() === 'ar' ? 'عرض سعر / فاتورة ' : 'Price offer / Invoice ') . $invoice->invoice_number;

        return view('invoices.charity-email-compose', compact('invoice', 'defaultSubject', 'defaultIntro'));
    }

    /**
     * معاينة محتوى الإيميل الذي سيُرسل للجمعية قبل الإرسال (نفس شكل الإيميل الفعلي).
     */
    public function previewCharityEmail(Invoice $invoice)
    {
        $this->authorize('invoices.view');
        $invoice->load(['patient.charityEntity', 'items.service']);

        $patient = $invoice->patient;
        if (! $patient || $patient->payment_type !== 'charity' || ! $patient->charityEntity) {
            return redirect()->route('invoices.show', $invoice)->withErrors([
                'error' => app()->getLocale() === 'ar' ? 'الفاتورة غير مرتبطة بمريض جمعية.' : 'Invoice is not linked to a charity patient.',
            ]);
        }

        $partySend = new InvoicePartySend;
        $partySend->invoice_id = $invoice->id;
        $partySend->recipient_name = app()->getLocale() === 'ar' && ! empty($patient->charityEntity->name_ar)
            ? $patient->charityEntity->name_ar
            : $patient->charityEntity->name;
        $partySend->setRelation('invoice', $invoice);

        $settings = [
            'hospital_name' => Setting::get('hospital_name', ''),
            'hospital_name_en' => Setting::get('hospital_name_en', ''),
            'health_cluster_name' => Setting::get('health_cluster_name', ''),
            'health_cluster_name_en' => Setting::get('health_cluster_name_en', ''),
            'manager_name' => Setting::get('manager_name', ''),
            'logo' => Setting::get('logo', ''),
            'bank_name' => Setting::get('bank_name', ''),
            'account_number' => Setting::get('account_number', ''),
            'iban_number' => Setting::get('iban_number', ''),
            'manager_signature' => Setting::get('manager_signature', ''),
            'department_manager_name' => Setting::get('department_manager_name', ''),
            'department_manager_signature' => Setting::get('department_manager_signature', ''),
        ];

        $confirmUrl = '#';
        $rejectUrl = '#';

        return response()->view('emails.invoice-to-party', compact('partySend', 'settings', 'confirmUrl', 'rejectUrl'))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * إرسال عرض سعر / فاتورة طبية للجمعية (يدعم التعديل والمرفقات من صفحة التحرير).
     */
    public function sendCharityPriceOffer(Request $request, Invoice $invoice)
    {
        $this->authorize('invoices.view');
        $invoice->load(['patient.charityEntity', 'visit', 'items.service']);

        $patient = $invoice->patient;
        if (! $patient || $patient->payment_type !== 'charity') {
            return back()->withErrors(['error' => __('Invoice is not linked to a charity patient.')]);
        }

        $charityEntity = $patient->charityEntity;
        if (! $charityEntity || ! $charityEntity->email) {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'لا يوجد بريد إلكتروني مسجل للجمعية. استخدم "إرسال لطرف" لإدخال بريد يدوياً.' : 'No email registered for the charity. Use "Send to party" to enter an email manually.']);
        }

        $request->validate([
            'subject' => 'nullable|string|max:255',
            'custom_intro' => 'nullable|string|max:4000',
            'treatment_duration' => 'nullable|string|max:500',
            'attachments.*' => 'nullable|file|max:20480',
        ]);

        $recipientName = app()->getLocale() === 'ar' && ! empty($charityEntity->name_ar) ? $charityEntity->name_ar : $charityEntity->name;
        $token = InvoicePartySend::generateToken();
        $partySend = InvoicePartySend::create([
            'invoice_id' => $invoice->id,
            'recipient_type' => 'charity',
            'recipient_entity_id' => $charityEntity->id,
            'recipient_email' => $charityEntity->email,
            'recipient_name' => $recipientName,
            'token' => $token,
            'sent_at' => now(),
            'sent_by' => auth()->user()?->getKey(),
        ]);

        $settings = [
            'hospital_name' => Setting::get('hospital_name', ''),
            'hospital_name_en' => Setting::get('hospital_name_en', ''),
            'health_cluster_name' => Setting::get('health_cluster_name', ''),
            'health_cluster_name_en' => Setting::get('health_cluster_name_en', ''),
            'manager_name' => Setting::get('manager_name', ''),
            'logo' => Setting::get('logo', ''),
            'bank_name' => Setting::get('bank_name', ''),
            'account_number' => Setting::get('account_number', ''),
            'iban_number' => Setting::get('iban_number', ''),
            'manager_signature' => Setting::get('manager_signature', ''),
            'department_manager_name' => Setting::get('department_manager_name', ''),
            'department_manager_signature' => Setting::get('department_manager_signature', ''),
        ];

        $pdfDir = 'invoice-party-pdfs';
        $pdfFilename = $token . '.pdf';
        $pdfRelativePath = $pdfDir . '/' . $pdfFilename;
        $extraPaths = [];
        $attachDir = 'invoice-party-attachments/' . $token;

        try {
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if ($file->isValid()) {
                        $name = $file->getClientOriginalName() ?: ('attachment-' . uniqid());
                        $stored = $file->storeAs($attachDir, $name, 'local');
                        if ($stored) {
                            $extraPaths[] = $stored;
                        }
                    }
                }
            }

            $attachmentPaths = $invoice->getAttachmentPathsForPdf();
            $html = view('invoices.price-offer-pdf', [
                'invoice' => $invoice,
                'recipientName' => $recipientName,
                'settings' => $settings,
                'attachmentPaths' => $attachmentPaths,
            ])->render();
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font_size' => 11,
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 16,
                'margin_bottom' => 16,
            ]);
            $mpdf->SetDirectionality('rtl');
            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');
            Storage::disk('local')->put($pdfRelativePath, $pdfContent);

            $customSubject = $request->filled('subject') ? $request->input('subject') : null;
            $customIntro = $request->filled('custom_intro') ? $request->input('custom_intro') : null;
            $treatmentDuration = $request->filled('treatment_duration') ? $request->input('treatment_duration') : null;

            Mail::to($partySend->recipient_email)->send(new InvoiceToPartyMail(
                $partySend,
                $pdfRelativePath,
                $customSubject,
                $customIntro,
                $treatmentDuration,
                $extraPaths
            ));

            $invoice->update(['status' => 'sent_to_charity']);
            \App\Models\CharityClaim::updateOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'charity_entity_id' => $patient->charity_entity_id,
                    'status'            => 'sent',
                    'sent_date'         => today(),
                    'sent_by'           => auth()->user()?->id,
                ]
            );

            ActivityLogger::log('Invoice Sent', 'Invoice', $invoice->id, 'Charity price offer email sent to ' . $partySend->recipient_name, null, $partySend->toArray());

            Storage::disk('local')->delete($pdfRelativePath);
            foreach ($extraPaths as $p) {
                Storage::disk('local')->delete($p);
            }
        } catch (\Throwable $e) {
            Log::error('Charity price offer email failed: ' . $e->getMessage(), ['invoice_id' => $invoice->id]);
            if (Storage::disk('local')->exists($pdfRelativePath)) {
                Storage::disk('local')->delete($pdfRelativePath);
            }
            foreach ($extraPaths as $p) {
                if (Storage::disk('local')->exists($p)) {
                    Storage::disk('local')->delete($p);
                }
            }
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'فشل إرسال الإيميل. حاول مرة أخرى.' : 'Failed to send email. Please try again.']);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', app()->getLocale() === 'ar' ? 'تم إرسال عرض السعر / الفاتورة الطبية للجمعية بنجاح. يمكن للجمعية التأكيد أو الرفض عبر الرابط في الإيميل.' : 'Price offer / medical invoice sent to charity. They can confirm or reject via the email link.');
    }

    /** عرض نموذج تنفيذ الخدمة (GET) — نعيد توجيه لصفحة الفاتورة */
    public function showExecuteService(Invoice $invoice, \App\Models\InvoiceItem $item)
    {
        if (!auth()->user()->can('invoices.edit') && !auth()->user()->can('invoices.execute_services')) {
            abort(403);
        }
        return redirect()->route('invoices.show', $invoice);
    }

    /** تنفيذ الخدمة: تحديد تاريخ التنفيذ وتغيير الحالة إلى مكتملة (المحصل أو من لديه تعديل فواتير) */
    public function executeService(Request $request, Invoice $invoice, \App\Models\InvoiceItem $item)
    {
        if (!auth()->user()->can('invoices.edit') && !auth()->user()->can('invoices.execute_services')) {
            abort(403);
        }

        if ($item->invoice_id !== $invoice->id) {
            abort(404);
        }

        if (!$item->canBeCompleted()) {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'الخدمة منفذة بالفعل.' : 'Service already executed.']);
        }

        $request->validate([
            'execution_date' => 'required|date',
        ]);

        $item->markAsCompleted($request->input('execution_date'));

        ActivityLogger::log('Service Executed', 'InvoiceItem', $item->id, 'Service marked as executed on ' . $request->input('execution_date'), null, $item->toArray());

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تنفيذ الخدمة بنجاح.' : 'Service executed successfully.');
    }

    /** إرسال إيميل للجمعية بعد اكتمال تنفيذ جميع الخدمات */
    public function notifyCharityCompleted(Invoice $invoice)
    {
        $this->authorizeInvoiceEdit();

        $invoice->load(['patient.charityEntity', 'items.service', 'items.completedByUser']);

        // التحقق أن المريض من نوع جمعية
        if (!$invoice->patient || $invoice->patient->payment_type !== 'charity') {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'هذه الفاتورة ليست مرتبطة بمريض جمعية.' : 'This invoice is not linked to a charity patient.']);
        }

        // التحقق أن جميع الخدمات منفذة
        if (!$invoice->isFullyCompleted()) {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'لم يتم تنفيذ جميع الخدمات بعد.' : 'Not all services have been executed yet.']);
        }

        $charityEntity = $invoice->patient->charityEntity;
        if (!$charityEntity || !$charityEntity->email) {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'لا يوجد بريد إلكتروني مسجل للجمعية.' : 'No email registered for the charity entity.']);
        }

        try {
            Mail::to($charityEntity->email)->send(new \App\Mail\CharityServicesCompletedMail($invoice));

            // Update tracking flag
            $invoice->update(['sent_to_charity_mail_at' => now()]);

            ActivityLogger::log('Charity Notified', 'Invoice', $invoice->id, 'Charity completion email sent to ' . $charityEntity->email, null, null);
            return back()->with('success', app()->getLocale() === 'ar' ? 'تم إرسال إيميل الاكتمال للجمعية بنجاح.' : 'Charity completion email sent successfully.');
        } catch (\Throwable $e) {
            Log::error('Charity completion email failed: ' . $e->getMessage(), ['invoice_id' => $invoice->id]);
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'فشل إرسال الإيميل. حاول مرة أخرى.' : 'Failed to send email. Please try again.']);
        }
    }

    /** إرسال تذكير بالسداد للجمعية من صفحة الفاتورة */
    public function sendCharityPaymentReminder(Invoice $invoice)
    {
        $this->authorize('invoices.view');

        $invoice->load(['patient.charityEntity']);

        if (!$invoice->patient || $invoice->patient->payment_type !== 'charity') {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'هذه الفاتورة ليست مرتبطة بمريض جمعية.' : 'This invoice is not linked to a charity patient.']);
        }

        $charityEntity = $invoice->patient->charityEntity;
        if (!$charityEntity || !$charityEntity->email) {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'لا يوجد بريد إلكتروني مسجل للجمعية.' : 'No email registered for the charity entity.']);
        }

        try {
            Mail::to($charityEntity->email)->send(new \App\Mail\CharityPaymentReminderMail($invoice));
            ActivityLogger::log('Charity Payment Reminder Sent', 'Invoice', $invoice->id, 'Payment reminder sent to ' . $charityEntity->email, null, null);
            return back()->with('success', app()->getLocale() === 'ar' ? 'تم إرسال تذكير السداد للجمعية بنجاح.' : 'Payment reminder sent to charity.');
        } catch (\Throwable $e) {
            Log::error('Charity payment reminder failed: ' . $e->getMessage(), ['invoice_id' => $invoice->id]);
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'فشل إرسال الإيميل. حاول مرة أخرى.' : 'Failed to send email. Please try again.']);
        }
    }

    public function edit(Invoice $invoice)

    {
        $this->authorizeInvoiceEdit();
        if (auth()->user()->hasRole('insurance_clerk') && $invoice->patient && $invoice->patient->payment_type !== 'insurance') {
            abort(403);
        }

        $invoice->load(['items.service', 'items.completedByUser']);
        $patientsQuery = Patient::where('is_active', true)->orderBy('name');
        if (auth()->user()->hasRole('insurance_clerk')) {
            $patientsQuery->where('payment_type', 'insurance');
        }
        $patients = $patientsQuery->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'patients', 'services'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoiceEdit();

        $oldValues = $invoice->toArray();

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'status' => 'required|string|in:pending,sent_to_insurance,sent_to_charity,approved,rejected,paid',
            'notes' => 'nullable|string',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.unit_price' => 'required|numeric|min:0',
            'services.*.total_price' => 'required|numeric|min:0',
            'services.*.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $invoice->update([
                'invoice_date' => $validated['invoice_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);

            // Delete old items and create new ones
            $invoice->items()->delete();
            foreach ($validated['services'] as $serviceData) {
                $invoice->items()->create([
                    'service_id' => $serviceData['service_id'],
                    'quantity' => $serviceData['quantity'],
                    'unit_price' => $serviceData['unit_price'],
                    'total_price' => $serviceData['total_price'],
                    'description' => $serviceData['description'] ?? null,
                ]);
            }

            InvoiceAmountHelper::syncInvoiceTotalsFromItems($invoice->refresh());
            if (Patient::isTreatmentEligibility($invoice->payment_type ?? null)) {
                InvoiceAmountHelper::applyTreatmentEligibilityZeroInvoice($invoice->refresh());
            }

            $newValues = $invoice->toArray();
            ActivityLogger::log('Invoice Updated', 'Invoice', $invoice->id, 'Invoice details updated', $oldValues, $newValues);

            DB::commit();

            return redirect()->route('invoices.show', $invoice)->with('success', __('Invoice updated successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating invoice: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('invoices.delete');
        $oldValues = $invoice->toArray();
        $invoice->delete();

        ActivityLogger::log('Invoice Deleted', 'Invoice', $invoice->id, 'Invoice deleted', $oldValues, null);

        return redirect()->route('invoices.index')->with('success', __('Invoice deleted successfully.'));
    }

    public function uploadSignedDocument(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoiceEdit();

        $request->validate([
            'document_type' => 'required|string|in:signed_commitment,signed_non_commitment,signed_other',
            'signed_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ]);

        $collection = $request->input('document_type');
        $file = $request->file('signed_file');

        try {
            // If it's single file collection, Spatie will handle replacing the old one
            $media = $invoice->addMedia($file)
                ->toMediaCollection($collection);

            ActivityLogger::log('Signed Document Uploaded', 'Invoice', $invoice->id, 'Signed document uploaded to collection: ' . $collection, null, [
                'file_name' => $media->file_name,
                'collection' => $collection,
                'mime_type' => $media->mime_type,
            ]);

            return back()->with('success', app()->getLocale() === 'ar' ? 'تم رفع المستند بنجاح.' : 'Document uploaded successfully.');
        } catch (\Exception $e) {
            Log::error('Signed document upload failed: ' . $e->getMessage(), ['invoice_id' => $invoice->id]);
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'فشل رفع المستند. حاول مرة أخرى.' : 'Failed to upload document. Please try again.']);
        }
    }

    public function deleteSignedDocument(Invoice $invoice, $mediaId)
    {
        $this->authorizeInvoiceEdit();

        $media = $invoice->media()->findOrFail($mediaId);
        $media->delete();

        ActivityLogger::log('Signed Document Deleted', 'Invoice', $invoice->id, 'Signed document deleted: ' . $media->file_name, [
            'file_name' => $media->file_name,
            'collection' => $media->collection_name,
        ], null);

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم حذف المستند بنجاح.' : 'Document deleted successfully.');
    }
}
