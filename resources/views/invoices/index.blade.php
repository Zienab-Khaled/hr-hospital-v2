@extends('layouts.app')
@section('title', __('Invoices'))
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <h2 class="text-xl font-semibold text-slate-800">{{ __('Invoices') }}</h2>
        @if(\App\Support\RoleNav::canCreateInvoiceWithServices(auth()->user()))
            <a href="{{ route('invoices.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 shadow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ app()->getLocale() === 'ar' ? 'إنشاء فاتورة جديدة' : 'Create Invoice' }}
            </a>
        @endif
    </div>

    {{-- Search and Filter using Global Component --}}
    <x-index-filters :action="route('invoices.index')" :searchPlaceholder="app()->getLocale() === 'ar' ? 'رقم الفاتورة، اسم المريض...' : 'Invoice no, patient name...'">
        @if ($isAdmin)
            <div class="w-36">
                <label
                    class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">{{ app()->getLocale() === 'ar' ? 'المناوبة' : 'Shift' }}</label>
                <select name="shift_id"
                    class="w-full px-2 py-1 text-sm border-2 border-slate-300 rounded focus:ring-2 focus:ring-blue-500 bg-white text-slate-800">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach ($shifts as $s)
                        <option value="{{ $s->id }}" {{ request('shift_id') == $s->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' && $s->name_ar ? $s->name_ar : $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="w-40">
            <label
                class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</label>
            <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}"
                class="w-full px-2 py-1 text-sm border-2 border-slate-300 rounded focus:ring-2 focus:ring-blue-500 bg-white text-slate-800">
        </div>
        <div class="w-32">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                {{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}
            </label>
            <select name="status"
                class="w-full px-2 py-1 text-sm border-2 border-slate-300 rounded focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>
                    {{ app()->getLocale() === 'ar' ? 'مدفوعة' : 'Paid' }}</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>
                    {{ app()->getLocale() === 'ar' ? 'غير مدفوعة' : 'Unpaid' }}</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>
                    {{ app()->getLocale() === 'ar' ? 'معتمد' : 'Approved' }}</option>
                <option value="sent_to_insurance" {{ request('status') === 'sent_to_insurance' ? 'selected' : '' }}>
                    {{ app()->getLocale() === 'ar' ? 'مرسل لشركة التأمين' : 'Sent to insurance' }}</option>
                <option value="sent_to_charity" {{ request('status') === 'sent_to_charity' ? 'selected' : '' }}>
                    {{ app()->getLocale() === 'ar' ? 'مرسل للجمعية' : 'Sent to charity' }}</option>
            </select>
        </div>
    </x-index-filters>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة' : 'Invoice No' }}</th>
                    <th class="text-start p-3">{{ __('Patients') }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Total' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'المتبقي' : 'Remaining' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th class="text-start p-3 w-28">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ $inv->invoice_number ?? $inv->id }}</td>
                        <td class="p-3">{{ $inv->patient?->name }}</td>
                        <td class="p-3">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
                        <td class="p-3">@currencyInvoice($inv->total_amount)</td>
                        <td class="p-3">@currencyInvoice($inv->remaining_amount)</td>
                        <td class="p-3">
                            <div class="flex flex-col gap-1">
                                <span class="font-medium text-slate-900">{{ $inv->status_label }}</span>
                                @if (in_array($inv->invoice_type, ['eligibility', 'charity_treatment_free'], true))
                                    <span
                                        class="{{ $inv->invoice_type === 'eligibility' ? 'bg-purple-100 text-purple-800' : 'bg-teal-100 text-teal-900' }} px-1.5 py-0.5 rounded text-[10px] font-bold w-fit uppercase">{{ $inv->invoice_type_label }}</span>
                                @endif
                                @if ($inv->payment_type === 'charity')
                                    <span
                                        class="bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded text-[10px] font-bold w-fit">{{ app()->getLocale() === 'ar' ? 'جمعية خيرية' : 'Charity' }}</span>
                                @elseif($inv->payment_type === 'insurance')
                                    <span
                                        class="bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded text-[10px] font-bold w-fit">{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</span>
                                @elseif($inv->payment_type === 'treatment_eligibility')
                                    <span
                                        class="bg-teal-100 text-teal-800 px-1.5 py-0.5 rounded text-[10px] font-bold w-fit">{{ \App\Models\Patient::paymentTypeLabel('treatment_eligibility') }}</span>
                                @else
                                    <span
                                        class="bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded text-[10px] font-bold w-fit">{{ app()->getLocale() === 'ar' ? 'كاش (نقدي)' : 'Cash' }}</span>
                                @endif
                            </div>
                            <div class="flex gap-1 mt-1">
                                @if ($inv->sent_to_charity_mail_at)
                                    <span
                                        title="{{ app()->getLocale() === 'ar' ? 'تم إرسال بريد إلكتروني للجمعية: ' . $inv->sent_to_charity_mail_at : 'Mail sent to charity: ' . $inv->sent_to_charity_mail_at }}"
                                        class="cursor-help">📧</span>
                                @endif
                                @if ($inv->printed_commitment_at)
                                    <span
                                        title="{{ app()->getLocale() === 'ar' ? 'تم طباعة محضر التعهد: ' . $inv->printed_commitment_at : 'Commitment form printed: ' . $inv->printed_commitment_at }}"
                                        class="cursor-help">📄</span>
                                @endif
                                @if ($inv->printed_non_commitment_at)
                                    <span
                                        title="{{ app()->getLocale() === 'ar' ? 'تم طباعة إقرار بعدم التوقيع: ' . $inv->printed_non_commitment_at : 'Non-commitment form printed: ' . $inv->printed_non_commitment_at }}"
                                        class="cursor-help">📝</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                @can('invoices.view')
                                    <a href="{{ route('invoices.show', $inv) }}"
                                        title="{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}"
                                        class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                @endcan
                                @can('invoices.edit')
                                    <a href="{{ route('invoices.edit', $inv) }}"
                                        title="{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}"
                                        class="text-amber-600 hover:text-amber-800 p-1 rounded hover:bg-amber-50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                @endcan
                                @can('invoices.delete')
                                    <form action="{{ route('invoices.destroy', $inv) }}" method="POST" class="inline"
                                        onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذه الفاتورة؟' : 'Are you sure you want to delete this invoice?' }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}"
                                            class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-500">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد فواتير' : 'No invoices yet' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($invoices->hasPages())
            <div class="p-3 border-t">{{ $invoices->links() }}</div>
        @endif
    </div>
@endsection
