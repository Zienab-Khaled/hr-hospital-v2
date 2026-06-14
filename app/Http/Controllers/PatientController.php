<?php

namespace App\Http\Controllers;

use App\Models\CharityEntity;
use App\Models\Department;
use App\Models\InsuranceCompany;
use App\Models\Patient;
use App\Models\PatientTransfer;
use App\Models\Shift;
use App\Models\Visit;
use App\Services\IdentityDocumentExtractor;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Search for patient by identity number (ID/Iqama/Passport)
     */
    public function search(Request $request)
    {
        $this->authorize('patients.view');

        if (!$request->has('identity')) {
            return view('patients.search');
        }

        $identity = $request->get('identity');

        $patient = Patient::where('identity_value', $identity)
            ->with(['insuranceCompany', 'charityEntity', 'visits'])
            ->first();

        if ($patient && auth()->user()->hasRole('insurance_clerk') && $patient->payment_type !== 'insurance') {
            $patient = null;
        }

        return view('patients.search', compact('patient'));
    }

    /**
     * Check if a patient with the given identity exists in our system and has insurance.
     * Used when creating a new patient with payment_type=insurance to suggest company from existing records.
     * Official CHI verification: https://www.chi.gov.sa/ServicesDirectory/Pages/Eservices-CheckInsurance.aspx
     */
    public function checkInsurance(Request $request)
    {
        $this->authorize('patients.view');
        $request->validate([
            'identity_value' => 'required|string|max:50',
        ]);
        $identityValue = $request->input('identity_value');
        $patient = Patient::where('identity_value', $identityValue)
            ->with('insuranceCompany')
            ->first();
        if (!$patient) {
            return response()->json([
                'found' => false,
                'message' => app()->getLocale() === 'ar' ? 'لا يوجد مريض مسجل بهذا الرقم في السجلات.' : 'No patient with this identity found in our records.',
            ]);
        }
        if (!$patient->insurance_company_id || !$patient->insuranceCompany) {
            return response()->json([
                'found' => true,
                'has_insurance' => false,
                'message' => app()->getLocale() === 'ar' ? 'المريض مسجل لكن بدون شركة تأمين في سجلاتنا.' : 'Patient is registered but has no insurance company in our records.',
            ]);
        }
        return response()->json([
            'found' => true,
            'has_insurance' => true,
            'insurance_company_id' => $patient->insurance_company_id,
            'insurance_company_name' => $patient->insuranceCompany->name,
            'message' => app()->getLocale() === 'ar'
                ? 'تم العثور على تأمين مسجل لهذا الرقم في السجلات.'
                : 'Insurance found in our records for this identity.',
        ]);
    }

    /**
     * Extract identity data (name, identity number) from an uploaded document image via OCR.
     * Used when creating a patient: scan/upload ID then auto-fill form; the same file can be re-uploaded with the form.
     */
    public function extractIdentityDocument(Request $request)
    {
        try {
            $this->authorize('patients.create');
            $request->validate([
                'document' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
            ]);
            $result = app(IdentityDocumentExtractor::class)->extract($request->file('document'));
            return response()->json($result);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'غير مصرح لك بهذا الإجراء.' : 'You are not authorized to perform this action.',
                'data' => [],
            ], 403);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'data' => [],
            ], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar'
                    ? 'حدث خطأ أثناء الاستخراج. تحقق من تثبيت Tesseract OCR (راجع docs/TESSERACT_SETUP.md).'
                    : 'Extraction error. Ensure Tesseract OCR is installed (see docs/TESSERACT_SETUP.md).',
                'data' => [],
            ], 500);
        }
    }

    public function create()
    {
        $this->authorize('patients.create');
        $insuranceCompanies = InsuranceCompany::orderBy('name')->get();
        $charityEntities = CharityEntity::orderByRaw('COALESCE(name_ar, name)')->get();
        return view('patients.create', compact('insuranceCompanies', 'charityEntities'));
    }

    /**
     * يجمّع حقول اليوم/الشهر/السنة في تاريخ ميلاد واحد بصيغة Y-m-d.
     * - لو الثلاثة فاضيين: يترك التاريخ فارغاً (null).
     * - لو ناقص جزء أو التاريخ غير صحيح (مثل 31 فبراير): يضع قيمة غير صالحة ليفشل التحقق.
     */
    private function mergeBirthDate(Request $request): void
    {
        $day = trim((string) $request->input('dob_day'));
        $month = trim((string) $request->input('dob_month'));
        $year = trim((string) $request->input('dob_year'));

        $filledCount = count(array_filter([$day, $month, $year], fn ($v) => $v !== ''));

        if ($filledCount === 0) {
            $request->merge(['date_of_birth' => null]);

            return;
        }

        if ($filledCount === 3 && checkdate((int) $month, (int) $day, (int) $year)) {
            $request->merge([
                'date_of_birth' => sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day),
            ]);

            return;
        }

        // جزء ناقص أو تركيب تاريخ غير صحيح → افشل التحقق برسالة تاريخ غير صالح
        $request->merge(['date_of_birth' => 'invalid-date']);
    }

    public function store(Request $request)
    {
        $this->authorize('patients.create');
        $this->mergeBirthDate($request);
        $valid = $request->validate([
            'file_number' => 'required|string|max:50|unique:patients',
            'name' => 'required|string|max:255',
            'name_ar_first' => 'required|string|max:255',
            'name_ar_father' => 'nullable|string|max:255',
            'name_ar_family' => 'required|string|max:255',
            'identity_type' => 'required|in:national_id,visit_visa,iqama,passport,border_number,visa_number',
            'identity_value' => 'required|string|max:50|unique:patients,identity_value',
            'date_of_birth' => 'required|date|before_or_equal:today|after:1900-01-01',
            'gender' => 'nullable|in:male,female',
            'country_of_origin' => 'nullable|string|max:255',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_phone' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'payment_type' => Patient::paymentTypeValidationRule(),
            'insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'charity_entity_id' => 'nullable|exists:charity_entities,id',
            'notes' => 'nullable|string',
        ]);

        if ($valid['identity_type'] === 'iqama') {
            if (empty($valid['sponsor_name'])) {
                return back()->withErrors(['sponsor_name' => 'Sponsor name is required for Iqama holders.'])->withInput();
            }
            if (empty($valid['sponsor_phone'])) {
                return back()->withErrors(['sponsor_phone' => 'Sponsor phone is required for Iqama holders.'])->withInput();
            }
        }

        if ($valid['payment_type'] === 'insurance') {
            $valid['charity_entity_id'] = null;
        } elseif ($valid['payment_type'] === 'charity') {
            $valid['insurance_company_id'] = null;
        } else {
            $valid['insurance_company_id'] = null;
            $valid['charity_entity_id'] = null;
        }
        $valid['is_active'] = true;

        if (auth()->user() && auth()->user()->employee && auth()->user()->employee->department_id) {
            $valid['department_id'] = auth()->user()->employee->department_id;
        }

        $patient = Patient::create($valid);

        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $patient->addMedia($document)->toMediaCollection('documents');
            }
        }

        ActivityLogger::log('Patient Created', 'Patient', $patient->id, 'Patient registered', null, $patient->toArray());

        // System Notification for Managers and Accountants
        $notifyUsers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['manager', 'admin', 'accountant']);
        })->get();
        if ($notifyUsers->isNotEmpty()) {
            Notification::send($notifyUsers, new SystemNotification([
                'title' => app()->getLocale() === 'ar' ? 'مريض جديد مسجل' : 'New Patient Registered',
                'message' => (app()->getLocale() === 'ar' ? 'تم تسجيل المريض: ' : 'Patient registered: ') . $patient->fullArabicName() . ' (#' . $patient->file_number . ')',
                'action_url' => route('patients.show', $patient),
                'type' => 'info',
            ]));
        }

        // إنشاء زيارة تلقائية للمريض والتوجيه لها
        $user = auth()->user();
        $departmentId = $patient->department_id ?? $user?->department_id;
        $currentShift = Shift::currentAt();
        $dept = $departmentId ? Department::find($departmentId) : null;

        $visit = Visit::create([
            'patient_id' => $patient->id,
            'department_id' => $departmentId,
            'visit_date' => now()->toDateString(),
            'shift_id' => $currentShift?->id,
            'case_type' => $dept ? ($dept->name_ar ?? $dept->name ?? 'reception') : 'reception',
            'notes' => null,
            'registered_by' => $user?->getKey(),
        ]);

        ActivityLogger::log('Visit Created', 'Visit', $visit->id, 'Visit auto-created on patient registration', null, $visit->toArray());

        return redirect()->route('visits.create', [
            'patient_id' => $patient->id,
            'visit_id' => $visit->id,
            'registered' => 1,
        ])->with('success', app()->getLocale() === 'ar'
            ? 'تم تسجيل المريض وإنشاء زيارة جديدة. تم التوجيه لشاشة الزيارة والخدمات.'
            : 'Patient registered and visit created. Redirected to visit services screen.');
    }

    public function show(Patient $patient)
    {
        $this->authorize('patients.view');
        if (auth()->user()->hasRole('insurance_clerk') && $patient->payment_type !== 'insurance') {
            abort(403);
        }

        $patient->load([
            'insuranceCompany',
            'charityEntity',
            'department',
            'transfers.fromDepartment',
            'transfers.toDepartment',
            'contactReports' => fn($q) => $q->latest()->limit(5),
        ]);

        $visits = $patient->visits()->with('department')->latest()->paginate(5, ['*'], 'visits_page');
        $invoices = $patient->invoices()->latest()->paginate(5, ['*'], 'invoices_page');

        // أقسام تم تنفيذ خدمات فيها لهذا المريض (عيادات، أشعة، مختبر، مركز أورام، ...)
        $completedDepartments = \App\Models\InvoiceItem::whereHas('invoice', fn ($q) => $q->where('patient_id', $patient->id))
            ->where('status', 'completed')
            ->with('service.department')
            ->get()
            ->pluck('service.department')
            ->filter()
            ->unique('id')
            ->values();

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('patients.show', compact('patient', 'departments', 'visits', 'invoices', 'completedDepartments'));
    }

    public function transfer(Request $request, Patient $patient)
    {
        $this->authorize('patients.edit');
        $request->validate([
            'to_department_id' => 'required|exists:departments,id',
            'notes' => 'nullable|string|max:500',
        ]);
        $toDepartmentId = (int) $request->input('to_department_id');
        $fromDepartmentId = $patient->department_id;
        if (! $fromDepartmentId) {
            return back()->withErrors(['to_department_id' => app()->getLocale() === 'ar' ? 'المريض غير مرتبط بقسم حالي. يمكن تعيين القسم من التعديل.' : 'Patient has no current department.'])->withInput();
        }
        if ($fromDepartmentId === $toDepartmentId) {
            return back()->withErrors(['to_department_id' => app()->getLocale() === 'ar' ? 'اختر قسماً مختلفاً عن القسم الحالي.' : 'Choose a different department.'])->withInput();
        }
        PatientTransfer::create([
            'patient_id' => $patient->id,
            'from_department_id' => $fromDepartmentId,
            'to_department_id' => $toDepartmentId,
            'transferred_at' => now(),
            'transferred_by' => auth()->user()?->getKey(),
            'notes' => $request->input('notes'),
        ]);
        $patient->update(['department_id' => $toDepartmentId]);
        return redirect()->route('patients.show', $patient)->with('success', app()->getLocale() === 'ar' ? 'تم تحويل المريض إلى القسم الجديد.' : 'Patient transferred successfully.');
    }

    public function edit(Patient $patient)
    {
        $this->authorize('patients.edit');
        if (auth()->user()->hasRole('insurance_clerk') && $patient->payment_type !== 'insurance') {
            abort(403);
        }
        $insuranceCompanies = InsuranceCompany::orderBy('name')->get();
        $charityEntities = CharityEntity::orderByRaw('COALESCE(name_ar, name)')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('patients.edit', compact('patient', 'insuranceCompanies', 'charityEntities', 'departments'));
    }

    public function update(Request $request, Patient $patient)
    {
        $this->authorize('patients.edit');
        if (auth()->user()->hasRole('insurance_clerk') && $patient->payment_type !== 'insurance') {
            abort(403);
        }

        $this->mergeBirthDate($request);
        $valid = $request->validate([
            'file_number' => 'required|string|max:50|unique:patients,file_number,' . $patient->id,
            'name' => 'required|string|max:255',
            'name_ar_first' => 'required|string|max:255',
            'name_ar_father' => 'nullable|string|max:255',
            'name_ar_family' => 'required|string|max:255',
            'identity_type' => 'required|in:national_id,visit_visa,iqama,passport,border_number,visa_number',
            'identity_value' => 'required|string|max:50|unique:patients,identity_value,' . $patient->id,
            'date_of_birth' => 'nullable|date|before_or_equal:today|after:1900-01-01',
            'gender' => 'nullable|in:male,female',
            'country_of_origin' => 'nullable|string|max:255',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_phone' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'payment_type' => Patient::paymentTypeValidationRule(),
            'insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'charity_entity_id' => 'nullable|exists:charity_entities,id',
            'department_id' => 'nullable|exists:departments,id',
            'notes' => 'nullable|string',
        ]);

        if ($valid['identity_type'] === 'iqama') {
            if (empty($valid['sponsor_name'])) {
                return back()->withErrors(['sponsor_name' => 'Sponsor name is required for Iqama holders.'])->withInput();
            }
            if (empty($valid['sponsor_phone'])) {
                return back()->withErrors(['sponsor_phone' => 'Sponsor phone is required for Iqama holders.'])->withInput();
            }
        }

        if ($valid['payment_type'] === 'insurance') {
            $valid['charity_entity_id'] = null;
        } elseif ($valid['payment_type'] === 'charity') {
            $valid['insurance_company_id'] = null;
        } else {
            $valid['insurance_company_id'] = null;
            $valid['charity_entity_id'] = null;
        }

        $oldValues = $patient->toArray();
        $patient->update($valid);

        ActivityLogger::log('Patient Updated', 'Patient', $patient->id, 'Patient details updated', $oldValues, $patient->toArray());

        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $patient->addMedia($document)->toMediaCollection('documents');
            }
        }

        return redirect()->route('patients.show', $patient)->with('success', __('Patient updated successfully.'));
    }

    public function destroy(Patient $patient)
    {
        $this->authorize('patients.delete');

        // Check if patient has related records
        if ($patient->visits()->exists() || $patient->invoices()->exists()) {
            return back()->with('error', __('Cannot delete patient with existing visits or invoices.'));
        }

        $oldValues = $patient->toArray();
        $patient->delete();

        ActivityLogger::log('Patient Deleted', 'Patient', $patient->id, 'Patient deleted', $oldValues, null);

        return redirect()->route('patients.section.followup')->with('success', __('Patient deleted successfully.'));
    }
}
