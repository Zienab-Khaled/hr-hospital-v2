<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Patient;
use App\Models\Shift;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * شاشة إنشاء زيارة: اختيار مريض (بحث أو إضافة جديد) ثم تسجيل دخول القسم ثم تحويل / إحقاق علاج / خدمات / فاتورة
     */
    public function create(Request $request)
    {
        $this->authorize('invoices.create');

        $currentShift = Shift::currentAt();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $myDepartment = auth()->user()?->employee?->department_id
            ? Department::find(auth()->user()->employee->department_id)
            : null;

        $patient = null;
        $visit = null;
        $patientId = $request->get('patient_id');
        $visitId = $request->get('visit_id');
        $registered = $request->boolean('registered');

        if ($patientId) {
            $patient = Patient::with(['department', 'insuranceCompany', 'charityEntity'])
                ->find($patientId);
            if ($visitId) {
                $visit = Visit::where('patient_id', $patientId)->find($visitId);
            } elseif ($patient) {
                $visit = $patient->visits()->whereDate('visit_date', today())->latest()->first();
            }
        }

        return view('visits.create', compact(
            'currentShift', 'departments', 'myDepartment', 'patient', 'visit', 'registered'
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
        $employee = $user?->employee;
        $departmentId = $employee?->department_id;
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
     * طباعة إحقاق علاج (placeholder — تفاصيل لاحقاً)
     */
    public function treatmentEligibilityPrint(Visit $visit)
    {
        $this->authorize('invoices.create');
        $visit->load(['patient', 'department', 'shift']);
        return view('visits.treatment-eligibility-print', compact('visit'));
    }
}
