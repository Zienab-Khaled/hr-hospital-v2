<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\WrittenCommitment;
use App\Models\NonCommitmentReport;
use App\Models\ContactReport;
use App\Models\DebtInventory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardApiController extends Controller
{
    use AuthorizesRequests;

    /**
     * Search for patients for the dashboard modals
     */
    public function searchPatients(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $patients = Patient::where('name', 'LIKE', "%{$query}%")
            ->orWhere('name_ar', 'LIKE', "%{$query}%")
            ->orWhere('file_number', 'LIKE', "%{$query}%")
            ->orWhere('identity_value', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'name_ar', 'file_number', 'identity_value']);

        return response()->json($patients);
    }

    /**
     * Store a new Written Commitment
     */
    public function storeWrittenCommitment(Request $request)
    {
        $this->authorize('procedures.written_commitment');

        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $valid['commitment_date'] = now();
        $valid['status'] = 'pending';
        $valid['created_by'] = auth()->id();

        WrittenCommitment::create($valid);

        return response()->json(['success' => true, 'message' => __('Commitment saved successfully.')]);
    }

    /**
     * Store a new Non-Commitment Report
     */
    public function storeNonCommitmentReport(Request $request)
    {
        $this->authorize('procedures.non_commitment');

        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'notes' => 'nullable|string',
        ]);

        $valid['report_date'] = now();
        $valid['created_by'] = auth()->id();

        NonCommitmentReport::create($valid);

        return response()->json(['success' => true, 'message' => __('Report saved successfully.')]);
    }

    /**
     * Store a new Contact Report
     */
    public function storeContactReport(Request $request)
    {
        $this->authorize('procedures.contact_report');

        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'result' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $valid['contact_date'] = now();
        $valid['created_by'] = auth()->id();

        ContactReport::create($valid);

        return response()->json(['success' => true, 'message' => __('Contact report saved successfully.')]);
    }

    /**
     * Store a new Debt Inventory
     */
    public function storeDebtInventory(Request $request)
    {
        $this->authorize('procedures.debt_inventory');

        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'total_debt' => 'required|numeric|min:0',
            'details' => 'nullable|string',
        ]);

        $valid['inventory_date'] = now();
        $valid['created_by'] = auth()->id();

        DebtInventory::create($valid);

        return response()->json(['success' => true, 'message' => __('Inventory saved successfully.')]);
    }

    /**
     * Get invoices for a specific patient
     */
    public function getPatientInvoices(Request $request)
    {
        $patientId = $request->get('patient_id');
        if (!$patientId) {
            return response()->json([]);
        }

        $invoices = \App\Models\Invoice::where('patient_id', $patientId)
            ->latest()
            ->get(['id', 'invoice_number', 'total', 'status', 'created_at']);

        return response()->json($invoices);
    }

    /**
     * Get record details for Quick View
     */
    public function getRecordDetails(Request $request)
    {
        $type = $request->get('type');
        $id = $request->get('id');

        if (!$type || !$id) {
            return response()->json(['success' => false, 'message' => 'Missing parameters']);
        }

        $data = null;
        switch ($type) {
            case 'patient':
                $data = Patient::with(['insuranceCompany', 'charityEntity'])->find($id);
                break;
            case 'visit':
                $data = \App\Models\Visit::with(['patient', 'department', 'shift'])->find($id);
                break;
            case 'commitment':
                $data = WrittenCommitment::with('patient')->find($id);
                break;
            case 'non_commitment':
                $data = NonCommitmentReport::with('patient')->find($id);
                break;
            case 'contact':
                $data = ContactReport::with('patient')->find($id);
                break;
            case 'debt':
                $data = DebtInventory::with('patient')->find($id);
                break;
        }

        if (!$data) {
            \Illuminate\Support\Facades\Log::warning("Quick View: Record not found. Type: {$type}, ID: {$id}");
            return response()->json(['success' => false, 'message' => 'Record not found']);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Store a new patient via simplified modal
     */
    public function storePatient(Request $request)
    {
        $this->authorize('patients.create');

        $valid = $request->validate([
            'name' => 'required|string|max:255',
            'identity_value' => 'required|string|max:255|unique:patients,identity_value',
            'identity_type' => 'required|string',
            'phone' => 'nullable|string',
            'payment_type' => 'required|in:cash,insurance,charity',
        ]);

        $valid['file_number'] = 'F-' . date('Ymd') . '-' . rand(1000, 9999);
        $valid['created_by'] = auth()->id();

        $patient = Patient::create($valid);

        return response()->json([
            'success' => true,
            'message' => __('Patient registered successfully.'),
            'patient' => $patient
        ]);
    }

    /**
     * Store a new visit via simplified modal
     */
    public function storeVisit(Request $request)
    {
        $this->authorize('visits.create');

        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'department_id' => 'required|exists:departments,id',
            'case_type' => 'required|in:clinics,emergency',
        ]);

        // Find active shift
        $shift = \App\Models\Shift::where('is_active', true)
            ->whereTime('start_time', '<=', now()->format('H:i:s'))
            ->whereTime('end_time', '>=', now()->format('H:i:s'))
            ->first();

        if (!$shift) {
            $shift = \App\Models\Shift::where('is_active', true)->first();
        }

        $visit = \App\Models\Visit::create([
            'patient_id' => $valid['patient_id'],
            'department_id' => $valid['department_id'],
            'shift_id' => $shift ? $shift->id : null,
            'visit_date' => now(),
            'case_type' => $valid['case_type'],
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Visit registered successfully.'),
            'visit_id' => $visit->id
        ]);
    }
}
