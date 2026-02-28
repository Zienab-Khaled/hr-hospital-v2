<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class RevenueWorkflowController extends Controller
{
    public function controlRoom(Request $request)
    {
        Gate::authorize('reports.view');

        $date = $request->input('date', Carbon::today()->toDateString());
        $shiftId = $request->input('shift_id');

        $query = Invoice::whereHas('payments', function($pq) use ($date) {
                $pq->whereDate('received_date', $date);
            })
            ->with(['patient', 'visit.shift', 'items.service', 'payments.receivedByUser', 'media', 'payments.receipt']);

        if ($shiftId) {
            $query->whereHas('visit', function ($q) use ($shiftId) {
                $q->where('shift_id', $shiftId);
            });
        }

        $invoices = $query->latest()->get();
        $shifts = Shift::where('is_active', true)->orderBy('sort_order')->get();

        // Calculate actual collection total for the selected date
        $totalCollectedToday = Payment::whereDate('received_date', $date)
            ->whereNotNull('approved_by')
            ->when($shiftId, function ($q) use ($shiftId) {
                $q->whereHas('invoice.visit', function($vq) use ($shiftId) {
                    $vq->where('shift_id', $shiftId);
                });
            })
            ->sum('amount');

        // ملخص المحاسب: عدد الفواتير + تم تأكيده + تم رفضه + التوتال لكل حالة (مرتبط بفلتر التاريخ والوردية)
        $controlRoomStats = [
            'total_count' => $invoices->count(),
            'total_amount' => (float) $invoices->sum('paid_amount'),
            'matched_count' => $invoices->where('audit_status', 'matched')->count(),
            'matched_amount' => (float) $invoices->where('audit_status', 'matched')->sum('paid_amount'),
            'rejected_count' => $invoices->where('audit_status', 'rejected')->count(),
            'rejected_amount' => (float) $invoices->where('audit_status', 'rejected')->sum('paid_amount'),
            'pending_count' => $invoices->where('audit_status', 'under_review')->count(),
            'pending_amount' => (float) $invoices->where('audit_status', 'under_review')->sum('paid_amount'),
        ];

        return view('revenue.control-room', compact('invoices', 'shifts', 'date', 'shiftId', 'totalCollectedToday', 'controlRoomStats'));
    }

    public function match(Invoice $invoice)
    {
        Gate::authorize('reports.view');

        DB::transaction(function() use ($invoice) {
            $invoice->update([
                'audit_status' => 'matched',
                'status' => 'approved'
            ]);

            // Also match any linked payments and ensure they are marked as approved for reports
            $invoice->payments()->update([
                'audit_status' => 'matched',
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', app()->getLocale() === 'ar' ? 'تمت المطابقة بنجاح (جاهز للتوريد).' : 'Matched successfully (Ready for deposit).');
    }

    public function reject(Request $request, Invoice $invoice)
    {
        Gate::authorize('reports.view');

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $invoice->update([
            'audit_status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason')
        ]);

        return back()->with('warning', app()->getLocale() === 'ar' ? 'تم رفض المعاملة وإعادتها للمحصل.' : 'Transaction rejected and returned to collector.');
    }

    public function markReadyForDeposit(Invoice $invoice)
    {
        Gate::authorize('reports.view');

        // Generate a 6-digit OTP for the cashier receipt phase
        $otp = (string) rand(100000, 999999);

        $invoice->update([
            'audit_status' => 'ready_for_deposit',
            'cashier_otp' => $otp
        ]);

        return back()->with('success', app()->getLocale() === 'ar'
            ? "تم اعتماد المعاملة للتوريد. رمز التحقق المرسل لأمين الصندوق هو: ($otp)"
            : "Invoice approved for deposit. OTP for cashier is: ($otp)");
    }

    /**
     * تبويب أمين الصندوق: قائمة الفواتير المُحوّلة بعد المطابقة + جاهزة للتوريد.
     */
    public function treasuryIndex(Request $request)
    {
        Gate::authorize('reports.view');

        $date = $request->input('date', Carbon::today()->toDateString());

        $matchedInvoices = Invoice::where('audit_status', 'matched')
            ->whereHas('payments', fn($q) => $q->whereDate('received_date', $date))
            ->with(['patient', 'visit.shift', 'payments.receipt'])
            ->latest()
            ->get();

        $readyForDepositInvoices = Invoice::where('audit_status', 'ready_for_deposit')
            ->whereHas('payments', fn($q) => $q->whereDate('received_date', $date))
            ->with(['patient', 'visit.shift', 'payments.receipt'])
            ->latest()
            ->get();

        $depositedInvoices = Invoice::where('audit_status', 'deposited')
            ->whereDate('deposited_at', $date)
            ->with(['patient', 'visit.shift', 'payments.receipt'])
            ->latest('deposited_at')
            ->get();

        // ملخص أمين الصندوق: عدد كل مرحلة + التوتال (مرتبط بفلتر التاريخ)
        $treasuryStats = [
            'matched_count' => $matchedInvoices->count(),
            'matched_amount' => (float) $matchedInvoices->sum('paid_amount'),
            'ready_count' => $readyForDepositInvoices->count(),
            'ready_amount' => (float) $readyForDepositInvoices->sum('paid_amount'),
            'deposited_count' => $depositedInvoices->count(),
            'deposited_amount' => (float) $depositedInvoices->sum('paid_amount'),
        ];

        return view('revenue.treasury.index', compact('matchedInvoices', 'readyForDepositInvoices', 'depositedInvoices', 'date', 'treasuryStats'));
    }

    /**
     * تم التوريد (إقفال من ناحية الإدارة).
     */
    public function markDeposited(Invoice $invoice)
    {
        Gate::authorize('reports.view');

        $invoice->update([
            'audit_status' => 'deposited',
            'deposited_at' => now(),
        ]);

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم تسجيل التوريد وإقفال المعاملة.'
            : 'Deposit recorded and transaction closed.');
    }

    /**
     * ملخص الإيرادات اليومي (نموذج موارد - ٤): ثلاث فترات، تصنيف شيكات مصدقة / نقاط بيع / نقدي، ونطاق أرقام الإيصالات.
     */
    public function dailyRevenueSummary(Request $request)
    {
        Gate::authorize('reports.view');

        $date = $request->input('date', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($date);

        $periods = [
            1 => [
                'label_ar' => 'الفترة الأولى (من ١٢ منتصف الليل إلى الثامنة صباحاً)',
                'start' => $carbonDate->copy()->startOfDay(),
                'end' => $carbonDate->copy()->setTime(7, 59, 59),
            ],
            2 => [
                'label_ar' => 'الفترة الثانية (من الثامنة صباحاً إلى الرابعة مساءً)',
                'start' => $carbonDate->copy()->setTime(8, 0, 0),
                'end' => $carbonDate->copy()->setTime(15, 59, 59),
            ],
            3 => [
                'label_ar' => 'الفترة الثالثة (من الرابعة مساءً إلى ١٢ منتصف الليل)',
                'start' => $carbonDate->copy()->setTime(16, 0, 0),
                'end' => $carbonDate->copy()->endOfDay(),
            ],
        ];

        $summary = [];
        foreach ($periods as $key => $period) {
            $receipts = PaymentReceipt::query()
                ->whereRaw('COALESCE(collected_at, created_at) BETWEEN ? AND ?', [$period['start'], $period['end']])
                ->get();
            $receiptNumbers = $receipts->pluck('receipt_number')->filter()->values();
            $byMethod = $receipts->groupBy('payment_method')->map->sum('amount');

            $collectorIds = $receipts->pluck('collected_by')->filter()->unique()->values()->all();
            $handoverNames = $collectorIds
                ? User::whereIn('id', $collectorIds)->get()->map(fn ($u) => $u->name_ar ?? $u->name)->filter()->unique()->values()->all()
                : [];

            $certifiedChecks = (float) ($byMethod['cheque'] ?? 0) + (float) ($byMethod['bank_transfer'] ?? 0);
            $pos = (float) ($byMethod['card'] ?? 0);
            $cash = (float) ($byMethod['cash'] ?? 0);
            $other = (float) ($byMethod['insurance'] ?? 0) + (float) ($byMethod['charity'] ?? 0);
            $total = $certifiedChecks + $pos + $cash + $other;

            $summary[$key] = [
                'label_ar' => $period['label_ar'],
                'certified_checks' => $certifiedChecks,
                'pos' => $pos,
                'cash' => $cash,
                'other' => $other,
                'total' => $total,
                'receipt_from' => $receiptNumbers->first(),
                'receipt_to' => $receiptNumbers->last(),
                'count' => $receipts->count(),
                'handover_names' => $handoverNames,
                'receiver_names' => [], // filled below from next period
            ];
        }

        foreach ([1 => 2, 2 => 3] as $current => $next) {
            if (isset($summary[$next]['handover_names'])) {
                $summary[$current]['receiver_names'] = $summary[$next]['handover_names'];
            }
        }

        $dayName = $carbonDate->locale('ar')->dayName;
        $hijri = $carbonDate->locale('ar')->translatedFormat('d / m / Y');
        $gregorian = $carbonDate->format('d / m / Y');

        $receiptsDay = PaymentReceipt::query()
            ->whereRaw('DATE(COALESCE(collected_at, created_at)) = ?', [$date])
            ->get();
        $byMethodDay = $receiptsDay->groupBy('payment_method')->map->sum('amount');
        $dayTotals = [
            'certified_checks' => (float) (($byMethodDay['cheque'] ?? 0) + ($byMethodDay['bank_transfer'] ?? 0)),
            'pos' => (float) ($byMethodDay['card'] ?? 0),
            'cash' => (float) ($byMethodDay['cash'] ?? 0),
            'other' => (float) (($byMethodDay['insurance'] ?? 0) + ($byMethodDay['charity'] ?? 0)),
        ];
        $dayTotals['total'] = $dayTotals['certified_checks'] + $dayTotals['pos'] + $dayTotals['cash'] + $dayTotals['other'];
        $dayReceiptNumbers = $receiptsDay->pluck('receipt_number')->filter()->values();
        $dayCollectorIds = $receiptsDay->pluck('collected_by')->filter()->unique()->values()->all();
        $dayCollectorNames = $dayCollectorIds
            ? User::whereIn('id', $dayCollectorIds)->get()->map(fn ($u) => $u->name_ar ?? $u->name)->filter()->unique()->values()->all()
            : [];

        $activeTab = $request->input('tab', 'moarad-4');
        $tabs = [
            'moarad-4' => ['label_ar' => 'خلاصة الإيرادات اليومية (موارد - ٤)', 'label_en' => 'Daily Revenue Summary (Resources-4)'],
            'by-method' => ['label_ar' => 'ملخص حسب طريقة التحصيل', 'label_en' => 'Summary by collection method'],
            'monthly' => ['label_ar' => 'خلاصة الإيرادات الشهرية (موارد - ١١)', 'label_en' => 'Monthly Revenue Summary (Resources-11)'],
            'monthly-stats' => ['label_ar' => 'إحصائية شهرية (موارد - ٩)', 'label_en' => 'Monthly Statistics (Resources-9)'],
            'receipt-order' => ['label_ar' => 'أمر قبض (موارد - ٥)', 'label_en' => 'Receipt Order (Resources-5)'],
        ];

        $monthInput = $request->input('month', Carbon::today()->format('Y-m'));
        $monthStart = Carbon::parse($monthInput . '-01')->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();
        $monthlyWeeks = [];
        $current = $monthStart->copy();
        $weekLabels = ['الأسبوع الأول', 'الأسبوع الثاني', 'الأسبوع الثالث', 'الأسبوع الرابع', 'الأسبوع الخامس', 'الأسبوع السادس'];
        $seq = 0;
        while ($current <= $monthEnd && $seq < 6) {
            $weekStart = $current->copy()->startOfWeek(0);
            $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
            $periodStart = $weekStart->lt($monthStart) ? $monthStart->copy() : $weekStart->copy();
            $periodEnd = $weekEnd->gt($monthEnd) ? $monthEnd->copy() : $weekEnd->copy();
            if ($periodStart->gt($monthEnd)) {
                $current->addDay();
                continue;
            }
            $receiptsWeek = PaymentReceipt::query()
                ->whereRaw('COALESCE(collected_at, created_at) BETWEEN ? AND ?', [$periodStart, $periodEnd])
                ->get();
            $collected = (float) $receiptsWeek->sum('amount');
            $receiptFrom = $receiptsWeek->pluck('receipt_number')->filter()->first();
            $receiptTo = $receiptsWeek->pluck('receipt_number')->filter()->last();
            $deposited = (float) Invoice::whereNotNull('deposited_at')
                ->whereBetween('deposited_at', [$periodStart, $periodEnd])
                ->sum('paid_amount');
            $diff = round($collected - $deposited, 2);
            $monthlyWeeks[] = [
                'label' => $weekLabels[$seq] ?? ('الأسبوع ' . ($seq + 1)),
                'from_date' => $periodStart->format('Y-m-d'),
                'to_date' => $periodEnd->format('Y-m-d'),
                'receipt_from' => $receiptFrom,
                'receipt_to' => $receiptTo,
                'collected' => $collected,
                'deposited' => $deposited,
                'difference' => $diff,
            ];
            $seq++;
            $current = $weekEnd->addDay();
        }
        $monthlyTotalCollected = array_sum(array_column($monthlyWeeks, 'collected'));
        $monthlyTotalDeposited = array_sum(array_column($monthlyWeeks, 'deposited'));
        $monthlyTotalDiff = round($monthlyTotalCollected - $monthlyTotalDeposited, 2);
        $monthYearAr = $monthStart->locale('ar')->translatedFormat('F Y');
        $monthYearEn = $monthStart->format('F Y');
        $hospitalName = \App\Models\Setting::get('hospital_name', 'المستشفى');

        $monthlyStatsRows = Invoice::query()
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween(\DB::raw('DATE(created_at)'), [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                    ->orWhereHas('payments', function ($pq) use ($monthStart, $monthEnd) {
                        $pq->whereBetween('received_date', [$monthStart, $monthEnd]);
                    });
            })
            ->orderBy('created_at')
            ->get()
            ->map(function ($inv) {
                return [
                    'file_number' => $inv->invoice_number,
                    'total_amount' => (float) $inv->total_amount,
                    'paid_at_entry' => (float) $inv->paid_amount,
                    'advance_installments' => 0,
                    'remaining_amount' => (float) $inv->remaining_amount,
                ];
            })
            ->all();
        $monthlyStatsTotals = [
            'total_amount' => array_sum(array_column($monthlyStatsRows, 'total_amount')),
            'paid_at_entry' => array_sum(array_column($monthlyStatsRows, 'paid_at_entry')),
            'advance_installments' => array_sum(array_column($monthlyStatsRows, 'advance_installments')),
            'remaining_amount' => array_sum(array_column($monthlyStatsRows, 'remaining_amount')),
        ];

        return view('revenue.daily-summary', compact(
            'date', 'summary', 'dayName', 'hijri', 'gregorian', 'activeTab', 'tabs',
            'dayTotals', 'dayCollectorNames', 'dayReceiptNumbers',
            'monthInput', 'monthlyWeeks', 'monthlyTotalCollected', 'monthlyTotalDeposited', 'monthlyTotalDiff',
            'monthYearAr', 'monthYearEn', 'hospitalName',
            'monthlyStatsRows', 'monthlyStatsTotals'
        ));
    }
}
