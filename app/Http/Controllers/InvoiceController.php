<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Visit;
use App\Models\Approval;
use App\Mail\ApprovalRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    public function create(Request $request)
    {
        $this->authorize('invoices.create');
        
        $patient = null;
        if ($request->has('patient_id')) {
            $patient = Patient::with(['insuranceCompany', 'charityEntity'])->findOrFail($request->get('patient_id'));
        }
        
        $patients = Patient::where('is_active', true)->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        
        return view('invoices.create', compact('patients', 'services', 'patient'));
    }
    
    public function store(Request $request)
    {
        $this->authorize('invoices.create');
        
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
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
            // Create visit for this invoice
            $patient = Patient::findOrFail($validated['patient_id']);
            $visit = Visit::create([
                'patient_id' => $patient->id,
                'visit_date' => $validated['invoice_date'],
                'notes' => 'Invoice created',
                'registered_by' => auth()->id(),
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
            
            // Create invoice items
            foreach ($validated['services'] as $serviceData) {
                $invoice->items()->create([
                    'service_id' => $serviceData['service_id'],
                    'quantity' => $serviceData['quantity'],
                    'unit_price' => $serviceData['unit_price'],
                    'total_price' => $serviceData['total_price'],
                    'description' => $serviceData['description'] ?? null,
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
                    'requested_by' => auth()->id(),
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
            return back()->withErrors(['error' => 'Error creating invoice: ' . $e->getMessage()])->withInput();
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
