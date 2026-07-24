<?php

namespace App\Support;

use App\Models\Department;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * إحصائيات الإيرادات — نسبة التحصيل للأقسام الطبية بشكل صحيح.
 *
 * الزيارات غالباً تُسجَّل على قسم المحصل (استقبال/دخول)، بينما القسم الطبي
 * يكون في بنود الفاتورة أو مسار الدخول (عيادات/طوارئ).
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
        $clinicsDeptId = self::findDeptIdByKeywords($medicalDepts, ['عيادات', 'clinic', 'outpatient']);
        $emergencyDeptId = self::findDeptIdByKeywords($medicalDepts, ['طوارئ', 'طوارى', 'emergency', 'e.r']);

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
                $clinicsDeptId,
                $emergencyDeptId
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
     * @param  list<int>  $medicalIds
     */
    public static function resolveMedicalDepartmentId(
        ?Invoice $invoice,
        array $medicalIds,
        ?int $clinicsDeptId,
        ?int $emergencyDeptId
    ): ?int {
        if (! $invoice) {
            return null;
        }

        $medicalLookup = array_fill_keys($medicalIds, true);

        // 1) بنود الفاتورة (كشفية دخول / خدمات) — الأدق
        $byDept = [];
        $items = $invoice->relationLoaded('items') ? $invoice->items : $invoice->items()->with('service')->get();
        foreach ($items as $item) {
            $candidates = array_filter([
                $item->department_id ?? null,
                $item->service?->department_id ?? null,
            ]);
            foreach ($candidates as $candidate) {
                $candidate = (int) $candidate;
                if (isset($medicalLookup[$candidate])) {
                    $byDept[$candidate] = ($byDept[$candidate] ?? 0) + (float) ($item->total_price ?? 0);
                }
            }
        }
        if ($byDept !== []) {
            arsort($byDept);

            return (int) array_key_first($byDept);
        }

        $visit = $invoice->relationLoaded('visit') ? $invoice->visit : $invoice->visit;

        // 2) تحويل / قسم طباعة الأحقية / قسم الزيارة إن كان طبياً
        foreach ([
            $visit?->transferred_department_id,
            $visit?->eligibility_print_department_id,
            $visit?->department_id,
        ] as $candidate) {
            if ($candidate && isset($medicalLookup[(int) $candidate])) {
                return (int) $candidate;
            }
        }

        // 3) مسار الدخول: عيادات خارجية / طوارئ
        $source = $visit?->admission_entry_source;
        if ($source === Visit::ADMISSION_EMERGENCY && $emergencyDeptId) {
            return $emergencyDeptId;
        }
        if ($source === Visit::ADMISSION_OUTPATIENT_CLINICS && $clinicsDeptId) {
            return $clinicsDeptId;
        }

        // 4) case_type كنص (أحياناً اسم القسم)
        $case = mb_strtolower(trim((string) ($visit?->case_type ?? '')));
        if ($case !== '') {
            if ($emergencyDeptId && self::blobHasAny($case, ['طوارئ', 'طوارى', 'emergency', 'e.r'])) {
                return $emergencyDeptId;
            }
            if ($clinicsDeptId && self::blobHasAny($case, ['عيادات', 'clinic', 'outpatient'])) {
                return $clinicsDeptId;
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, Department>  $medicalDepts
     * @param  list<string>  $keywords
     */
    private static function findDeptIdByKeywords(Collection $medicalDepts, array $keywords): ?int
    {
        $found = $medicalDepts->first(function (Department $dept) use ($keywords) {
            $blob = mb_strtolower(trim(($dept->name_ar ?? '').' '.($dept->name ?? '').' '.($dept->code ?? '')));

            return self::blobHasAny($blob, $keywords);
        });

        return $found?->id;
    }

    /** @param  list<string>  $needles */
    private static function blobHasAny(string $blob, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($blob, mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }
}
