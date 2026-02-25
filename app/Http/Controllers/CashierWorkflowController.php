<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CashierWorkflowController extends Controller
{
    /**
     * Display a listing of invoices ready for deposit.
     */
    public function index(Request $request)
    {
        // For simplicity, using reports.view permission or we can define a new one
        Gate::authorize('reports.view');

        $date = $request->input('date', Carbon::today()->toDateString());

        $invoices = Invoice::where('audit_status', 'ready_for_deposit')
            ->whereDate('invoice_date', $date)
            ->with(['patient', 'visit.shift', 'payments'])
            ->latest()
            ->get();

        return view('revenue.cashier.index', compact('invoices', 'date'));
    }

    /**
     * Verify OTP and receive funds.
     */
    public function receive(Request $request, Invoice $invoice)
    {
        Gate::authorize('reports.view');

        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        if ($request->otp !== $invoice->cashier_otp) {
            return back()->with('error', app()->getLocale() === 'ar'
                ? 'رمز التحقق غير صحيح. يرجى التأكد من المحاسب.'
                : 'Invalid OTP. Please check with the accountant.');
        }

        $invoice->update([
            'audit_status' => 'paid', // Or a new status like 'deposited_with_cashier'
            'cashier_id' => auth()->id(),
            'cashier_received_at' => now(),
            'status' => 'paid'
        ]);

        // Mark all payments as matched/paid if they weren't already
        $invoice->payments()->update(['audit_status' => 'matched']);

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم استلام المبلغ وتوثيق العملية بنجاح.'
            : 'Funds received and transaction documented successfully.');
    }
}
