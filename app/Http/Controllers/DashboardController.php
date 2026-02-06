<?php

namespace App\Http\Controllers;

use App\Models\ContactReport;
use App\Models\DebtInventory;
use App\Models\Invoice;
use App\Models\NonCommitmentReport;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\WrittenCommitment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $recentPatients = Patient::with(['insuranceCompany', 'charityEntity'])
            ->latest()
            ->take(10)
            ->get();

        $recentContactReports = ContactReport::with(['patient', 'employee'])
            ->latest()
            ->take(5)
            ->get();

        $recentWrittenCommitments = WrittenCommitment::with('patient')
            ->latest()
            ->take(5)
            ->get();

        $recentNonCommitmentReports = NonCommitmentReport::with('patient')
            ->latest()
            ->take(5)
            ->get();

        $recentDebtInventories = DebtInventory::with('patient')
            ->latest()
            ->take(5)
            ->get();

        $totalInvoiced = (float) Invoice::sum('total_amount');
        $totalCollected = (float) Payment::sum('amount');
        $totalRemaining = (float) Invoice::sum('remaining_amount');

        return view('dashboard', compact(
            'recentPatients',
            'recentContactReports',
            'recentWrittenCommitments',
            'recentNonCommitmentReports',
            'recentDebtInventories',
            'totalInvoiced',
            'totalCollected',
            'totalRemaining'
        ));
    }

    /** لوحة تحكم المدير - تطوير الإيرادات */
    protected function managerDashboard()
    {
        $today = Carbon::today()->toDateString();

        $revenueToday = (float) Payment::whereNotNull('approved_by')
            ->whereDate('received_date', $today)
            ->sum('amount');

        $totalCollected = (float) Payment::whereNotNull('approved_by')->sum('amount');
        $totalInvoiced = (float) Invoice::sum('total_amount');
        $totalDebts = (float) Invoice::sum('remaining_amount');
        $remainingUncollected = $totalDebts;

        $collectionRate = $totalInvoiced > 0
            ? round(($totalCollected / $totalInvoiced) * 100, 1)
            : 0;

        // توزيع الإيرادات حسب نوع الدفع (جمعيات، تأمين، كاش)
        $paymentsByType = Payment::whereNotNull('approved_by')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('patients', 'invoices.patient_id', '=', 'patients.id')
            ->select('patients.payment_type', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('patients.payment_type')
            ->pluck('total', 'payment_type');

        $totalByType = $paymentsByType->sum();
        $revenueCharity = $totalByType > 0 ? round(($paymentsByType->get('charity', 0) / $totalByType) * 100, 0) : 0;
        $revenueInsurance = $totalByType > 0 ? round(($paymentsByType->get('insurance', 0) / $totalByType) * 100, 0) : 0;
        $revenueCash = $totalByType > 0 ? round(($paymentsByType->get('cash', 0) / $totalByType) * 100, 0) : 0;

        // نمو الإيرادات الشهرية (آخر 6 أشهر)
        $monthlyRevenue = Payment::whereNotNull('approved_by')
            ->get()
            ->groupBy(fn ($p) => $p->received_date?->format('Y-m'))
            ->map(fn ($items, $month) => (object)['month' => $month, 'total' => $items->sum('amount')])
            ->sortKeys()
            ->take(-6)
            ->values();
        if ($monthlyRevenue->isEmpty()) {
            $monthlyRevenue = collect([(object)['month' => Carbon::now()->format('Y-m'), 'total' => 0]]);
        }

        // حالة الديون: متأخر 30، 60، 90 يوم (حسب تاريخ الفاتورة)
        $todayCarbon = Carbon::today();
        $overdue30 = (float) Invoice::where('remaining_amount', '>', 0)
            ->whereDate('invoice_date', '<=', $todayCarbon->copy()->subDays(30))
            ->sum('remaining_amount');
        $overdue60 = (float) Invoice::where('remaining_amount', '>', 0)
            ->whereDate('invoice_date', '<=', $todayCarbon->copy()->subDays(60))
            ->sum('remaining_amount');
        $overdue90 = (float) Invoice::where('remaining_amount', '>', 0)
            ->whereDate('invoice_date', '<=', $todayCarbon->copy()->subDays(90))
            ->sum('remaining_amount');

        // تحصيل اليوم (نفس إيرادات اليوم)
        $collectionToday = $revenueToday;
        // تحصيل الشهر الحالي
        $collectionMonth = (float) Payment::whereNotNull('approved_by')
            ->whereMonth('received_date', $todayCarbon->month)
            ->whereYear('received_date', $todayCarbon->year)
            ->sum('amount');

        // تنبيهات
        $alerts = [];
        if (Invoice::where('remaining_amount', '>', 0)->whereDate('invoice_date', '<=', $todayCarbon->copy()->subDays(30))->exists()) {
            $alerts[] = app()->getLocale() === 'ar' ? 'مريض متأخر عن السداد' : 'Patient overdue on payment';
        }
        $alerts[] = app()->getLocale() === 'ar' ? 'مطالبة تأمين متأخرة' : 'Overdue insurance claim';
        $alerts[] = app()->getLocale() === 'ar' ? 'انخفاض تحصيل أحد الأقسام' : 'Department collection decline';

        return view('manager.dashboard', compact(
            'revenueToday',
            'totalCollected',
            'totalDebts',
            'remainingUncollected',
            'collectionRate',
            'revenueCharity',
            'revenueInsurance',
            'revenueCash',
            'monthlyRevenue',
            'overdue30',
            'overdue60',
            'overdue90',
            'collectionToday',
            'collectionMonth',
            'alerts'
        ));
    }
}
