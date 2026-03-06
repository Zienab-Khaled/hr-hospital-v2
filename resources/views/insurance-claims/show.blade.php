@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تفاصيل مطالبة التأمين' : 'Insurance Claim Details')

@section('content')
    @php
        $statusColors = [
            'draft'        => 'bg-slate-100 text-slate-700 border-slate-300',
            'sent'         => 'bg-blue-100 text-blue-800 border-blue-300',
            'under_review' => 'bg-amber-100 text-amber-800 border-amber-300',
            'approved'     => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'rejected'     => 'bg-red-100 text-red-800 border-red-300',
            'paid'         => 'bg-purple-100 text-purple-800 border-purple-300',
        ];
        $statusLabels = [
            'draft'        => 'مسودة',
            'sent'         => 'مرسلة',
            'under_review' => 'قيد المراجعة',
            'approved'     => 'معتمدة',
            'rejected'     => 'مرفوضة',
            'paid'         => 'مدفوعة',
        ];
        $color = $statusColors[$insuranceClaim->status] ?? 'bg-slate-100 text-slate-700 border-slate-300';
        $label = $statusLabels[$insuranceClaim->status] ?? $insuranceClaim->status;
    @endphp

    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-slate-800">
                🏥 {{ app()->getLocale() === 'ar' ? 'مطالبة تأمين' : 'Insurance Claim' }}
                <span class="text-slate-500 text-lg font-normal ms-2">{{ $insuranceClaim->invoice?->invoice_number }}</span>
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('charity-claims.index', ['tab' => 'insurance']) }}"
                   class="bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-slate-300">
                    ← {{ app()->getLocale() === 'ar' ? 'قائمة المطالبات' : 'Claims List' }}
                </a>
                <a href="{{ route('invoices.show', $insuranceClaim->invoice) }}"
                   class="bg-blue-600 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-blue-700">
                    {{ app()->getLocale() === 'ar' ? 'عرض الفاتورة' : 'View Invoice' }}
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-300 text-red-800 text-sm">
                @foreach($errors->all() as $e) <p>⚠️ {{ $e }}</p> @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Claim Info --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Claim Summary --}}
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="font-bold text-slate-800 mb-4 text-lg border-b pb-2">{{ app()->getLocale() === 'ar' ? 'بيانات المطالبة' : 'Claim Info' }}</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</span>
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-bold border {{ $color }}">{{ $label }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ الإرسال' : 'Sent Date' }}</span>
                            <span class="font-medium text-slate-800">{{ $insuranceClaim->sent_date?->format('Y-m-d') ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}</span>
                            <span class="font-medium text-slate-800">{{ $insuranceClaim->insuranceCompany?->name_ar ?? $insuranceClaim->insuranceCompany?->name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved Amount' }}</span>
                            <span class="font-bold text-emerald-700 text-lg">
                                {{ $insuranceClaim->approved_amount ? number_format((float)$insuranceClaim->approved_amount, 2) : '—' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'أرسل بواسطة' : 'Sent By' }}</span>
                            <span class="font-medium text-slate-800">{{ $insuranceClaim->sentByUser?->name ?? $insuranceClaim->sentByUser?->username ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold block mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي الفاتورة' : 'Invoice Total' }}</span>
                            <span class="font-bold text-slate-800 text-lg">{{ number_format((float)$insuranceClaim->invoice?->total_amount, 2) }}</span>
                        </div>
                    </div>
                    @if($insuranceClaim->notes)
                        <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="text-slate-500 font-semibold text-sm">{{ app()->getLocale() === 'ar' ? 'ملاحظات:' : 'Notes:' }}</span>
                            <p class="text-slate-800 text-sm mt-1">{{ $insuranceClaim->notes }}</p>
                        </div>
                    @endif
                    @if($insuranceClaim->company_response_notes)
                        <div class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
                            <span class="text-amber-700 font-semibold text-sm">{{ app()->getLocale() === 'ar' ? 'رد الشركة:' : 'Company Response:' }}</span>
                            <p class="text-slate-800 text-sm mt-1">{{ $insuranceClaim->company_response_notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Patient Info --}}
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="font-bold text-slate-800 mb-3 border-b pb-2">{{ app()->getLocale() === 'ar' ? 'بيانات المريض' : 'Patient Info' }}</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-slate-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</span>
                            <span class="font-medium text-slate-800 ms-1">{{ $insuranceClaim->invoice?->patient?->name_ar ?? $insuranceClaim->invoice?->patient?->name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</span>
                            <span class="font-medium text-slate-800 ms-1">{{ $insuranceClaim->invoice?->patient?->file_number ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Services --}}
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="font-bold text-slate-800 mb-3 border-b pb-2">{{ app()->getLocale() === 'ar' ? 'الخدمات' : 'Services' }}</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="border border-slate-300 px-3 py-2 text-start font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الخدمة' : 'Service' }}</th>
                                    <th class="border border-slate-300 px-3 py-2 text-center font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                    <th class="border border-slate-300 px-3 py-2 text-center font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                    <th class="border border-slate-300 px-3 py-2 text-center font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'التنفيذ' : 'Status' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($insuranceClaim->invoice?->items ?? [] as $item)
                                    <tr class="border-b border-slate-200 {{ $item->isCompleted() ? 'bg-emerald-50/40' : '' }}">
                                        <td class="border border-slate-200 px-3 py-2">{{ $item->service?->name_ar ?? $item->service?->name ?? '—' }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->quantity }}</td>
                                        <td class="border border-slate-200 px-3 py-2 text-center font-medium">@currency($item->total_price)</td>
                                        <td class="border border-slate-200 px-3 py-2 text-center">
                                            @if($item->isCompleted())
                                                <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-semibold">✅ {{ app()->getLocale() === 'ar' ? 'منفذ' : 'Done' }}</span>
                                            @else
                                                <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-semibold">⏳ {{ app()->getLocale() === 'ar' ? 'معلق' : 'Pending' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right: Update Status --}}
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow p-5 sticky top-4">
                    <h3 class="font-bold text-slate-800 mb-4 text-lg border-b pb-2">
                        🔄 {{ app()->getLocale() === 'ar' ? 'تحديث الحالة' : 'Update Status' }}
                    </h3>

                    <form method="POST" action="{{ route('insurance-claims.update-status', $insuranceClaim) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'الحالة الجديدة' : 'New Status' }} *</label>
                            <select name="status" required
                                    class="w-full border-2 border-slate-400 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">{{ app()->getLocale() === 'ar' ? 'اختر الحالة' : 'Select status' }}</option>
                                <option value="under_review" {{ $insuranceClaim->status === 'under_review' ? 'selected' : '' }}>قيد المراجعة</option>
                                <option value="approved" {{ $insuranceClaim->status === 'approved' ? 'selected' : '' }}>معتمدة ✅</option>
                                <option value="rejected" {{ $insuranceClaim->status === 'rejected' ? 'selected' : '' }}>مرفوضة ❌</option>
                                <option value="paid" {{ $insuranceClaim->status === 'paid' ? 'selected' : '' }}>مدفوعة 💰</option>
                            </select>
                        </div>

                        <div id="approved-amount-field" class="{{ $insuranceClaim->status === 'approved' ? '' : 'hidden' }}">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved Amount' }}</label>
                            <input type="number" name="approved_amount" step="0.01" min="0"
                                   value="{{ $insuranceClaim->approved_amount ?? $insuranceClaim->invoice?->total_amount }}"
                                   class="w-full border-2 border-slate-400 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ app()->getLocale() === 'ar' ? 'ملاحظات رد الشركة' : 'Company Response Notes' }}</label>
                            <textarea name="company_response_notes" rows="3"
                                      class="w-full border-2 border-slate-400 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">{{ $insuranceClaim->company_response_notes }}</textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 py-2.5 rounded-lg font-bold hover:bg-blue-700 shadow">
                            ✅ {{ app()->getLocale() === 'ar' ? 'حفظ التحديث' : 'Save Update' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('select[name="status"]').addEventListener('change', function() {
            const field = document.getElementById('approved-amount-field');
            field.classList.toggle('hidden', this.value !== 'approved');
        });
    </script>
@endsection
