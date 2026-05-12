@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تفاصيل تسليم المناوبة' : 'Shift Handover Details')
@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="mb-6">
                <a href="{{ route('shift-handovers.index') }}" class="text-slate-600 hover:underline text-sm">{{ app()->getLocale() === 'ar' ? '← قائمة التسليمات' : '← Back to handovers' }}</a>
                <h2 class="text-2xl font-bold text-slate-800 mt-2">
                    {{ app()->getLocale() === 'ar' && $handover->shift->name_ar ? $handover->shift->name_ar : $handover->shift->name }}
                    — {{ $handover->handover_date?->format('Y-m-d') }}
                </h2>
                <p class="text-slate-600 text-sm mt-1">
                    {{ app()->getLocale() === 'ar' ? 'تم التسليم' : 'Handed over' }} {{ $handover->handed_over_at?->format('Y-m-d H:i') }}
                    {{ app()->getLocale() === 'ar' ? 'بواسطة' : 'by' }} {{ $handover->handedOverBy->name ?? $handover->handedOverBy->username ?? '—' }}
                </p>
                @if ($handover->notes)
                    <div class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
                        <p class="text-sm font-medium text-amber-900">{{ app()->getLocale() === 'ar' ? 'ملاحظات للشيفت القادم' : 'Notes for next shift' }}</p>
                        <p class="text-slate-700 mt-1 whitespace-pre-wrap">{{ $handover->notes }}</p>
                    </div>
                @endif
            </div>

            <h3 class="text-lg font-semibold text-slate-800 mb-2">{{ app()->getLocale() === 'ar' ? 'مكتب الدخول' : 'Admission office' }} ({{ $visits->count() }})</h3>
            @if ($visits->isNotEmpty())
                <ul class="border border-slate-200 rounded-lg divide-y divide-slate-200 mb-6">
                    @foreach ($visits as $v)
                        <li class="px-4 py-3 flex flex-wrap items-center justify-between gap-2">
                            <span>{{ $v->patient->name ?? '—' }}</span>
                            <span class="text-slate-500 text-sm">{{ $v->visit_date?->format('Y-m-d') }}</span>
                            @if ($v->department)
                                <span class="text-sm">{{ app()->getLocale() === 'ar' && $v->department->name_ar ? $v->department->name_ar : $v->department->name }}</span>
                            @endif
                            <a href="{{ route('patients.show', $v->patient) }}" class="text-blue-600 hover:underline text-sm">{{ app()->getLocale() === 'ar' ? 'عرض المريض' : 'View patient' }}</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-slate-500 text-sm mb-6">{{ app()->getLocale() === 'ar' ? 'لا توجد زيارات في هذه المناوبة.' : 'No visits in this shift.' }}</p>
            @endif

            <h3 class="text-lg font-semibold text-slate-800 mb-2">{{ app()->getLocale() === 'ar' ? 'الفواتير' : 'Invoices' }} ({{ $invoices->count() }})</h3>
            @if ($invoices->isNotEmpty())
                <ul class="border border-slate-200 rounded-lg divide-y divide-slate-200">
                    @foreach ($invoices as $inv)
                        <li class="px-4 py-3 flex flex-wrap items-center justify-between gap-2">
                            <span>#{{ $inv->invoice_number ?? $inv->id }}</span>
                            <span>{{ $inv->patient->name ?? '—' }}</span>
                            <span class="text-slate-600">{{ $inv->total_amount ?? '—' }}</span>
                            <a href="{{ route('invoices.show', $inv) }}" class="text-blue-600 hover:underline text-sm">{{ app()->getLocale() === 'ar' ? 'عرض الفاتورة' : 'View invoice' }}</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير في هذه المناوبة.' : 'No invoices in this shift.' }}</p>
            @endif
        </div>
    </div>
@endsection
