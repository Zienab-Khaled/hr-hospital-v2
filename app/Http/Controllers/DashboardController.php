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
        $user = auth()->user();
        $isInsuranceClerk = $user && $user->hasRole('insurance_clerk');

        $recentPatients = Patient::with(['insuranceCompany', 'charityEntity'])
            ->when($isInsuranceClerk, fn ($q) => $q->where('payment_type', 'insurance'))
            ->latest()
            ->take(10)
            ->get();

        $recentVisits = \App\Models\Visit::with(['patient', 'department', 'shift'])
            ->when($isInsuranceClerk, fn ($q) => $q->whereHas('patient', fn ($pq) => $pq->where('payment_type', 'insurance')))
            ->latest()
            ->take(10)
            ->get();

        $recentInvoices = \App\Models\Invoice::with(['patient'])
            ->when($isInsuranceClerk, fn ($q) => $q->whereHas('patient', fn ($pq) => $pq->where('payment_type', 'insurance')))
            ->latest()
            ->take(10)
            ->get();

        // Combined Insurance and Charity Claims (موظف التأمين يرى مطالبات التأمين فقط)
        $insuranceClaims = \App\Models\InsuranceClaim::with(['invoice.patient', 'insuranceCompany'])->latest()->take(5)->get();
        $charityClaims = $isInsuranceClerk ? collect() : \App\Models\CharityClaim::with(['invoice.patient', 'charityEntity'])->latest()->take(5)->get();

        $recentClaims = $insuranceClaims->concat($charityClaims)->sortByDesc('created_at')->take(10);

        // Debt Tracking: Invoices from Insurance and Charity that are not fully paid
        $totalInvoiced = (float) Invoice::sum('total_amount');
        $totalCollected = (float) Payment::sum('amount');

        // Outstanding debts are remaining amounts on invoices
        // These will decrease as claims are marked as 'paid' and payments are recorded
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

}
