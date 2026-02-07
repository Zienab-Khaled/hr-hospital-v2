@extends('layouts.app')
@section('title', __('Invoices'))
@section('content')
    <h2 class="text-xl font-semibold text-slate-800 mb-4">{{ __('Invoices') }}</h2>
    
    {{-- Search and Filter using Global Component --}}
    <x-index-filters 
        :action="route('invoices.index')"
        :searchPlaceholder="app()->getLocale() === 'ar' ? 'رقم الفاتورة، اسم المريض...' : 'Invoice no, patient name...'">
        <div class="w-32">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                {{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}
            </label>
            <select name="status" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مدفوعة' : 'Paid' }}</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'غير مدفوعة' : 'Unpaid' }}</option>
            </select>
        </div>
    </x-index-filters>
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة' : 'Invoice No' }}</th>
                    <th class="text-start p-3">{{ __("Patients") }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Total' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'المتبقي' : 'Remaining' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ $inv->invoice_number ?? $inv->id }}</td>
                        <td class="p-3">{{ $inv->patient?->name }}</td>
                        <td class="p-3">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
                        <td class="p-3">@currency($inv->total_amount)</td>
                        <td class="p-3">@currency($inv->remaining_amount)</td>
                        <td class="p-3">{{ $inv->status ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير' : 'No invoices yet' }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())<div class="p-3 border-t">{{ $invoices->links() }}</div>@endif
    </div>
@endsection
