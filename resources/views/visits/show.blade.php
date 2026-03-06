@extends('layouts.app')

@section('title', (app()->getLocale() === 'ar' ? 'تفاصيل الزيارة' : 'Visit Details') . ' - #' . $visit->id)

@section('content')
    <style>
        .font-cairo {
            font-family: 'Cairo', sans-serif !important;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
    </style>

    <div class="max-w-5xl mx-auto px-4 py-8 font-cairo">
        {{-- Top Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-800">
                        {{ app()->getLocale() === 'ar' ? 'تفاصيل الزيارة' : 'Visit Details' }}
                        <span class="text-indigo-600 ml-2">#{{ $visit->id }}</span>
                    </h1>
                    <p class="text-slate-500 font-medium">
                        {{ $visit->visit_date?->format('Y-m-d') ?? ($visit->created_at?->format('Y-m-d') ?? '—') }} |
                        {{ app()->getLocale() === 'ar' ? 'تسجيل بواسطة' : 'Registered by' }}:
                        {{ $visit->registeredBy?->name ?? '—' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('patients.show', $visit->patient_id) }}"
                    class="flex items-center gap-2 px-5 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200 transition-all border border-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'ملف المريض' : 'Patient Profile' }}
                </a>

                @can('visits.delete')
                    <a href="{{ route('visits.edit', $visit) }}"
                        class="flex items-center gap-2 px-5 py-2.5 bg-white text-indigo-600 rounded-xl font-bold hover:bg-indigo-50 transition-all border border-indigo-100 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Visit Info --}}
            <div class="lg:col-span-2 space-y-8">
                <div class="glass-card rounded-3xl p-8">
                    <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                        <div class="w-2 h-6 bg-indigo-600 rounded-full"></div>
                        {{ app()->getLocale() === 'ar' ? 'بيانات الزيارة' : 'Visit Information' }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'القسم الطبي' : 'Medical Department' }}
                                </span>
                                <p class="text-lg font-bold text-slate-700">
                                    {{ $visit->department ? (app()->getLocale() === 'ar' ? $visit->department->name_ar ?? $visit->department->name : $visit->department->name) : '—' }}
                                </p>
                            </div>
                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'الشفت' : 'Shift' }}
                                </span>
                                <p class="text-lg font-bold text-slate-700">
                                    {{ $visit->shift ? (app()->getLocale() === 'ar' ? $visit->shift->name_ar ?? $visit->shift->name : $visit->shift->name) : '—' }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'نوع الحالة' : 'Case Type' }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-3 h-3 rounded-full {{ $visit->case_type === 'emergency' ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                                    <p class="text-lg font-bold text-slate-700">
                                        {{ $visit->case_type === 'emergency' ? (app()->getLocale() === 'ar' ? 'طوارئ' : 'Emergency') : (app()->getLocale() === 'ar' ? 'عيادات' : 'Clinics') }}
                                    </p>
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'تاريخ الزيارة' : 'Visit Date' }}
                                </span>
                                <p class="text-lg font-bold text-slate-700">
                                    {{ $visit->visit_date?->format('Y-m-d') ?? $visit->created_at->format('Y-m-d') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if ($visit->notes)
                        <div class="mt-8 p-6 bg-indigo-50/30 rounded-2xl border border-indigo-100/50">
                            <span class="text-xs text-indigo-400 font-bold uppercase tracking-wider block mb-2">
                                {{ app()->getLocale() === 'ar' ? 'ملاحظات الزيارة' : 'Visit Notes' }}
                            </span>
                            <p class="text-slate-600 leading-relaxed font-medium">
                                {{ $visit->notes }}
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Associated Invoices --}}
                <div class="glass-card rounded-3xl p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-black text-slate-800 flex items-center gap-2">
                            <div class="w-2 h-6 bg-blue-500 rounded-full"></div>
                            {{ app()->getLocale() === 'ar' ? 'الفواتير والخدمات' : 'Invoices & Services' }}
                        </h3>
                    </div>

                    @if ($visit->invoices->isEmpty())
                        <div class="text-center py-12 bg-slate-50/50 rounded-3xl border-2 border-dashed border-slate-200">
                            <div
                                class="w-16 h-16 bg-slate-200 rounded-2xl flex items-center justify-center text-slate-400 mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <p class="text-slate-500 font-bold">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد فواتير لهذه الزيارة' : 'No invoices for this visit' }}
                            </p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach ($visit->invoices as $invoice)
                                <div
                                    class="border border-slate-100 rounded-3xl overflow-hidden hover:shadow-lg transition-all">
                                    <div
                                        class="bg-slate-50/80 p-5 flex items-center justify-between border-b border-slate-100">
                                        <div class="flex items-center gap-4">
                                            <span class="text-indigo-600 font-black">#{{ $invoice->invoice_number }}</span>
                                            <span
                                                class="status-badge {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ app()->getLocale() === 'ar' ? ($invoice->status === 'paid' ? 'تم السداد' : 'بانتظار السداد') : ucfirst($invoice->status) }}
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="text-xs text-slate-400 font-bold block">{{ app()->getLocale() === 'ar' ? 'المبلغ الإجمالي' : 'Total Amount' }}</span>
                                            <span
                                                class="text-xl font-black text-slate-900">{{ number_format($invoice->total_amount, 2) }}
                                                SAR</span>
                                        </div>
                                    </div>

                                    <div class="p-5">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr
                                                    class="text-slate-400 text-left {{ app()->getLocale() === 'ar' ? 'text-right' : '' }}">
                                                    <th class="pb-3 font-bold">
                                                        {{ app()->getLocale() === 'ar' ? 'الخدمة' : 'Service' }}</th>
                                                    <th class="pb-3 font-bold">
                                                        {{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                                    <th class="pb-3 font-bold">
                                                        {{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50">
                                                @foreach ($invoice->items as $item)
                                                    <tr>
                                                        <td class="py-3 font-bold text-slate-700">
                                                            @if ($item->service_id)
                                                                {{ app()->getLocale() === 'ar' ? ($item->service?->name_ar ?? $item->service?->name) : $item->service?->name }}
                                                            @else
                                                                {{ $item->description ?? (app()->getLocale() === 'ar' ? 'كشفية دخول' : 'Entry fee') }}
                                                            @endif
                                                        </td>
                                                        <td class="py-3 text-slate-500">{{ (int) $item->quantity }}</td>
                                                        <td class="py-3 font-bold text-slate-900">
                                                            {{ number_format($item->total_price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between">
                                            <div class="flex gap-2">
                                                <a href="{{ route('invoices.show', $invoice) }}"
                                                    class="text-sm font-bold text-indigo-600 hover:text-indigo-800">
                                                    {{ app()->getLocale() === 'ar' ? 'عرض الفاتورة' : 'View Invoice' }} →
                                                </a>
                                            </div>
                                            <div class="flex items-center gap-6">
                                                <div class="text-center">
                                                    <span
                                                        class="text-[10px] text-slate-400 font-bold block tracking-tighter">{{ app()->getLocale() === 'ar' ? 'المدفوع' : 'PAID' }}</span>
                                                    <span
                                                        class="text-sm font-bold text-emerald-600">{{ number_format($invoice->paid_amount, 2) }}</span>
                                                </div>
                                                <div class="text-center">
                                                    <span
                                                        class="text-[10px] text-slate-400 font-bold block tracking-tighter">{{ app()->getLocale() === 'ar' ? 'المتبقي' : 'BALANCE' }}</span>
                                                    <span
                                                        class="text-sm font-bold text-rose-500">{{ number_format($invoice->remaining_amount, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Patient Summary --}}
            <div class="space-y-8">
                <div class="glass-card rounded-3xl p-8 sticky top-8">
                    <h3 class="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                        <div class="w-2 h-6 bg-violet-600 rounded-full"></div>
                        {{ app()->getLocale() === 'ar' ? 'بيانات المريض' : 'Patient Info' }}
                    </h3>

                    <div class="flex flex-col items-center mb-8">
                        <div
                            class="w-24 h-24 bg-gradient-to-br from-violet-100 to-indigo-100 rounded-full flex items-center justify-center text-indigo-600 mb-4 ring-4 ring-white shadow-md">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h4 class="text-xl font-black text-slate-900 text-center">
                            {{ $visit->patient?->name_ar ?? ($visit->patient?->name ?? '—') }}</h4>
                        <span class="text-sm text-slate-400 font-bold mt-1">#{{ $visit->patient->file_number }}</span>
                    </div>

                    <div class="space-y-5">
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'رقم الهوية' : 'ID Number' }}</span>
                            <span class="text-slate-700 font-black">{{ $visit->patient?->identity_value ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment' }}</span>
                            <span
                                class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg font-black uppercase text-[10px]">
                                {{ $visit->patient?->payment_type ?? '—' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</span>
                            <span class="text-slate-700 font-black"
                                dir="ltr">{{ $visit->patient?->phone ?? '—' }}</span>
                        </div>

                        @if ($visit->patient->insuranceCompany)
                            <div class="pt-4 border-t border-slate-50">
                                <span
                                    class="text-xs text-slate-400 font-bold block mb-2">{{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance' }}</span>
                                <p class="text-sm font-black text-slate-800">
                                    {{ $visit->patient->insuranceCompany->name_ar ?? $visit->patient->insuranceCompany->name }}
                                </p>
                            </div>
                        @endif

                        @if ($visit->patient->charityEntity)
                            <div class="pt-4 border-t border-slate-50">
                                <span
                                    class="text-xs text-slate-400 font-bold block mb-2">{{ app()->getLocale() === 'ar' ? 'الجمعية الخيرية' : 'Charity' }}</span>
                                <p class="text-sm font-black text-slate-800">
                                    {{ $visit->patient->charityEntity->name_ar ?? $visit->patient->charityEntity->name }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('patients.show', $visit->patient_id) }}"
                            class="w-full flex items-center justify-center gap-2 py-4 bg-indigo-600 rounded-2xl font-black text-sm hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                            {{ app()->getLocale() === 'ar' ? 'فتح الملف كاملاً' : 'Open Full Profile' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
