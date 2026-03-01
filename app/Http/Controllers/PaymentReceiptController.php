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
            'payment_method' => 'required|string|in:cash,card,bank_transfer,cheque,insurance,charity',
            'patient_cash_amount' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'ministry_receipt_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'physical_receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'collector_screenshot' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'exists:invoice_items,id',
        ]);

        if (isset($validated['patient_cash_amount']) && (float) $validated['patient_cash_amount'] > (float) $validated['amount']) {
            return back()->withErrors(['patient_cash_amount' => __('Cash amount from patient cannot exceed total payment.')])->withInput();
        }
        $validated['patient_cash_amount'] = isset($validated['patient_cash_amount']) ? (float) $validated['patient_cash_amount'] : null;

        $invoice = Invoice::with(['patient', 'items.service', 'payments.receipt'])->findOrFail($validated['invoice_id']);

        // المبلغ الفعلي اللي يُسجّل كدفعة على الفاتورة: لو دخل "المبلغ اللي يدفعه المريض كاش" نستخدمه، وإلا المبلغ الكامل
        $amountToRecord = ($validated['patient_cash_amount'] !== null && $validated['patient_cash_amount'] > 0)
            ? (float) $validated['patient_cash_amount']
            : (float) $validated['amount'];

        // المتبقي على المريض = حصة المريض − المدفوع (لو فيه تغطية تأمين)، وإلا إجمالي الفاتورة − المدفوع
        $hasCoverage = $invoice->items->contains(fn ($i) => !empty($i->insurance_coverage_type));
        $effectiveTotalDue = $hasCoverage
            ? (float) $invoice->items->sum(fn ($i) => (float) $i->patient_amount)
            : (float) $invoice->total_amount;
        $effectiveRemaining = max(0, round($effectiveTotalDue - (float) $invoice->paid_amount, 2));

        if ($amountToRecord > $effectiveRemaining) {
            return back()->withErrors(['amount' => __('Amount cannot exceed the remaining balance.')])->withInput();
        }

        // Prepare selected items data for receipt snapshot
        $selectedItemsData = [];
        if (!empty($validated['item_ids'])) {
            $selectedItems = \App\Models\InvoiceItem::whereIn('id', $validated['item_ids'])->with('service')->get();
            foreach ($selectedItems as $item) {
                $selectedItemsData[] = [
                    'id' => $item->id,
                    'code' => $item->service?->code ?? '—',
                    'name' => $item->service?->name_ar ?? $item->service?->name ?? '—',
                    'qty' => $item->quantity,
                    'unit_price' => (float)$item->unit_price,
                    'total' => (float)$item->patient_amount,
                ];
            }
        }

        DB::beginTransaction();
        try {
            // 1. Create Payment record (بالمبلغ الفعلي اللي يُخصم من الفاتورة)
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_type' => $validated['payment_method'],
                'amount' => $amountToRecord,
                'received_date' => now(),
                'received_by' => auth()->user()->id,
                'approved_by' => auth()->user()->id, // Auto-approve manual payment receipt
                'approved_at' => now(),
                'reference_no' => $validated['reference_number'],
                'status' => 'approved',
                'notes' => $validated['notes'],
                'audit_status' => 'matched',
            ]);

            // 2. Create Payment Receipt (amount = المبلغ المسجّل فعلاً؛ total_payment_amount = إجمالي الدفعة للعرض إن وُجد)
            $newRemainingForReceipt = max(0, round($effectiveTotalDue - ($invoice->paid_amount + $amountToRecord), 2));
            $receipt = PaymentReceipt::create([
                'payment_id' => $payment->id,
                'patient_id' => $invoice->patient_id,
                'ministry_receipt_number' => $validated['ministry_receipt_number'] ?? null,
                'amount' => $amountToRecord,
                'patient_cash_amount' => $validated['patient_cash_amount'],
                'total_payment_amount' => ($validated['patient_cash_amount'] !== null && $validated['patient_cash_amount'] > 0) ? (float) $validated['amount'] : null,
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'invoice_snapshot_total' => $invoice->total_amount,
                'invoice_snapshot_paid' => $invoice->paid_amount + $amountToRecord,
                'invoice_snapshot_remaining' => $newRemainingForReceipt,
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

            // 4. Update Invoice: المتبقي = حصة المريض − المدفوع (حتى يتسق مع عرض "المتبقي عليه")
            $newPaidAmount = $invoice->paid_amount + $amountToRecord;
            $newRemainingAmount = max(0, round($effectiveTotalDue - $newPaidAmount, 2));

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newRemainingAmount <= 0 ? 'paid' : 'pending',
                'debt_status' => $newRemainingAmount <= 0 ? 'paid' : $invoice->debt_status,
            ]);

            DB::commit();

            ActivityLogger::log('Payment Recorded', 'Invoice', $invoice->id, "Payment of {$amountToRecord} recorded via {$validated['payment_method']}. services: " . collect($selectedItemsData)->pluck('name')->implode(', '));

            // Notify Accountants with ALL invoice file links
            $accountants = \App\Models\User::role('accountant')->get();
            if ($accountants->isNotEmpty()) {
                $fileLinks = $invoice->getAllRelatedMediaUrls();
                $messagePrefix = app()->getLocale() === 'ar' ? 'تم تحصيل دفعة جديدة للفاتورة: ' : 'New payment collected for invoice: ';

                \Illuminate\Support\Facades\Notification::send($accountants, new \App\Notifications\SystemNotification([
                    'title' => app()->getLocale() === 'ar' ? '✅ تم تحصيل مبلع' : '✅ Payment Collected',
                    'message' => $messagePrefix . " {$invoice->invoice_number} | " . (app()->getLocale() === 'ar' ? 'المبلغ: ' : 'Amount: ') . number_format($amountToRecord, 2),
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
        $manager = \App\Models\User::getManagerForSignature();
        $settingsData = [
            'logo' => \App\Models\Setting::get('logo'),
            'hospital_name' => \App\Models\Setting::get('hospital_name', 'مستشفى'),
            'hospital_name_en' => \App\Models\Setting::get('hospital_name_en'),
            'health_cluster_name' => \App\Models\Setting::get('health_cluster_name', ''),
            'stamp' => \App\Models\Setting::get('stamp'),
            'manager_name' => \App\Models\Setting::get('manager_name', ''),
            'manager_signature' => \App\Models\Setting::get('manager_signature'),
            'financial_dept_name' => \App\Models\Setting::get('financial_dept_name', 'إدارة الموارد المالية'),
        ];

        return view('invoices.print-receipt', compact('receipt', 'invoice', 'settingsData', 'manager'));
    }
}
