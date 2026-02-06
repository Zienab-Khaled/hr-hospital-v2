<?php

namespace App\Http\Controllers;

use App\Models\CharityEntity;
use App\Models\InsuranceCompany;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
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
            'file_number' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'id_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'payment_type' => 'required|in:cash,insurance,charity',
            'insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'charity_entity_id' => 'nullable|exists:charity_entities,id',
            'notes' => 'nullable|string',
        ]);
        if ($valid['payment_type'] === 'insurance') {
            $valid['charity_entity_id'] = null;
        }
        if ($valid['payment_type'] === 'charity') {
            $valid['insurance_company_id'] = null;
        }
        $valid['is_active'] = true;
        Patient::create($valid);
        return redirect()->route('patients.index')->with('success', __('Saved successfully.'));
    }
}
