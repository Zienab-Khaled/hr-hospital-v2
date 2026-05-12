<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\PaymentReceiptSplit;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentReceiptController extends Controller
{
    /** طرق الدفع المسموح بها في أسطر الإيصال. لا يجمع تأمين/جمعية مع طرق أخرى في نفس الإيصال. */
    private const SPLIT_METHODS = ['cash', 'card', 'bank_transfer', 'cheque', 'loyalty_points', 'insurance', 'charity'];

    private const SINGLE_METHODS = ['cash', 'card', 'bank_transfer', 'cheque', 'loyalty_points', 'insurance', 'charity'];

    public function store(Request $request)
    {
        $this->authorize('payments.create');

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => ['nullable', 'string', Rule::in(self::SINGLE_METHODS)],
            'split_lines' => 'nullable|array|max:12',
            'split_lines.*.payment_method' => ['nullable', 'string', Rule::in(self::SPLIT_METHODS)],
            'split_lines.*.amount' => 'nullable|numeric|min:0.01',
            'split_lines.*.reference_number' => 'nullable|string|max:100',
            'patient_cash_amount' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'ministry_receipt_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'physical_receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'collector_screenshot' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'exists:invoice_items,id',
        ]);

        $invoice = Invoice::with(['patient', 'items.service', 'payments.receipt'])->findOrFail($validated['invoice_id']);

        $normalizedSplits = $this->normalizePaymentSplits($request, $validated);

        if ($normalizedSplits->isEmpty()) {
            return back()->withErrors(['split_lines' => app()->getLocale() === 'ar'
                ? 'أضف سطراً واحداً على الأقل لطرق الدفع والمبالغ.'
                : 'Add at least one payment line with method and amount.'])->withInput();
        }

        $methods = $normalizedSplits->pluck('payment_method');
        if ($methods->contains('insurance') || $methods->contains('charity')) {
            if ($normalizedSplits->count() !== 1) {
                return back()->withErrors(['split_lines' => app()->getLocale() === 'ar'
                    ? 'دفعة التأمين أو الجمعية يجب أن تكون في سطر واحد دون دمج مع طرق أخرى.'
                    : 'Insurance or charity payment must be a single line (cannot combine with other methods).'])->withInput();
            }
        }

        $sumSplits = round((float) $normalizedSplits->sum('amount'), 2);
        $declared = round((float) $validated['amount'], 2);
        if (abs($sumSplits - $declared) > 0.02) {
            return back()->withErrors(['amount' => app()->getLocale() === 'ar'
                ? 'مجموع طرق الدفع (' . number_format($sumSplits, 2) . ') يجب أن يساوي إجمالي الدفع (' . number_format($declared, 2) . ').'
                : 'Sum of payment lines (' . number_format($sumSplits, 2) . ') must equal total (' . number_format($declared, 2) . ').'])->withInput();
        }

        $amountToRecord = $sumSplits;

        if (isset($validated['patient_cash_amount']) && (float) $validated['patient_cash_amount'] > $amountToRecord) {
            return back()->withErrors(['patient_cash_amount' => __('Cash amount from patient cannot exceed total payment.')])->withInput();
        }
        $patientCashOpt = isset($validated['patient_cash_amount']) ? (float) $validated['patient_cash_amount'] : null;

        $hasCoverage = $invoice->items->contains(fn ($i) => ! empty($i->insurance_coverage_type));
        $isInsuranceOrCharity = in_array($invoice->payment_type ?? $invoice->patient?->payment_type ?? '', ['insurance', 'charity']);
        $totalPatientShare = ($hasCoverage || $isInsuranceOrCharity)
            ? (float) $invoice->items->sum(fn ($i) => (float) $i->patient_amount)
            : (float) $invoice->total_amount;
        $patientPaidSoFar = (float) $invoice->payments->whereNotIn('payment_type', ['insurance', 'charity'])->sum('amount');
        $effectiveRemainingPatient = max(0, round($totalPatientShare - $patientPaidSoFar, 2));

        if ($amountToRecord > $effectiveRemainingPatient) {
            return back()->withErrors(['amount' => __('Amount cannot exceed the remaining balance.')])->withInput();
        }

        $selectedItemsData = [];
        if (! empty($validated['item_ids'])) {
            $selectedItems = \App\Models\InvoiceItem::whereIn('id', $validated['item_ids'])->with('service')->get();
            foreach ($selectedItems as $item) {
                $desc = trim((string) ($item->description ?? ''));
                $lineName = $item->service?->name_ar
                    ?? $item->service?->name
                    ?? ($desc !== '' ? $desc : null)
                    ?? '—';
                $selectedItemsData[] = [
                    'id' => $item->id,
                    'code' => $item->service?->code ?? '—',
                    'name' => $lineName,
                    'qty' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total' => (float) $item->patient_amount,
                ];
            }
        }

        $paymentType = $normalizedSplits->count() > 1
            ? 'mixed'
            : ($normalizedSplits->first()['payment_method'] ?? 'cash');

        $receiptPaymentMethod = $normalizedSplits->count() > 1 ? 'mixed' : $paymentType;
        $cashFromSplits = round((float) $normalizedSplits->where('payment_method', 'cash')->sum('amount'), 2);

        if ($normalizedSplits->count() > 1) {
            $patientCashStored = null;
            $totalPaymentStored = null;
        } else {
            $patientCashStored = ($patientCashOpt !== null && $patientCashOpt > 0)
                ? $patientCashOpt
                : ($cashFromSplits > 0 ? $cashFromSplits : null);
            $totalPaymentStored = ($patientCashStored !== null && (float) $patientCashStored > 0 && (float) $patientCashStored < $amountToRecord)
                ? $amountToRecord
                : null;
        }

        $mergedRef = $normalizedSplits->pluck('reference_number')->filter()->implode(' / ');
        if ($mergedRef === '') {
            $mergedRef = $validated['reference_number'] ?? null;
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_type' => $paymentType,
                'amount' => $amountToRecord,
                'received_date' => now(),
                'received_by' => auth()->user()->id,
                'approved_by' => auth()->user()->id,
                'approved_at' => now(),
                'reference_no' => $mergedRef,
                'status' => 'approved',
                'notes' => $validated['notes'],
                'audit_status' => 'matched',
            ]);

            $newPaidTotal = $invoice->paid_amount + $amountToRecord;
            $newRemainingTotal = max(0, round((float) $invoice->total_amount - $newPaidTotal, 2));

            $receipt = PaymentReceipt::create([
                'payment_id' => $payment->id,
                'patient_id' => $invoice->patient_id,
                'ministry_receipt_number' => $validated['ministry_receipt_number'] ?? null,
                'amount' => $amountToRecord,
                'patient_cash_amount' => $patientCashStored,
                'total_payment_amount' => $totalPaymentStored,
                'payment_method' => $receiptPaymentMethod,
                'reference_number' => $mergedRef,
                'invoice_snapshot_total' => $invoice->total_amount,
                'invoice_snapshot_paid' => $newPaidTotal,
                'invoice_snapshot_remaining' => $newRemainingTotal,
                'collected_by' => auth()->user()->id,
                'collected_at' => now(),
                'notes' => $validated['notes'],
                'selected_items' => $selectedItemsData,
            ]);

            foreach ($normalizedSplits->values() as $i => $line) {
                PaymentReceiptSplit::create([
                    'payment_receipt_id' => $receipt->id,
                    'payment_method' => $line['payment_method'],
                    'amount' => $line['amount'],
                    'reference_number' => $line['reference_number'],
                    'sort_order' => (int) $i,
                ]);
            }

            if ($request->hasFile('physical_receipt')) {
                $receipt->addMediaFromRequest('physical_receipt')
                    ->toMediaCollection('physical_receipt');
            }
            if ($request->hasFile('collector_screenshot')) {
                $receipt->addMediaFromRequest('collector_screenshot')
                    ->toMediaCollection('collector_screenshot');
            }

            $newPaidAmount = $invoice->paid_amount + $amountToRecord;
            $newRemainingAmount = max(0, round((float) $invoice->total_amount - $newPaidAmount, 2));

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newRemainingAmount <= 0 ? 'paid' : 'pending',
                'debt_status' => $newRemainingAmount <= 0 ? 'paid' : $invoice->debt_status,
            ]);

            DB::commit();

            $splitNote = $normalizedSplits->map(fn ($l) => $l['payment_method'] . ':' . $l['amount'])->implode(', ');
            ActivityLogger::log('Payment Recorded', 'Invoice', $invoice->id, "Payment of {$amountToRecord} recorded ({$splitNote}). services: " . collect($selectedItemsData)->pluck('name')->implode(', '));

            $accountants = \App\Models\User::role('accountant')->get();
            if ($accountants->isNotEmpty()) {
                $fileLinks = $invoice->getAllRelatedMediaUrls();
                $messagePrefix = app()->getLocale() === 'ar' ? 'تم تحصيل دفعة جديدة للفاتورة: ' : 'New payment collected for invoice: ';

                \Illuminate\Support\Facades\Notification::send($accountants, new \App\Notifications\SystemNotification([
                    'title' => app()->getLocale() === 'ar' ? '✅ تم تحصيل مبلغ' : '✅ Payment Collected',
                    'message' => $messagePrefix . " {$invoice->invoice_number} | " . (app()->getLocale() === 'ar' ? 'المبلغ: ' : 'Amount: ') . number_format($amountToRecord, 2),
                    'action_url' => route('payment-receipts.print', $receipt),
                    'type' => 'success',
                    'metadata' => ['links' => $fileLinks],
                ]));
            }

            return redirect()->route('payment-receipts.print', $receipt)->with('success', __('Payment recorded and receipt generated. Please print it.'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('PaymentReceipt store error: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Error recording payment: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{payment_method: string, amount: float, reference_number: ?string}>
     */
    private function normalizePaymentSplits(Request $request, array $validated): \Illuminate\Support\Collection
    {
        $rows = $request->input('split_lines', []);
        $out = collect();

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $m = $row['payment_method'] ?? null;
                $amt = isset($row['amount']) ? (float) $row['amount'] : 0;
                $ref = isset($row['reference_number']) ? trim((string) $row['reference_number']) : '';
                if ($m && in_array($m, self::SPLIT_METHODS, true) && $amt > 0) {
                    $out->push([
                        'payment_method' => $m,
                        'amount' => round($amt, 2),
                        'reference_number' => $ref !== '' ? $ref : null,
                    ]);
                }
            }
        }

        if ($out->isNotEmpty()) {
            return $out;
        }

        $singleMethod = $validated['payment_method'] ?? null;
        if ($singleMethod && in_array($singleMethod, self::SINGLE_METHODS, true)) {
            $amt = round((float) $validated['amount'], 2);
            $ref = isset($validated['reference_number']) ? trim((string) $validated['reference_number']) : '';
            if ($amt > 0) {
                $out->push([
                    'payment_method' => $singleMethod,
                    'amount' => $amt,
                    'reference_number' => $ref !== '' ? $ref : null,
                ]);
            }
        }

        return $out;
    }

    public function print(PaymentReceipt $receipt)
    {
        $this->authorize('invoices.view');

        $receipt->load(['payment.invoice.items.service', 'patient', 'collectedBy', 'splits']);
        $invoice = $receipt->payment->invoice;
        $rawItems = $receipt->selected_items;
        if (! is_array($rawItems)) {
            $rawItems = [];
        }
        $displaySelectedItems = [];
        foreach ($rawItems as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '' || $name === '—') {
                $iid = isset($item['id']) ? (int) $item['id'] : 0;
                $invItem = $iid ? $invoice->items->firstWhere('id', $iid) : null;
                if (! $invItem && $iid) {
                    $invItem = $invoice->items->firstWhere('service_id', $iid);
                }
                if ($invItem) {
                    $name = trim((string) ($invItem->service?->name_ar ?? ''))
                        ?: trim((string) ($invItem->service?->name ?? ''))
                        ?: trim((string) ($invItem->description ?? ''));
                }
                if ($name === '') {
                    $name = '—';
                }
            }
            $displaySelectedItems[] = array_merge($item, ['name' => $name]);
        }
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

        return view('invoices.print-receipt', compact('receipt', 'invoice', 'settingsData', 'manager', 'displaySelectedItems'));
    }
}
