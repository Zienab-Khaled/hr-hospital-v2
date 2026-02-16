<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Shift;
use App\Models\Visit;
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
                    'case_type' => 'clinics',
                    'notes' => null,
                    'registered_by' => auth()->user()->id,
                ]);
                $patient->update(['department_id' => $myDepartment->id]);
                $registered = true;
                session()->flash('success', app()->getLocale() === 'ar' ? 'تم إنشاء الزيارة وتسجيل دخول المريض للقسم.' : 'Visit created and patient registered to department.');
            }
        }

        $eligibilityDepartments = Department::where('is_active', true)
            ->where(function ($q) {
                $q->where('name_ar', 'LIKE', '%مختبر%')
                  ->orWhere('name_ar', 'LIKE', '%أشعة%')
                  ->orWhere('name_ar', 'LIKE', '%تنويم%')
                  ->orWhere('name_ar', 'LIKE', '%عيادات%')
                  ->orWhere('name_ar', 'LIKE', '%طوار%');
            })
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
        $user = auth()->user();
        $departmentId = $user?->department_id;
        $currentShift = Shift::currentAt();

        if (!$departmentId) {
            return back()->withErrors(['patient_id' => app()->getLocale() === 'ar'
                ? 'يجب أن يكون الموظف مرتبطاً بقسم لتسجيل زيارة.'
                : 'Employee must be assigned to a department.'])->withInput();
        }

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'department_id' => $departmentId,
            'visit_date' => today(),
            'shift_id' => $currentShift?->id,
            'case_type' => 'clinics',
            'notes' => null,
            'registered_by' => $user?->getKey(),
        ]);

        $patient->update(['department_id' => $departmentId]);

        return redirect()->route('visits.create', [
            'patient_id' => $patient->id,
            'visit_id' => $visit->id,
            'registered' => 1,
        ])->with('success', app()->getLocale() === 'ar' ? 'تم تسجيل دخول المريض للقسم وإنشاء الزيارة.' : 'Visit registered successfully.');
    }

    /**
     * بحث الخدمات حسب القسم (لأحقية العلاج — عيادة / مختبر / أشعة / تنويم / طوارئ)
     */
    public function searchServicesForEligibility(Request $request)
    {
        $this->authorize('invoices.create');
        $departmentId = $request->get('department_id');
        $q = trim((string) $request->get('q', ''));

        if (!$departmentId) {
            return response()->json([]);
        }

        $query = Service::where('is_active', true)->where('department_id', $departmentId);
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

        return view('visits.treatment-eligibility-print', compact('visit', 'services'));
    }

    /**
     * معالجة طباعة إحقاق العلاج (POST)
     */
    public function treatmentEligibilityPrintSubmit(Request $request, Visit $visit)
    {
        return $this->treatmentEligibilityPrint($request, $visit);
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

        $query = Visit::with(['patient', 'department', 'shift', 'registeredBy']);

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

    public function edit(Visit $visit)
    {
        $this->authorize('visits.delete'); // Using delete permission or similar admin permission
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $shifts = Shift::where('is_active', true)->orderBy('sort_order')->get();
        return view('visits.edit', compact('visit', 'departments', 'shifts'));
    }

    public function update(Request $request, Visit $visit)
    {
        $this->authorize('visits.delete');

        $valid = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'shift_id' => 'required|exists:shifts,id',
            'visit_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $visit->update($valid);

        // Update patient department if it's the latest visit? Maybe not needed for edit history.
        // But if we changed department, maybe we should update patient's current department?
        // Let's keep it simple: update visit record.

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
        $visit->update(['transferred_department_id' => $request->input('to_department_id')]);

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

        $visit->delete();
        return redirect()->route('visits.index')->with('success', app()->getLocale() === 'ar' ? 'تم حذف الزيارة.' : 'Visit deleted.');
    }
}
