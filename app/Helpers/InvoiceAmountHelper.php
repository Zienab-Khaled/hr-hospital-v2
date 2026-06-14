<?php

namespace App\Helpers;

use App\Models\Invoice;

/**
 * مزامنة إجمالي الفاتورة مع بنودها.
 * للفواتير النقدية (payment_type = cash): تقريب إجمالي المبلغ إلى ريال صحيح حسب طلب التشغيل:
 * إذا كانت هللات الكسر (0–99) أكبر من 51 → يُرفع للريال التالي؛ إذا كانت ≤ 51 → يُقص للأسفل إلى ريال صحيح.
 */
final class InvoiceAmountHelper
{
    /** @internal */
    public static function roundHalalahSumToWholeRiyalHalalah(int $sumHalalah): int
    {
        if ($sumHalalah <= 0) {
            return 0;
        }
        $riyals = intdiv($sumHalalah, 100);
        $remainder = $sumHalalah % 100;
        if ($remainder <= 51) {
            return $riyals * 100;
        }

        return ($riyals + 1) * 100;
    }

    /** تقريب مبلغ بالريال (كاش) إلى أقرب ريال صحيح بنفس قاعدة الهللات أعلاه. */
    public static function roundCashRiyalToWhole(float $amountRiyal): float
    {
        $h = (int) round(max(0.0, $amountRiyal) * 100);

        return round(self::roundHalalahSumToWholeRiyalHalalah($h) / 100, 2);
    }

    public static function syncInvoiceTotalsFromItems(Invoice $invoice): void
    {
        $invoice->unsetRelation('items');
        $items = $invoice->items()->orderBy('id')->get();
        if ($items->isEmpty()) {
            return;
        }

        $isCash = ($invoice->payment_type ?? '') === 'cash';

        if ($isCash) {
            $lineRows = [];
            $sumHalalah = 0;
            foreach ($items as $it) {
                $h = (int) round((float) $it->total_price * 100);
                $h = max(0, $h);
                $lineRows[] = ['model' => $it, 'h' => $h];
                $sumHalalah += $h;
            }

            $targetHalalah = self::roundHalalahSumToWholeRiyalHalalah($sumHalalah);
            $deltaHalalah = $targetHalalah - $sumHalalah;

            if ($deltaHalalah !== 0) {
                for ($i = count($lineRows) - 1; $i >= 0 && $deltaHalalah !== 0; $i--) {
                    $nh = $lineRows[$i]['h'] + $deltaHalalah;
                    if ($nh >= 0) {
                        $lineRows[$i]['h'] = $nh;
                        $deltaHalalah = 0;
                        break;
                    }
                    $deltaHalalah = $nh;
                    $lineRows[$i]['h'] = 0;
                }

                if ($deltaHalalah === 0) {
                    foreach ($lineRows as $row) {
                        $total = round($row['h'] / 100, 2);
                        $m = $row['model'];
                        $qty = max(1, (int) $m->quantity);
                        $m->update([
                            'total_price' => $total,
                            'unit_price' => round($total / $qty, 2),
                        ]);
                    }
                }
            }
        }

        $invoice->unsetRelation('items');
        $invoice->load('items');
        $sum = round((float) $invoice->items->sum(fn ($i) => (float) $i->total_price), 2);
        $paid = (float) $invoice->paid_amount;

        $invoice->update([
            'total_amount' => $sum,
            'remaining_amount' => max(0, round($sum - $paid, 2)),
        ]);
    }

    /**
     * فواتير أحقية العلاج (خادم / سائق): إجمالي 0، نوع الفاتورة أحقية، لا تحصيل نقدي.
     */
    public static function applyTreatmentEligibilityZeroInvoice(Invoice $invoice): void
    {
        if (! \App\Models\Patient::isTreatmentEligibility($invoice->payment_type ?? null)) {
            return;
        }

        $invoice->unsetRelation('items');
        foreach ($invoice->items()->orderBy('id')->get() as $item) {
            $item->update([
                'total_price' => 0,
                'unit_price' => 0,
                'insurance_coverage_type' => 'percentage',
                'insurance_coverage_value' => 100,
            ]);
        }

        $notes = trim((string) ($invoice->notes ?? ''));
        if ($notes === '') {
            $notes = app()->getLocale() === 'ar' ? 'أحقية العلاج' : 'Treatment Eligibility';
        }

        $invoice->update([
            'total_amount' => 0,
            'paid_amount' => 0,
            'remaining_amount' => 0,
            'status' => 'paid',
            'invoice_type' => 'eligibility',
            'payment_type' => 'treatment_eligibility',
            'notes' => $notes,
        ]);
    }
}
