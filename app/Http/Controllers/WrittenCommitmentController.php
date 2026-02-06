<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\WrittenCommitment;
use Illuminate\Http\Request;

class WrittenCommitmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('procedures.written_commitment');
        $items = WrittenCommitment::with('patient')->latest()->paginate(15);
        return view('written-commitments.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('procedures.written_commitment');
        $patients = Patient::orderBy('name')->get();
        return view('written-commitments.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $this->authorize('procedures.written_commitment');
        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'amount' => 'required|numeric|min:0',
            'commitment_date' => 'required|date',
            'status' => 'nullable|in:pending,signed,fulfilled,breached',
            'notes' => 'nullable|string',
        ]);
        $valid['created_by'] = auth()->id();
        WrittenCommitment::create($valid);
        return redirect()->route('written-commitments.index')->with('success', __('Saved successfully.'));
    }
}
