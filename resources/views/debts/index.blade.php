@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'المديونيات — حصر الفواتير غير المسددة' : 'Debts — Unpaid Invoices')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 bg-slate-50 min-h-screen" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-900 mb-1">
            {{ app()->getLocale() === 'ar' ? 'المديونيات' : 'Debts' }}
        </h1>
        <p class="text-slate-600 font-medium">
            {{ app()->getLocale() === 'ar' ? 'حصر الفواتير التي لم تُسدّد (أو سُدّد جزء منها) — تبليغ المريض بالمبلغ المستحق ومتابعة التحصيل' : 'Inventory of unpaid or partially paid invoices — notify patient of amount due and follow up collection' }}
        </p>
    </div>

    @if(session('warning'))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl font-medium flex items-center gap-3">
            <span class="text-2xl">⚠️</span> {{ session('warning') }}
        </div>
    @endif

    {{-- إحصائيات سريعة — جنب بعض في صف واحد (flex لا يلزم سطر جديد) --}}
    <div class="flex flex-nowrap gap-3 sm:gap-4 mb-8 overflow-x-auto pb-1">
        <div class="flex-1 min-w-[120px] sm:min-w-0 bg-white rounded-2xl p-4 sm:p-5 border-2 border-slate-200 shadow-sm flex-shrink-0">
            <p class="text-[10px] sm:text-xs font-black text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'عدد الفواتير غير المسددة' : 'Unpaid Invoices' }}</p>
            <p class="text-xl sm:text-2xl font-black text-slate-800">{{ $stats['total_count'] }}</p>
        </div>
        <div class="flex-1 min-w-[120px] sm:min-w-0 bg-white rounded-2xl p-4 sm:p-5 border-2 border-rose-200 shadow-sm flex-shrink-0">
            <p class="text-[10px] sm:text-xs font-black text-rose-600 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي المبلغ المستحق' : 'Total Amount Due' }}</p>
            <p class="text-xl sm:text-2xl font-black text-rose-800">@currency($stats['total_remaining'])</p>
        </div>
        <div class="flex-1 min-w-[120px] sm:min-w-0 bg-white rounded-2xl p-4 sm:p-5 border-2 border-amber-200 shadow-sm flex-shrink-0">
            <p class="text-[10px] sm:text-xs font-black text-amber-600 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'لم يُبلّغ' : 'Not notified' }}</p>
            <p class="text-xl sm:text-2xl font-black text-amber-800">{{ $stats['not_notified_count'] }}</p>
        </div>
        <div class="flex-1 min-w-[120px] sm:min-w-0 bg-white rounded-2xl p-4 sm:p-5 border-2 border-emerald-200 shadow-sm flex-shrink-0">
            <p class="text-[10px] sm:text-xs font-black text-emerald-600 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'تم التبليغ' : 'Notified' }}</p>
            <p class="text-xl sm:text-2xl font-black text-emerald-800">{{ $stats['notified_count'] }}</p>
        </div>
    </div>

    {{-- فلتر حسب حالة المديونية --}}
    <div class="bg-white rounded-2xl p-4 mb-6 border border-slate-200 shadow-sm">
        <form action="{{ route('debts.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <label class="text-sm font-bold text-slate-600">{{ app()->getLocale() === 'ar' ? 'حالة التبليغ:' : 'Notification status:' }}</label>
            <select name="debt_status" class="rounded-xl border-slate-300 text-sm font-medium focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                <option value="not_notified" {{ request('debt_status') === 'not_notified' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'لم يُبلّغ' : 'Not notified' }}</option>
                <option value="notified" {{ request('debt_status') === 'notified' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'تم التبليغ' : 'Notified' }}</option>
            </select>
            <button type="submit" style="background-color: #77786c;" class="px-5 text-white py-2.5 bg-slate-800 rounded-xl font-bold text-sm hover:bg-rose-600 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'تطبيق' : 'Apply' }}
            </button>
        </form>
    </div>

    {{-- جدول الفواتير --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 border-b border-slate-200">
                    <tr>
                        <th class="text-right py-3 px-4 font-black text-slate-700">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</th>
                        <th class="text-right py-3 px-4 font-black text-slate-700">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة' : 'Invoice' }}</th>
                        <th class="text-right py-3 px-4 font-black text-slate-700">{{ app()->getLocale() === 'ar' ? 'المبلغ المستحق' : 'Amount Due' }}</th>
                        <th class="text-right py-3 px-4 font-black text-slate-700">{{ app()->getLocale() === 'ar' ? 'الخدمات' : 'Services' }}</th>
                        <th class="text-right py-3 px-4 font-black text-slate-700">{{ app()->getLocale() === 'ar' ? 'حالة المديونية' : 'Debt Status' }}</th>
                        <th class="text-right py-3 px-4 font-black text-slate-700">{{ app()->getLocale() === 'ar' ? 'إجراء' : 'Action' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        @php
                            $patient = $invoice->patient;
                            $servicesList = $invoice->items->map(fn ($i) => $i->service?->name_ar ?? $i->service?->name ?? '—')->take(5)->implode('، ');
                            if ($invoice->items->count() > 5) {
                                $servicesList .= ' …';
                            }
                        @endphp
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $patient->name_ar ?? $patient->name }}
                                @if($patient->phone)
                                    <span class="block text-xs text-slate-500">{{ $patient->phone }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-rose-600 font-bold hover:underline">INV-{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="py-3 px-4 font-black text-rose-700">@currency($invoice->remaining_amount)</td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="{{ $servicesList }}">{{ $servicesList ?: '—' }}</td>
                            <td class="py-3 px-4">
                                @if($invoice->debt_status === 'notified')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        {{ $invoice->debt_status_label }}
                                        @if($invoice->debt_notified_at)
                                            <span class="mr-1 text-[10px]">({{ \Carbon\Carbon::parse($invoice->debt_notified_at)->format('Y-m-d') }})</span>
                                        @endif
                                    </span>
                                @elseif($invoice->debt_status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700">{{ $invoice->debt_status_label }}</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">{{ $invoice->debt_status_label }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($invoice->debt_status !== 'notified')
                                    <form action="{{ route('debts.notify', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل تود تسجيل واقعة تبليغ المريض بالمبلغ المستحق ' : 'Do you want to record notification to patient for amount ' }}@currency($invoice->remaining_amount){{ app()->getLocale() === 'ar' ? '؟' : '?' }}');">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs transition-colors">
                                            {{ app()->getLocale() === 'ar' ? 'إرسال تبليغ للمريض' : 'Notify Patient' }}
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('debts.mark-paid', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'تأكيد تسجيل تحصيل المبلغ ' : 'Confirm recording payment of ' }}@currency($invoice->remaining_amount){{ app()->getLocale() === 'ar' ? ' وإضافته للإيرادات؟' : ' into today\'s revenue?' }}');">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition-colors flex items-center gap-2">
                                            <span>💰</span>
                                            {{ app()->getLocale() === 'ar' ? 'تسجيل السداد' : 'Record Payment' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500 font-medium">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد فواتير غير مسددة.' : 'No unpaid invoices.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <p class="mt-6 text-xs text-slate-500">
        {{ app()->getLocale() === 'ar' ? 'عند النقر على «إرسال تبليغ للمريض» يتم تسجيل واقعة التبليغ بالمبلغ المستحق والخدمات المقدمة. وعند تحصيل المبلغ فعلياً، يتم النقر على «تسجيل السداد» لإثبات العملية في الإيرادات اليومية.' : 'Clicking «Notify Patient» records that the patient was notified of the amount due and services. When the payment is actually collected, click «Record Payment» to reflect it in daily revenue.' }}
    </p>
</div>
@endsection
