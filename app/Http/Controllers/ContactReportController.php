<?php

namespace App\Http\Controllers;

use App\Models\ContactReport;
use App\Models\Patient;
use Illuminate\Http\Request;

class ContactReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('procedures.contact_report');
        $reports = ContactReport::with(['patient', 'employee', 'createdByUser'])->latest()->paginate(15);
        return view('contact-reports.index', compact('reports'));
    }

    public function create(Request $request)
    {
        $this->authorize('procedures.contact_report');
        
        // If patient_id is provided (from search), load that patient
        $patient = null;
        if ($request->has('patient_id')) {
            $patient = Patient::with(['insuranceCompany', 'charityEntity'])->findOrFail($request->get('patient_id'));
        }
        
        $patients = Patient::orderBy('name')->get();
        $users = \App\Models\User::where('is_active', true)->orderBy('name')->get();
        
        return view('contact-reports.create', compact('patients', 'patient', 'users'));
    }

    public function store(Request $request)
    {
        $this->authorize('procedures.contact_report');
        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'contact_date' => 'required|date',
            'result' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'referred_to' => 'nullable|exists:users,id',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
        
        $valid['created_by'] = auth()->id();
        $valid['employee_id'] = auth()->user()->employee_id ?? null;
        
        $report = ContactReport::create($valid);
        
        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $report->addMedia($document)->toMediaCollection('documents');
            }
        }
        
        // Handle patient papers (scanned data)
        if ($request->hasFile('patient_papers')) {
            foreach ($request->file('patient_papers') as $paper) {
                $report->addMedia($paper)->toMediaCollection('patient-papers');
            }
        }
        
        // Create a visit record for this contact
        $patient = Patient::find($valid['patient_id']);
        $visit = $patient->visits()->create([
            'visit_date' => $valid['contact_date'],
            'notes' => 'Contact Report: ' . ($valid['result'] ?? ''),
            'registered_by' => auth()->id(),
        ]);
        
        // Link visit to contact report
        $report->update(['visit_id' => $visit->id]);
        
        $message = $request->has('referred_to') ? 
            __('Contact report created and referred successfully.') : 
            __('Contact report created successfully.');
            
        return redirect()->route('contact-reports.index')->with('success', $message);
    }
}
