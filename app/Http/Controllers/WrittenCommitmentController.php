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

    public function create(Request $request)
    {
        $this->authorize('procedures.written_commitment');

        $patient = null;
        $invoice = null;

        if ($request->has('patient_id')) {
            $patient = Patient::with(['insuranceCompany', 'charityEntity'])->findOrFail($request->get('patient_id'));
        }

        if ($request->has('invoice_id')) {
            $invoice = \App\Models\Invoice::with('items')->findOrFail($request->get('invoice_id'));
        }

        $patients = Patient::orderBy('name')->get();
        return view('written-commitments.create', compact('patients', 'patient', 'invoice'));
    }

    public function store(Request $request)
    {
        $this->authorize('procedures.written_commitment');
        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'visit_id' => 'nullable|exists:visits,id',
            'amount' => 'required|numeric|min:0',
            'commitment_date' => 'required|date',
            'status' => 'required|in:pending,signed,refused',
            'signed_file_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'refusal_reason' => 'required_if:status,refused|nullable|string',
            'witness_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $valid['created_by'] = auth()->id();

        $commitment = WrittenCommitment::create($valid);

        // Handle signature file upload
        if ($request->hasFile('signed_file_path')) {
            $path = $request->file('signed_file_path')->store('commitments', 'public');
            $commitment->update(['signed_file_path' => $path]);
        }

        $message = $valid['status'] === 'signed'
            ? __('Commitment signed successfully.')
            : __('Commitment refusal recorded.');

        return redirect()->route('written-commitments.index')->with('success', $message);
    }

    public function print(WrittenCommitment $commitment)
    {
        $this->authorize('procedures.written_commitment');

        $commitment->load(['patient', 'createdByUser']);
        $settings = \App\Models\Setting::first();

        return view('written-commitments.print', compact('commitment', 'settings'));
    }
}
