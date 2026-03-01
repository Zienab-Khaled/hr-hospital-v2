<?php

namespace App\Http\Controllers;

use App\Models\InsuranceClaim;
use App\Models\InsuranceCompany;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InsuranceReportController extends Controller
{
    /**
     * تقارير التأمين: متابعة الطلبات، المدخول، المرفوض، النسب (لرئيس قسم التأمين)
     */
    public function index(Request $request)
    {
        $this->authorize('insurance_reports.view');

        $baseQuery = InsuranceClaim::query();

        // إحصائيات (كل المطالبات بدون فلتر تاريخ افتراضي؛ يمكن إضافة فلتر فترة لاحقاً)
        $total = (clone $baseQuery)->count();
        $approvedCount = (clone $baseQuery)->whereIn('status', ['approved', 'paid'])->count();
        $rejectedCount = (clone $baseQuery)->where('status', 'rejected')->count();
        $underReviewCount = (clone $baseQuery)->where('status', 'under_review')->count();
        $sentCount = (clone $baseQuery)->where('status', 'sent')->count();
        $draftCount = (clone $baseQuery)->where('status', 'draft')->count();

        $resolvedTotal = $approvedCount + $rejectedCount; // ما تم البت فيه
        $approvalRate = $resolvedTotal > 0 ? round(($approvedCount / $resolvedTotal) * 100, 1) : 0;
        $rejectionRate = $resolvedTotal > 0 ? round(($rejectedCount / $resolvedTotal) * 100, 1) : 0;

        // جدول متابعة الطلبات مع فلتر
        $claimsQuery = InsuranceClaim::with(['invoice.patient', 'insuranceCompany', 'sentByUser']);

        if ($request->filled('status')) {
            $claimsQuery->where('status', $request->status);
        }
        if ($request->filled('insurance_company_id')) {
            $claimsQuery->where('insurance_company_id', $request->insurance_company_id);
        }
        if ($request->filled('from_date')) {
            $claimsQuery->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $claimsQuery->whereDate('created_at', '<=', $request->to_date);
        }

        $claims = $claimsQuery->latest()->paginate(20)->withQueryString();
        $insuranceCompanies = InsuranceCompany::where('is_active', true)->orderBy('name_ar')->get(['id', 'name', 'name_ar']);

        // فواتير مرضى التأمين — ليستينج لكل فاتورة مع إمكانية إنشاء مطالبة
        $insuredInvoicesQuery = Invoice::whereHas('patient', fn ($q) => $q->where('payment_type', 'insurance'))
            ->with(['patient.insuranceCompany', 'insuranceClaims', 'items']);
        if ($request->filled('insured_invoice_from')) {
            $insuredInvoicesQuery->whereDate('invoice_date', '>=', $request->insured_invoice_from);
        }
        if ($request->filled('insured_invoice_to')) {
            $insuredInvoicesQuery->whereDate('invoice_date', '<=', $request->insured_invoice_to);
        }
        $insuredInvoices = $insuredInvoicesQuery->latest('invoice_date')->latest('id')->paginate(15, ['*'], 'insured_page')->withQueryString();

        return view('insurance-reports.index', compact(
            'total', 'approvedCount', 'rejectedCount', 'underReviewCount', 'sentCount', 'draftCount',
            'approvalRate', 'rejectionRate', 'claims', 'insuranceCompanies', 'insuredInvoices'
        ));
    }
}
