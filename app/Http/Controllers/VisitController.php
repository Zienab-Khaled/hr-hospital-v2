<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Shift;
use App\Models\Visit;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Approval;
use App\Mail\ApprovalRequestMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Helpers\ActivityLogger;
use App\Helpers\InvoiceAmountHelper;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;
use App\Traits\HasIndexFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VisitController extends Controller
{
    use HasIndexFilters;

    /**
     * شاشة إنشاء زيارة: اختيار مريض (بحث أو إضافة جديد) ثم تسجيل دخول القسم ثم تحويل / إحقية علاج / خدمات / فاتورة
     */
    public function create(Request $request)
    {
        $this->authorize('invoices.create');

        $currentShift = Shift::currentAt();
        $departments = Department::where('is_active', true)->where('category', 'medical')->orderBy('name')->get();
        $myDepartment = auth()->user()?->department_id
            ? Department::find(auth()->user()->department_id)
            : null;

        $patient = null;
        $visit = null;
        $patientId = $request->get('patient_id');
        $visitId = $request->get('visit_id');
        $registered = $request->boolean('registered');

        $activeVisits = collect();

        if ($patientId) {
            $patient = Patient::with(['department', 'insuranceCompany', 'charityEntity'])
                ->find($patientId);

            // البحث عن زيارات سابقة لليوم
            $todayVisits = $patient ? $patient->visits()->with(['department', 'shift'])->whereDate('visit_date', today())->latest()->get() : collect();

            if ($visitId) {
                $visit = $todayVisits->where('id', $visitId)->first();
                if ($visit) {
                    $visit->load('invoices');
                }
            } elseif ($request->boolean('new_visit')) {
                // إجبار إنشاء زيارة جديدة
                $visit = null;
            } elseif ($todayVisits->isNotEmpty()) {
                // يوجد زيارات سابقة: نعرضها للمستخدم ليختار فتحها أو إنشاء جديدة
                $activeVisits = $todayVisits;
                $visit = null;
            }

            // إنشاء الزيارة تلقائياً عند الدخول بمريض:
            // 1. لا توجد زيارة محددة ($visit null)
            // 2. لا توجد قائمة زيارات للاختيار منها ($activeVisits empty) أو طلب المستخدم زيارة جديدة explicitely
            $shouldCreate = $patient && !$visit && $myDepartment && $currentShift && ($activeVisits->isEmpty() || $request->boolean('new_visit'));

            if ($shouldCreate) {
                $visit = Visit::create([
                    'patient_id' => $patient->id,
                    'department_id' => $myDepartment->id,
                    'admission_entry_source' => Visit::inferAdmissionEntryFromDepartment($myDepartment),
                    'visit_date' => today(),
                    'shift_id' => $currentShift->id,
                    'case_type' => $myDepartment->name_ar ?? $myDepartment->name ?? 'clinics',
                    'notes' => null,
                    'registered_by' => auth()->user()->id,
                ]);
                $patient->update(['department_id' => $myDepartment->id]);
                $registered = true;
                session()->flash('success', app()->getLocale() === 'ar' ? 'تم إنشاء الزيارة وتسجيل دخول المريض للقسم.' : 'Visit created and patient registered to department.');
            }
        }

        $eligibilityDepartments = Department::where('is_active', true)
            ->where('category', 'medical')
            ->orderBy('name')
            ->get();

        $entryFeeDepartments = Department::where('is_active', true)
            ->where('category', 'medical')
            ->whereNotNull('entry_fee')
            ->where('entry_fee', '>', 0)
            ->orderBy('name')
            ->get();

        $defaultAdmissionSource = $myDepartment
            ? Visit::inferAdmissionEntryFromDepartment($myDepartment)
            : Visit::ADMISSION_OUTPATIENT_CLINICS;

        return view('visits.create', compact(
            'currentShift', 'departments', 'eligibilityDepartments', 'entryFeeDepartments', 'myDepartment', 'patient', 'visit', 'activeVisits', 'registered', 'defaultAdmissionSource'
        ));
    }

    /**
     * تسجيل دخول المريض للقسم (تعيين قسم المريض + إنشاء زيارة للشيفت الحالي)
     */
    public function store(Request $request)
    {
        $this->authorize('invoices.create');

        $patient = Patient::findOrFail($request->input('patient_id'));

        $validationRules = [
            'patient_id' => 'required|exists:patients,id',
            'admission_entry_source' => 'required|in:outpatient_clinics,emergency',
        ];
        if ($patient->payment_type === 'charity') {
            $validationRules['charity_approval_document'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }
        $request->validate($validationRules);

        $user = auth()->user();
        $departmentId = $user?->department_id;
        $currentShift = Shift::currentAt();

        if (!$departmentId) {
            return back()->withErrors(['patient_id' => app()->getLocale() === 'ar'
                ? 'يجب أن يكون الموظف مرتبطاً بقسم لتسجيل زيارة.'
                : 'Employee must be assigned to a department.'])->withInput();
        }

        $dept = Department::find($departmentId);
        $visit = Visit::create([
            'patient_id' => $patient->id,
            'department_id' => $departmentId,
            'admission_entry_source' => $request->input('admission_entry_source'),
            'visit_date' => today(),
            'shift_id' => $currentShift?->id,
            'case_type' => $dept->name_ar ?? $dept->name ?? 'clinics',
            'notes' => null,
            'registered_by' => $user?->getKey(),
        ]);

        // Upload charity approval document if provided
        if ($request->hasFile('charity_approval_document')) {
            $visit->addMedia($request->file('charity_approval_document'))
                ->toMediaCollection('charity_approval');
        }

        $patient->update(['department_id' => $departmentId]);

        ActivityLogger::log('Visit Created', 'Visit', $visit->id, 'Patient registered to department', null, $visit->toArray());

    // System Notification for Managers and Accountants
    $notifyUsers = User::whereHas('roles', function($q) {
        $q->whereIn('name', ['manager', 'admin', 'accountant']);
    })->get();
    if ($notifyUsers->isNotEmpty()) {
        $deptName = $visit->department->name_ar ?? $visit->department->name;
        Notification::send($notifyUsers, new SystemNotification([
            'title' => app()->getLocale() === 'ar' ? 'زيارة جديدة' : 'New Patient Visit',
            'message' => (app()->getLocale() === 'ar' ? "تم تسجيل زيارة للمريض: " : "Visit registered for patient: ") . $patient->fullArabicName() . (app()->getLocale() === 'ar' ? " بلقسم: " : " in department: ") . $deptName,
            'action_url' => route('visits.show', $visit),
            'type' => 'success',
        ]));
    }

        return redirect()->route('visits.create', [
            'patient_id' => $patient->id,
            'visit_id' => $visit->id,
            'registered' => 1,
        ])->with('success', app()->getLocale() === 'ar' ? 'تم تسجيل دخول المريض للقسم وإنشاء الزيارة.' : 'Visit registered successfully.');
    }

    /**
     * بحث الخدمات حسب القسم (لأحقية العلاج — عيادة / مختبر / أشعة / تنويم / طوارئ)
     * إذا لم يتم تحديد قسم، يتم البحث في كل الخدمات
     */
    public function searchServicesForEligibility(Request $request)
    {
        $this->authorize('invoices.create');
        $departmentId = $request->get('department_id');
        $q = trim((string) $request->get('q', ''));

        // Start with all active services
        $query = Service::where('is_active', true);

        // Filter by department if provided (skip «بدون قسم» sentinel)
        if ($departmentId && $departmentId !== 'none') {
            $query->where('department_id', $departmentId);
        }

        // Filter by search query if provided
        if ($q !== '') {
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('name_ar', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $services = $query->orderBy('name')->limit(50)->get()->map(function ($s) {
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

    /**
     * إنشاء فاتورة دخول (كشفية قسم) وطباعة الأحقية — للمريض الجديد عند دخول عيادة/طوارئ/مختبر إلخ
     */
    public function storeEntryFeeInvoice(Request $request, Visit $visit)
    {
        $this->authorize('invoices.create');
        $visit->load(['patient', 'department']);

        $rules = ['department_id' => 'required|exists:departments,id'];
        if ($visit->patient && $visit->patient->payment_type === 'insurance') {
            $rules['insurance_coverage_type'] = 'nullable|in:percentage,fixed';
            $rules['insurance_coverage_value'] = 'nullable|numeric|min:0';
        }
        $request->validate($rules);

        $department = Department::find($request->input('department_id'));
        $entryFee = $department->entry_fee;
        if ($entryFee === null || (float) $entryFee < 0) {
            return back()->withErrors(['department_id' => app()->getLocale() === 'ar' ? 'هذا القسم لا يملك كشفية دخول معرّفة. حدّث أسعار الأقسام من إعدادات الأقسام.' : 'This department has no entry fee defined.']);
        }
        $entryFee = (float) $entryFee;

        $patient = $visit->patient;
        if (! $patient) {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'الزيارة غير مرتبطة بمريض.' : 'Visit has no patient.']);
        }

        $insuranceCoverageType = $patient->payment_type === 'insurance' && $request->filled('insurance_coverage_type')
            ? $request->input('insurance_coverage_type')
            : null;
        $insuranceCoverageValue = $insuranceCoverageType && $request->filled('insurance_coverage_value')
            ? (float) $request->input('insurance_coverage_value')
            : null;

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(Invoice::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        $finalPaymentType = $patient->payment_type;
        if ($patient->payment_type === 'charity') {
            $hasApprovalOnVisit = $visit->hasMedia('charity_approval');
            $hasApprovalOnPatient = $patient->hasMedia('charity-approvals');
            if (! $hasApprovalOnVisit && ! $hasApprovalOnPatient) {
                $finalPaymentType = 'cash';
            }
        }
        $isTreatmentEligibility = Patient::isTreatmentEligibility($finalPaymentType);
        $entryAmount = $isTreatmentEligibility ? 0.0 : $entryFee;

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                'visit_id' => $visit->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'total_amount' => $entryAmount,
                'paid_amount' => 0,
                'remaining_amount' => $entryAmount,
                'deposit_amount' => 0,
                'status' => $isTreatmentEligibility ? 'paid' : 'pending',
                'notes' => $isTreatmentEligibility
                    ? (app()->getLocale() === 'ar' ? 'أحقية العلاج' : 'Treatment Eligibility')
                    : (app()->getLocale() === 'ar' ? 'كشفية دخول القسم' : 'Department entry fee'),
                'payment_type' => $finalPaymentType,
                'invoice_type' => 'eligibility',
                'audit_status' => 'under_review',
            ]);

            $deptName = $department->name_ar ?? $department->name;
            $description = (app()->getLocale() === 'ar' ? 'كشفية ' : 'Entry fee ') . $deptName;

            $invoice->items()->create([
                'service_id' => null,
                'department_id' => $department->id,
                'quantity' => 1,
                'unit_price' => $entryAmount,
                'total_price' => $entryAmount,
                'description' => $description,
                'insurance_coverage_type' => $isTreatmentEligibility ? 'percentage' : $insuranceCoverageType,
                'insurance_coverage_value' => $isTreatmentEligibility ? 100.0 : $insuranceCoverageValue,
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);

            InvoiceAmountHelper::syncInvoiceTotalsFromItems($invoice->refresh());
            if ($isTreatmentEligibility) {
                InvoiceAmountHelper::applyTreatmentEligibilityZeroInvoice($invoice->refresh());
            }

            if (in_array($patient->payment_type, ['insurance', 'charity'])) {
                Approval::create([
                    'invoice_id' => $invoice->id,
                    'patient_id' => $patient->id,
                    'approval_type' => $patient->payment_type,
                    'insurance_company_id' => $patient->insurance_company_id,
                    'charity_entity_id' => $patient->charity_entity_id,
                    'requested_amount' => (float) $invoice->total_amount,
                    'status' => 'pending',
                    'requested_by' => auth()->user()?->getKey(),
                ]);
            }

            DB::commit();
            ActivityLogger::log('Entry Fee Invoice', 'Invoice', $invoice->id, 'Department entry fee invoice created for visit: ' . $visit->id, null, $invoice->toArray());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Entry fee invoice failed: ' . $e->getMessage());
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'فشل إنشاء فاتورة الدخول.' : 'Failed to create entry fee invoice.']);
        }

        $printUrl = route('visits.treatment-eligibility-print', ['visit' => $visit]) . '?entry_invoice_id=' . $invoice->id;
        return redirect($printUrl)->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء فاتورة الدخول. يمكنك طباعة الأحقية ثم تسجيل الدفع من صفحة الفاتورة (الدفع اختياري).' : 'Entry fee invoice created. You can print eligibility then record payment from invoice page (payment is optional).');
    }

    /**
     * طباعة إحقية علاج — GET بدون خدمات، POST مع قائمة الخدمات المختارة، أو entry_invoice_id لفاتورة دخول
     */
    public function treatmentEligibilityPrint(Request $request, Visit $visit)
    {
        $this->authorize('invoices.create');
        $visit->load(['patient', 'department', 'shift']);

        $services = [];
        $entryInvoiceId = $request->query('entry_invoice_id');

        if ($entryInvoiceId) {
            $entryInvoice = Invoice::where('visit_id', $visit->id)->find($entryInvoiceId);
            if ($entryInvoice) {
                foreach ($entryInvoice->items as $item) {
                    $services[] = [
                        'name' => $item->description ?: (app()->getLocale() === 'ar' ? 'كشفية دخول' : 'Entry fee'),
                        'name_ar' => $item->description ?: 'كشفية دخول',
                        'total' => (float) $item->total_price,
                        'service_id' => $item->service_id,
                        'department_id' => $item->department_id,
                    ];
                }
            }
        }

        if (empty($services) && $request->isMethod('post') && $request->has('services')) {
            $services = is_array($request->input('services')) ? $request->input('services') : [];
        }

        $deptInput = $request->input('department_id');
        $withoutDepartment = $deptInput === 'none';
        $eligibilityNotes = trim((string) $request->input('eligibility_notes', ''));

        if ($request->isMethod('post')) {
            if ($withoutDepartment) {
                $request->validate([
                    'eligibility_notes' => 'required|string|min:2|max:2000',
                ]);
            } elseif ($request->filled('department_id') && $deptInput !== 'none') {
                $request->validate([
                    'department_id' => 'exists:departments,id',
                ]);
            }
        }

        $printDepartmentId = null;
        if (! $withoutDepartment && $request->filled('department_id') && $deptInput !== 'none') {
            $printDepartmentId = (int) $deptInput;
        }

        // Default manager: System Manager or Revenue Manager
        $manager = User::getManagerForSignature();

        // إذا مُختار قسم الأحقية نستخدمه، وإلا نعتمد على "حالة الزيارة (نوع الحالة)" أو قسم المريض
        $targetDepartment = null;
        $targetDepartmentName = null;

        if ($withoutDepartment && $eligibilityNotes !== '') {
            $targetDepartmentName = $eligibilityNotes;
        } elseif ($printDepartmentId) {
            $targetDepartment = Department::with('manager')->find($printDepartmentId);
            if ($targetDepartment) {
                $targetDepartmentName = $targetDepartment->name_ar ?? $targetDepartment->name;
            }
        }

        if (!empty($services) && !empty($services[0]['department_id'])) {
            $targetDepartment = Department::with('manager')->find($services[0]['department_id']);
            if ($targetDepartment) {
                $targetDepartmentName = $targetDepartment->name_ar ?? $targetDepartment->name;
            }
        }
        if (!$targetDepartmentName && $request->filled('department_id') && $deptInput !== 'none') {
            $targetDepartment = Department::with('manager')->find($request->input('department_id'));
            if ($targetDepartment) {
                $targetDepartmentName = $targetDepartment->name_ar ?? $targetDepartment->name;
            }
        }

        if (!$targetDepartmentName && ! $withoutDepartment && $visit->eligibility_print_department_id) {
            $targetDepartment = Department::with('manager')->find($visit->eligibility_print_department_id);
            if ($targetDepartment) {
                $targetDepartmentName = $targetDepartment->name_ar ?? $targetDepartment->name;
            }
        }

        if (!$targetDepartmentName && $visit->eligibility_without_department && $visit->eligibility_notes) {
            $targetDepartmentName = $visit->eligibility_notes;
        }

        if (!$targetDepartmentName) {
            // الأولوية: حالة الزيارة (نوع الحالة) — القسم اللي اليوزر اختاره في "بيانات الزيارة والمتابعة"
            $caseType = trim($visit->case_type ?? '');
            if ($caseType !== '' && $caseType !== 'emergency') {
                $targetDepartmentName = $caseType;
                $targetDepartment = Department::where('name_ar', $caseType)->orWhere('name', $caseType)->first();
            }
            if (!$targetDepartmentName && $visit->transferred_department_id) {
                $targetDepartment = Department::with('manager')->find($visit->transferred_department_id);
                if ($targetDepartment) {
                    $targetDepartmentName = $targetDepartment->name_ar ?? $targetDepartment->name;
                }
            }
            if (!$targetDepartmentName && $visit->patient && $visit->patient->department_id) {
                $targetDepartment = Department::with('manager')->find($visit->patient->department_id);
                if ($targetDepartment) {
                    $targetDepartmentName = $targetDepartment->name_ar ?? $targetDepartment->name;
                }
            }
            if (!$targetDepartmentName && $visit->department) {
                $targetDepartmentName = $visit->department->name_ar ?? $visit->department->name;
                $targetDepartment = $visit->department;
            }
        }

        // إن لم يتوفر أي قسم: استخدام قسم أول خدمة إن وُجدت
        if (!$targetDepartmentName && !empty($services)) {
            $firstServiceId = $services[0]['service_id'] ?? null;
            if ($firstServiceId) {
                $firstService = Service::with('department')->find($firstServiceId);
                if ($firstService && $firstService->department) {
                    $targetDepartmentName = $firstService->department->name_ar ?? $firstService->department->name;
                    $targetDepartment = $firstService->department;
                }
            }
        }

        // Update tracking flag and save services (skip auto-invoice when we already have entry_invoice_id)
        $visitUpdate = [
            'printed_eligibility_at' => now(),
            'last_eligibility_services' => $services,
        ];
        if ($request->isMethod('post')) {
            $visitUpdate['eligibility_notes'] = $eligibilityNotes !== '' ? $eligibilityNotes : null;
            $visitUpdate['eligibility_print_department_id'] = $printDepartmentId;
            $visitUpdate['eligibility_without_department'] = $withoutDepartment;
        }
        $visit->update($visitUpdate);
        $visit->refresh();

        if ($eligibilityNotes === '' && $visit->eligibility_notes) {
            $eligibilityNotes = $visit->eligibility_notes;
        }

        // Auto-create Invoice if services are provided (not when entry fee invoice already created)
        if (!empty($services) && !$entryInvoiceId) {
            DB::beginTransaction();
            try {
                $patient = $visit->patient;
                $totalAmount = 0;
                foreach ($services as $s) {
                    $totalAmount += (float) ($s['total'] ?? 0);
                }

                $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(Invoice::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

                // Determine initial payment_type for the auto-created invoice
                $finalPaymentType = $patient->payment_type;
                if ($patient->payment_type === 'charity') {
                    // Check for approval document on visit or patient profile
                    $hasApprovalOnVisit = $visit->hasMedia('charity_approval');
                    $hasApprovalOnPatient = $patient->hasMedia('charity-approvals');

                    if (!$hasApprovalOnVisit && !$hasApprovalOnPatient) {
                        $finalPaymentType = 'cash';
                    }
                }
                $isTreatmentEligibility = Patient::isTreatmentEligibility($finalPaymentType);
                $invoiceTotal = $isTreatmentEligibility ? 0.0 : $totalAmount;

                $invoiceNotes = app()->getLocale() === 'ar' ? 'أحقية العلاج' : 'Treatment Eligibility';
                if ($eligibilityNotes !== '') {
                    $invoiceNotes = $eligibilityNotes.' — '.$invoiceNotes;
                } elseif ($visit->eligibility_notes) {
                    $invoiceNotes = $visit->eligibility_notes.' — '.$invoiceNotes;
                }

                // ربط الفاتورة بالزيارة (visit_id) حتى تظهر في ليستينج فواتير الزيارة
                $invoice = Invoice::create([
                    'patient_id' => $patient->id,
                    'visit_id' => $visit->id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => now(),
                    'total_amount' => $invoiceTotal,
                    'paid_amount' => 0,
                    'remaining_amount' => $invoiceTotal,
                    'deposit_amount' => 0,
                    'status' => $isTreatmentEligibility ? 'paid' : 'pending',
                    'notes' => $invoiceNotes,
                    'payment_type' => $finalPaymentType,
                    'invoice_type' => 'eligibility',
                    'audit_status' => 'under_review',
                ]);

                foreach ($services as $s) {
                    $lineTotal = $isTreatmentEligibility ? 0.0 : (float) ($s['total'] ?? $s['total_price'] ?? 0);
                    $lineUnit = $isTreatmentEligibility ? 0.0 : (float) ($s['unit_price'] ?? $s['price'] ?? 0);
                    $coverageType = $isTreatmentEligibility
                        ? 'percentage'
                        : (isset($s['insurance_coverage_type']) && in_array($s['insurance_coverage_type'], ['percentage', 'fixed']) ? $s['insurance_coverage_type'] : null);
                    $coverageValue = $isTreatmentEligibility
                        ? 100.0
                        : (isset($s['insurance_coverage_value']) && $s['insurance_coverage_value'] !== '' ? (float) $s['insurance_coverage_value'] : null);
                    if (! $isTreatmentEligibility && $finalPaymentType === 'charity' && ! $coverageType) {
                        $coverageType = 'percentage';
                        $coverageValue = 100.0;
                    }
                    $invoice->items()->create([
                        'service_id' => $s['service_id'] ?? null,
                        'quantity' => (int) round((float) ($s['qty'] ?? $s['quantity'] ?? 1)),
                        'unit_price' => $lineUnit,
                        'total_price' => $lineTotal,
                        'insurance_coverage_type' => $coverageType,
                        'insurance_coverage_value' => $coverageValue,
                        'description' => $s['name'] ?? null,
                    ]);
                }

                InvoiceAmountHelper::syncInvoiceTotalsFromItems($invoice->refresh());
                if ($isTreatmentEligibility) {
                    InvoiceAmountHelper::applyTreatmentEligibilityZeroInvoice($invoice->refresh());
                }

                // Approval request
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
                        try {
                            // DISABLED: النظام لا يرسل إيميلات حالياً
                            // Mail::to($recipientEmail)->send(new ApprovalRequestMail($approval));
                        } catch (\Exception $e) {
                            Log::error('Failed to send auto-approval email: ' . $e->getMessage());
                        }
                    }
                }

                DB::commit();
                ActivityLogger::log('Invoice Auto-Created', 'Invoice', $invoice->id, 'Invoice created automatically from Eligibility Print for patient: ' . $patient->fullArabicName(), null, $invoice->toArray());
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Auto-invoice creation failed: ' . $e->getMessage());
            }
        }

        // Log the action
        ActivityLogger::log('Print Eligibility', 'Visit', $visit->id, 'Treatment eligibility form printed for patient: ' . $visit->patient->fullArabicName(), null, null);

        return view('visits.treatment-eligibility-print', compact('visit', 'services', 'manager', 'targetDepartment', 'targetDepartmentName', 'eligibilityNotes'));
    }

    /**
     * معالجة طباعة إحقية العلاج (POST)
     */
    public function treatmentEligibilityPrintSubmit(Request $request, Visit $visit)
    {
        return $this->treatmentEligibilityPrint($request, $visit);
    }

    /**
     * طباعة أحقية علاج لخدمة واحدة (بند فاتورة) — بدون إنشاء فاتورة، مرتبط بالخدمة المنفذة
     */
    public function treatmentEligibilityPrintForItem(Invoice $invoice, InvoiceItem $item)
    {
        $this->authorize('invoices.create');

        if ((int) $item->invoice_id !== (int) $invoice->id) {
            abort(404);
        }

        $item->load(['service.department']);
        $visit = $invoice->visit;
        if (! $visit) {
            return redirect()->route('invoices.show', $invoice)->withErrors([
                'error' => app()->getLocale() === 'ar' ? 'الفاتورة غير مرتبطة بزيارة. لا يمكن طباعة أحقية علاج.' : 'Invoice has no linked visit. Cannot print treatment eligibility.',
            ]);
        }

        $visit->load(['patient', 'department', 'shift']);
        $service = $item->service;
        $services = [
            [
                'service_id' => $item->service_id,
                'name' => $service->name ?? '-',
                'name_ar' => $service->name_ar ?? $service->name ?? '-',
            ],
        ];

        $manager = User::getManagerForSignature();
        $targetDepartment = $service->department;
        $targetDepartmentName = $targetDepartment ? ($targetDepartment->name_ar ?? $targetDepartment->name) : ($visit->department->name_ar ?? $visit->department->name ?? '—');
        $eligibilityNotes = $visit->eligibility_notes ?? '';

        return view('visits.treatment-eligibility-print', compact('visit', 'services', 'manager', 'targetDepartment', 'targetDepartmentName', 'eligibilityNotes'));
    }

    /**
     * طباعة عرض سعر استعلامي — للاستعلام فقط بدون تسجيل إيرادات
     * Price Inquiry Print — for quotation/estimation only, does NOT record revenue
     */
    public function priceInquiryPrint(Request $request, Visit $visit)
    {
        $this->authorize('invoices.create');
        $visit->load(['patient', 'department', 'shift']);

        $services = [];
        if ($request->isMethod('post') && $request->has('services')) {
            $services = is_array($request->input('services')) ? $request->input('services') : [];
        }

        $manager = User::getManagerForSignature();
        $printTitle = $request->get('print_title', 'price_quotation');

        // Update tracking flag and save services
        $visit->update([
            'printed_price_inquiry_at' => now(),
            'last_price_inquiry_services' => $services
        ]);

        // Log the action
        ActivityLogger::log('Print Price Inquiry', 'Visit', $visit->id, 'Price inquiry form printed for patient: ' . $visit->patient->fullArabicName(), null, ['title' => $printTitle]);

        return view('visits.price-inquiry-print', compact('visit', 'services', 'manager', 'printTitle'));
    }

    /**
     * معالجة طباعة عرض السعر الاستعلامي (POST)
     */
    public function priceInquiryPrintSubmit(Request $request, Visit $visit)
    {
        return $this->priceInquiryPrint($request, $visit);
    }

    /**
     * إنشاء فاتورة تفصيلية من خدمات جدول الزيارة — تُسجَّل في الإيرادات
     */
    public function storeServicesRevenueInvoice(Request $request, Visit $visit)
    {
        $this->authorize('invoices.create');
        $visit->load(['patient']);

        $services = is_array($request->input('services')) ? $request->input('services') : [];
        if (empty($services)) {
            return redirect()->route('visits.create', [
                'patient_id' => $visit->patient_id,
                'visit_id' => $visit->id,
                'registered' => 1,
            ])->withErrors(['error' => app()->getLocale() === 'ar' ? 'يرجى إضافة خدمة واحدة على الأقل.' : 'Please add at least one service.']);
        }

        $patient = $visit->patient;
        if (! $patient) {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'الزيارة غير مرتبطة بمريض.' : 'Visit has no patient.']);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0.0;
            foreach ($services as $s) {
                $totalAmount += (float) ($s['total'] ?? $s['total_price'] ?? 0);
            }

            $finalPaymentType = $patient->payment_type;
            if ($patient->payment_type === 'charity') {
                $hasApprovalOnVisit = $visit->hasMedia('charity_approval');
                $hasApprovalOnPatient = $patient->hasMedia('charity-approvals');
                if (! $hasApprovalOnVisit && ! $hasApprovalOnPatient) {
                    $finalPaymentType = 'cash';
                }
            }

            $isTreatmentEligibility = Patient::isTreatmentEligibility($finalPaymentType);
            $invoiceTotal = $isTreatmentEligibility ? 0.0 : $totalAmount;
            $invoiceType = $isTreatmentEligibility ? 'eligibility' : 'regular';

            $invoiceNumber = 'INV-'.date('Ymd').'-'.str_pad(Invoice::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                'visit_id' => $visit->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'total_amount' => $invoiceTotal,
                'paid_amount' => 0,
                'remaining_amount' => $invoiceTotal,
                'deposit_amount' => 0,
                'status' => $isTreatmentEligibility ? 'paid' : 'pending',
                'notes' => app()->getLocale() === 'ar' ? 'فاتورة تفصيلية' : 'Detailed invoice',
                'payment_type' => $finalPaymentType,
                'invoice_type' => $invoiceType,
                'audit_status' => 'under_review',
            ]);

            foreach ($services as $s) {
                $lineTotal = $isTreatmentEligibility ? 0.0 : (float) ($s['total'] ?? $s['total_price'] ?? 0);
                $lineUnit = $isTreatmentEligibility ? 0.0 : (float) ($s['unit_price'] ?? $s['price'] ?? 0);
                $coverageType = $isTreatmentEligibility
                    ? 'percentage'
                    : (isset($s['insurance_coverage_type']) && in_array($s['insurance_coverage_type'], ['percentage', 'fixed'], true) ? $s['insurance_coverage_type'] : null);
                $coverageValue = $isTreatmentEligibility
                    ? 100.0
                    : (isset($s['insurance_coverage_value']) && $s['insurance_coverage_value'] !== '' ? (float) $s['insurance_coverage_value'] : null);
                if (! $isTreatmentEligibility && $finalPaymentType === 'charity' && ! $coverageType) {
                    $coverageType = 'percentage';
                    $coverageValue = 100.0;
                }

                $invoice->items()->create([
                    'service_id' => $s['service_id'] ?? null,
                    'quantity' => (int) round((float) ($s['qty'] ?? $s['quantity'] ?? 1)),
                    'unit_price' => $lineUnit,
                    'total_price' => $lineTotal,
                    'insurance_coverage_type' => $coverageType,
                    'insurance_coverage_value' => $coverageValue,
                    'description' => $s['name'] ?? null,
                ]);
            }

            InvoiceAmountHelper::syncInvoiceTotalsFromItems($invoice->refresh());
            if ($isTreatmentEligibility) {
                InvoiceAmountHelper::applyTreatmentEligibilityZeroInvoice($invoice->refresh());
            }

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
                    try {
                        // DISABLED: النظام لا يرسل إيميلات حالياً
                        // Mail::to($recipientEmail)->send(new ApprovalRequestMail($approval));
                    } catch (\Exception $e) {
                        Log::error('Failed to send approval email: '.$e->getMessage());
                    }
                }
            }

            DB::commit();
            ActivityLogger::log('Invoice Created', 'Invoice', $invoice->id, 'Detailed invoice from visit services', null, $invoice->toArray());

            $visit->load(['patient', 'department', 'shift']);
            $manager = User::getManagerForSignature();
            $printTitle = 'detailed_invoice';
            $printServices = array_map(function ($s) {
                return [
                    'code' => $s['code'] ?? '',
                    'name' => $s['name'] ?? '',
                    'qty' => $s['qty'] ?? $s['quantity'] ?? 1,
                    'unit_price' => $s['unit_price'] ?? 0,
                    'total' => $s['total'] ?? $s['total_price'] ?? 0,
                    'insurance_coverage_type' => $s['insurance_coverage_type'] ?? '',
                    'insurance_coverage_value' => $s['insurance_coverage_value'] ?? 0,
                ];
            }, $services);

            return view('visits.price-inquiry-print', [
                'visit' => $visit,
                'services' => $printServices,
                'manager' => $manager,
                'printTitle' => $printTitle,
                'invoice' => $invoice->fresh(['items']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Visit services revenue invoice failed: '.$e->getMessage());

            return redirect()->route('visits.create', [
                'patient_id' => $visit->patient_id,
                'visit_id' => $visit->id,
                'registered' => 1,
            ])->withErrors(['error' => app()->getLocale() === 'ar' ? 'تعذّر إنشاء الفاتورة.' : 'Could not create invoice.']);
        }
    }


    /**
     * قائمة الزيارات
     */
    public function index(Request $request)
    {
        Gate::authorize('visits.view');

        $user = auth()->user();
        // يرى الكل ويقدر يفلتر: الإدارة + فني متابعة المرضى (بدل التقييد على قسمه وشيفته فقط)
        $isAdmin = \App\Support\RoleNav::canSeeAllVisits($user);
        $currentShift = Shift::currentAt();

        // For full-view roles: if no filters at all, redirect with today + current shift as defaults
        if ($isAdmin && !$request->hasAny(['date', 'date_from', 'date_to', 'shift_id', 'department_id', 'search', 'insurance_company_id', 'registered_by', 'admission_entry_source', 'page'])) {
            $today = today()->toDateString();
            $defaults = [
                'date_from' => $today,
                'date_to' => $today,
            ];
            if ($currentShift) {
                $defaults['shift_id'] = $currentShift->id;
            }
            return redirect()->route('visits.index', $defaults);
        }

        $query = Visit::with(['patient', 'department', 'shift', 'registeredBy', 'invoices.partySends']);

        if (!$isAdmin) {
            $deptId = $user->department_id;
            if ($deptId && $currentShift) {
                // الموظف يرى زيارات شيفت اليوم في قسمه فقط
                $query->where('department_id', $deptId)
                    ->where('shift_id', $currentShift->id)
                    ->whereDate('visit_date', today());
            } else {
                // إذا لم يكن مرتبطاً بقسم أو لا يوجد شيفت حالي، لا يرى شيئاً (أو يمكن تعديله ليرى زياراته فقط)
                $query->whereRaw('1 = 0');
            }
        } else {
            // الأدمن يرى الكل ويمكنه الفلترة
            if ($request->filled('shift_id')) {
                $query->where('shift_id', $request->input('shift_id'));
            }
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->input('department_id'));
            }
            if ($request->filled('date_from')) {
                $query->whereDate('visit_date', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('visit_date', '<=', $request->input('date_to'));
            }
            if (! $request->filled('date_from') && ! $request->filled('date_to') && $request->filled('date')) {
                $query->whereDate('visit_date', $request->input('date'));
            }
            if ($request->filled('admission_entry_source')) {
                $query->where('admission_entry_source', $request->input('admission_entry_source'));
            }
        }

        $this->applyIndexFilters(
            $query,
            $request,
            ['patient.name', 'patient.name_ar', 'patient.name_ar_first', 'patient.name_ar_father', 'patient.name_ar_family', 'patient.file_number', 'patient.identity_value', 'notes'],
            ['shift_id' => 'shift_id', 'department_id' => 'department_id', 'registered_by' => 'registered_by'],
            ['insurance_company_id' => ['patient', 'insurance_company_id']]
        );

        $visits = $query->latest('visit_date')->latest('id')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        $shifts = Shift::where('is_active', true)->orderBy('sort_order')->get();
        $departments = Department::where('is_active', true)->where('category', 'medical')->orderBy('name')->get();
        $registrars = \App\Models\User::orderBy('name')->get();
        $insuranceCompanies = \App\Models\InsuranceCompany::where('is_active', true)->orderBy('name')->get();

        return view('visits.index', compact('visits', 'currentShift', 'isAdmin', 'shifts', 'departments', 'registrars', 'insuranceCompanies'));
    }

    public function show(Visit $visit)
    {
        $this->authorize('invoices.view');
        $visit->load(['patient', 'department', 'shift', 'registeredBy', 'eligibilityPrintDepartment', 'invoices.items.service', 'invoices.payments']);

        return view('visits.show', compact('visit'));
    }

    public function edit(Visit $visit)
    {
        $this->authorize('visits.delete'); // Using delete permission or similar admin permission
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $shifts = Shift::where('is_active', true)->orderBy('sort_order')->get();
        return view('visits.edit', compact('visit', 'departments', 'shifts'));
    }

    public function update(Request $request, Visit $visit)
    {
        // Require invoices.create permission to update visit status/notes from create page
        // Or visits.delete for general edit
        // We'll allow invoices.create since it's the main role using this.
        if (!auth()->user()->can('invoices.create') && !auth()->user()->can('visits.delete')) {
            abort(403);
        }

        $rules = [
            'department_id' => 'nullable|exists:departments,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'visit_date' => 'nullable|date',
            'case_type' => 'required|string',
            'notes' => 'nullable|string',
            'admission_entry_source' => 'nullable|in:outpatient_clinics,emergency',
        ];
        if ($request->boolean('redirect_to_create')) {
            $rules['admission_entry_source'] = 'required|in:outpatient_clinics,emergency';
        }

        $valid = $request->validate($rules);

        $oldValues = $visit->getOriginal();

        // Filter out null values to prevent overwriting with null if only partial update
        $updateData = array_filter($valid, fn($v) => !is_null($v));
        $visit->update($updateData);

        ActivityLogger::log('Visit Updated', 'Visit', $visit->id, 'Visit details updated', $oldValues, $visit->toArray());

        if ($request->boolean('redirect_to_create')) {
            return redirect()->route('visits.create', [
                'patient_id' => $visit->patient_id,
                'visit_id' => $visit->id,
                'registered' => 1
            ])->with('success', app()->getLocale() === 'ar' ? 'تم تحديث بيانات الزيارة.' : 'Visit data updated.');
        }

        return redirect()->route('visits.index')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الزيارة بنجاح.' : 'Visit updated successfully.');
    }

    /**
     * تحويل الزيارة إلى قسم آخر (التحويل للزيارة وليس للمريض)
     */
    public function transfer(Request $request, Visit $visit)
    {
        $visitDepartmentId = $visit->department_id;

        $request->validate([
            'to_department_id' => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($visitDepartmentId) {
                    if ((int) $value === (int) $visitDepartmentId) {
                        $fail(app()->getLocale() === 'ar' ? 'يجب اختيار قسم مختلف عن قسم الزيارة الحالي.' : 'Target department must be different from current visit department.');
                    }
                    $dept = Department::find($value);
                    if ($dept && $dept->category !== 'medical') {
                        $fail(app()->getLocale() === 'ar' ? 'يمكن التحويل للأقسام الطبية فقط.' : 'Transfers are only allowed to medical departments.');
                    }
                },
            ],
            'notes' => 'nullable|string|max:500',
        ]);

        // سجل التحويل (من → إلى) للتدقيق
        \App\Models\PatientTransfer::create([
            'patient_id' => $visit->patient_id,
            'from_department_id' => $visit->department_id,
            'to_department_id' => $request->input('to_department_id'),
            'transferred_at' => now(),
            'transferred_by' => auth()->user()->id,
            'notes' => $request->input('notes'),
        ]);

        // تحديث الزيارة فقط: تعيين القسم المُحوّل إليه (التحويل للزيارة وليس للمريض)
        $oldValues = $visit->getOriginal();
        $visit->update(['transferred_department_id' => $request->input('to_department_id')]);

        ActivityLogger::log('Visit Transferred', 'Visit', $visit->id, 'Visit transferred to department', $oldValues, $visit->toArray());

        return redirect()->route('visits.create', [
            'patient_id' => $visit->patient_id,
            'visit_id' => $visit->id,
            'registered' => 1
        ])->with('success', app()->getLocale() === 'ar' ? 'تم تحويل الزيارة إلى القسم الجديد بنجاح.' : 'Visit transferred successfully.');
    }
    public function destroy(Visit $visit)
    {
        $this->authorize('visits.delete');

        // Check for dependencies? Invoices?
        // Usually forced delete or check

        $oldValues = $visit->toArray();
        $visit->delete();

        ActivityLogger::log('Visit Deleted', 'Visit', $visit->id, 'Visit record deleted', $oldValues, null);

        return redirect()->route('visits.index')->with('success', app()->getLocale() === 'ar' ? 'تم حذف الزيارة.' : 'Visit deleted.');
    }
}
