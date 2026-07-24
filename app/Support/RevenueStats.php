<?php

namespace App\Support;

use App\Models\Department;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * إحصائيات الإيرادات حسب القسم الطبي الفعلي.
 *
 * مهم: مسار الدخول (عيادات/طوارئ) ≠ القسم الطبي.
 * لا ننسب أي إيراد إلى «العيادات الخارجية» فقط لأن مسار الدخول عيادات.
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
            ->filter(fn (Department $dept) => self::isGenericEntryDepartment($dept))
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
     * تحديد القسم الطبي المناسب لفاتورة/دفعة.
     *
     * الترتيب:
     * 1) التحويل أو القسم المتخصص المختار للزيارة
     * 2) قسم الأحقية المتخصص
     * 3) قسم الخدمة/بند الفاتورة المتخصص
     * 4) الأقسام العامة (عيادات خارجية/طوارئ) كحل أخير
     *
     * لا نستخدم مسار الدخول (outpatient_clinics) أبداً — هذا سبب ظهور «العيادات الخارجية» بالغلط.
     *
     * @param  list<int>  $medicalIds
     * @param  list<int>  $genericDepartmentIds
     */
    public static function resolveMedicalDepartmentId(
        ?Invoice $invoice,
        array $medicalIds,
        array $genericDepartmentIds = []
    ): ?int
    {
        if (! $invoice) {
            return null;
        }

        $medicalLookup = array_fill_keys(array_map('intval', $medicalIds), true);
        $genericLookup = array_fill_keys(array_map('intval', $genericDepartmentIds), true);
        $visit = $invoice->relationLoaded('visit') ? $invoice->visit : $invoice->visit;

        // 1) التحويل له الأولوية، ثم قسم الزيارة إذا كان متخصصاً.
        foreach ([
            $visit?->transferred_department_id,
            $visit?->department_id,
        ] as $candidate) {
            if ($candidate
                && isset($medicalLookup[(int) $candidate])
                && ! isset($genericLookup[(int) $candidate])) {
                return (int) $candidate;
            }
        }

        // 2) قسم الأحقية إذا كان متخصصاً.
        $eligibilityDepartmentId = (int) ($visit?->eligibility_print_department_id ?? 0);
        if ($eligibilityDepartmentId
            && isset($medicalLookup[$eligibilityDepartmentId])
            && ! isset($genericLookup[$eligibilityDepartmentId])) {
            return $eligibilityDepartmentId;
        }

        // 3) بنود الفاتورة / الخدمات، مع تفضيل القسم المتخصص.
        $byDept = [];
        $items = $invoice->relationLoaded('items') ? $invoice->items : $invoice->items()->with('service')->get();
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
                if (! isset($genericLookup[(int) $departmentId])) {
                    return (int) $departmentId;
                }
            }
        }

        // 4) case_type = اسم القسم
        $case = trim((string) ($visit?->case_type ?? ''));
        if ($case !== '') {
            $matched = Department::query()
                ->whereIn('id', $medicalIds)
                ->where(function ($q) use ($case) {
                    $q->where('name_ar', $case)->orWhere('name', $case);
                })
                ->value('id');
            if ($matched) {
                $matched = (int) $matched;
                if (! isset($genericLookup[$matched])) {
                    return $matched;
                }
            }
        }

        // 5) الأقسام العامة لا تُستخدم إلا عند عدم وجود أي قسم متخصص.
        foreach ([
            $eligibilityDepartmentId,
            $visit?->department_id,
            array_key_first($byDept),
        ] as $candidate) {
            if ($candidate && isset($medicalLookup[(int) $candidate])) {
                return (int) $candidate;
            }
        }

        return null;
    }

    private static function isGenericEntryDepartment(Department $department): bool
    {
        $blob = mb_strtolower(trim(
            ($department->name_ar ?? '').' '
            .($department->name ?? '').' '
            .($department->code ?? '')
        ));

        foreach (['العيادات الخارجية', 'outpatient', 'طوارئ', 'طوارى', 'emergency'] as $needle) {
            if (str_contains($blob, mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }
}
