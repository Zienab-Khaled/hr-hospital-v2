<?php

namespace App\Http\Controllers;

use App\Models\CharityEntity;
use App\Models\Department;
use App\Models\InsuranceCompany;
use App\Models\Patient;
use App\Models\PatientTransfer;
use App\Services\IdentityDocumentExtractor;
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
        $charityEntities = CharityEntity::orderBy('name')->get();
        return view('patients.create', compact('insuranceCompanies', 'charityEntities'));
    }

    public function store(Request $request)
    {
        $this->authorize('patients.create');
        $valid = $request->validate([
            'file_number' => 'required|string|max:50|unique:patients',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'identity_type' => 'required|in:national_id,visit_visa,iqama,passport,border_number,visa_number',
            'identity_value' => 'required|string|max:50|unique:patients,identity_value',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => 'nullable|in:male,female',
            'country_of_origin' => 'nullable|string|max:255',
            'current_location' => 'nullable|string|max:255',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_phone' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'payment_type' => 'required|in:cash,insurance,charity',
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
        }
        if ($valid['payment_type'] === 'charity') {
            $valid['insurance_company_id'] = null;
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

        return redirect()->route('invoices.create', ['patient_id' => $patient->id])
            ->with('success', __('Patient registered successfully. You can now add services and create an invoice.'));
    }

    public function show(Patient $patient)
    {
        $this->authorize('patients.view');

        $patient->load([
            'insuranceCompany',
            'charityEntity',
            'department',
            'transfers.fromDepartment',
            'transfers.toDepartment',
            'visits' => fn($q) => $q->latest()->limit(10),
            'invoices' => fn($q) => $q->latest()->limit(10),
            'contactReports' => fn($q) => $q->latest()->limit(5),
            'writtenCommitments' => fn($q) => $q->latest()->limit(5),
        ]);

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('patients.show', compact('patient', 'departments'));
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
        $insuranceCompanies = InsuranceCompany::orderBy('name')->get();
        $charityEntities = CharityEntity::orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('patients.edit', compact('patient', 'insuranceCompanies', 'charityEntities', 'departments'));
    }

    public function update(Request $request, Patient $patient)
    {
        $this->authorize('patients.edit');

        $valid = $request->validate([
            'file_number' => 'required|string|max:50|unique:patients,file_number,' . $patient->id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'identity_type' => 'required|in:national_id,visit_visa,iqama,passport,border_number,visa_number',
            'identity_value' => 'required|string|max:50|unique:patients,identity_value,' . $patient->id,
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => 'nullable|in:male,female',
            'country_of_origin' => 'nullable|string|max:255',
            'current_location' => 'nullable|string|max:255',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_phone' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'payment_type' => 'required|in:cash,insurance,charity',
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
        }
        if ($valid['payment_type'] === 'charity') {
            $valid['insurance_company_id'] = null;
        }

        $patient->update($valid);

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

        $patient->delete();

        return redirect()->route('patients.section.followup')->with('success', __('Patient deleted successfully.'));
    }
}
