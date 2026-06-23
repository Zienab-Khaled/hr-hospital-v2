<?php

namespace App\Http\Controllers;

use App\Traits\HasIndexFilters;
use App\Models\ActivityLog;
use App\Models\Authorization;
use App\Models\CharityClaim;
use App\Models\CharityEntity;
use App\Models\Department;
use App\Models\InsuranceClaim;
use App\Models\InsuranceCompany;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use App\Helpers\ActivityLogger;
use App\Services\OfficialCodesImporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentialsMail;
use Spatie\Permission\Models\Role;

class PlaceholderController extends Controller
{
    use HasIndexFilters;

    public function patientsIndex(Request $request)
    {
        Gate::authorize('patients.view');

        $query = Patient::with(['insuranceCompany', 'charityEntity']);

        if (auth()->user()->hasRole('insurance_clerk')) {
            $query->where('payment_type', 'insurance');
        }

        $this->applyIndexFilters(
            $query,
            $request,
            ['name', 'name_ar', 'file_number', 'identity_value', 'phone', 'country_of_origin'],
            [
                'payment_type' => 'payment_type',
                'identity_type' => 'identity_type',
                'insurance_company_id' => 'insurance_company_id',
                'charity_entity_id' => 'charity_entity_id',
                'gender' => 'gender',
            ]
        );

        if ($request->filled('country_of_origin')) {
            $query->where('country_of_origin', 'like', '%' . $request->get('country_of_origin') . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        if ($request->filled('age_from')) {
            $query->where('age', '>=', $request->get('age_from'));
        }
        if ($request->filled('age_to')) {
            $query->where('age', '<=', $request->get('age_to'));
        }

        $patients = $query->latest()
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        return view('patients.index', compact('patients'));
    }

    /** مرضى حسب القسم: جمعيات، كاش، تأمين، متابعة، تحصيل */
    public function patientsBySection(Request $request)
    {
        Gate::authorize('patients.view');
        $routeName = request()->route()->getName();
        $section = str_replace('patients.section.', '', $routeName);

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

        if (auth()->user()->hasRole('insurance_clerk') && in_array($section, ['charity', 'cash'])) {
            abort(403);
        }

        $query = Patient::with(['insuranceCompany', 'charityEntity']);

        if (auth()->user()->hasRole('insurance_clerk')) {
            $query->where('payment_type', 'insurance');
        }

        // Apply section-specific filters
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

        // Apply global filters based on section
        $filters = ['identity_type' => 'identity_type'];

        if (in_array($section, ['followup', 'collection'])) {
            $filters['payment_type'] = 'payment_type';
        }

        // Charity-specific filters
        if ($section === 'charity') {
            $filters['charity_entity_id'] = 'charity_entity_id';
            $filters['gender'] = 'gender';
        }

        // Cash section: same demographic filters as charity
        if ($section === 'cash') {
            $filters['gender'] = 'gender';
        }

        // Insurance-specific filters
        if ($section === 'insurance') {
            $filters['insurance_company_id'] = 'insurance_company_id';
        }

        $this->applyIndexFilters(
            $query,
            $request,
            ['name', 'name_ar', 'file_number', 'identity_value', 'phone', 'sponsor_name', 'country_of_origin'],
            $filters
        );

        // Age range (charity + cash + followup + collection)
        if ($request->filled('age_from')) {
            $query->where('age', '>=', $request->get('age_from'));
        }
        if ($request->filled('age_to')) {
            $query->where('age', '<=', $request->get('age_to'));
        }

        // Date range (registration date) – all sections
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Country of origin (text search)
        if ($request->filled('country_of_origin')) {
            $query->where('country_of_origin', 'like', '%' . $request->get('country_of_origin') . '%');
        }

        $patients = $query->latest()
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        $sectionTitle = $sectionTitles[$section][app()->getLocale()] ?? $sectionTitles[$section]['ar'];
        return view('patients.index', compact('patients', 'section', 'sectionTitle'));
    }

    /** صفحة اختيار القسم لعرض مرضى القسم */
    public function patientsDepartmentsList()
    {
        Gate::authorize('patients.view');
        $departments = Department::where('is_active', true)->where('category', 'medical')->orderBy('name')->get();
        return view('patients.departments-list', compact('departments'));
    }

    /** مرضى حسب القسم (الأقسام الموجودة في النظام) — يظهر من تم تحويلهم من هذا القسم مع شارة "تم تحويله" */
    public function patientsByDepartment(Department $department)
    {
        Gate::authorize('patients.view');
        $query = Patient::with(['department', 'insuranceCompany', 'charityEntity'])
            ->where(function ($q) use ($department) {
                $q->where('department_id', $department->id)
                    ->orWhereHas('transfers', fn ($q2) => $q2->where('from_department_id', $department->id));
            });
        if (auth()->user()->hasRole('insurance_clerk')) {
            $query->where('payment_type', 'insurance');
        }
        $this->applyIndexFilters(
            $query,
            request(),
            ['name', 'name_ar', 'file_number', 'identity_value', 'phone'],
            []
        );
        $patients = $query->latest()->paginate($this->getPerPage(request()))->withQueryString();
        $departmentTitle = app()->getLocale() === 'ar' && $department->name_ar ? $department->name_ar : $department->name;
        return view('patients.index', compact('patients', 'department', 'departmentTitle'));
    }

    /**
     * قائمة الزيارات: للأدمن/المدير كل الزيارات، وللموظف زيارات الشيفت الحالي في قسمه فقط.
     */
    public function visitsIndex(Request $request)
    {
        Gate::authorize('invoices.view');

        $user = auth()->user();
        $isAdmin = \App\Support\RoleNav::canSeeAllVisits($user);
        $currentShift = Shift::currentAt();

        $query = Visit::with(['patient', 'department', 'shift', 'registeredBy']);

        if ($user->hasRole('insurance_clerk')) {
            $query->whereHas('patient', fn ($q) => $q->where('payment_type', 'insurance'));
        }

        if (!$isAdmin) {
            $deptId = $user->department_id;
            if ($deptId && $currentShift) {
                $query->where('department_id', $deptId)
                    ->where('shift_id', $currentShift->id)
                    ->whereDate('visit_date', today());
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            if ($request->filled('shift_id')) {
                $query->where('shift_id', $request->input('shift_id'));
            }
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->input('department_id'));
            }
            if ($request->filled('date')) {
                $query->whereDate('visit_date', $request->input('date'));
            }
        }

        $this->applyIndexFilters(
            $query,
            $request,
            ['patient.name', 'patient.name_ar', 'patient.name_ar_first', 'patient.name_ar_father', 'patient.name_ar_family', 'patient.file_number', 'patient.identity_value', 'notes'],
            [],
            []
        );

        $visits = $query->latest('visit_date')->latest('id')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        $shifts = Shift::where('is_active', true)->orderBy('sort_order')->get();
        $departments = Department::where('is_active', true)->where('category', 'medical')->orderBy('name')->get();

        return view('visits.index', compact('visits', 'currentShift', 'isAdmin', 'shifts', 'departments'));
    }

    public function invoicesIndex(Request $request)
    {
        Gate::authorize('invoices.view');

        $user = auth()->user();
        $canSeeAllInvoices = \App\Support\RoleNav::canSeeAllInvoices($user);
        $isAdmin = \App\Support\RoleNav::hasSupervisorVisibility($user);

        $query = Invoice::with(['patient', 'visit.shift', 'visit.department']);

        if ($user->hasRole('insurance_clerk')) {
            $query->whereHas('patient', fn ($q) => $q->where('payment_type', 'insurance'));
        }

        // تقييد تاريخ اليوم للموظفين العاديين فقط — المحاسب/أمين الصندوق/الإدارة يرون الكل
        if (! $canSeeAllInvoices && ! $request->has('date')) {
            $request->merge(['date' => date('Y-m-d')]);
        }

        if (! $isAdmin && ! $canSeeAllInvoices && ! $request->has('shift_id')) {
            $currentShift = Shift::currentAt();
            if ($currentShift) {
                $request->merge(['shift_id' => $currentShift->id]);
            }
        }

        $this->applyIndexFilters(
            $query,
            $request,
            ['invoice_number', 'patient.name', 'patient.name_ar', 'patient.name_ar_first', 'patient.name_ar_father', 'patient.name_ar_family', 'patient.file_number'],
            [
                'registered_by' => 'created_by', // Wait, does invoice have created_by? Let's check.
            ]
        );

        if ($request->filled('date')) {
            $query->whereDate('invoice_date', $request->input('date'));
        }

        if ($request->filled('shift_id')) {
            $query->whereHas('visit', function($q) use ($request) {
                $q->where('shift_id', $request->input('shift_id'));
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('visit', function($q) use ($request) {
                $q->where('department_id', $request->input('department_id'));
            });
        }

        // Custom status filter: غير مدفوعة = لم يُدفع منها أي مبلغ (paid_amount = 0)
        $status = $request->query('status');
        if ($status !== null && $status !== '') {
            if ($status === 'paid') {
                $query->where('remaining_amount', '=', 0);
            } elseif ($status === 'unpaid') {
                $query->where(function ($q) {
                    $q->where('paid_amount', '=', 0)->orWhereNull('paid_amount');
                });
            } elseif (in_array($status, ['sent_to_insurance', 'sent_to_charity', 'approved', 'rejected'], true)) {
                $query->where('status', $status);
            }
        }

        $invoices = $query->latest()
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        $shifts = Shift::all();
        $departments = Department::where('category', 'medical')->get();
        $registrars = User::all(); // Simplified for now
        $insuranceCompanies = InsuranceCompany::all();

        return view('invoices.index', compact('invoices', 'shifts', 'departments', 'registrars', 'insuranceCompanies', 'isAdmin', 'canSeeAllInvoices'));
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

    public function reportsIndex(\Illuminate\Http\Request $request)
    {
        Gate::authorize('reports.view');
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

        // حالة الديون للمتأخرات
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
        // تحصيل الشهر الحالي
        $collectionMonth = (float) Payment::whereNotNull('approved_by')
            ->whereBetween('received_date', [$start, $end])
            ->sum('amount');

        // --- إحصائيات الأداء حسب الأقسام ---
        $deptPerformance = \App\Models\Department::where('category', 'medical')->get()->map(function($dept) use ($start, $end) {
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

        foreach ($deptPerformance as $dept) {
            if ($dept->level === 'low' && $dept->total > 0) {
                $alerts[] = (app()->getLocale() === 'ar' ? 'انخفاض تحصيل قسم ' : 'Low collection in ') . $dept->name_ar;
            }
        }

        return view('reports.index', compact(
            'revenueToday', 'totalCollected', 'totalDebts', 'remainingUncollected', 'collectionRate',
            'revenueCharity', 'revenueInsurance', 'revenueCash', 'monthlyRevenue',
            'overdue30', 'overdue60', 'overdue90', 'collectionToday', 'collectionMonth', 'alerts',
            'deptPerformance', 'topCharities', 'topInsurances', 'topServices'
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

    public function departmentsIndex(Request $request)
    {
        Gate::authorize('departments.manage');

        $query = Department::withCount('users')->with('manager');

        $this->applyIndexFilters(
            $query,
            $request,
            ['name', 'name_ar', 'code'],
            ['category' => 'category']
        );

        $departments = $query->orderBy('name')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function departmentsCreate()
    {
        Gate::authorize('departments.manage');
        $users = User::orderBy('name')->get();
        return view('departments.create', compact('users'));
    }

    public function departmentsStore(Request $request)
    {
        Gate::authorize('departments.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:20|unique:departments,code',
            'is_active' => 'nullable|boolean',
            'category' => 'required|in:medical,administrative',
            'manager_id' => 'nullable|exists:users,id',
            'entry_fee' => 'nullable|numeric|min:0',
        ]);
        $category = $request->input('category');
        $dept = Department::create([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: null,
            'category' => $category,
            'code' => $request->filled('code') ? strtoupper($request->input('code')) : null,
            'is_active' => $request->boolean('is_active', true),
            'manager_id' => $request->input('manager_id'),
            'entry_fee' => $category === 'medical' && $request->filled('entry_fee') ? (float) $request->input('entry_fee') : null,
        ]);
        ActivityLogger::log('department_created', Department::class, $dept->id, __('Department created') . ': ' . $dept->name, null, $dept->toArray());
        return redirect()->route('departments.index')->with('success', __('Department created successfully.'));
    }

    public function departmentsShow(Department $department)
    {
        Gate::authorize('departments.manage');
        return view('departments.show', compact('department'));
    }

    public function departmentsEdit(Department $department)
    {
        Gate::authorize('departments.manage');
        $users = User::orderBy('name')->get();
        return view('departments.edit', compact('department', 'users'));
    }

    public function departmentsUpdate(Request $request, Department $department)
    {
        Gate::authorize('departments.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:20|unique:departments,code,' . $department->id,
            'is_active' => 'nullable|boolean',
            'category' => 'required|in:medical,administrative',
            'manager_id' => 'nullable|exists:users,id',
            'entry_fee' => 'nullable|numeric|min:0',
        ]);
        $category = $request->input('category');
        $entryFee = ($category === 'medical' && $request->filled('entry_fee')) ? (float) $request->input('entry_fee') : null;
        $department->update([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: null,
            'category' => $category,
            'code' => $request->filled('code') ? strtoupper($request->input('code')) : null,
            'is_active' => $request->boolean('is_active', true),
            'manager_id' => $request->input('manager_id'),
            'entry_fee' => $entryFee,
        ]);
        return redirect()->route('departments.index')->with('success', __('Department updated successfully.'));
    }

    public function departmentsDestroy(Department $department)
    {
        Gate::authorize('departments.manage');
        $department->delete();
        return redirect()->route('departments.index')->with('success', __('Department deleted successfully.'));
    }

    public function servicesIndex(Request $request)
    {
        Gate::authorize('services.manage');

        $query = Service::with('department');

        $this->applyIndexFilters(
            $query,
            $request,
            ['name', 'name_ar', 'code'],
            ['department_id' => 'department_id']
        );

        $services = $query->orderBy('name')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        $departments = Department::orderBy('name')->get();
        return view('services.index', compact('services', 'departments'));
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
            'is_multi_session' => 'boolean',
            'session_count' => 'nullable|integer|min:1|required_if:is_multi_session,1',
            'session_wait_time' => 'nullable|integer|min:1|required_if:is_multi_session,1',
            'session_wait_unit' => 'nullable|in:days,weeks,months|required_if:is_multi_session,1',
        ]);

        $service = Service::create([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: $request->input('name'),
            'code' => $request->input('code'),
            'default_price' => $request->input('default_price'),
            'department_id' => $request->input('department_id'),
            'is_active' => true,
            'is_multi_session' => $request->boolean('is_multi_session', false),
            'session_count' => $request->boolean('is_multi_session') ? $request->input('session_count') : null,
            'session_wait_time' => $request->boolean('is_multi_session') ? $request->input('session_wait_time') : null,
            'session_wait_unit' => $request->boolean('is_multi_session') ? $request->input('session_wait_unit') : null,
        ]);
        ActivityLogger::log('service_created', Service::class, $service->id, __('Service created') . ': ' . $service->name, null, $service->toArray());

        return redirect()->route('services.index')->with('success', __('Service created successfully.'));
    }

    public function servicesShow(Service $service)
    {
        Gate::authorize('services.manage');
        return view('services.show', compact('service'));
    }

    public function servicesEdit(Service $service)
    {
        Gate::authorize('services.manage');
        $departments = Department::orderBy('name')->get();
        return view('services.edit', compact('service', 'departments'));
    }

    public function servicesUpdate(Request $request, Service $service)
    {
        Gate::authorize('services.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'required|string|max:50|unique:services,code,' . $service->id,
            'default_price' => 'required|numeric|min:0',
            'department_id' => 'required|exists:departments,id',
            'is_active' => 'boolean',
            'is_multi_session' => 'boolean',
            'session_count' => 'nullable|integer|min:1|required_if:is_multi_session,1',
            'session_wait_time' => 'nullable|integer|min:1|required_if:is_multi_session,1',
            'session_wait_unit' => 'nullable|in:days,weeks,months|required_if:is_multi_session,1',
        ]);

        $old = $service->toArray();
        $service->update([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: $request->input('name'),
            'code' => $request->input('code'),
            'default_price' => $request->input('default_price'),
            'department_id' => $request->input('department_id'),
            'is_active' => $request->boolean('is_active', true),
            'is_multi_session' => $request->boolean('is_multi_session', false),
            'session_count' => $request->boolean('is_multi_session') ? $request->input('session_count') : null,
            'session_wait_time' => $request->boolean('is_multi_session') ? $request->input('session_wait_time') : null,
            'session_wait_unit' => $request->boolean('is_multi_session') ? $request->input('session_wait_unit') : null,
        ]);
        ActivityLogger::log('service_updated', Service::class, $service->id, __('Service updated') . ': ' . $service->name, $old, $service->toArray());

        return redirect()->route('services.index')->with('success', __('Service updated successfully.'));
    }

    public function servicesDestroy(Service $service)
    {
        Gate::authorize('services.manage');
        $old = $service->toArray();
        $name = $service->name;
        $id = $service->id;
        $service->delete();
        ActivityLogger::log('service_deleted', Service::class, $id, __('Service deleted') . ': ' . $name, $old, null);
        return redirect()->route('services.index')->with('success', __('Service deleted successfully.'));
    }

    public function usersIndex(Request $request)
    {
        Gate::authorize('users.manage');

        $query = User::with('department')->whereNotNull('username');

        $this->applyIndexFilters(
            $query,
            $request,
            ['username', 'email', 'name', 'name_ar'],
            [],
            ['department_id' => 'department_id']
        );

        $users = $query->orderBy('username')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        $departments = Department::orderBy('name')->get();
        return view('users.index', compact('users', 'departments'));
    }

    public function usersCreate()
    {
        Gate::authorize('users.manage');
        $departments = Department::where('is_active', true)->where('category', 'administrative')->orderBy('name')->get();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        return view('users.create', compact('departments', 'roles'));
    }

    public function usersStore(Request $request)
    {
        Gate::authorize('users.manage');

        $request->validate([
            // User fields
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'job_title_ar' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',

            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
            'signature_data' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $userData = [
                'department_id' => $request->input('department_id'),
                'username' => $request->input('username'),
                'email' => $request->input('email') ?: null,
                'password' => bcrypt($request->input('password')),
                'name' => $request->input('name'),
                'name_ar' => $request->input('name_ar') ?: null,
                'job_title' => $request->input('job_title') ?: null,
                'job_title_ar' => $request->input('job_title_ar') ?: null,
                'status' => $request->input('status', 'active'),
            ];
            $signaturePath = $this->saveSignatureFromBase64($request->input('signature_data'));
            if ($signaturePath) {
                $userData['signature'] = $signaturePath;
            }

            // Create user
            $user = User::create($userData);

            // Assign role
            $user->assignRole($request->input('role'));
            ActivityLogger::log('user_created', User::class, $user->id, __('User created') . ': ' . $user->username, null, ['username' => $user->username, 'name' => $user->name]);

            // Email sending block removed as per user request

        });

        return redirect()->route('users.index')->with('success', __('User created successfully.'));
    }

    /** صفحة حسابي — أي موظف مسجل يستطيع رؤية بياناته وتعديل التوقيع الإلكتروني بدون صلاحية users.manage */
    public function accountEdit()
    {
        $user = auth()->user();
        $user->load('department', 'roles');
        return view('account.edit', compact('user'));
    }

    public function accountUpdate(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'signature_data' => 'nullable|string',
        ]);
        $signaturePath = $this->saveSignatureFromBase64($request->input('signature_data'));
        if ($signaturePath) {
            $oldSignature = $user->signature;
            if ($oldSignature && Storage::disk('public')->exists($oldSignature)) {
                Storage::disk('public')->delete($oldSignature);
            }
            $user->update(['signature' => $signaturePath]);
        }
        return redirect()->route('account.edit')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث التوقيع الإلكتروني.' : 'Electronic signature updated.');
    }

    public function usersShow(User $user)
    {
        Gate::authorize('users.manage');
        $user->load('department', 'roles');
        return view('users.show', compact('user'));
    }

    public function usersEdit(User $user)
    {
        Gate::authorize('users.manage');
        $user->load('department', 'roles');
        $departments = Department::where('is_active', true)->where('category', 'administrative')->orderBy('name')->get();
        $roles = Role::where('guard_name', 'web')->orderBy('name')->get();
        return view('users.edit', compact('user', 'departments', 'roles'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        Gate::authorize('users.manage');

        $request->validate([
            // User fields
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'job_title_ar' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',

            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
            'signature_data' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $user) {
            // Update user
            $userData = [
                'department_id' => $request->input('department_id'),
                'username' => $request->input('username'),
                'email' => $request->input('email') ?: null,
                'name' => $request->input('name'),
                'name_ar' => $request->input('name_ar') ?: null,
                'job_title' => $request->input('job_title') ?: null,
                'job_title_ar' => $request->input('job_title_ar') ?: null,
                'status' => $request->input('status', 'active'),
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->input('password'));
            }

            // Handle employee electronic signature (drawn)
            $signaturePath = $this->saveSignatureFromBase64($request->input('signature_data'));
            if ($signaturePath) {
                $oldSignature = $user->signature;
                if ($oldSignature && Storage::disk('public')->exists($oldSignature)) {
                    Storage::disk('public')->delete($oldSignature);
                }
                $userData['signature'] = $signaturePath;
            }

            $user->update($userData);

            // Sync role
            $user->syncRoles([$request->input('role')]);
            ActivityLogger::log('user_updated', User::class, $user->id, __('User updated') . ': ' . $user->username, null, ['username' => $user->username]);
        });

        return redirect()->route('users.index')->with('success', __('User updated successfully.'));
    }

    public function usersDestroy(User $user)
    {
        Gate::authorize('users.manage');

        // Prevent deleting own account
        if (auth()->id() === $user->id) {
            return back()->with('error', __('You cannot delete your own account.'));
        }

        $username = $user->username;
        $userId = $user->id;
        DB::transaction(function () use ($user) {
            // Delete user first (this will also delete roles via pivot table)
            $user->delete();
        });
        ActivityLogger::log('user_deleted', User::class, $userId, __('User deleted') . ': ' . $username, ['username' => $username], null);

        return redirect()->route('users.index')->with('success', __('User deleted successfully.'));
    }

    public function settingsIndex()
    {
        Gate::authorize('settings.manage');
        $hospitalName = Setting::get('hospital_name', '');
        $hospitalNameEn = Setting::get('hospital_name_en', '');
        $healthClusterName = Setting::get('health_cluster_name', '');
        $healthClusterNameEn = Setting::get('health_cluster_name_en', '');
        $managerName = Setting::get('manager_name', '');
        $logoPath = Setting::get('logo', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyAddress = Setting::get('company_address', '');
        $accountNumber = Setting::get('account_number', '');
        $ibanNumber = Setting::get('iban_number', '');
        $bankName = Setting::get('bank_name', '');
        $stampPath = Setting::get('stamp', '');
        $managerSignaturePath = Setting::get('manager_signature', '');
        $departmentManagerName = Setting::get('department_manager_name', '');
        $departmentManagerSignaturePath = Setting::get('department_manager_signature', '');
        // على الاستضافة بدون symlink (مثل Hostinger) الملفات تُقدّم عبر route؛ نتحقق من وجود الملف لتجنّب أيقونة صورة مكسورة
        $logoExists = $logoPath && File::exists(storage_path('app/public/' . $logoPath));
        $stampExists = $stampPath && File::exists(storage_path('app/public/' . $stampPath));
        $managerSignatureExists = $managerSignaturePath && File::exists(storage_path('app/public/' . $managerSignaturePath));
        $departmentManagerSignatureExists = $departmentManagerSignaturePath && File::exists(storage_path('app/public/' . $departmentManagerSignaturePath));
        // مزامنة مرة واحدة: نسخ من storage إلى public/storage حتى يعمل الرابط بدون symlink/route
        if ($logoPath && $logoExists) {
            $this->copyStorageToPublic($logoPath);
        }
        if ($stampPath && $stampExists) {
            $this->copyStorageToPublic($stampPath);
        }
        if ($managerSignaturePath && $managerSignatureExists) {
            $this->copyStorageToPublic($managerSignaturePath);
        }
        if ($departmentManagerSignaturePath && $departmentManagerSignatureExists) {
            $this->copyStorageToPublic($departmentManagerSignaturePath);
        }
        return view('settings.index', compact(
            'hospitalName', 'hospitalNameEn', 'healthClusterName', 'healthClusterNameEn',
            'managerName', 'logoPath', 'companyPhone', 'companyEmail', 'companyAddress',
            'accountNumber', 'ibanNumber', 'bankName', 'stampPath', 'managerSignaturePath',
            'departmentManagerName', 'departmentManagerSignaturePath',
            'logoExists', 'stampExists', 'managerSignatureExists', 'departmentManagerSignatureExists'
        ));
    }

    public function settingsUpdate(Request $request)
    {
        Gate::authorize('settings.manage');
        $request->validate([
            'hospital_name' => 'nullable|string|max:255',
            'hospital_name_en' => 'nullable|string|max:255',
            'health_cluster_name' => 'nullable|string|max:255',
            'health_cluster_name_en' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'company_phone' => 'nullable|string|max:100',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string|max:500',
            'account_number' => 'nullable|string|max:100',
            'iban_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'stamp' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'manager_signature_data' => 'nullable|string',
            'department_manager_name' => 'nullable|string|max:255',
            'department_manager_signature_data' => 'nullable|string',
        ]);
        Setting::set('hospital_name', $request->input('hospital_name', ''), 'general');
        Setting::set('hospital_name_en', $request->input('hospital_name_en', ''), 'general');
        Setting::set('health_cluster_name', $request->input('health_cluster_name', ''), 'general');
        Setting::set('health_cluster_name_en', $request->input('health_cluster_name_en', ''), 'general');
        Setting::set('manager_name', $request->input('manager_name', ''), 'general');
        Setting::set('company_phone', $request->input('company_phone', ''), 'general');
        Setting::set('company_email', $request->input('company_email', ''), 'general');
        Setting::set('company_address', $request->input('company_address', ''), 'general');
        Setting::set('account_number', $request->input('account_number', ''), 'general');
        Setting::set('iban_number', $request->input('iban_number', ''), 'general');
        Setting::set('bank_name', $request->input('bank_name', ''), 'general');
        Setting::set('department_manager_name', $request->input('department_manager_name', ''), 'general');

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('logo', '');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
                $this->deletePublicStorageFile($oldLogo);
            }
            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('logo', $path, 'general');
            $this->copyStorageToPublic($path);
        }

        if ($request->hasFile('stamp')) {
            $oldStamp = Setting::get('stamp', '');
            if ($oldStamp && Storage::disk('public')->exists($oldStamp)) {
                Storage::disk('public')->delete($oldStamp);
                $this->deletePublicStorageFile($oldStamp);
            }
            $path = $request->file('stamp')->store('settings', 'public');
            Setting::set('stamp', $path, 'general');
            $this->copyStorageToPublic($path);
        }

        $managerSigPath = $this->saveSignatureFromBase64($request->input('manager_signature_data'), 'settings');
        if ($managerSigPath) {
            $oldSig = Setting::get('manager_signature', '');
            if ($oldSig && Storage::disk('public')->exists($oldSig)) {
                Storage::disk('public')->delete($oldSig);
                $this->deletePublicStorageFile($oldSig);
            }
            Setting::set('manager_signature', $managerSigPath, 'general');
            $this->copyStorageToPublic($managerSigPath);
        }

        $deptManagerSigPath = $this->saveSignatureFromBase64($request->input('department_manager_signature_data'), 'settings');
        if ($deptManagerSigPath) {
            $oldDeptSig = Setting::get('department_manager_signature', '');
            if ($oldDeptSig && Storage::disk('public')->exists($oldDeptSig)) {
                Storage::disk('public')->delete($oldDeptSig);
                $this->deletePublicStorageFile($oldDeptSig);
            }
            Setting::set('department_manager_signature', $deptManagerSigPath, 'general');
            $this->copyStorageToPublic($deptManagerSigPath);
        }


        ActivityLogger::log('settings_updated', null, null, __('Settings updated'), null, ['hospital_name' => $request->input('hospital_name')]);

        return back()->with('success', __('Saved successfully.'));
    }

    /**
     * نسخ ملف من storage/app/public إلى public/storage حتى يخدمه الويب مباشرة (بدون symlink/route).
     */
    private function copyStorageToPublic(string $relativePath): void
    {
        $src = storage_path('app/public/' . $relativePath);
        $dst = public_path('storage/' . $relativePath);
        if (! File::exists($src) || File::isDirectory($src)) {
            return;
        }
        $dir = dirname($dst);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        @copy($src, $dst);
    }

    /**
     * حذف نسخة الملف من public/storage عند استبدال الشعار/الختم/التوقيع.
     */
    private function deletePublicStorageFile(string $relativePath): void
    {
        $path = public_path('storage/' . $relativePath);
        if (File::exists($path) && File::isFile($path)) {
            File::delete($path);
        }
    }

    public function codesUpload()
    {
        Gate::authorize('codes.upload');
        return view('codes.upload');
    }

    public function codesUploadStore(Request $request)
    {
        Gate::authorize('codes.upload');
        $request->validate(['file' => 'required|file|max:20480'], [], ['file' => __('File')]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?? '');
        $allowed = ['csv', 'txt', 'xlsx', 'xls'];
        if (! in_array($extension, $allowed, true)) {
            return back()->withErrors(['file' => __('Invalid file type. Use CSV, TXT, XLSX or XLS.')]);
        }

        $path = $file->getRealPath();

        $importer = new OfficialCodesImporter();

        // Check Row Limit (40 records)
        $rowCount = $importer->countTotalRows($path, $extension);
        if ($rowCount > 41) { // 40 data rows + 1 header row
            return back()->withErrors(['file' => app()->getLocale() === 'ar' ? 'عذراً، الحد الأقصى للملف الواحد هو 40 سجلاً فقط.' : 'Sorry, the maximum number of records per file is 40 only.']);
        }

        [$created, $updated] = $importer->import($path, $extension);

        ActivityLogger::log('codes_uploaded', null, null, __('Upload official codes') . " (created: {$created}, updated: {$updated})", null, ['created' => $created, 'updated' => $updated]);

        return back()->with('success', __('Processed successfully. Created: :c, Updated: :u', ['c' => $created, 'u' => $updated]));
    }

    public function activityIndex()
    {
        Gate::authorize('activity.view');
        $logs = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->paginate(50);
        return view('activity.index', compact('logs'));
    }

    // ——— Insurance Companies ———
    public function insuranceCompaniesIndex(Request $request)
    {
        Gate::authorize('insurance_companies.manage');

        $query = InsuranceCompany::withCount('patients');

        $this->applyIndexFilters(
            $query,
            $request,
            ['name', 'name_ar', 'contact_person', 'phone', 'email'],
            []
        );

        $companies = $query->orderBy('name')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        return view('insurance-companies.index', compact('companies'));
    }

    public function insuranceCompaniesCreate()
    {
        Gate::authorize('insurance_companies.manage');
        return view('insurance-companies.create');
    }

    public function insuranceCompaniesStore(Request $request)
    {
        Gate::authorize('insurance_companies.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'fax' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        $company = InsuranceCompany::create([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: null,
            'contact_person' => $request->input('contact_person') ?: null,
            'phone' => $request->input('phone') ?: null,
            'email' => $request->input('email') ?: null,
            'fax' => $request->input('fax') ?: null,
            'address' => $request->input('address') ?: null,
            'notes' => $request->input('notes') ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        ActivityLogger::log('insurance_company_created', InsuranceCompany::class, $company->id, __('Insurance company created') . ': ' . $company->name, null, $company->toArray());
        return redirect()->route('insurance-companies.index')->with('success', __('Insurance company created successfully.'));
    }

    public function insuranceCompaniesShow(InsuranceCompany $insurance_company)
    {
        Gate::authorize('insurance_companies.manage');
        $insurance_company->loadCount('patients');
        return view('insurance-companies.show', compact('insurance_company'));
    }

    public function insuranceCompaniesEdit(InsuranceCompany $insurance_company)
    {
        Gate::authorize('insurance_companies.manage');
        return view('insurance-companies.edit', compact('insurance_company'));
    }

    public function insuranceCompaniesUpdate(Request $request, InsuranceCompany $insurance_company)
    {
        Gate::authorize('insurance_companies.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'fax' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        $old = $insurance_company->toArray();
        $insurance_company->update([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: null,
            'contact_person' => $request->input('contact_person') ?: null,
            'phone' => $request->input('phone') ?: null,
            'email' => $request->input('email') ?: null,
            'fax' => $request->input('fax') ?: null,
            'address' => $request->input('address') ?: null,
            'notes' => $request->input('notes') ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        ActivityLogger::log('insurance_company_updated', InsuranceCompany::class, $insurance_company->id, __('Insurance company updated') . ': ' . $insurance_company->name, $old, $insurance_company->toArray());
        return redirect()->route('insurance-companies.index')->with('success', __('Insurance company updated successfully.'));
    }

    public function insuranceCompaniesDestroy(InsuranceCompany $insurance_company)
    {
        Gate::authorize('insurance_companies.manage');
        $old = $insurance_company->toArray();
        $name = $insurance_company->name;
        $id = $insurance_company->id;
        $insurance_company->delete();
        ActivityLogger::log('insurance_company_deleted', InsuranceCompany::class, $id, __('Insurance company deleted') . ': ' . $name, $old, null);
        return redirect()->route('insurance-companies.index')->with('success', __('Insurance company deleted successfully.'));
    }

    // ——— Charity Entities ———
    public function charityEntitiesIndex(Request $request)
    {
        Gate::authorize('charity_entities.manage');

        $query = CharityEntity::withCount('patients');

        $this->applyIndexFilters(
            $query,
            $request,
            ['name', 'name_ar', 'contact_person', 'phone', 'email'],
            []
        );

        $entities = $query->orderByRaw('COALESCE(name_ar, name)')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        return view('charity-entities.index', compact('entities'));
    }

    public function charityEntitiesCreate()
    {
        Gate::authorize('charity_entities.manage');
        return view('charity-entities.create');
    }

    public function charityEntitiesStore(Request $request)
    {
        Gate::authorize('charity_entities.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'fax' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        $entity = CharityEntity::create([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: null,
            'contact_person' => $request->input('contact_person') ?: null,
            'phone' => $request->input('phone') ?: null,
            'email' => $request->input('email') ?: null,
            'fax' => $request->input('fax') ?: null,
            'address' => $request->input('address') ?: null,
            'notes' => $request->input('notes') ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        ActivityLogger::log('charity_entity_created', CharityEntity::class, $entity->id, __('Charity entity created') . ': ' . $entity->name, null, $entity->toArray());
        return redirect()->route('charity-entities.index')->with('success', __('Charity entity created successfully.'));
    }

    public function charityEntitiesShow(CharityEntity $charity_entity)
    {
        Gate::authorize('charity_entities.manage');
        $charity_entity->loadCount('patients');
        return view('charity-entities.show', compact('charity_entity'));
    }

    public function charityEntitiesEdit(CharityEntity $charity_entity)
    {
        Gate::authorize('charity_entities.manage');
        return view('charity-entities.edit', compact('charity_entity'));
    }

    public function charityEntitiesUpdate(Request $request, CharityEntity $charity_entity)
    {
        Gate::authorize('charity_entities.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'fax' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        $old = $charity_entity->toArray();
        $charity_entity->update([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: null,
            'contact_person' => $request->input('contact_person') ?: null,
            'phone' => $request->input('phone') ?: null,
            'email' => $request->input('email') ?: null,
            'fax' => $request->input('fax') ?: null,
            'address' => $request->input('address') ?: null,
            'notes' => $request->input('notes') ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        ActivityLogger::log('charity_entity_updated', CharityEntity::class, $charity_entity->id, __('Charity entity updated') . ': ' . $charity_entity->name, $old, $charity_entity->toArray());
        return redirect()->route('charity-entities.index')->with('success', __('Charity entity updated successfully.'));
    }

    public function charityEntitiesDestroy(CharityEntity $charity_entity)
    {
        Gate::authorize('charity_entities.manage');
        $old = $charity_entity->toArray();
        $name = $charity_entity->name;
        $id = $charity_entity->id;
        $charity_entity->delete();
        ActivityLogger::log('charity_entity_deleted', CharityEntity::class, $id, __('Charity entity deleted') . ': ' . $name, $old, null);
        return redirect()->route('charity-entities.index')->with('success', __('Charity entity deleted successfully.'));
    }

    /** Decode base64 image (data URL from signature pad) and save to storage. Returns path or null. */
    private function saveSignatureFromBase64(?string $data, string $folder = 'signatures'): ?string
    {
        if (! $data || ! preg_match('/^data:image\/(\w+);base64,/', $data)) {
            return null;
        }
        $data = substr($data, (int) strpos($data, ',') + 1);
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return null;
        }
        $filename = $folder . '/' . uniqid('sig_', true) . '.png';
        Storage::disk('public')->put($filename, $decoded);

        return $filename;
    }
}
