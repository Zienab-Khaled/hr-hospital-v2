@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تفاصيل الفاتورة' : 'Invoice Details')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-slate-800">
                {{ app()->getLocale() === 'ar' ? 'تفاصيل الفاتورة' : 'Invoice Details' }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('invoices.index') }}"
                    class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-semibold hover:bg-slate-300">
                    {{ app()->getLocale() === 'ar' ? '← قائمة الفواتير' : '← Invoices List' }}
                </a>
                @can('invoices.edit')
                    <a href="{{ route('invoices.edit', $invoice) }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700">
                        {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                    </a>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 rounded-lg bg-green-100 border border-green-400 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            {{-- Invoice header --}}
            <div class="p-6 border-b-2 border-slate-200 bg-slate-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة:' : 'Invoice No:' }}</span>
                        <p class="text-lg font-bold text-slate-900">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div>
                        <span class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }}</span>
                        <p class="text-lg font-medium text-slate-900">{{ $invoice->invoice_date?->format('Y-m-d') }}</p>
                    </div>
                    <div>
                        <span class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'الحالة:' : 'Status:' }}</span>
                        <p class="text-lg font-medium text-slate-900">{{ $invoice->status ?? '—' }}</p>
                    </div>
                    @if($invoice->visit?->referral_number)
                        <div>
                            <span class="text-slate-600 text-sm font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الإحالة:' : 'Referral No:' }}</span>
                            <p class="text-lg font-medium text-slate-900">{{ $invoice->visit->referral_number }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Patient --}}
            @if($invoice->patient)
                <div class="p-6 border-b border-slate-200 bg-blue-50/50">
                    <h3 class="font-bold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'معلومات المريض' : 'Patient' }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                        <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</span> {{ $invoice->patient->name }}</p>
                        @if($invoice->patient->name_ar)
                            <p dir="rtl"><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي):' : 'Name (AR):' }}</span> {{ $invoice->patient->name_ar }}</p>
                        @endif
                        <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</span> {{ $invoice->patient->file_number }}</p>
                        <p><span class="text-slate-600 font-semibold">{{ app()->getLocale() === 'ar' ? 'نوع الدفع:' : 'Payment:' }}</span> {{ $invoice->patient->payment_type ?? '—' }}</p>
                    </div>
                </div>
            @endif

            {{-- Services table (الخدمات المقدمة) --}}
            <div class="p-6">
                <h3 class="font-bold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'الخدمات المقدمة' : 'Provided Services' }}</h3>
                <div class="overflow-x-auto border-2 border-slate-300 rounded-lg">
                    <table class="w-full border-collapse" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                        <thead>
                            <tr class="bg-slate-200 border-b-2 border-slate-500">
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'الرمز' : 'Code' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'البيان' : 'Description' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'السعر الافرادي' : 'Unit Price' }}</th>
                                <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                                <tr class="border-b border-slate-300">
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm">{{ $item->service?->code ?? '—' }}</td>
                                    <td class="border border-slate-300 px-2 py-2 text-sm">
                                        {{ app()->getLocale() === 'ar' && $item->service?->name_ar ? $item->service->name_ar : ($item->service?->name ?? '—') }}
                                        @if($item->description)
                                            <br><span class="text-slate-500 text-xs">{{ $item->description }}</span>
                                        @endif
                                    </td>
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm">{{ $item->quantity }}</td>
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm">@currency($item->unit_price)</td>
                                    <td class="border border-slate-300 px-2 py-2 text-center text-sm font-medium">@currency($item->total_price)</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="border border-slate-300 p-4 text-center text-slate-500">
                                        {{ app()->getLocale() === 'ar' ? 'لا توجد بنود' : 'No items' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Totals --}}
            <div class="px-6 pb-6">
                <div class="max-w-md ms-auto space-y-2 border-2 border-slate-300 rounded-lg p-4 bg-slate-50">
                    <div class="flex justify-between text-sm">
                        <span class="font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الإجمالي:' : 'Total:' }}</span>
                        <span class="font-bold">@currency($invoice->total_amount)</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المدفوع:' : 'Paid:' }}</span>
                        <span class="font-bold text-green-700">@currency($invoice->paid_amount)</span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-300">
                        <span class="font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المتبقي:' : 'Remaining:' }}</span>
                        <span class="font-bold text-slate-900">@currency($invoice->remaining_amount)</span>
                    </div>
                </div>
                @if($invoice->notes)
                    <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200">
                        <span class="text-sm font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'ملاحظات:' : 'Notes:' }}</span>
                        <p class="text-sm text-slate-800 mt-1">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
