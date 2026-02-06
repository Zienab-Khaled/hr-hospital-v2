<?php

namespace App\Http\Controllers;

use App\Models\NonCommitmentReport;
use App\Models\Patient;
use Illuminate\Http\Request;

class NonCommitmentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('procedures.non_commitment');
        $reports = NonCommitmentReport::with('patient')->latest()->paginate(15);
        return view('non-commitment-reports.index', compact('reports'));
    }

    public function create()
    {
        $this->authorize('procedures.non_commitment');
        $patients = Patient::orderBy('name')->get();
        return view('non-commitment-reports.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $this->authorize('procedures.non_commitment');
        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'report_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        $valid['created_by'] = auth()->id();
        NonCommitmentReport::create($valid);
        return redirect()->route('non-commitment-reports.index')->with('success', __('Saved successfully.'));
    }
}
