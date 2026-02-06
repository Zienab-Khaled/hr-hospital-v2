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

    public function create()
    {
        $this->authorize('procedures.contact_report');
        $patients = Patient::orderBy('name')->get();
        return view('contact-reports.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $this->authorize('procedures.contact_report');
        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'contact_date' => 'required|date',
            'result' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);
        $valid['created_by'] = auth()->id();
        $valid['employee_id'] = auth()->user()->employee_id;
        ContactReport::create($valid);
        return redirect()->route('contact-reports.index')->with('success', __('Saved successfully.'));
    }
}
