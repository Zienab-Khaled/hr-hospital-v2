<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Visit;
use App\Models\Approval;
use App\Models\InsuranceCompany;
use App\Models\CharityEntity;
use App\Mail\ApprovalRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function create(Request $request)
    {
        $this->authorize('invoices.create');

        $patient = null;
        $patientId = $request->get('patient_id') ?? old('patient_id');
        if ($patientId) {
            $patient = Patient::with(['insuranceCompany', 'charityEntity'])->find($patientId);
        }

        $patients = Patient::where('is_active', true)->orderBy('name')->get();
        $insuranceCompanies = InsuranceCompany::orderBy('name')->get();
        $charityEntities = CharityEntity::orderBy('name')->get();

        return view('invoices.create', compact('patients', 'patient', 'insuranceCompanies', 'charityEntities'));
    }

    /** بحث المرضى للفاتورة: اسم / رقم هوية / رقم فيزا / رقم ملف / هاتف */
    public function searchPatients(Request $request)
    {
        $this->authorize('invoices.create');

        $q = $request->get('q', '');
        $q = trim($q);
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $patients = Patient::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('name_ar', 'like', "%{$q}%")
                    ->orWhere('file_number', 'like', "%{$q}%")
                    ->orWhere('identity_value', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('sponsor_name', 'like', "%{$q}%")
                    ->orWhere('sponsor_phone', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'name_ar' => $p->name_ar,
                    'file_number' => $p->file_number,
                    'identity_value' => $p->identity_value,
                    'phone' => $p->phone,
                    'payment_type' => $p->payment_type,
                ];
            });

        return response()->json($patients);
    }

    /** بحث الخدمات بالاسم أو الكود (لصفحة إنشاء الفاتورة بدون تحميل الكل). */
    public function searchServices(Request $request)
    {
        $this->authorize('invoices.create');

        $q = $request->get('q', '');
        $q = trim($q);
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $services = Service::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('name_ar', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'name_ar' => $s->name_ar,
                    'code' => $s->code,
                    'default_price' => (float) $s->default_price,
                    'is_multi_session' => (bool) $s->is_multi_session,
                    'session_count' => (int) ($s->session_count ?: 1),
                ];
            });

        return response()->json($services);
    }

    public function store(Request $request)
    {
        $this->authorize('invoices.create');

        Log::info('Invoice store request', [
            'has_patient_id' => $request->has('patient_id'),
            'patient_id' => $request->input('patient_id'),
            'services_count' => is_array($request->input('services')) ? count($request->input('services')) : 0,
            'services_keys' => is_array($request->input('services')) ? array_keys($request->input('services')) : [],
        ]);

        try {
            $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'referral_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|numeric|min:1',
            'services.*.unit_price' => 'required|numeric|min:0',
            'services.*.total_price' => 'required|numeric|min:0',
            'services.*.description' => 'nullable|string',
            // Optional patient updates from invoice form
            'patient_name' => 'nullable|string|max:255',
            'patient_name_ar' => 'nullable|string|max:255',
            'patient_file_number' => 'nullable|string|max:100',
            'patient_identity_type' => 'nullable|string|max:50',
            'patient_identity_value' => 'nullable|string|max:100',
            'patient_phone' => 'nullable|string|max:50',
            'patient_age' => 'nullable|integer|min:0|max:150',
            'patient_gender' => 'nullable|string|max:20',
            'patient_country_of_origin' => 'nullable|string|max:100',
            'patient_sponsor_name' => 'nullable|string|max:255',
            'patient_sponsor_phone' => 'nullable|string|max:50',
            'patient_payment_type' => 'nullable|string|in:cash,insurance,charity',
            'patient_insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'patient_charity_entity_id' => 'nullable|exists:charity_entities,id',
        ]);

        DB::beginTransaction();
        try {
            $patient = Patient::findOrFail($validated['patient_id']);

            // Update patient if form sent editable fields (including payment type)
            $identityType = $validated['patient_identity_type'] ?? null;
            $gender = $validated['patient_gender'] ?? null;
            $paymentType = $validated['patient_payment_type'] ?? null;
            $patientUpdates = array_filter([
                'name' => $validated['patient_name'] ?? null,
                'name_ar' => $validated['patient_name_ar'] ?? null,
                'file_number' => $validated['patient_file_number'] ?? null,
                'identity_type' => $identityType && array_key_exists($identityType, Patient::identityTypeOptions()) ? $identityType : null,
                'identity_value' => $validated['patient_identity_value'] ?? null,
                'phone' => $validated['patient_phone'] ?? null,
                'age' => isset($validated['patient_age']) ? (int) $validated['patient_age'] : null,
                'gender' => in_array($gender, ['male', 'female'], true) ? $gender : null,
                'country_of_origin' => $validated['patient_country_of_origin'] ?? null,
                'sponsor_name' => $validated['patient_sponsor_name'] ?? null,
                'sponsor_phone' => $validated['patient_sponsor_phone'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');
            if (in_array($paymentType, ['cash', 'insurance', 'charity'], true)) {
                $patientUpdates['payment_type'] = $paymentType;
                $patientUpdates['insurance_company_id'] = $paymentType === 'insurance' ? ($validated['patient_insurance_company_id'] ?? null) : null;
                $patientUpdates['charity_entity_id'] = $paymentType === 'charity' ? ($validated['patient_charity_entity_id'] ?? null) : null;
            }
            if (!empty($patientUpdates)) {
                $patient->update($patientUpdates);
            }

            // Create visit for this invoice
            $visit = Visit::create([
                'patient_id' => $patient->id,
                'visit_date' => $validated['invoice_date'],
                'notes' => 'Invoice created',
                'referral_number' => $validated['referral_number'] ?? null,
                'registered_by' => auth()->user()?->getKey(),
            ]);

            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(Invoice::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total amount
            $totalAmount = collect($validated['services'])->sum('total_price');

            // Create invoice
            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                'visit_id' => $visit->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['invoice_date'],
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'remaining_amount' => $totalAmount,
                'deposit_amount' => 0,
                'status' => 'pending',
                'notes' => $validated['notes'],
            ]);

            // Create invoice items (quantity: ensure integer for DB)
            foreach ($validated['services'] as $serviceData) {
                $invoice->items()->create([
                    'service_id' => (int) $serviceData['service_id'],
                    'quantity' => (int) round((float) $serviceData['quantity']),
                    'unit_price' => (float) $serviceData['unit_price'],
                    'total_price' => (float) $serviceData['total_price'],
                    'description' => isset($serviceData['description']) && $serviceData['description'] !== '' ? (string) $serviceData['description'] : null,
                ]);
            }

            // Create approval request for insurance/charity patients
            if (in_array($patient->payment_type, ['insurance', 'charity'])) {
                $approval = Approval::create([
                    'invoice_id' => $invoice->id,
                    'patient_id' => $patient->id,
                    'approval_type' => $patient->payment_type,
                    'insurance_company_id' => $patient->insurance_company_id,
                    'charity_entity_id' => $patient->charity_entity_id,
                    'requested_amount' => $totalAmount,
                    'status' => 'pending',
                    'requested_by' => auth()->user()?->getKey(),
                ]);

                // Get email recipient
                $recipientEmail = null;
                if ($patient->payment_type === 'insurance' && $patient->insuranceCompany) {
                    $recipientEmail = $patient->insuranceCompany->email;
                } elseif ($patient->payment_type === 'charity' && $patient->charityEntity) {
                    $recipientEmail = $patient->charityEntity->email;
                }

                // Send approval request email
                if ($recipientEmail) {
                    try {
                        Mail::to($recipientEmail)->send(new ApprovalRequestMail($approval));
                    } catch (\Exception $e) {
                        // Log error but don't fail the invoice creation
                        \Log::error('Failed to send approval email: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            $message = in_array($patient->payment_type, ['insurance', 'charity'])
                ? __('Invoice created and approval request sent successfully.')
                : __('Invoice created successfully.');

            return redirect()->route('invoices.show', $invoice)->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice store exception: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['error' => 'Error creating invoice: ' . $e->getMessage()])->withInput();
        }
        } catch (ValidationException $e) {
            Log::warning('Invoice store validation failed', ['errors' => $e->errors()]);
            throw $e;
        }
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('invoices.view');

        $invoice->load([
            'patient.insuranceCompany',
            'patient.charityEntity',
            'items.service',
            'payments',
            'visit'
        ]);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('invoices.edit');

        $invoice->load('items.service');
        $patients = Patient::where('is_active', true)->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'patients', 'services'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('invoices.edit');

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'notes' => 'nullable|string',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.unit_price' => 'required|numeric|min:0',
            'services.*.total_price' => 'required|numeric|min:0',
            'services.*.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Calculate total amount
            $totalAmount = collect($validated['services'])->sum('total_price');

            // Update invoice
            $invoice->update([
                'invoice_date' => $validated['invoice_date'],
                'total_amount' => $totalAmount,
                'remaining_amount' => $totalAmount - $invoice->paid_amount,
                'notes' => $validated['notes'],
            ]);

            // Delete old items and create new ones
            $invoice->items()->delete();
            foreach ($validated['services'] as $serviceData) {
                $invoice->items()->create([
                    'service_id' => $serviceData['service_id'],
                    'quantity' => $serviceData['quantity'],
                    'unit_price' => $serviceData['unit_price'],
                    'total_price' => $serviceData['total_price'],
                    'description' => $serviceData['description'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)->with('success', __('Invoice updated successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating invoice: ' . $e->getMessage()])->withInput();
        }
    }
}
