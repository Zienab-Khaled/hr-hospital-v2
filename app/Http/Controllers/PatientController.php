<?php

namespace App\Http\Controllers;

use App\Models\CharityEntity;
use App\Models\InsuranceCompany;
use App\Models\Patient;
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
        
        // Search in all identity fields
        $patient = Patient::where(function($query) use ($identity) {
            $query->where('id_number', $identity)
                  ->orWhere('passport_number', $identity)
                  ->orWhere('iqama_number', $identity);
        })
        ->with(['insuranceCompany', 'charityEntity', 'visits'])
        ->first();
        
        return view('patients.search', compact('patient'));
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
            'id_number' => 'nullable|string|max:50|unique:patients',
            'passport_number' => 'nullable|string|max:50|unique:patients',
            'iqama_number' => 'nullable|string|max:50|unique:patients',
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
        
        // Ensure at least one identity document is provided
        if (empty($valid['id_number']) && empty($valid['passport_number']) && empty($valid['iqama_number'])) {
            return back()->withErrors(['id_number' => 'At least one identity document (ID/Passport/Iqama) is required.'])->withInput();
        }
        
        if ($valid['payment_type'] === 'insurance') {
            $valid['charity_entity_id'] = null;
        }
        if ($valid['payment_type'] === 'charity') {
            $valid['insurance_company_id'] = null;
        }
        $valid['is_active'] = true;
        
        $patient = Patient::create($valid);
        
        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $patient->addMedia($document)->toMediaCollection('documents');
            }
        }
        
        return redirect()->route('patients.search')->with('success', __('Patient registered successfully.'));
    }
    
    public function show(Patient $patient)
    {
        $this->authorize('patients.view');
        
        $patient->load([
            'insuranceCompany', 
            'charityEntity', 
            'visits' => fn($q) => $q->latest()->limit(10),
            'invoices' => fn($q) => $q->latest()->limit(10),
            'contactReports' => fn($q) => $q->latest()->limit(5),
            'writtenCommitments' => fn($q) => $q->latest()->limit(5),
        ]);
        
        return view('patients.show', compact('patient'));
    }
}
