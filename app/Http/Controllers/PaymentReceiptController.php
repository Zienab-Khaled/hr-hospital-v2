<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentReceiptController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('invoices.edit');

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        if ($validated['amount'] > $invoice->remaining_amount) {
            return back()->withErrors(['amount' => __('Amount cannot exceed the remaining balance.')])->withInput();
        }

        DB::beginTransaction();
        try {
            // 1. Create Payment record
            $paymentType = $invoice->patient->payment_type ?? 'cash';
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_type' => $paymentType,
                'amount' => $validated['amount'],
                'received_date' => now(),
                'received_by' => auth()->user()->id,
                'reference_no' => $validated['reference_number'],
                'status' => 'pending', // Pending approval if needed
                'notes' => $validated['notes'],
            ]);

            // 2. Create Payment Receipt
            $receipt = PaymentReceipt::create([
                'payment_id' => $payment->id,
                'patient_id' => $invoice->patient_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'collected_by' => auth()->user()->id,
                'collected_at' => now(),
                'notes' => $validated['notes'],
            ]);

            // 3. Update Invoice
            $newPaidAmount = $invoice->paid_amount + $validated['amount'];
            $newRemainingAmount = $invoice->remaining_amount - $validated['amount'];

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newRemainingAmount <= 0 ? 'paid' : 'pending',
            ]);

            DB::commit();

            ActivityLogger::log('Payment Recorded', 'Invoice', $invoice->id, "Payment of {$validated['amount']} recorded via {$validated['payment_method']}", null, $receipt->toArray());

            return redirect()->route('invoices.show', $invoice)->with('success', __('Payment recorded successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error recording payment: ' . $e->getMessage()])->withInput();
        }
    }
}
