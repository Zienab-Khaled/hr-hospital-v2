<?php

namespace App\Http\Controllers;

use App\Helpers\NumeralHelper;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Shift;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Support\RoleNav;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class RevenueWorkflowController extends Controller
{
    protected function authorizeControlRoom(): void
    {
        if (RoleNav::canSeeControlRoom(auth()->user()) || auth()->user()?->can('reports.view')) {
            return;
        }
        abort(403);
    }

    protected function authorizeTreasury(): void
    {
        if (RoleNav::canSeeTreasury(auth()->user()) || auth()->user()?->can('reports.view')) {
            return;
        }
        abort(403);
    }

    protected function authorizeRevenueAdmin(): void
    {
        if (! RoleNav::canConfirmAsManager(auth()->user())) {
            abort(403, app()->getLocale() === 'ar'
                ? 'هذا الإجراء للمدير فقط.'
                : 'This action is for managers only.');
        }
    }

    protected function authorizeAuditAction(): void
    {
        if (! RoleNav::canAuditInControlRoom(auth()->user())) {
            abort(403, app()->getLocale() === 'ar'
                ? 'المطابقة والرفض للمحاسب فقط.'
                : 'Match and reject are for the accountant only.');
        }
    }

    protected function authorizeTreasuryAction(): void
    {
        if (! RoleNav::canOperateTreasury(auth()->user())) {
            abort(403, app()->getLocale() === 'ar'
                ? 'عمليات الخزينة لأمين الصندوق فقط.'
                : 'Treasury operations are for the cashier only.');
        }
    }

    public function controlRoom(Request $request)
    {
        $this->authorizeControlRoom();

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
        $this->authorizeAuditAction();

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

        $this->notifyWorkflowUsers($invoice, 'match');

        return back()->with('success', app()->getLocale() === 'ar' ? 'تمت المطابقة بنجاح (جاهز للإيداع).' : 'Matched successfully (Ready for deposit).');
    }

    public function reject(Request $request, Invoice $invoice)
    {
        $this->authorizeAuditAction();

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $invoice->update([
            'audit_status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason')
        ]);

        return back()->with('warning', app()->getLocale() === 'ar' ? 'تم رفض المعاملة وإعادتها للمحصل.' : 'Transaction rejected and returned to collector.');
    }

    /**
     * أمين الصندوق يعلن أن الفاتورة جاهزة للإيداع للبنك (استلم المبلغ).
     */
    public function markReadyForDeposit(Invoice $invoice)
    {
        $this->authorizeTreasuryAction();

        if ($invoice->audit_status !== 'matched') {
            return back()->withErrors(['audit_status' => app()->getLocale() === 'ar' ? 'الحالة الحالية لا تسمح بهذا الإجراء.' : 'Current status does not allow this action.']);
        }

        $invoice->update([
            'audit_status' => 'ready_for_deposit',
            'cashier_otp' => (string) rand(100000, 999999),
        ]);

        $this->notifyWorkflowUsers($invoice, 'ready_for_deposit');

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم تسجيل جاهزية الإيداع للبنك. بانتظار تأكيد المدير.'
            : 'Marked ready for bank deposit. Awaiting manager confirmation.');
    }

    /**
     * المدير يؤكد أن أمين الصندوق استلم المبلغ — بعدها فقط يمكن لأمين الصندوق تسجيل "تم الإيداع".
     */
    public function markManagerConfirmed(Invoice $invoice)
    {
        $this->authorizeRevenueAdmin();

        if ($invoice->audit_status !== 'ready_for_deposit') {
            return back()->withErrors(['audit_status' => app()->getLocale() === 'ar' ? 'يجب أن تكون الفاتورة في حالة "جاهز للإيداع" لتأكيد المدير.' : 'Invoice must be ready for deposit to confirm.']);
        }

        $invoice->update(['audit_status' => 'manager_confirmed']);

        $this->notifyWorkflowUsers($invoice, 'manager_confirmed');

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم التأكيد من المدير. أمين الصندوق يمكنه الآن تسجيل الإيداع في البنك.'
            : 'Manager confirmed. Cashier can now record bank deposit.');
    }

    /**
     * تبويب أمين الصندوق: قائمة الفواتير المُحوّلة بعد المطابقة + جاهزة للإيداع.
     */
    public function treasuryIndex(Request $request)
    {
        $this->authorizeTreasury();

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

        $managerConfirmedInvoices = Invoice::where('audit_status', 'manager_confirmed')
            ->whereHas('payments', fn($q) => $q->whereDate('received_date', $date))
            ->with(['patient', 'visit.shift', 'payments.receipt'])
            ->latest()
            ->get();

        $depositedInvoices = Invoice::where('audit_status', 'deposited')
            ->whereDate('deposited_at', $date)
            ->with(['patient', 'visit.shift', 'payments.receipt', 'media'])
            ->latest('deposited_at')
            ->get();

        $treasuryStats = [
            'matched_count' => $matchedInvoices->count(),
            'matched_amount' => (float) $matchedInvoices->sum('paid_amount'),
            'ready_count' => $readyForDepositInvoices->count(),
            'ready_amount' => (float) $readyForDepositInvoices->sum('paid_amount'),
            'manager_confirmed_count' => $managerConfirmedInvoices->count(),
            'manager_confirmed_amount' => (float) $managerConfirmedInvoices->sum('paid_amount'),
            'deposited_count' => $depositedInvoices->count(),
            'deposited_amount' => (float) $depositedInvoices->sum('paid_amount'),
        ];

        return view('revenue.treasury.index', compact('matchedInvoices', 'readyForDepositInvoices', 'managerConfirmedInvoices', 'depositedInvoices', 'date', 'treasuryStats'));
    }

    /**
     * تم الإيداع في البنك (إقفال). مسموح فقط بعد تأكيد المدير (manager_confirmed).
     */
    public function markDeposited(Request $request, Invoice $invoice)
    {
        $this->authorizeTreasuryAction();

        if ($invoice->audit_status !== 'manager_confirmed') {
            return back()->withErrors(['audit_status' => app()->getLocale() === 'ar'
                ? 'لا يمكن تسجيل الإيداع إلا بعد تأكيد المدير أن أمين الصندوق استلم. الحالة الحالية: ' . ($invoice->getStatusLabelAttribute() ?? $invoice->audit_status)
                : 'Deposit can only be recorded after manager confirmation.']);
        }

        if ($request->hasFile('deposit_slip')) {
            $request->validate([
                'deposit_slip' => ['file', 'image', 'max:10240'],
            ]);
            $invoice->addMediaFromRequest('deposit_slip')->toMediaCollection('bank_deposit');
        }

        $invoice->update([
            'audit_status' => 'deposited',
            'deposited_at' => now(),
        ]);

        $this->notifyWorkflowUsers($invoice, 'deposited');

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم تسجيل الإيداع وإقفال المعاملة.'
            : 'Deposit recorded and transaction closed.');
    }

    /**
     * إشعار المدير وأمين الصندوق والمحاسب بخطوات الفلو.
     */
    protected function notifyWorkflowUsers(Invoice $invoice, string $step): void
    {
        $users = User::role(['manager', 'admin', 'accountant'])->get();
        if ($users->isEmpty()) {
            return;
        }

        $invNum = $invoice->invoice_number;
        $amount = number_format((float) $invoice->paid_amount, 2);
        $url = route('revenue.control-room', ['date' => $invoice->payments()->first()?->received_date?->format('Y-m-d') ?? now()->format('Y-m-d')]);

        $messages = [
            'match' => [
                'ar' => "تمت مطابقة الفاتورة #{$invNum} (مبلغ {$amount} ريال). جاهزة لأمين الصندوق لتسجيل جاهزية الإيداع.",
                'en' => "Invoice #{$invNum} matched (amount {$amount}). Ready for cashier to mark ready for deposit.",
            ],
            'ready_for_deposit' => [
                'ar' => "أمين الصندوق سجّل جاهزية الإيداع للبنك للفاتورة #{$invNum}. يرجى التأكيد من المدير.",
                'en' => "Cashier marked invoice #{$invNum} ready for bank deposit. Manager confirmation required.",
            ],
            'manager_confirmed' => [
                'ar' => "تم التأكيد من المدير للفاتورة #{$invNum}. يمكن تسجيل الإيداع في البنك من تبويب أمين الصندوق.",
                'en' => "Manager confirmed invoice #{$invNum}. You can now record bank deposit in Treasury.",
            ],
            'deposited' => [
                'ar' => "تم تسجيل الإيداع في البنك للفاتورة #{$invNum} (مبلغ {$amount} ريال).",
                'en' => "Bank deposit recorded for invoice #{$invNum} (amount {$amount}).",
            ],
        ];

        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $msg = $messages[$step][$locale] ?? $messages[$step]['ar'];

        Notification::send($users, new SystemNotification([
            'title' => app()->getLocale() === 'ar' ? 'فلو الإيداع - تحديث' : 'Deposit workflow update',
            'message' => $msg,
            'action_url' => $url,
            'type' => 'info',
            'metadata' => ['invoice_id' => $invoice->id, 'step' => $step],
        ]));
    }

    /**
     * ملخص الإيرادات اليومي (نموذج موارد - 4): ثلاث فترات، تصنيف شيكات مصدقة / نقاط بيع / نقدي، ونطاق أرقام الإيصالات.
     */
    public function dailyRevenueSummary(Request $request)
    {
        if (! RoleNav::canSeeRevenueSummary(auth()->user())) {
            abort(403);
        }

        $date = $request->input('date', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($date);

        $periods = [
            1 => [
                'label_ar' => 'الفترة الأولى (من 12 منتصف الليل إلى الثامنة صباحاً)',
                'start' => $carbonDate->copy()->startOfDay(),
                'end' => $carbonDate->copy()->setTime(7, 59, 59),
            ],
            2 => [
                'label_ar' => 'الفترة الثانية (من الثامنة صباحاً إلى الرابعة مساءً)',
                'start' => $carbonDate->copy()->setTime(8, 0, 0),
                'end' => $carbonDate->copy()->setTime(15, 59, 59),
            ],
            3 => [
                'label_ar' => 'الفترة الثالثة (من الرابعة مساءً إلى 12 منتصف الليل)',
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
            $insurance = (float) ($byMethod['insurance'] ?? 0);
            $charity = (float) ($byMethod['charity'] ?? 0);
            $total = $certifiedChecks + $pos + $cash + $insurance + $charity;

            $summary[$key] = [
                'label_ar' => $period['label_ar'],
                'certified_checks' => $certifiedChecks,
                'pos' => $pos,
                'cash' => $cash,
                'insurance' => $insurance,
                'charity' => $charity,
                'total' => $total,
                'receipt_from' => $receiptNumbers->first(),
                'receipt_to' => $receiptNumbers->last(),
                'count' => $receipts->count(),
                'handover_names' => $handoverNames,
                'receiver_names' => [], // filled below from next period
            ];
        }

        // دفعات اليوم بدون إيصال (مثل دفعات الجمعية) نضيفها للفترة الثانية لظهور الإجمالي الفعلي
        $receiptPaymentIds = PaymentReceipt::query()
            ->whereRaw('DATE(COALESCE(collected_at, created_at)) = ?', [$date])
            ->pluck('payment_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $orphanPayments = Payment::query()
            ->where('received_date', $date)
            ->when(!empty($receiptPaymentIds), fn ($q) => $q->whereNotIn('id', $receiptPaymentIds))
            ->get();
        if ($orphanPayments->isNotEmpty()) {
            $ocash = 0.0;
            $oins = 0.0;
            $ochar = 0.0;
            foreach ($orphanPayments as $p) {
                $amt = (float) $p->amount;
                $t = $p->payment_type ?? 'cash';
                if ($t === 'insurance') {
                    $oins += $amt;
                } elseif ($t === 'charity') {
                    $ochar += $amt;
                } else {
                    $ocash += $amt;
                }
            }
            $orphanTotal = $ocash + $oins + $ochar;
            $summary[2]['cash'] += $ocash;
            $summary[2]['insurance'] += $oins;
            $summary[2]['charity'] += $ochar;
            $summary[2]['total'] += $orphanTotal;
        }

        foreach ([1 => 2, 2 => 3] as $current => $next) {
            if (isset($summary[$next]['handover_names'])) {
                $summary[$current]['receiver_names'] = $summary[$next]['handover_names'];
            }
        }

        $dayName = $carbonDate->locale('ar')->dayName;
        $hijri = NumeralHelper::toWesternDigits($carbonDate->locale('ar')->translatedFormat('d / m / Y'));
        $gregorian = $carbonDate->format('d / m / Y');

        // إجمالي اليوم من سجل الدفعات (Payment) حتى تظهر كل الفواتير حتى بدون إيصال
        $paymentsDay = Payment::query()
            ->where('received_date', $date)
            ->with('receipt')
            ->get();
        $dayTotalsFromPayments = [
            'certified_checks' => 0.0,
            'pos' => 0.0,
            'cash' => 0.0,
            'insurance' => 0.0,
            'charity' => 0.0,
        ];
        foreach ($paymentsDay as $p) {
            $amt = (float) $p->amount;
            $method = $p->receipt?->payment_method ?? $p->payment_type;
            if (in_array($method, ['cheque', 'bank_transfer'])) {
                $dayTotalsFromPayments['certified_checks'] += $amt;
            } elseif ($method === 'card') {
                $dayTotalsFromPayments['pos'] += $amt;
            } elseif ($method === 'cash') {
                $dayTotalsFromPayments['cash'] += $amt;
            } elseif ($method === 'insurance') {
                $dayTotalsFromPayments['insurance'] += $amt;
            } elseif ($method === 'charity') {
                $dayTotalsFromPayments['charity'] += $amt;
            } else {
                $dayTotalsFromPayments['cash'] += $amt;
            }
        }
        $dayTotalsFromPayments['total'] = (float) $paymentsDay->sum('amount');

        $receiptsDay = PaymentReceipt::query()
            ->whereRaw('DATE(COALESCE(collected_at, created_at)) = ?', [$date])
            ->get();
        $byMethodDay = $receiptsDay->groupBy('payment_method')->map->sum('amount');
        // إجمالي اليوم من سجل الدفعات (يظهر الفواتير حتى بدون إيصال)؛ إن لم يوجد دفعات نستخدم الإيصالات
        if ($dayTotalsFromPayments['total'] > 0) {
            $dayTotals = [
                'certified_checks' => (float) $dayTotalsFromPayments['certified_checks'],
                'pos' => (float) $dayTotalsFromPayments['pos'],
                'cash' => (float) $dayTotalsFromPayments['cash'],
                'insurance' => (float) $dayTotalsFromPayments['insurance'],
                'charity' => (float) $dayTotalsFromPayments['charity'],
                'total' => $dayTotalsFromPayments['total'],
            ];
        } else {
            $dayTotals = [
                'certified_checks' => (float) (($byMethodDay['cheque'] ?? 0) + ($byMethodDay['bank_transfer'] ?? 0)),
                'pos' => (float) ($byMethodDay['card'] ?? 0),
                'cash' => (float) ($byMethodDay['cash'] ?? 0),
                'insurance' => (float) ($byMethodDay['insurance'] ?? 0),
                'charity' => (float) ($byMethodDay['charity'] ?? 0),
            ];
            $dayTotals['total'] = $dayTotals['certified_checks'] + $dayTotals['pos'] + $dayTotals['cash'] + $dayTotals['insurance'] + $dayTotals['charity'];
        }
        $dayReceiptNumbers = $receiptsDay->pluck('receipt_number')->filter()->values();
        $dayCollectorIds = $receiptsDay->pluck('collected_by')->filter()->unique()->values()->all();
        $dayCollectorNames = $dayCollectorIds
            ? User::whereIn('id', $dayCollectorIds)->get()->map(fn ($u) => $u->name_ar ?? $u->name)->filter()->unique()->values()->all()
            : [];

        $activeTab = $request->input('tab', 'moarad-4');
        $tabs = [
            'moarad-4' => ['label_ar' => 'خلاصة الإيرادات اليومية (موارد - 4)', 'label_en' => 'Daily Revenue Summary (Resources-4)'],
            'by-method' => ['label_ar' => 'ملخص حسب طريقة التحصيل', 'label_en' => 'Summary by collection method'],
            'monthly' => ['label_ar' => 'خلاصة الإيرادات الشهرية (موارد - 11)', 'label_en' => 'Monthly Revenue Summary (Resources-11)'],
            'monthly-stats' => ['label_ar' => 'إحصائية شهرية (موارد - 9)', 'label_en' => 'Monthly Statistics (Resources-9)'],
            'receipt-order' => ['label_ar' => 'أمر قبض (موارد - 5)', 'label_en' => 'Receipt Order (Resources-5)'],
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
            $byMethod = $receiptsWeek->groupBy('payment_method')->map->sum('amount');
            $collectedCash = (float) (($byMethod['cash'] ?? 0) + ($byMethod['card'] ?? 0) + ($byMethod['cheque'] ?? 0) + ($byMethod['bank_transfer'] ?? 0));
            $collectedInsurance = (float) ($byMethod['insurance'] ?? 0);
            $collectedCharity = (float) ($byMethod['charity'] ?? 0);
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
                'collected_cash' => $collectedCash,
                'collected_insurance' => $collectedInsurance,
                'collected_charity' => $collectedCharity,
                'deposited' => $deposited,
                'difference' => $diff,
            ];
            $seq++;
            $current = $weekEnd->addDay();
        }
        $monthlyTotalCollected = array_sum(array_column($monthlyWeeks, 'collected'));
        $monthlyTotalDeposited = array_sum(array_column($monthlyWeeks, 'deposited'));
        $monthlyTotalDiff = round($monthlyTotalCollected - $monthlyTotalDeposited, 2);
        $monthlyTotalCash = array_sum(array_column($monthlyWeeks, 'collected_cash'));
        $monthlyTotalInsurance = array_sum(array_column($monthlyWeeks, 'collected_insurance'));
        $monthlyTotalCharity = array_sum(array_column($monthlyWeeks, 'collected_charity'));
        $monthYearAr = NumeralHelper::toWesternDigits($monthStart->locale('ar')->translatedFormat('F Y'));
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
            'monthlyTotalCash', 'monthlyTotalInsurance', 'monthlyTotalCharity',
            'monthYearAr', 'monthYearEn', 'hospitalName',
            'monthlyStatsRows', 'monthlyStatsTotals'
        ));
    }
}
