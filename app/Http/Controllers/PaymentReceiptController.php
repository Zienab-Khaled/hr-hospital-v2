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
            'payment_method' => 'required|string|in:cash,card,bank_transfer,cheque',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'physical_receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'collector_screenshot' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'exists:invoice_items,id',
        ]);

        $invoice = Invoice::with(['patient', 'items.service', 'payments.receipt'])->findOrFail($validated['invoice_id']);

        if ($validated['amount'] > $invoice->remaining_amount) {
            return back()->withErrors(['amount' => __('Amount cannot exceed the remaining balance.')])->withInput();
        }

        // Prepare selected items data for receipt snapshot
        $selectedItemsData = [];
        if (!empty($validated['item_ids'])) {
            $selectedItems = \App\Models\InvoiceItem::whereIn('id', $validated['item_ids'])->with('service')->get();
            foreach ($selectedItems as $item) {
                $selectedItemsData[] = [
                    'id' => $item->id,
                    'name' => $item->service?->name_ar ?? $item->service?->name ?? '—',
                    'qty' => $item->quantity,
                    'unit_price' => (float)$item->unit_price,
                    'total' => (float)$item->patient_amount,
                ];
            }
        }

        DB::beginTransaction();
        try {
            // 1. Create Payment record
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_type' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'received_date' => now(),
                'received_by' => auth()->user()->id,
                'approved_by' => auth()->user()->id, // Auto-approve manual payment receipt
                'approved_at' => now(),
                'reference_no' => $validated['reference_number'],
                'status' => 'approved',
                'notes' => $validated['notes'],
                'audit_status' => 'matched',
            ]);

            // 2. Create Payment Receipt
            $receipt = PaymentReceipt::create([
                'payment_id' => $payment->id,
                'patient_id' => $invoice->patient_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'invoice_snapshot_total' => $invoice->total_amount,
                'invoice_snapshot_paid' => $invoice->paid_amount + $validated['amount'],
                'invoice_snapshot_remaining' => $invoice->remaining_amount - $validated['amount'],
                'collected_by' => auth()->user()->id,
                'collected_at' => now(),
                'notes' => $validated['notes'],
                'selected_items' => $selectedItemsData,
            ]);

            // 3. Handle Media Uploads
            if ($request->hasFile('physical_receipt')) {
                $receipt->addMediaFromRequest('physical_receipt')
                    ->toMediaCollection('physical_receipt');
            }
            if ($request->hasFile('collector_screenshot')) {
                $receipt->addMediaFromRequest('collector_screenshot')
                    ->toMediaCollection('collector_screenshot');
            }

            // 4. Update Invoice
            $newPaidAmount = $invoice->paid_amount + $validated['amount'];
            $newRemainingAmount = $invoice->remaining_amount - $validated['amount'];

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newRemainingAmount <= 0 ? 'paid' : 'pending',
            ]);

            DB::commit();

            ActivityLogger::log('Payment Recorded', 'Invoice', $invoice->id, "Payment of {$validated['amount']} recorded via {$validated['payment_method']}. services: " . collect($selectedItemsData)->pluck('name')->implode(', '));

            // Notify Accountants with ALL invoice file links
            $accountants = \App\Models\User::role('accountant')->get();
            if ($accountants->isNotEmpty()) {
                $fileLinks = $invoice->getAllRelatedMediaUrls();
                $messagePrefix = app()->getLocale() === 'ar' ? 'تم تحصيل دفعة جديدة للفاتورة: ' : 'New payment collected for invoice: ';

                \Illuminate\Support\Facades\Notification::send($accountants, new \App\Notifications\SystemNotification([
                    'title' => app()->getLocale() === 'ar' ? '✅ تم تحصيل مبلع' : '✅ Payment Collected',
                    'message' => $messagePrefix . " {$invoice->invoice_number} | " . (app()->getLocale() === 'ar' ? 'المبلغ: ' : 'Amount: ') . number_format($validated['amount'], 2),
                    'action_url' => route('payment-receipts.print', $receipt),
                    'type' => 'success',
                    'metadata' => ['links' => $fileLinks] // This allows the notification system to show document links
                ]));
            }

            return redirect()->route('payment-receipts.print', $receipt)->with('success', __('Payment recorded and receipt generated. Please print it.'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('PaymentReceipt store error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error recording payment: ' . $e->getMessage()])->withInput();
        }
    }

    public function print(PaymentReceipt $receipt)
    {
        $this->authorize('invoices.view');

        $receipt->load(['payment.invoice', 'patient', 'collectedBy']);
        $invoice = $receipt->payment->invoice;
        $settings = \App\Models\Setting::first();
        $manager = \App\Models\User::getManagerForSignature();

        return view('invoices.print-receipt', compact('receipt', 'invoice', 'settings', 'manager'));
    }
}
