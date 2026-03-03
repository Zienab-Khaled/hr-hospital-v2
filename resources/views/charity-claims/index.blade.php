@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'المطالبات' : 'Claims')

@section('content')
    @php $activeTab = request('tab', 'charity'); @endphp

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            📋 {{ app()->getLocale() === 'ar' ? 'المطالبات' : 'Claims' }}
        </h2>
        @if($activeTab === 'insurance')
            <a href="{{ route('insurance-claims.create') }}"
               class="bg-red-600 px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-red-700 shadow-md transition-all flex items-center gap-2">
                ➕ {{ app()->getLocale() === 'ar' ? 'إنشاء مطالبة تأمين' : 'Create Insurance Claim' }}
            </a>
        @endif
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 border-b-2 border-slate-200">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'charity']) }}"
           class="px-5 py-2.5 text-sm font-semibold rounded-t-lg transition-colors
                  {{ $activeTab === 'charity' ? 'bg-slate-100 border-2 border-b-0 border-slate-200 text-slate-800' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
            🤝 {{ app()->getLocale() === 'ar' ? 'مطالبات الجمعيات' : 'Charity Claims' }}
            <span class="ms-1 text-xs bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-full">{{ $claims->total() }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'insurance']) }}"
           class="px-5 py-2.5 text-sm font-semibold rounded-t-lg transition-colors
                  {{ $activeTab === 'insurance' ? 'bg-slate-100 border-2 border-b-0 border-slate-200 text-slate-800' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
            🏥 {{ app()->getLocale() === 'ar' ? 'مطالبات التأمين' : 'Insurance Claims' }}
            <span class="ms-1 text-xs bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-full">{{ $insuranceClaims->total() }}</span>
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm font-medium">✅ {{ session('success') }}</div>
    @endif

    {{-- ===== CHARITY TAB ===== --}}
    @if($activeTab === 'charity')
        {{-- Filters --}}
        <form method="GET" action="{{ route('charity-claims.index') }}" class="mb-5 bg-white rounded-lg shadow p-4 flex flex-wrap gap-3 items-end">
            <input type="hidden" name="tab" value="charity">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة، اسم المريض...' : 'Invoice no, patient name...' }}"
                       class="w-full border-2 border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500">
            </div>
            <div class="w-44">
                <label class="block text-xs font-semibold text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'الجمعية' : 'Charity' }}</label>
                <select name="charity_entity_id" class="w-full border-2 border-slate-300 rounded-lg px-2 py-2 text-sm bg-white focus:ring-2 focus:ring-red-500">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach($charityEntities as $e)
                        <option value="{{ $e->id }}" {{ request('charity_entity_id') == $e->id ? 'selected' : '' }}>
                            {{ $e->name_ar ?: $e->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-semibold text-slate-600 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                <select name="status" class="w-full border-2 border-slate-300 rounded-lg px-2 py-2 text-sm bg-white focus:ring-2 focus:ring-red-500">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach(['draft'=>'مسودة','sent'=>'مرسلة','under_review'=>'قيد المراجعة','approved'=>'معتمدة','rejected'=>'مرفوضة','paid'=>'مدفوعة'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-red-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700">🔍 {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}</button>
                <a href="{{ route('charity-claims.index', ['tab' => 'charity']) }}" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-300">{{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}</a>
            </div>
        </form>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                    <thead class="bg-slate-100 border-b-2 border-slate-300">
                        <tr>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة' : 'Invoice No' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الجمعية' : 'Charity' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'تاريخ الإرسال' : 'Sent Date' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                            @php
                                $sc = ['draft'=>'bg-slate-100 text-slate-700','sent'=>'bg-blue-100 text-blue-800','under_review'=>'bg-amber-100 text-amber-800','approved'=>'bg-emerald-100 text-emerald-800','rejected'=>'bg-red-100 text-red-800','paid'=>'bg-purple-100 text-purple-800'];
                                $sl = ['draft'=>'مسودة','sent'=>'مرسلة','under_review'=>'قيد المراجعة','approved'=>'معتمدة','rejected'=>'مرفوضة','paid'=>'مدفوعة'];
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="p-3"><a href="{{ route('invoices.show', $claim->invoice) }}" class="text-blue-600 hover:underline font-medium">{{ $claim->invoice?->invoice_number ?? '—' }}</a></td>
                                <td class="p-3">{{ $claim->invoice?->patient?->name_ar ?? $claim->invoice?->patient?->name ?? '—' }}</td>
                                <td class="p-3">{{ $claim->charityEntity?->name_ar ?? $claim->charityEntity?->name ?? '—' }}</td>
                                <td class="p-3">{{ $claim->sent_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="p-3 font-medium">{{ $claim->approved_amount ? number_format((float)$claim->approved_amount, 2) : '—' }}</td>
                                <td class="p-3"><span class="inline-block px-2 py-1 rounded-full text-xs font-semibold {{ $sc[$claim->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $sl[$claim->status] ?? $claim->status }}</span></td>
                                <td class="p-3"><a href="{{ route('charity-claims.show', $claim) }}" class="inline-flex items-center gap-1 bg-blue-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-blue-700 font-semibold">{{ app()->getLocale() === 'ar' ? 'عرض / تعديل' : 'View / Edit' }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-8 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مطالبات بعد' : 'No claims yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($claims->hasPages())<div class="px-4 py-3 border-t">{{ $claims->withQueryString()->links() }}</div>@endif
        </div>

    {{-- ===== INSURANCE TAB ===== --}}
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                    <thead class="bg-slate-100 border-b-2 border-slate-300">
                        <tr>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة' : 'Invoice No' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Co.' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'تاريخ الإرسال' : 'Sent Date' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                            <th class="text-start p-3 font-bold text-slate-700"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($insuranceClaims as $claim)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="p-3"><a href="{{ route('invoices.show', $claim->invoice) }}" class="text-blue-600 hover:underline font-medium">{{ $claim->invoice?->invoice_number ?? '—' }}</a></td>
                                <td class="p-3">{{ $claim->invoice?->patient?->name_ar ?? $claim->invoice?->patient?->name ?? '—' }}</td>
                                <td class="p-3">{{ $claim->insuranceCompany?->name_ar ?? $claim->insuranceCompany?->name ?? '—' }}</td>
                                <td class="p-3">{{ $claim->sent_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="p-3 font-medium">{{ $claim->approved_amount ? number_format((float)$claim->approved_amount, 2) : '—' }}</td>
                                <td class="p-3">
                                    @php
                                        $isc = ['draft'=>'bg-slate-100 text-slate-700','sent'=>'bg-blue-100 text-blue-800','under_review'=>'bg-amber-100 text-amber-800','approved'=>'bg-emerald-100 text-emerald-800','rejected'=>'bg-red-100 text-red-800','paid'=>'bg-purple-100 text-purple-800'];
                                        $isl = ['draft'=>'مسودة','sent'=>'مرسلة','under_review'=>'قيد المراجعة','approved'=>'معتمدة','rejected'=>'مرفوضة','paid'=>'مدفوعة'];
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold {{ $isc[$claim->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $isl[$claim->status] ?? $claim->status }}</span>
                                        @if($claim->getFirstMediaUrl('arqos_file'))
                                            <a href="{{ $claim->getFirstMediaUrl('arqos_file') }}" target="_blank" title="{{ app()->getLocale() === 'ar' ? 'تحميل ملف اركوس' : 'Download Arqos File' }}" class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('insurance-claims.show', $claim) }}" class="inline-flex items-center gap-1 bg-blue-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-blue-700 font-semibold">
                                            {{ app()->getLocale() === 'ar' ? 'عرض / تعديل' : 'View / Edit' }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-8 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مطالبات تأمين' : 'No insurance claims yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($insuranceClaims->hasPages())<div class="px-4 py-3 border-t">{{ $insuranceClaims->withQueryString()->links() }}</div>@endif
        </div>
    @endif

@endsection
