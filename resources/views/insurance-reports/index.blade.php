@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تقارير التأمين' : 'Insurance Reports')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            📊 {{ app()->getLocale() === 'ar' ? 'تقارير التأمين' : 'Insurance Reports' }}
        </h2>
        <a href="{{ route('charity-claims.index', ['tab' => 'insurance']) }}"
           class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-300">
            {{ app()->getLocale() === 'ar' ? '← مطالبات التأمين' : '← Insurance Claims' }}
        </a>
    </div>

    {{-- بطاقات الإحصائيات --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 border border-slate-200">
            <p class="text-xs font-semibold text-slate-500 uppercase mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي الطلبات' : 'Total' }}</p>
            <p class="text-2xl font-bold text-slate-800">{{ $total }}</p>
        </div>
        <div class="bg-emerald-50 rounded-xl shadow p-4 border border-emerald-200">
            <p class="text-xs font-semibold text-emerald-700 uppercase mb-1">{{ app()->getLocale() === 'ar' ? 'المدخول (معتمد/مدفوع)' : 'Approved / Paid' }}</p>
            <p class="text-2xl font-bold text-emerald-800">{{ $approvedCount }}</p>
        </div>
        <div class="bg-red-50 rounded-xl shadow p-4 border border-red-200">
            <p class="text-xs font-semibold text-red-700 uppercase mb-1">{{ app()->getLocale() === 'ar' ? 'المرفوض' : 'Rejected' }}</p>
            <p class="text-2xl font-bold text-red-800">{{ $rejectedCount }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border border-amber-200">
            <p class="text-xs font-semibold text-amber-700 uppercase mb-1">{{ app()->getLocale() === 'ar' ? 'نسبة القبول' : 'Approval Rate' }}</p>
            <p class="text-2xl font-bold text-amber-800">{{ $approvalRate }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border border-red-200">
            <p class="text-xs font-semibold text-red-600 uppercase mb-1">{{ app()->getLocale() === 'ar' ? 'نسبة الرفض' : 'Rejection Rate' }}</p>
            <p class="text-2xl font-bold text-red-700">{{ $rejectionRate }}%</p>
        </div>
        <div class="bg-slate-50 rounded-xl shadow p-4 border border-slate-200">
            <p class="text-xs font-semibold text-slate-600 uppercase mb-1">{{ app()->getLocale() === 'ar' ? 'قيد المراجعة' : 'Under Review' }}</p>
            <p class="text-2xl font-bold text-slate-800">{{ $underReviewCount }}</p>
        </div>
    </div>

    {{-- متابعة الطلبات --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <h3 class="px-4 py-3 border-b border-slate-200 font-semibold text-slate-800">
            {{ app()->getLocale() === 'ar' ? 'متابعة الطلبات' : 'Claims Follow-up' }}
        </h3>

        <form method="GET" action="{{ route('insurance-reports.index') }}" class="p-4 flex flex-wrap gap-3 items-end border-b border-slate-100">
            <div class="w-40">
                <label class="block text-xs font-semibold text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                <select name="status" class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach(['draft'=>'مسودة','sent'=>'مرسلة','under_review'=>'قيد المراجعة','approved'=>'معتمدة','rejected'=>'مرفوضة','paid'=>'مدفوعة'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-xs font-semibold text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Co' }}</label>
                <select name="insurance_company_id" class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm bg-white">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach($insuranceCompanies as $c)
                        <option value="{{ $c->id }}" {{ request('insurance_company_id') == $c->id ? 'selected' : '' }}>{{ $c->name_ar ?? $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-semibold text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'من تاريخ' : 'From' }}</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm">
            </div>
            <div class="w-36">
                <label class="block text-xs font-semibold text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'إلى تاريخ' : 'To' }}</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm">
            </div>
            <button type="submit" class="bg-red-600  px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700">{{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}</button>
            <a href="{{ route('insurance-reports.index') }}" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-300">{{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                <thead class="bg-slate-100 border-b-2 border-slate-300">
                    <tr>
                        <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة' : 'Invoice No' }}</th>
                        <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</th>
                        <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Co' }}</th>
                        <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'تاريخ الإرسال' : 'Sent Date' }}</th>
                        <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved' }}</th>
                        <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="text-start p-3 font-bold text-slate-700"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusClass = ['draft'=>'bg-slate-100 text-slate-700','sent'=>'bg-blue-100 text-blue-800','under_review'=>'bg-amber-100 text-amber-800','approved'=>'bg-emerald-100 text-emerald-800','rejected'=>'bg-red-100 text-red-800','paid'=>'bg-purple-100 text-purple-800'];
                        $statusLabel = ['draft'=>'مسودة','sent'=>'مرسلة','under_review'=>'قيد المراجعة','approved'=>'معتمدة','rejected'=>'مرفوضة','paid'=>'مدفوعة'];
                    @endphp
                    @forelse($claims as $claim)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-3"><a href="{{ route('invoices.show', $claim->invoice) }}" class="text-blue-600 hover:underline font-medium">{{ $claim->invoice?->invoice_number ?? '—' }}</a></td>
                            <td class="p-3">{{ $claim->invoice?->patient?->name_ar ?? $claim->invoice?->patient?->name ?? '—' }}</td>
                            <td class="p-3">{{ $claim->insuranceCompany?->name_ar ?? $claim->insuranceCompany?->name ?? '—' }}</td>
                            <td class="p-3">{{ $claim->sent_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="p-3 font-medium">{{ $claim->approved_amount ? number_format((float)$claim->approved_amount, 2) : '—' }}</td>
                            <td class="p-3">
                                <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold {{ $statusClass[$claim->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statusLabel[$claim->status] ?? $claim->status }}</span>
                            </td>
                            <td class="p-3">
                                <a href="{{ route('insurance-claims.show', $claim) }}" class="inline-flex items-center gap-1 bg-blue-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-blue-700 font-semibold">
                                    {{ app()->getLocale() === 'ar' ? 'عرض / تعديل' : 'View / Edit' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مطالبات' : 'No claims' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($claims->hasPages())
            <div class="px-4 py-3 border-t">{{ $claims->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
