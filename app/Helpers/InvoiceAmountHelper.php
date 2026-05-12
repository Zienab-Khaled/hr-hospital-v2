<?php

namespace App\Helpers;

use App\Models\Invoice;

/**
 * مزامنة إجمالي الفاتورة مع بنودها.
 * للفواتير النقدية (payment_type = cash): تقريب إجمالي المبلغ إلى ريال صحيح حسب قاعدة الهللات السعودية:
 * أقل من 50 هللة من الكسر تُهمل (للأسفل)، 50 هللة فأكثر تُعدّ ريالاً كاملاً (للأعلى).
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
        if ($remainder < 50) {
            return $riyals * 100;
        }

        return ($riyals + 1) * 100;
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
}
