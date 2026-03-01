<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * تاب المديونيات: حصر الفواتير غير المسددة (أو المسددة جزئياً)،
 * إرسال تبليغ للمريض، وتحديث الحالة (تم التبليغ / تم السداد).
 */
class DebtsController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('procedures.debt_inventory');

        $query = Invoice::where('remaining_amount', '>', 0)
            ->with(['patient', 'items.service', 'debtNotifiedBy']);

        $statusFilter = $request->input('debt_status');
        if ($statusFilter !== null && $statusFilter !== '') {
            if ($statusFilter === 'not_notified') {
                $query->whereNull('debt_status');
            } else {
                $query->where('debt_status', $statusFilter);
            }
        }

        $invoices = $query->latest('invoice_date')->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'total_count' => Invoice::where('remaining_amount', '>', 0)->count(),
            'total_remaining' => (float) Invoice::where('remaining_amount', '>', 0)->sum('remaining_amount'),
            'notified_count' => Invoice::where('remaining_amount', '>', 0)->where('debt_status', 'notified')->count(),
            'not_notified_count' => Invoice::where('remaining_amount', '>', 0)->whereNull('debt_status')->count(),
        ];

        return view('debts.index', compact('invoices', 'stats'));
    }

    /**
     * إرسال تبليغ للمريض بأن عليه مبلغ X مقابل خدمات Y,Z وتحديث الحالة إلى تم التبليغ.
     */
    public function notify(Invoice $invoice)
    {
        Gate::authorize('procedures.debt_inventory');

        if ((float) $invoice->remaining_amount <= 0) {
            return back()->with('warning', app()->getLocale() === 'ar' ? 'هذه الفاتورة مسددة بالكامل.' : 'This invoice is fully paid.');
        }

        $invoice->update([
            'debt_status' => 'notified',
            'debt_notified_at' => now(),
            'debt_notified_by' => auth()->id(),
        ]);

        $patient = $invoice->patient;
        $servicesList = $invoice->items->map(fn ($i) => $i->service?->name_ar ?? $i->service?->name ?? $i->description)->filter()->implode('، ');
        $amount = number_format((float) $invoice->remaining_amount, 2);
        $patientName = $patient->name_ar ?? $patient->name;

        ActivityLogger::log(
            'Debt Notification Sent',
            'Invoice',
            $invoice->id,
            "تم التبليغ للمريض: {$patientName} — مبلغ مستحق: {$amount} ريال — خدمات: {$servicesList}",
            null,
            ['debt_notified_by' => auth()->id()]
        );

        $message = app()->getLocale() === 'ar'
            ? "تم تسجيل التبليغ بنجاح. المريض {$patientName} — المبلغ المستحق: {$amount} ريال — الخدمات: {$servicesList}"
            : "Notification recorded. Patient {$patientName} — Amount due: {$amount} SAR — Services: {$servicesList}";

        return back()->with('success', $message);
    }

    /**
     * تسجيل السداد من تاب المديونيات — ينزل المبلغ في إيرادات اليوم.
     */
    public function markPaid(Invoice $invoice)
    {
        Gate::authorize('procedures.debt_inventory');

        if ((float) $invoice->remaining_amount <= 0) {
            return back()->with('warning', app()->getLocale() === 'ar' ? 'هذه الفاتورة مسددة بالكامل.' : 'This invoice is fully paid.');
        }

        if ($invoice->debt_status !== 'notified') {
            return back()->with('warning', app()->getLocale() === 'ar' ? 'يُفضّل تسجيل «تم التبليغ» قبل «تم السداد».' : 'Notify the patient first, then mark as paid.');
        }

        $amount = (float) $invoice->remaining_amount;

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_type' => 'cash',
                'amount' => $amount,
                'received_date' => now(),
                'received_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'status' => 'approved',
                'audit_status' => 'matched',
                'notes' => app()->getLocale() === 'ar' ? 'سداد من تاب المديونيات' : 'Payment recorded from Debts tab',
            ]);

            $newPaidAmount = $invoice->paid_amount + $amount;
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => 0,
                'status' => 'paid',
                'debt_status' => 'paid',
            ]);

            DB::commit();

            ActivityLogger::log('Debt Marked Paid', 'Invoice', $invoice->id, "تم السداد من تاب المديونيات — مبلغ: " . number_format($amount, 2) . " ريال", null, ['payment_id' => $payment->id]);

            $message = app()->getLocale() === 'ar'
                ? "تم تسجيل السداد بنجاح. المبلغ " . number_format($amount, 2) . " ريال سيظهر في إيرادات اليوم."
                : "Payment recorded. Amount " . number_format($amount, 2) . " SAR will appear in today's revenue.";

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
