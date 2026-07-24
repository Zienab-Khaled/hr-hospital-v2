<?php

namespace App\Support;

use App\Models\Department;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * إحصائيات الإيرادات حسب القسم الطبي (التخصص).
 *
 * مسار الدخول (عيادات/طوارئ) ≠ التخصص الطبي (عيون / باطنية / مختبر…).
 * الإيراد يُنسب للتخصص إن وُجد، وإلا لمسار الدخول / قسم البند.
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
            ->keyBy(fn (Department $d) => (int) $d->id);

        if ($medicalDepts->isEmpty()) {
            return collect();
        }

        /** @var list<int> $medicalIds */
        $medicalIds = $medicalDepts->keys()->map(fn ($id) => (int) $id)->all();
        $genericDepartmentIds = $medicalDepts
            ->filter(fn (Department $dept) => $dept->isGenericEntryDepartment())
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        $payments = Payment::query()
            ->whereNotNull('approved_by')
            ->whereBetween('received_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->with([
                'invoice.items.service.department',
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

            // مهم: وحّد النوع int — لو فشل الربط يضيع المبلغ ويظهر الكل 0
            $deptId = $deptId !== null ? (int) $deptId : null;

            if (! $deptId || ! isset($totals[$deptId])) {
                // آخر شبكة أمان: أول بند فاتورة بقسم طبي
                $itemDept = (int) ($payment->invoice?->items?->first()?->department_id
                    ?? $payment->invoice?->items?->first()?->service?->department_id
                    ?? 0);
                if ($itemDept && isset($totals[$itemDept])) {
                    $deptId = $itemDept;
                }
            }

            if (! $deptId || ! isset($totals[$deptId])) {
                continue;
            }

            $totals[$deptId] += (float) $payment->amount;
            if ($payment->invoice_id) {
                $invoiceIds[$deptId][(int) $payment->invoice_id] = true;
            }
        }

        return $medicalDepts->map(function (Department $dept) use ($totals, $invoiceIds) {
            $id = (int) $dept->id;
            $total = (float) ($totals[$id] ?? 0);
            $count = count($invoiceIds[$id] ?? []);

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
                'id' => $id,
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
     * تحديد القسم الطبي للفاتورة.
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
        $visit = $invoice->relationLoaded('visit') ? $invoice->visit : $invoice->visit()->with(['department', 'transferredDepartment', 'eligibilityPrintDepartment'])->first();

        $isMedical = static fn (?int $id): bool => $id > 0 && isset($medicalLookup[$id]);
        $isSpecialized = static fn (?int $id): bool => $isMedical($id) && ! isset($genericLookup[$id]);

        // 1) تحويل متخصص
        $transferred = (int) ($visit?->transferred_department_id ?? 0);
        if ($isSpecialized($transferred)) {
            return $transferred;
        }

        // 2) تخصص الزيارة (عيون / باطنية / مختبر…)
        $visitDept = (int) ($visit?->department_id ?? 0);
        if ($isSpecialized($visitDept)) {
            return $visitDept;
        }

        // 3) بنود الفاتورة / الخدمات — المتخصص أولاً
        $byDept = [];
        $items = $invoice->relationLoaded('items')
            ? $invoice->items
            : $invoice->items()->with('service.department')->get();

        foreach ($items as $item) {
            foreach ([
                $item->department_id ?? null,
                $item->service?->department_id ?? null,
            ] as $candidate) {
                $candidate = (int) ($candidate ?? 0);
                if ($isMedical($candidate)) {
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

        // 5) case_type = اسم القسم
        $case = trim((string) ($visit?->case_type ?? ''));
        if ($case !== '') {
            $matched = (int) (Department::query()
                ->whereIn('id', $medicalIds)
                ->where(function ($q) use ($case) {
                    $q->where('name_ar', $case)->orWhere('name', $case);
                })
                ->value('id') ?? 0);
            if ($isSpecialized($matched)) {
                return $matched;
            }
            if ($isMedical($matched)) {
                return $matched;
            }
        }

        // 6) أي قسم طبي من البنود (بما فيها العيادات/الطوارئ)
        if ($byDept !== []) {
            return (int) array_key_first($byDept);
        }

        // 7) قسم الزيارة / الأحقية لو طبي (حتى لو عيادات)
        foreach ([$visitDept, $eligibilityDepartmentId, $transferred] as $candidate) {
            if ($isMedical((int) $candidate)) {
                return (int) $candidate;
            }
        }

        // 8) مسار الدخول → العيادات / الطوارئ (لما الزيارة على استقبال إداري ومفيش بند)
        $admission = $visit?->admission_entry_source;
        if ($admission === Visit::ADMISSION_EMERGENCY || $admission === Visit::ADMISSION_OUTPATIENT_CLINICS) {
            foreach ($genericLookup as $genericId => $_) {
                $dept = Department::find($genericId);
                if (! $dept) {
                    continue;
                }
                $blob = mb_strtolower(($dept->name_ar ?? '').' '.($dept->name ?? ''));
                if ($admission === Visit::ADMISSION_EMERGENCY
                    && (str_contains($blob, 'طوار') || str_contains($blob, 'emergency'))) {
                    return (int) $genericId;
                }
                if ($admission === Visit::ADMISSION_OUTPATIENT_CLINICS
                    && (str_contains($blob, 'عيادات') || str_contains($blob, 'outpatient'))) {
                    return (int) $genericId;
                }
            }
        }

        return null;
    }
}
