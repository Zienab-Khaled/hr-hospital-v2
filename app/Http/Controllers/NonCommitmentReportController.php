<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\NonCommitmentReport;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Support\RoleNav;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class NonCommitmentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function authorizeWorkflowAccess(): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if (
            RoleNav::hasSupervisorVisibility($user)
            || $user->can('procedures.non_commitment')
            || $user->hasAnyRole(['patient_follow_up', 'accountant', 'manager', 'admin'])
        ) {
            return;
        }

        abort(403);
    }

    public function index()
    {
        $this->authorizeWorkflowAccess();

        $user = auth()->user();
        $query = NonCommitmentReport::with(['patient', 'invoice', 'collector', 'followUpUser', 'accountant', 'manager'])
            ->latest('reported_at')
            ->latest('id');

        if (RoleNav::hasSupervisorVisibility($user) || $user->hasRole('admin')) {
            // الإدارة ترى الكل
        } elseif ($user->hasRole('patient_follow_up')) {
            $query->where('workflow_status', NonCommitmentReport::STATUS_PENDING_FOLLOW_UP);
        } elseif ($user->hasRole('accountant')) {
            $query->where('workflow_status', NonCommitmentReport::STATUS_PENDING_ACCOUNTANT);
        } elseif ($user->hasRole('manager')) {
            $query->where('workflow_status', NonCommitmentReport::STATUS_PENDING_MANAGER);
        }

        $reports = $query->paginate(20);

        return view('non-commitment-reports.index', compact('reports'));
    }

    public function show(NonCommitmentReport $nonCommitmentReport)
    {
        $this->authorizeWorkflowAccess();

        $report = $nonCommitmentReport->load([
            'patient',
            'invoice.items.service',
            'collector',
            'followUpUser',
            'accountant',
            'manager',
            'createdByUser',
        ]);

        return view('non-commitment-reports.show', compact('report'));
    }

    public function create()
    {
        $this->authorize('procedures.non_commitment');
        $patients = Patient::orderBy('name')->get();

        return view('non-commitment-reports.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $this->authorize('procedures.non_commitment');
        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'report_date' => 'required|date',
            'notes' => 'nullable|string',
            'report_number' => 'nullable|string|max:100',
        ]);

        $report = NonCommitmentReport::create([
            'patient_id' => $valid['patient_id'],
            'report_date' => $valid['report_date'],
            'notes' => $valid['notes'] ?? null,
            'report_number' => $valid['report_number'] ?? null,
            'reported_at' => now(),
            'workflow_status' => NonCommitmentReport::STATUS_PENDING_FOLLOW_UP,
            'created_by' => auth()->id(),
            'collector_id' => auth()->id(),
        ]);

        $this->notifyNextStage($report, NonCommitmentReport::STATUS_PENDING_FOLLOW_UP);

        return redirect()
            ->route('non-commitment-reports.show', $report)
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء محضر رفض التوقيع وإحالته لمتابعة المرضى.' : 'Refusal-to-sign report created and sent to patient follow-up.');
    }

    public function updateReportNumber(Request $request, NonCommitmentReport $nonCommitmentReport)
    {
        $this->authorizeWorkflowAccess();

        $valid = $request->validate([
            'report_number' => 'nullable|string|max:100',
        ]);

        $nonCommitmentReport->update([
            'report_number' => $valid['report_number'] ?: null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'report_number' => $nonCommitmentReport->report_number]);
        }

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم حفظ رقم المحضر.' : 'Report number saved.');
    }

    public function advance(NonCommitmentReport $nonCommitmentReport)
    {
        $this->authorizeWorkflowAccess();

        $user = auth()->user();
        if (! $nonCommitmentReport->canAdvance($user)) {
            abort(403, app()->getLocale() === 'ar'
                ? 'لا يمكنك إحالة هذا المحضر في المرحلة الحالية.'
                : 'You cannot advance this report at the current stage.');
        }

        $status = $nonCommitmentReport->workflow_status;

        if ($status === NonCommitmentReport::STATUS_PENDING_FOLLOW_UP) {
            $nonCommitmentReport->update([
                'workflow_status' => NonCommitmentReport::STATUS_PENDING_ACCOUNTANT,
                'follow_up_id' => $user->id,
                'follow_up_at' => now(),
            ]);
            $this->notifyNextStage($nonCommitmentReport->fresh(), NonCommitmentReport::STATUS_PENDING_ACCOUNTANT);
            $msg = app()->getLocale() === 'ar' ? 'تم التأكيد وإحالة المحضر للمحاسب.' : 'Confirmed and forwarded to accountant.';
        } elseif ($status === NonCommitmentReport::STATUS_PENDING_ACCOUNTANT) {
            $nonCommitmentReport->update([
                'workflow_status' => NonCommitmentReport::STATUS_PENDING_MANAGER,
                'accountant_id' => $user->id,
                'accountant_at' => now(),
            ]);
            $this->notifyNextStage($nonCommitmentReport->fresh(), NonCommitmentReport::STATUS_PENDING_MANAGER);
            $msg = app()->getLocale() === 'ar' ? 'تم التأكيد وإحالة المحضر للمدير.' : 'Confirmed and forwarded to manager.';
        } elseif ($status === NonCommitmentReport::STATUS_PENDING_MANAGER) {
            $nonCommitmentReport->update([
                'workflow_status' => NonCommitmentReport::STATUS_COMPLETED,
                'manager_id' => $user->id,
                'manager_at' => now(),
            ]);
            $this->notifyNextStage($nonCommitmentReport->fresh(), NonCommitmentReport::STATUS_COMPLETED);
            $msg = app()->getLocale() === 'ar' ? 'تم اعتماد المحضر من المدير.' : 'Report approved by manager.';
        } else {
            return back()->withErrors(['error' => app()->getLocale() === 'ar' ? 'المحضر مكتمل مسبقاً.' : 'Report already completed.']);
        }

        ActivityLogger::log(
            'Refusal Sign Advanced',
            'NonCommitmentReport',
            $nonCommitmentReport->id,
            'Refusal-to-sign report advanced to '.$nonCommitmentReport->fresh()->workflow_status,
            null,
            null
        );

        return back()->with('success', $msg);
    }

    public function print(NonCommitmentReport $nonCommitmentReport)
    {
        $this->authorizeWorkflowAccess();

        $report = $nonCommitmentReport->load(['patient', 'invoice.items.service', 'collector', 'followUpUser', 'accountant', 'manager']);
        $invoice = $report->invoice;
        $manager = User::getManagerForSignature();
        $settings = \App\Models\Setting::first();

        return view('invoices.print-non-commitment', compact('report', 'invoice', 'manager', 'settings'));
    }

    protected function notifyNextStage(NonCommitmentReport $report, string $status): void
    {
        $roles = match ($status) {
            NonCommitmentReport::STATUS_PENDING_FOLLOW_UP => ['patient_follow_up'],
            NonCommitmentReport::STATUS_PENDING_ACCOUNTANT => ['accountant'],
            NonCommitmentReport::STATUS_PENDING_MANAGER => ['manager', 'admin'],
            NonCommitmentReport::STATUS_COMPLETED => ['manager', 'admin', 'accountant', 'patient_follow_up'],
            default => [],
        };

        if ($roles === []) {
            return;
        }

        $users = User::role($roles)->get();
        if ($users->isEmpty()) {
            return;
        }

        $patientName = $report->patient?->fullArabicName() ?? $report->patient?->name ?? '#'.$report->id;
        $url = route('non-commitment-reports.show', $report);
        $ar = app()->getLocale() === 'ar';

        $messages = [
            NonCommitmentReport::STATUS_PENDING_FOLLOW_UP => [
                'ar' => "محضر رفض توقيع جديد للمريض {$patientName} — بانتظار تأكيد متابعة المرضى.",
                'en' => "New refusal-to-sign report for {$patientName} — awaiting patient follow-up.",
            ],
            NonCommitmentReport::STATUS_PENDING_ACCOUNTANT => [
                'ar' => "محضر رفض توقيع للمريض {$patientName} أُحيل للمحاسب.",
                'en' => "Refusal-to-sign report for {$patientName} forwarded to accountant.",
            ],
            NonCommitmentReport::STATUS_PENDING_MANAGER => [
                'ar' => "محضر رفض توقيع للمريض {$patientName} بانتظار اعتماد المدير.",
                'en' => "Refusal-to-sign report for {$patientName} awaiting manager approval.",
            ],
            NonCommitmentReport::STATUS_COMPLETED => [
                'ar' => "اكتمل مسار محضر رفض التوقيع للمريض {$patientName}.",
                'en' => "Refusal-to-sign workflow completed for {$patientName}.",
            ],
        ];

        $locale = $ar ? 'ar' : 'en';
        $msg = $messages[$status][$locale] ?? $messages[$status]['ar'];

        Notification::send($users, new SystemNotification([
            'title' => $ar ? 'محضر رفض توقيع' : 'Refusal-to-sign report',
            'message' => $msg,
            'action_url' => $url,
            'type' => 'info',
            'metadata' => ['non_commitment_report_id' => $report->id, 'status' => $status],
        ]));
    }
}
