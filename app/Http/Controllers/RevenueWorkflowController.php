<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Shift;
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

        $query = Invoice::whereDate('invoice_date', $date)
            ->with(['patient', 'visit.shift', 'items.service', 'payments.receivedByUser']);

        if ($shiftId) {
            $query->whereHas('visit', function ($q) use ($shiftId) {
                $q->where('shift_id', $shiftId);
            });
        }

        $invoices = $query->latest()->get();
        $shifts = Shift::where('is_active', true)->orderBy('sort_order')->get();

        return view('revenue.control-room', compact('invoices', 'shifts', 'date', 'shiftId'));
    }

    public function match(Invoice $invoice)
    {
        Gate::authorize('reports.view');

        $invoice->update([
            'audit_status' => 'matched',
            'status' => 'approved' // If it was pending
        ]);

        // Also match any linked payments
        $invoice->payments()->update(['audit_status' => 'matched']);

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
}
