<?php

namespace App\Http\Controllers;

use App\Models\Authorization;
use App\Models\CharityClaim;
use App\Models\Department;
use App\Models\InsuranceClaim;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Helpers\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PlaceholderController extends Controller
{
    public function patientsIndex()
    {
        Gate::authorize('patients.view');
        $patients = Patient::with(['insuranceCompany', 'charityEntity'])->latest()->paginate(15);
        return view('patients.index', compact('patients'));
    }

    /** مرضى حسب القسم: جمعيات، كاش، تأمين، متابعة، تحصيل */
    public function patientsBySection()
    {
        Gate::authorize('patients.view');
        $routeName = request()->route()->getName();
        $section = str_replace('patients.section.', '', $routeName);
        $query = Patient::with(['insuranceCompany', 'charityEntity']);
        $sectionTitles = [
            'charity' => ['ar' => 'مرضى الجمعيات', 'en' => 'Charity Patients'],
            'cash' => ['ar' => 'مرضى الكاش', 'en' => 'Cash Patients'],
            'insurance' => ['ar' => 'مرضى التأمين', 'en' => 'Insurance Patients'],
            'followup' => ['ar' => 'متابعة المرضى', 'en' => 'Patient Follow-up'],
            'collection' => ['ar' => 'التحصيل', 'en' => 'Collection'],
        ];
        if (!isset($sectionTitles[$section])) {
            abort(404);
        }
        switch ($section) {
            case 'charity':
                $query->where('payment_type', 'charity');
                break;
            case 'cash':
                $query->where('payment_type', 'cash');
                break;
            case 'insurance':
                $query->where('payment_type', 'insurance');
                break;
            case 'followup':
                // كل المرضى لمتابعة الحالة
                break;
            case 'collection':
                $query->whereHas('invoices', fn ($q) => $q->where('remaining_amount', '>', 0));
                break;
        }
        $patients = $query->latest()->paginate(15);
        $sectionTitle = $sectionTitles[$section][app()->getLocale()] ?? $sectionTitles[$section]['ar'];
        return view('patients.index', compact('patients', 'section', 'sectionTitle'));
    }

    public function invoicesIndex()
    {
        Gate::authorize('invoices.view');
        $invoices = Invoice::with('patient')->latest()->paginate(15);
        return view('invoices.index', compact('invoices'));
    }

    public function authorizationsIndex()
    {
        Gate::authorize('authorizations.view');
        $authorizations = Authorization::with(['patient', 'insuranceCompany', 'charityEntity'])->latest()->paginate(15);
        return view('authorizations.index', compact('authorizations'));
    }

    public function paymentsIndex()
    {
        Gate::authorize('payments.view');
        $payments = Payment::with(['invoice.patient', 'receivedByUser', 'approvedByUser'])->latest()->paginate(15);
        return view('payments.index', compact('payments'));
    }

    public function paymentApprove(Payment $payment)
    {
        Gate::authorize('payments.approve');
        $payment->update(['approved_by' => auth()->id(), 'approved_at' => now(), 'status' => 'approved']);
        return back()->with('success', __('Payment approved.'));
    }

    public function claimsIndex()
    {
        Gate::authorize('claims.view');
        $insuranceClaims = InsuranceClaim::with(['invoice.patient', 'insuranceCompany'])->latest()->paginate(10);
        $charityClaims = CharityClaim::with(['invoice.patient', 'charityEntity'])->latest()->paginate(10);
        return view('claims.index', compact('insuranceClaims', 'charityClaims'));
    }

    public function reportsIndex()
    {
        Gate::authorize('reports.view');
        $today = Carbon::today()->toDateString();
        $revenueToday = (float) Payment::whereNotNull('approved_by')->whereDate('received_date', $today)->sum('amount');
        $totalCollected = (float) Payment::whereNotNull('approved_by')->sum('amount');
        $totalInvoiced = (float) Invoice::sum('total_amount');
        $totalDebts = (float) Invoice::sum('remaining_amount');
        $remainingUncollected = $totalDebts;
        $collectionRate = $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100, 1) : 0;

        $paymentsByType = Payment::whereNotNull('approved_by')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->join('patients', 'invoices.patient_id', '=', 'patients.id')
            ->select('patients.payment_type', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('patients.payment_type')->pluck('total', 'payment_type');
        $totalByType = $paymentsByType->sum();
        $revenueCharity = $totalByType > 0 ? round(($paymentsByType->get('charity', 0) / $totalByType) * 100, 0) : 0;
        $revenueInsurance = $totalByType > 0 ? round(($paymentsByType->get('insurance', 0) / $totalByType) * 100, 0) : 0;
        $revenueCash = $totalByType > 0 ? round(($paymentsByType->get('cash', 0) / $totalByType) * 100, 0) : 0;

        $monthlyRevenue = Payment::whereNotNull('approved_by')->get()
            ->groupBy(fn ($p) => $p->received_date?->format('Y-m'))
            ->map(fn ($items, $month) => (object)['month' => $month, 'total' => $items->sum('amount')])
            ->sortKeys()->take(-6)->values();
        if ($monthlyRevenue->isEmpty()) {
            $monthlyRevenue = collect([(object)['month' => Carbon::now()->format('Y-m'), 'total' => 0]]);
        }

        $todayCarbon = Carbon::today();
        $overdue30 = (float) Invoice::where('remaining_amount', '>', 0)->whereDate('invoice_date', '<=', $todayCarbon->copy()->subDays(30))->sum('remaining_amount');
        $overdue60 = (float) Invoice::where('remaining_amount', '>', 0)->whereDate('invoice_date', '<=', $todayCarbon->copy()->subDays(60))->sum('remaining_amount');
        $overdue90 = (float) Invoice::where('remaining_amount', '>', 0)->whereDate('invoice_date', '<=', $todayCarbon->copy()->subDays(90))->sum('remaining_amount');

        $collectionToday = $revenueToday;
        $collectionMonth = (float) Payment::whereNotNull('approved_by')->whereMonth('received_date', $todayCarbon->month)->whereYear('received_date', $todayCarbon->year)->sum('amount');

        $alerts = [];
        if (Invoice::where('remaining_amount', '>', 0)->whereDate('invoice_date', '<=', $todayCarbon->copy()->subDays(30))->exists()) {
            $alerts[] = app()->getLocale() === 'ar' ? 'مريض متأخر عن السداد' : 'Patient overdue on payment';
        }
        $alerts[] = app()->getLocale() === 'ar' ? 'مطالبة تأمين متأخرة' : 'Overdue insurance claim';
        $alerts[] = app()->getLocale() === 'ar' ? 'انخفاض تحصيل أحد الأقسام' : 'Department collection decline';

        return view('reports.index', compact(
            'revenueToday', 'totalCollected', 'totalDebts', 'remainingUncollected', 'collectionRate',
            'revenueCharity', 'revenueInsurance', 'revenueCash', 'monthlyRevenue',
            'overdue30', 'overdue60', 'overdue90', 'collectionToday', 'collectionMonth', 'alerts'
        ));
    }

    public function reportsUploadCluster()
    {
        Gate::authorize('reports.upload_cluster');
        return view('reports.upload-cluster');
    }

    public function reportsUploadClusterStore(Request $request)
    {
        Gate::authorize('reports.upload_cluster');
        $request->validate(['file' => 'required|file|max:10240']);
        $path = $request->file('file')->store('cluster-reports', 'local');
        ActivityLogger::log('cluster_report_uploaded', null, null, 'Upload report to cluster', null, ['path' => $path]);
        return back()->with('success', __('File uploaded successfully.'));
    }

    public function departmentsIndex()
    {
        Gate::authorize('departments.manage');
        $departments = Department::withCount('employees')->orderBy('name')->get();
        return view('departments.index', compact('departments'));
    }

    public function departmentsCreate()
    {
        Gate::authorize('departments.manage');
        return view('departments.create');
    }

    public function departmentsStore(Request $request)
    {
        Gate::authorize('departments.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:20|unique:departments,code',
            'is_active' => 'nullable|boolean',
        ]);
        Department::create([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: null,
            'code' => $request->filled('code') ? strtoupper($request->input('code')) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('departments.index')->with('success', __('Department created successfully.'));
    }

    public function servicesIndex()
    {
        Gate::authorize('services.manage');
        $services = Service::with('department')->orderBy('name')->get();
        return view('services.index', compact('services'));
    }

    public function servicesCreate()
    {
        Gate::authorize('services.manage');
        $departments = Department::orderBy('name')->get();
        return view('services.create', compact('departments'));
    }

    public function servicesStore(Request $request)
    {
        Gate::authorize('services.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'required|string|max:50|unique:services,code',
            'default_price' => 'required|numeric|min:0',
            'department_id' => 'required|exists:departments,id',
        ]);

        Service::create([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: $request->input('name'),
            'code' => $request->input('code'),
            'default_price' => $request->input('default_price'),
            'department_id' => $request->input('department_id'),
            'is_active' => true,
        ]);

        return redirect()->route('services.index')->with('success', __('Service created successfully.'));
    }

    public function usersIndex()
    {
        Gate::authorize('users.manage');
        $users = User::with('employee.department')->whereNotNull('username')->orderBy('username')->get();
        return view('users.index', compact('users'));
    }

    public function settingsIndex()
    {
        Gate::authorize('settings.manage');
        $hospitalName = Setting::get('hospital_name', '');
        $managerName = Setting::get('manager_name', '');
        return view('settings.index', compact('hospitalName', 'managerName'));
    }

    public function settingsUpdate(Request $request)
    {
        Gate::authorize('settings.manage');
        $request->validate([
            'hospital_name' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
        ]);
        Setting::set('hospital_name', $request->input('hospital_name', ''), 'general');
        Setting::set('manager_name', $request->input('manager_name', ''), 'general');
        return back()->with('success', __('Saved successfully.'));
    }

    public function codesUpload()
    {
        Gate::authorize('codes.upload');
        return view('codes.upload');
    }

    public function codesUploadStore(Request $request)
    {
        Gate::authorize('codes.upload');
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        $file = $request->file('file');
        $updated = 0;
        $created = 0;
        $handle = fopen($file->getRealPath(), 'r');
            $first = fgetcsv($handle);
            $isHeader = $first && in_array(strtolower(trim((string)($first[0] ?? ''))), ['code', 'كود', 'id', 'رقم']);
            if ($first && !$isHeader) {
                $code = trim($first[0] ?? '');
                $name = trim($first[1] ?? '');
                $price = (float) ($first[2] ?? 0);
                $deptId = (int) ($first[3] ?? 1);
                if ($code) {
                    $svc = Service::updateOrCreate(
                        ['code' => $code],
                        ['name' => $name, 'name_ar' => $name, 'default_price' => $price, 'department_id' => $deptId ?: 1, 'is_active' => true]
                    );
                    $svc->wasRecentlyCreated ? $created++ : $updated++;
                }
            }
            while (($row = fgetcsv($handle)) !== false) {
                $code = trim($row[0] ?? '');
                $name = trim($row[1] ?? '');
                $price = (float) ($row[2] ?? 0);
                $deptId = (int) ($row[3] ?? 1);
                if (!$code) {
                    continue;
                }
                $svc = Service::updateOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'name_ar' => $name, 'default_price' => $price, 'department_id' => $deptId ?: 1, 'is_active' => true]
                );
                $svc->wasRecentlyCreated ? $created++ : $updated++;
            }
            fclose($handle);
        return back()->with('success', __('Processed successfully. Created: :c, Updated: :u', ['c' => $created, 'u' => $updated]));
    }
}
