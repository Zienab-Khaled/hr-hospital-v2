<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Authorization;
use App\Models\CharityClaim;
use App\Models\CharityEntity;
use App\Models\Department;
use App\Models\Employee;
use App\Models\InsuranceClaim;
use App\Models\InsuranceCompany;
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
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentialsMail;
use Spatie\Permission\Models\Role;

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
        $departments = Department::withCount('employees')->orderBy('name')->paginate(15);
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
        $dept = Department::create([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: null,
            'code' => $request->filled('code') ? strtoupper($request->input('code')) : null,
            'is_active' => $request->boolean('is_active', true),
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
        return view('departments.edit', compact('department'));
    }

    public function departmentsUpdate(Request $request, Department $department)
    {
        Gate::authorize('departments.manage');
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:20|unique:departments,code,' . $department->id,
            'is_active' => 'nullable|boolean',
        ]);
        $department->update([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: null,
            'code' => $request->filled('code') ? strtoupper($request->input('code')) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('departments.index')->with('success', __('Department updated successfully.'));
    }

    public function departmentsDestroy(Department $department)
    {
        Gate::authorize('departments.manage');
        $department->delete();
        return redirect()->route('departments.index')->with('success', __('Department deleted successfully.'));
    }

    public function servicesIndex()
    {
        Gate::authorize('services.manage');
        $services = Service::with('department')->orderBy('name')->paginate(15);
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

        $service = Service::create([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: $request->input('name'),
            'code' => $request->input('code'),
            'default_price' => $request->input('default_price'),
            'department_id' => $request->input('department_id'),
            'is_active' => true,
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
        ]);

        $old = $service->toArray();
        $service->update([
            'name' => $request->input('name'),
            'name_ar' => $request->input('name_ar') ?: $request->input('name'),
            'code' => $request->input('code'),
            'default_price' => $request->input('default_price'),
            'department_id' => $request->input('department_id'),
            'is_active' => $request->boolean('is_active', true)
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

    public function usersIndex()
    {
        Gate::authorize('users.manage');
        $users = User::with('employee.department')->whereNotNull('username')->orderBy('username')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function usersCreate()
    {
        Gate::authorize('users.manage');
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('departments', 'roles'));
    }

    public function usersStore(Request $request)
    {
        Gate::authorize('users.manage');

        $request->validate([
            // Employee fields
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'job_title_ar' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',

            // User fields
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        DB::transaction(function () use ($request) {
            // Create employee first
            $employee = Employee::create([
                'department_id' => $request->input('department_id'),
                'name' => $request->input('name'),
                'name_ar' => $request->input('name_ar') ?: null,
                'job_title' => $request->input('job_title') ?: null,
                'job_title_ar' => $request->input('job_title_ar') ?: null,
                'status' => $request->input('status', 'active'),
            ]);

            // Create user with employee_id
            $user = User::create([
                'employee_id' => $employee->id,
                'username' => $request->input('username'),
                'email' => $request->input('email') ?: null,
                'password' => bcrypt($request->input('password')),
                'name' => $request->input('name'),
            ]);

            // Assign role
            $user->assignRole($request->input('role'));
            ActivityLogger::log('user_created', User::class, $user->id, __('Employee created') . ': ' . $user->username, null, ['username' => $user->username, 'name' => $user->employee->name ?? $user->name]);

            // Send credentials email
            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new UserCredentialsMail($request->input('username'), $request->input('password')));
                } catch (\Exception $e) {
                    // Log error but don't fail transaction
                    \Log::error('Failed to send credentials email: ' . $e->getMessage());
                }
            }
        });

        return redirect()->route('users.index')->with('success', __('Employee created successfully.'));
    }

    public function usersShow(User $user)
    {
        Gate::authorize('users.manage');
        $user->load('employee.department', 'roles');
        return view('users.show', compact('user'));
    }

    public function usersEdit(User $user)
    {
        Gate::authorize('users.manage');
        $user->load('employee.department', 'roles');
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('users.edit', compact('user', 'departments', 'roles'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        Gate::authorize('users.manage');

        $request->validate([
            // Employee fields
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'job_title_ar' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',

            // User fields
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        DB::transaction(function () use ($request, $user) {
            // Update employee
            if ($user->employee) {
                $user->employee->update([
                    'department_id' => $request->input('department_id'),
                    'name' => $request->input('name'),
                    'name_ar' => $request->input('name_ar') ?: null,
                    'job_title' => $request->input('job_title') ?: null,
                    'job_title_ar' => $request->input('job_title_ar') ?: null,
                    'status' => $request->input('status', 'active'),
                ]);
            }

            // Update user
            $userData = [
                'username' => $request->input('username'),
                'email' => $request->input('email') ?: null,
                'name' => $request->input('name'),
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->input('password'));
            }

            $user->update($userData);

            // Sync role
            $user->syncRoles([$request->input('role')]);
            ActivityLogger::log('user_updated', User::class, $user->id, __('Employee updated') . ': ' . $user->username, null, ['username' => $user->username]);
        });

        return redirect()->route('users.index')->with('success', __('Employee updated successfully.'));
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
            $employeeId = $user->employee_id;

            // Delete user first (this will also delete roles via pivot table)
            $user->delete();

            // Delete employee if exists
            if ($employeeId) {
                Employee::where('id', $employeeId)->delete();
            }
        });
        ActivityLogger::log('user_deleted', User::class, $userId, __('Employee deleted') . ': ' . $username, ['username' => $username], null);

        return redirect()->route('users.index')->with('success', __('Employee deleted successfully.'));
    }

    public function settingsIndex()
    {
        Gate::authorize('settings.manage');
        $hospitalName = Setting::get('hospital_name', '');
        $managerName = Setting::get('manager_name', '');
        $logoPath = Setting::get('logo', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyAddress = Setting::get('company_address', '');
        return view('settings.index', compact('hospitalName', 'managerName', 'logoPath', 'companyPhone', 'companyEmail', 'companyAddress'));
    }

    public function settingsUpdate(Request $request)
    {
        Gate::authorize('settings.manage');
        $request->validate([
            'hospital_name' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'company_phone' => 'nullable|string|max:100',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string|max:500',
        ]);
        Setting::set('hospital_name', $request->input('hospital_name', ''), 'general');
        Setting::set('manager_name', $request->input('manager_name', ''), 'general');
        Setting::set('company_phone', $request->input('company_phone', ''), 'general');
        Setting::set('company_email', $request->input('company_email', ''), 'general');
        Setting::set('company_address', $request->input('company_address', ''), 'general');

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('logo', '');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('logo', $path, 'general');
        }
        ActivityLogger::log('settings_updated', null, null, __('Settings updated'), null, ['hospital_name' => $request->input('hospital_name')]);

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
        ActivityLogger::log('codes_uploaded', null, null, __('Upload official codes') . " (created: {$created}, updated: {$updated})", null, ['created' => $created, 'updated' => $updated]);
        return back()->with('success', __('Processed successfully. Created: :c, Updated: :u', ['c' => $created, 'u' => $updated]));
    }

    public function activityIndex()
    {
        Gate::authorize('activity.view');
        $logs = ActivityLog::with('user.employee')
            ->orderByDesc('created_at')
            ->paginate(50);
        return view('activity.index', compact('logs'));
    }

    // ——— Insurance Companies ———
    public function insuranceCompaniesIndex()
    {
        Gate::authorize('insurance_companies.manage');
        $companies = InsuranceCompany::withCount('patients')->orderBy('name')->paginate(15);
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
    public function charityEntitiesIndex()
    {
        Gate::authorize('charity_entities.manage');
        $entities = CharityEntity::withCount('patients')->orderBy('name')->paginate(15);
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
}
