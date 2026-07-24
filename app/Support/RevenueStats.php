<?php

namespace App\Support;

use App\Models\Department;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * إحصائيات الإيرادات حسب القسم الطبي (التخصص).
 *
 * مسار الدخول (عيادات/طوارئ) ≠ التخصص الطبي (تنويم باطنية / مختبر / أورام…).
 * الإيراد يُنسب للتخصص، حتى لو الكشفية صادرة من العيادات الخارجية.
 */
final class RevenueStats
{
    /**
     * أداء الأقسام الطبية للفترة (للوحة التقارير و PDF).
     *
     * @return Collection<int, object{id:int,name:string,name_ar:string,name_en:string,total:float,count:int,patient_count:int,color:string,level:string}>
     */
    public static function departmentPerformance(Carbon $start, Carbon $end): Collection
    {
        $medicalDepts = Department::query()
            ->where('category', 'medical')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        if ($medicalDepts->isEmpty()) {
            return collect();
        }

        $medicalIds = $medicalDepts->keys()->all();
        $genericDepartmentIds = $medicalDepts
            ->filter(fn (Department $dept) => $dept->isGenericEntryDepartment())
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        $payments = Payment::query()
            ->whereNotNull('approved_by')
            ->whereBetween('received_date', [$start->toDateString(), $end->toDateString()])
            ->with([
                'invoice.items.service',
                'invoice.visit.department',
                'invoice.visit.transferredDepartment',
                'invoice.visit.eligibilityPrintDepartment',
            ])
            ->get();

        $totals = [];
        $invoiceIds = [];
        foreach ($medicalIds as $id) {
            $totals[$id] = 0.0;
            $invoiceIds[$id] = [];
        }

        foreach ($payments as $payment) {
            $deptId = self::resolveMedicalDepartmentId(
                $payment->invoice,
                $medicalIds,
                $genericDepartmentIds
            );

            if (! $deptId || ! array_key_exists($deptId, $totals)) {
                continue;
            }

            $totals[$deptId] += (float) $payment->amount;
            if ($payment->invoice_id) {
                $invoiceIds[$deptId][$payment->invoice_id] = true;
            }
        }

        return $medicalDepts->map(function (Department $dept) use ($totals, $invoiceIds) {
            $total = (float) ($totals[$dept->id] ?? 0);
            $count = count($invoiceIds[$dept->id] ?? []);

            $color = '#fee2e2';
            $level = 'low';
            if ($total >= 10000) {
                $color = '#22c55e';
                $level = 'high';
            } elseif ($total >= 2000) {
                $color = '#fbbf24';
                $level = 'medium';
            }

            $nameAr = $dept->name_ar ?? $dept->name;
            $nameEn = $dept->name ?? $dept->name_ar;

            return (object) [
                'id' => $dept->id,
                'name' => $nameAr,
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'total' => $total,
                'count' => $count,
                'patient_count' => $count,
                'color' => $color,
                'level' => $level,
            ];
        })->sortByDesc('total')->values();
    }

    /**
     * تحديد التخصص الطبي للفاتورة.
     *
     * الترتيب:
     * 1) قسم التحويل المتخصص
     * 2) قسم الزيارة المختار (تنويم باطنية…) — المصدر الأساسي
     * 3) بنود الفاتورة المتخصصة (بعد ختم الكشفية بتخصص الزيارة)
     * 4) قسم الأحقية المتخصص / case_type
     * 5) العيادات/الطوارئ فقط كحل أخير
     *
     * @param  list<int>  $medicalIds
     * @param  list<int>  $genericDepartmentIds
     */
    public static function resolveMedicalDepartmentId(
        ?Invoice $invoice,
        array $medicalIds,
        array $genericDepartmentIds = []
    ): ?int {
        if (! $invoice) {
            return null;
        }

        $medicalLookup = array_fill_keys(array_map('intval', $medicalIds), true);
        $genericLookup = array_fill_keys(array_map('intval', $genericDepartmentIds), true);
        $visit = $invoice->visit;

        $isSpecialized = static function (?int $id) use ($medicalLookup, $genericLookup): bool {
            return $id
                && isset($medicalLookup[$id])
                && ! isset($genericLookup[$id]);
        };

        // 1) التحويل المتخصص
        $transferred = (int) ($visit?->transferred_department_id ?? 0);
        if ($isSpecialized($transferred)) {
            return $transferred;
        }

        // 2) تخصص الزيارة المختار صراحةً (تنويم باطنية / مختبر / أورام…)
        $visitDept = (int) ($visit?->department_id ?? 0);
        if ($isSpecialized($visitDept)) {
            return $visitDept;
        }

        // 3) بنود الفاتورة — فضّل المتخصص (الكشفية تُختم بتخصص الزيارة)
        $byDept = [];
        $items = $invoice->relationLoaded('items')
            ? $invoice->items
            : $invoice->items()->with('service')->get();
        foreach ($items as $item) {
            foreach (array_filter([
                $item->department_id ?? null,
                $item->service?->department_id ?? null,
            ]) as $candidate) {
                $candidate = (int) $candidate;
                if (isset($medicalLookup[$candidate])) {
                    $byDept[$candidate] = ($byDept[$candidate] ?? 0) + (float) ($item->total_price ?? 0);
                }
            }
        }
        if ($byDept !== []) {
            arsort($byDept);
            foreach (array_keys($byDept) as $departmentId) {
                if ($isSpecialized((int) $departmentId)) {
                    return (int) $departmentId;
                }
            }
        }

        // 4) قسم الأحقية المتخصص
        $eligibilityDepartmentId = (int) ($visit?->eligibility_print_department_id ?? 0);
        if ($isSpecialized($eligibilityDepartmentId)) {
            return $eligibilityDepartmentId;
        }

        // 5) case_type = اسم التخصص
        $case = trim((string) ($visit?->case_type ?? ''));
        if ($case !== '') {
            $matched = Department::query()
                ->whereIn('id', $medicalIds)
                ->where(function ($q) use ($case) {
                    $q->where('name_ar', $case)->orWhere('name', $case);
                })
                ->value('id');
            if ($matched && $isSpecialized((int) $matched)) {
                return (int) $matched;
            }
        }

        // 6) آخر حل: العيادات/الطوارئ العامة
        foreach ([
            $visitDept,
            $eligibilityDepartmentId,
            array_key_first($byDept),
        ] as $candidate) {
            $candidate = (int) ($candidate ?? 0);
            if ($candidate && isset($medicalLookup[$candidate])) {
                return $candidate;
            }
        }

        return null;
    }
}
