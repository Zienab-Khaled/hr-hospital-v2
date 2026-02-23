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
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager')) {
            return redirect()->route('manager.dashboard');
        }

        $recentPatients = Patient::with(['insuranceCompany', 'charityEntity'])
            ->latest()
            ->take(10)
            ->get();

        $recentVisits = \App\Models\Visit::with(['patient', 'department', 'shift'])
            ->latest()
            ->take(10)
            ->get();

        $recentInvoices = \App\Models\Invoice::with(['patient'])
            ->latest()
            ->take(10)
            ->get();

        // Combined Insurance and Charity Claims
        $insuranceClaims = \App\Models\InsuranceClaim::with(['patient', 'invoice'])->latest()->take(5)->get();
        $charityClaims = \App\Models\CharityClaim::with(['patient', 'invoice'])->latest()->take(5)->get();

        $recentClaims = $insuranceClaims->concat($charityClaims)->sortByDesc('created_at')->take(10);

        $totalInvoiced = (float) Invoice::sum('total_amount');
        $totalCollected = (float) Payment::sum('amount');
        $totalRemaining = (float) Invoice::sum('remaining_amount');

        return view('dashboard', compact(
            'recentPatients',
            'recentVisits',
            'recentInvoices',
            'recentClaims',
            'totalInvoiced',
            'totalCollected',
            'totalRemaining'
        ));
    }

    /** لوحة تحكم المدير - تطوير الإيرادات */
    public function managerDashboard(\Illuminate\Http\Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $revenueToday = (float) Payment::whereNotNull('approved_by')
            ->whereBetween('received_date', [$start, $end])
            ->sum('amount');

        $totalCollected = (float) Payment::whereNotNull('approved_by')
            ->whereBetween('received_date', [$start, $end])
            ->sum('amount');

        $totalInvoiced = (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('total_amount');
        $totalDebts = (float) Invoice::whereBetween('invoice_date', [$start, $end])->sum('remaining_amount');
        $remainingUncollected = $totalDebts;

        $collectionRate = $totalInvoiced > 0
            ? round(($totalCollected / $totalInvoiced) * 100, 1)
            : 0;

        // توزيع الإيرادات حسب نوع الدفع (جمعيات، تأمين، كاش)
        $paymentsByType = Payment::whereNotNull('approved_by')
            ->whereBetween('received_date', [$start, $end])
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('patients', 'invoices.patient_id', '=', 'patients.id')
            ->select('patients.payment_type', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('patients.payment_type')
            ->pluck('total', 'payment_type');

        $totalByType = $paymentsByType->sum();
        $revenueCharity = $totalByType > 0 ? round(($paymentsByType->get('charity', 0) / $totalByType) * 100, 0) : 0;
        $revenueInsurance = $totalByType > 0 ? round(($paymentsByType->get('insurance', 0) / $totalByType) * 100, 0) : 0;
        $revenueCash = $totalByType > 0 ? round(($paymentsByType->get('cash', 0) / $totalByType) * 100, 0) : 0;

        // نمو الإيرادات الشهرية (آخر 6 أشهر - دائمًا تظهر للنظرة العامة)
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

        // حالة الديون للمتأخرات (لا ترتبط بفلتر التاريخ المباشر لأنها تراكمية)
        $overdue30 = (float) Invoice::where('remaining_amount', '>', 0)
            ->whereDate('invoice_date', '<=', Carbon::today()->subDays(30))
            ->sum('remaining_amount');
        $overdue60 = (float) Invoice::where('remaining_amount', '>', 0)
            ->whereDate('invoice_date', '<=', Carbon::today()->subDays(60))
            ->sum('remaining_amount');
        $overdue90 = (float) Invoice::where('remaining_amount', '>', 0)
            ->whereDate('invoice_date', '<=', Carbon::today()->subDays(90))
            ->sum('remaining_amount');

        // تحصيل الفترة
        $collectionToday = $revenueToday;
        // تحصيل الشهر الحالي (أو الفترة المختارة إذا كانت شهراً كاملاً)
        $collectionMonth = (float) Payment::whereNotNull('approved_by')
            ->whereBetween('received_date', [$start, $end])
            ->sum('amount');

        // --- إحصائيات الأداء حسب الأقسام ---
        $deptPerformance = \App\Models\Department::all()->map(function($dept) use ($start, $end) {
            $stats = Payment::whereNotNull('approved_by')
                ->whereBetween('received_date', [$start, $end])
                ->whereHas('invoice.visit', function($q) use ($dept) {
                    $q->where('department_id', $dept->id);
                })
                ->select(
                    DB::raw('SUM(amount) as total'),
                    DB::raw('COUNT(DISTINCT invoice_id) as invoices_count')
                )
                ->first();

            $total = (float) ($stats->total ?? 0);

            // Color Logic: High (>10k), Med (2k-10k), Low (<2k)
            $color = '#fee2e2'; // Faint Red (Low)
            $level = 'low';
            if ($total >= 10000) {
                $color = '#22c55e'; // Green (High)
                $level = 'high';
            } elseif ($total >= 2000) {
                $color = '#fbbf24'; // Yellow (Medium)
                $level = 'medium';
            }

            return (object) [
                'id' => $dept->id,
                'name_ar' => $dept->name_ar ?? $dept->name,
                'name_en' => $dept->name,
                'total' => $total,
                'patient_count' => (int) ($stats->invoices_count ?? 0),
                'color' => $color,
                'level' => $level
            ];
        })->sortByDesc('total')->values();

        // --- الأكثر تعاملاً (خلال الفترة المختارة) ---
        $topCharities = \App\Models\CharityEntity::all()
            ->map(function($entity) use ($start, $end) {
                $total = (float) Invoice::where('status', '!=', 'draft')
                    ->whereBetween('invoice_date', [$start, $end])
                    ->whereHas('patient', function($q) use ($entity) {
                        $q->where('charity_entity_id', $entity->id);
                    })->sum('total_amount');

                $count = \App\Models\Patient::where('charity_entity_id', $entity->id)
                    ->whereHas('invoices', function($q) use ($start, $end) {
                        $q->whereBetween('invoice_date', [$start, $end]);
                    })->count();

                return (object) [
                    'name' => $entity->name_ar ?? $entity->name,
                    'count' => $count,
                    'total' => $total
                ];
            })->filter(fn($e) => $e->total > 0)->sortByDesc('total')->take(5)->values();

        $topInsurances = \App\Models\InsuranceCompany::all()
            ->map(function($company) use ($start, $end) {
                $total = (float) Invoice::where('status', '!=', 'draft')
                    ->whereBetween('invoice_date', [$start, $end])
                    ->whereHas('patient', function($q) use ($company) {
                        $q->where('insurance_company_id', $company->id);
                    })->sum('total_amount');

                $count = \App\Models\Patient::where('insurance_company_id', $company->id)
                    ->whereHas('invoices', function($q) use ($start, $end) {
                        $q->whereBetween('invoice_date', [$start, $end]);
                    })->count();

                return (object) [
                    'name' => $company->name_ar ?? $company->name,
                    'count' => $count,
                    'total' => $total
                ];
            })->filter(fn($e) => $e->total > 0)->sortByDesc('total')->take(5)->values();

        $topServices = \App\Models\InvoiceItem::with('service')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereBetween('invoices.invoice_date', [$start, $end])
            ->select('service_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(total_price) as total'))
            ->groupBy('service_id')
            ->orderByDesc('qty')
            ->take(8)
            ->get()
            ->filter(fn($item) => $item->qty > 0)
            ->map(function($item) {
                return (object) [
                    'name' => $item->service?->name_ar ?? $item->service?->name ?? 'Service',
                    'qty' => $item->qty,
                    'total' => $item->total
                ];
            })->take(5);

        // تنبيهات
        $alerts = [];
        if (Invoice::where('remaining_amount', '>', 0)->whereDate('invoice_date', '<=', \Carbon\Carbon::today()->subDays(30))->exists()) {
            $alerts[] = app()->getLocale() === 'ar' ? 'مريض متأخر عن السداد' : 'Patient overdue on payment';
        }
        if (\App\Models\InsuranceClaim::where('status', 'sent')->whereDate('sent_date', '<=', \Carbon\Carbon::today()->subDays(14))->exists()) {
            $alerts[] = app()->getLocale() === 'ar' ? 'مطالبة تأمين متأخرة' : 'Overdue insurance claim';
        }

        // التحقق من انخفاض التحصيل
        foreach ($deptPerformance as $dept) {
            if ($dept->level === 'low' && $dept->total > 0) {
                $alerts[] = (app()->getLocale() === 'ar' ? 'انخفاض تحصيل قسم ' : 'Low collection in ') . $dept->name_ar;
            }
        }

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
            'alerts',
            'deptPerformance',
            'topCharities',
            'topInsurances',
            'topServices'
        ));
    }
}
