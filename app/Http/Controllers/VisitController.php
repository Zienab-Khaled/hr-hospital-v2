<?php

namespace App\Http\Controllers;

use App\Models\Department;
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
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;
use App\Traits\HasIndexFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VisitController extends Controller
{
    use HasIndexFilters;

    /**
     * شاشة إنشاء زيارة: اختيار مريض (بحث أو إضافة جديد) ثم تسجيل دخول القسم ثم تحويل / إحقاق علاج / خدمات / فاتورة
     */
    public function create(Request $request)
    {
        $this->authorize('invoices.create');

        $currentShift = Shift::currentAt();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
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
            ->orderBy('name')
            ->get();

        return view('visits.create', compact(
            'currentShift', 'departments', 'eligibilityDepartments', 'myDepartment', 'patient', 'visit', 'activeVisits', 'registered'
        ));
    }

    /**
     * تسجيل دخول المريض للقسم (تعيين قسم المريض + إنشاء زيارة للشيفت الحالي)
     */
    public function store(Request $request)
    {
        $this->authorize('invoices.create');

        $request->validate(['patient_id' => 'required|exists:patients,id']);

        $patient = Patient::findOrFail($request->input('patient_id'));

        // Validate charity approval document if patient is charity type
        $validationRules = ['patient_id' => 'required|exists:patients,id'];
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
    $notifyUsers = User::role(['manager', 'admin', 'accountant'])->get();
    if ($notifyUsers->isNotEmpty()) {
        $deptName = $visit->department->name_ar ?? $visit->department->name;
        Notification::send($notifyUsers, new SystemNotification([
            'title' => app()->getLocale() === 'ar' ? 'زيارة جديدة' : 'New Patient Visit',
            'message' => (app()->getLocale() === 'ar' ? "تم تسجيل زيارة للمريض: " : "Visit registered for patient: ") . ($patient->name_ar ?? $patient->name) . (app()->getLocale() === 'ar' ? " بلقسم: " : " in department: ") . $deptName,
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

        // Filter by department if provided
        if ($departmentId) {
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
     * طباعة إحقاق علاج — GET بدون خدمات، POST مع قائمة الخدمات المختارة
     */
    public function treatmentEligibilityPrint(Request $request, Visit $visit)
    {
        $this->authorize('invoices.create');
        $visit->load(['patient', 'department', 'shift']);

        $services = [];
        if ($request->isMethod('post') && $request->has('services')) {
            $services = is_array($request->input('services')) ? $request->input('services') : [];
        }

        // Default manager: System Manager or Revenue Manager
        $manager = User::getManagerForSignature();

        // If a specific department is selected for eligibility, use its manager
        $targetDepartment = null;
        $targetDepartmentName = null;
        if ($request->filled('department_id')) {
            $targetDepartment = Department::with('manager')->find($request->input('department_id'));
            if ($targetDepartment) {
                $targetDepartmentName = $targetDepartment->name_ar ?? $targetDepartment->name;
            }
        }

        // Capture the first service's department if targetDepartmentName is still null
        if (!$targetDepartmentName && !empty($services)) {
            $firstServiceId = $services[0]['service_id'] ?? null;
            if ($firstServiceId) {
                $firstService = Service::with('department')->find($firstServiceId);
                if ($firstService && $firstService->department) {
                    $targetDepartmentName = $firstService->department->name_ar ?? $firstService->department->name;
                }
            }
        }

        // Final fallback to the visit's home department
        if (!$targetDepartmentName) {
            $targetDepartmentName = $visit->department->name_ar ?? $visit->department->name ?? null;
        }

        // Update tracking flag and save services
        $visit->update([
            'printed_eligibility_at' => now(),
            'last_eligibility_services' => $services
        ]);

        // Auto-create Invoice if services are provided
        if (!empty($services)) {
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

                $invoice = Invoice::create([
                    'patient_id' => $patient->id,
                    'visit_id' => $visit->id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => now(),
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'remaining_amount' => $totalAmount,
                    'deposit_amount' => 0,
                    'status' => 'pending',
                    'notes' => app()->getLocale() === 'ar' ? 'أحقية علاج' : 'Treatment Eligibility',
                    'payment_type' => $finalPaymentType,
                    'invoice_type' => 'eligibility',
                ]);

                foreach ($services as $s) {
                    $invoice->items()->create([
                        'service_id' => $s['service_id'] ?? null,
                        'quantity' => (int) round((float) ($s['qty'] ?? $s['quantity'] ?? 1)),
                        'unit_price' => (float) ($s['unit_price'] ?? $s['price'] ?? 0),
                        'total_price' => (float) ($s['total'] ?? $s['total_price'] ?? 0),
                        'insurance_coverage_type' => isset($s['insurance_coverage_type']) && in_array($s['insurance_coverage_type'], ['percentage', 'fixed']) ? $s['insurance_coverage_type'] : null,
                        'insurance_coverage_value' => isset($s['insurance_coverage_value']) && $s['insurance_coverage_value'] !== '' ? (float) $s['insurance_coverage_value'] : null,
                        'description' => $s['name'] ?? null,
                    ]);
                }

                // Approval request
                if (in_array($patient->payment_type, ['insurance', 'charity'])) {
                    $approval = Approval::create([
                        'invoice_id' => $invoice->id,
                        'patient_id' => $patient->id,
                        'approval_type' => $patient->payment_type,
                        'insurance_company_id' => $patient->insurance_company_id,
                        'charity_entity_id' => $patient->charity_entity_id,
                        'requested_amount' => $totalAmount,
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
                            Mail::to($recipientEmail)->send(new ApprovalRequestMail($approval));
                        } catch (\Exception $e) {
                            Log::error('Failed to send auto-approval email: ' . $e->getMessage());
                        }
                    }
                }

                DB::commit();
                ActivityLogger::log('Invoice Auto-Created', 'Invoice', $invoice->id, 'Invoice created automatically from Eligibility Print for patient: ' . ($patient->name_ar ?? $patient->name), null, $invoice->toArray());
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Auto-invoice creation failed: ' . $e->getMessage());
            }
        }

        // Log the action
        ActivityLogger::log('Print Eligibility', 'Visit', $visit->id, 'Treatment eligibility form printed for patient: ' . ($visit->patient->name_ar ?? $visit->patient->name), null, null);

        return view('visits.treatment-eligibility-print', compact('visit', 'services', 'manager', 'targetDepartment', 'targetDepartmentName'));
    }

    /**
     * معالجة طباعة إحقاق العلاج (POST)
     */
    public function treatmentEligibilityPrintSubmit(Request $request, Visit $visit)
    {
        return $this->treatmentEligibilityPrint($request, $visit);
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
        ActivityLogger::log('Print Price Inquiry', 'Visit', $visit->id, 'Price inquiry form printed for patient: ' . ($visit->patient->name_ar ?? $visit->patient->name), null, ['title' => $printTitle]);

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
     * قائمة الزيارات
     */
    public function index(Request $request)
    {
        Gate::authorize('invoices.view'); // Or visits.view if exists, usually invoices.view is used for reception tasks

        $user = auth()->user();
        $isAdmin = $user->hasRole('admin') || $user->hasRole('manager');
        $currentShift = Shift::currentAt();

        // For admin/manager: if no filters at all, redirect with today + current shift as defaults
        if ($isAdmin && !$request->hasAny(['date', 'shift_id', 'department_id', 'search', 'insurance_company_id', 'registered_by', 'page'])) {
            $defaults = ['date' => today()->toDateString()];
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
            if ($request->filled('date')) {
                $query->whereDate('visit_date', $request->input('date'));
            }
        }

        $this->applyIndexFilters(
            $query,
            $request,
            ['patient.name', 'patient.name_ar', 'patient.file_number', 'patient.identity_value', 'notes'],
            ['shift_id' => 'shift_id', 'department_id' => 'department_id', 'registered_by' => 'registered_by'],
            ['insurance_company_id' => ['patient', 'insurance_company_id']]
        );

        $visits = $query->latest('visit_date')->latest('id')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        $shifts = Shift::where('is_active', true)->orderBy('sort_order')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $registrars = \App\Models\User::orderBy('name')->get();
        $insuranceCompanies = \App\Models\InsuranceCompany::where('is_active', true)->orderBy('name')->get();

        return view('visits.index', compact('visits', 'currentShift', 'isAdmin', 'shifts', 'departments', 'registrars', 'insuranceCompanies'));
    }

    public function show(Visit $visit)
    {
        $this->authorize('invoices.view');
        $visit->load(['patient', 'department', 'shift', 'registeredBy', 'invoices.items.service', 'invoices.payments']);

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

        $valid = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'visit_date' => 'nullable|date',
            'case_type' => 'required|string',
            'notes' => 'nullable|string',
        ]);

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
     * تحويل المريض من الزيارة الحالية لزيارة قسم آخر
     */
    public function transfer(Request $request, Visit $visit)
    {
        // 1. Validate
        $request->validate([
            'to_department_id' => 'required|exists:departments,id|different:department_id',
            'notes' => 'nullable|string|max:500',
        ]);

        // 2. Create PatientTransfer record (logging)
        // assuming PatientTransfer model exists and has these fields
        // If not, we might need to use existing logic in PatientController or replicate it
        \App\Models\PatientTransfer::create([
            'patient_id' => $visit->patient_id,
            'from_department_id' => $visit->department_id,
            'to_department_id' => $request->input('to_department_id'),
            'transferred_at' => now(),
            'transferred_by' => auth()->user()->id,
            'notes' => $request->input('notes'),
        ]);

        // 3. Update Patient current department
        $visit->patient->update(['department_id' => $request->input('to_department_id')]);

        // 4. Update Visit to flag as transferred
        $oldValues = $visit->getOriginal();
        $visit->update(['transferred_department_id' => $request->input('to_department_id')]);

        ActivityLogger::log('Patient Transferred', 'Visit', $visit->id, 'Patient transferred to another department', $oldValues, $visit->toArray());

        // 5. Redirect back with success
        return redirect()->route('visits.create', [
            'patient_id' => $visit->patient_id,
            'visit_id' => $visit->id,
            'registered' => 1
        ])->with('success', app()->getLocale() === 'ar' ? 'تم تحويل المريض بنجاح.' : 'Patient transferred successfully.');
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
