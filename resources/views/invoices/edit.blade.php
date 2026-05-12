@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تعديل الفاتورة' : 'Edit Invoice')

@section('content')
    @php
        $inputClass = 'w-full rounded-lg border-2 border-slate-400 bg-white px-3 py-2 text-slate-900 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
    @endphp
    <div class="max-w-6xl mx-auto">
        <div class="rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-slate-800">
                    {{ app()->getLocale() === 'ar' ? '✏️ تعديل الفاتورة' : '✏️ Edit Invoice' }}
                    <span class="text-slate-500 text-lg font-normal ms-2">{{ $invoice->invoice_number }}</span>
                </h2>
                <a href="{{ route('invoices.show', $invoice) }}"
                   class="inline-flex items-center gap-1.5 bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-slate-300">
                    ← {{ app()->getLocale() === 'ar' ? 'رجوع' : 'Back' }}
                </a>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg border-2 border-red-400 bg-red-50 text-red-800">
                    <p class="font-bold mb-2">{{ app()->getLocale() === 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please fix the following errors:' }}</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoice-form" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Patient Info (read-only in edit) --}}
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-lg p-5 shadow-sm">
                    <h3 class="font-bold text-blue-900 text-lg mb-3">
                        {{ app()->getLocale() === 'ar' ? '👤 معلومات المريض' : '👤 Patient Information' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                        <div>
                            <span class="block text-blue-700 font-semibold mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</span>
                            <span class="font-medium text-slate-800">{{ $invoice->patient?->name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-blue-700 font-semibold mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</span>
                            <span class="font-medium text-slate-800">{{ $invoice->patient?->file_number ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-blue-700 font-semibold mb-1">{{ app()->getLocale() === 'ar' ? 'نوع الدفع:' : 'Payment Type:' }}</span>
                            <span class="font-medium text-slate-800">{{ $invoice->patient?->payment_type ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Invoice Date & Status & Notes --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'تاريخ الفاتورة' : 'Invoice Date' }} *
                        </label>
                        <input type="date" name="invoice_date"
                               value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}"
                               required class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'حالة الفاتورة' : 'Invoice Status' }} *
                        </label>
                        <select name="status" required class="{{ $inputClass }}">
                            @php
                                $statuses = [
                                    'pending' => ['ar' => 'قيد الانتظار', 'en' => 'Pending'],
                                    'sent_to_insurance' => ['ar' => 'مرسل لشركة التأمين', 'en' => 'Sent to insurance'],
                                    'sent_to_charity' => ['ar' => 'مرسل للجمعية', 'en' => 'Sent to charity'],
                                    'approved' => ['ar' => 'معتمد', 'en' => 'Approved'],
                                    'rejected' => ['ar' => 'مرفوض', 'en' => 'Rejected'],
                                    'paid' => ['ar' => 'مدفوعة', 'en' => 'Paid'],
                                ];
                                $currentLocale = app()->getLocale() === 'ar' ? 'ar' : 'en';
                            @endphp
                            @foreach($statuses as $val => $labels)
                                <option value="{{ $val }}" {{ old('status', $invoice->status) === $val ? 'selected' : '' }}>
                                    {{ $labels[$currentLocale] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}
                        </label>
                        <textarea name="notes" rows="2" class="{{ $inputClass }}">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                </div>

                {{-- Services Section --}}
                <div class="border-2 border-blue-300 rounded-lg p-6 bg-gradient-to-br from-blue-50 to-slate-50">
                    <h3 class="text-xl font-bold text-slate-800 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'الخدمات المقدمة' : 'Provided Services' }}
                    </h3>

                    {{-- Search --}}
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-800 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'إضافة خدمة (بحث بالاسم أو الكود)' : 'Add service (search by name or code)' }}
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <input type="text" id="service-search-input"
                                placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب اسم الخدمة أو الكود...' : 'Type service name or code...' }}"
                                class="flex-1 min-w-[200px] {{ $inputClass }}">
                            <button type="button" id="service-search-btn"
                                class="bg-blue-600 px-5 text-white py-3 rounded-lg font-bold text-base hover:bg-blue-700 shadow">
                                {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                            </button>
                        </div>
                    </div>
                    <div id="service-search-results"
                        class="mb-4 hidden border-2 border-slate-300 rounded-lg bg-white max-h-60 overflow-y-auto shadow-inner">
                    </div>

                    <div class="overflow-x-auto border-2 border-slate-400 rounded-lg bg-white">
                        <table class="w-full border-collapse" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                            <thead>
                                <tr class="bg-slate-200 border-b-2 border-slate-500">
                                    <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 w-24">{{ app()->getLocale() === 'ar' ? 'الرمز' : 'Code' }}</th>
                                    <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 min-w-[200px]">{{ app()->getLocale() === 'ar' ? 'البيان' : 'Description' }}</th>
                                    <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 w-20">{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                    <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 w-28">{{ app()->getLocale() === 'ar' ? 'السعر الافرادي' : 'Unit Price' }}</th>
                                    <th class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 w-28">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                    <th class="border border-slate-500 px-2 py-2 text-center w-14"></th>
                                </tr>
                            </thead>
                            <tbody id="services-container">
                                {{-- Existing items pre-filled --}}
                                @foreach ($invoice->items as $i => $item)
                                    <tr class="service-row border-b border-slate-400 hover:bg-slate-50" data-index="{{ $i }}">
                                        <td class="border border-slate-400 px-2 py-2 text-center text-sm font-medium text-slate-800">
                                            {{ $item->service?->code ?? '—' }}
                                        </td>
                                        <td class="border border-slate-400 px-2 py-2">
                                            <input type="hidden" name="services[{{ $i }}][service_id]" value="{{ $item->service_id }}">
                                            <div class="text-sm font-medium text-slate-900">
                                                {{ app()->getLocale() === 'ar' && $item->service?->name_ar ? $item->service->name_ar : ($item->service?->name ?? '—') }}
                                            </div>
                                            <input type="text" name="services[{{ $i }}][description]"
                                                   value="{{ old("services.$i.description", $item->description) }}"
                                                   placeholder="{{ app()->getLocale() === 'ar' ? 'ملاحظات (اختياري)' : 'Notes (optional)' }}"
                                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                        </td>
                                        <td class="border border-slate-400 px-2 py-2">
                                            <input type="number" name="services[{{ $i }}][quantity]"
                                                   value="{{ old("services.$i.quantity", $item->quantity) }}"
                                                   min="1" step="1" required
                                                   onchange="calculateRowTotal(this)"
                                                   class="w-full rounded border border-slate-400 px-2 py-2 text-sm text-center focus:ring-2 focus:ring-blue-500">
                                        </td>
                                        <td class="border border-slate-400 px-2 py-2">
                                            <input type="number" name="services[{{ $i }}][unit_price]"
                                                   value="{{ old("services.$i.unit_price", $item->unit_price) }}"
                                                   step="0.01" min="0" required
                                                   onchange="calculateRowTotal(this)"
                                                   class="w-full rounded border border-slate-400 px-2 py-2 text-sm text-center focus:ring-2 focus:ring-blue-500">
                                        </td>
                                        <td class="border border-slate-400 px-2 py-2">
                                            <input type="number" name="services[{{ $i }}][total_price]"
                                                   value="{{ old("services.$i.total_price", $item->total_price) }}"
                                                   step="0.01" readonly
                                                   class="w-full rounded border border-slate-400 px-2 py-2 text-sm text-center bg-slate-100 font-bold text-slate-800">
                                        </td>
                                        <td class="border border-slate-400 px-1 py-2 text-center">
                                            <button type="button" onclick="removeServiceRow(this)"
                                                    class="text-red-600 hover:text-red-800 p-1"
                                                    title="{{ app()->getLocale() === 'ar' ? 'حذف' : 'Remove' }}">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Total --}}
                    <div class="mt-6 pt-6 border-t-2 border-slate-300">
                        <div class="flex justify-between items-center bg-gradient-to-r from-slate-800 to-slate-700 p-4 rounded-lg shadow-lg">
                            <span class="text-xl font-bold ">
                                {{ app()->getLocale() === 'ar' ? 'المجموع الإجمالي:' : 'Total Amount:' }}
                            </span>
                            <span id="grand-total" class="text-3xl font-bold ">
                                {{ \App\Helpers\CurrencyHelper::formatAmountDecimal($invoice->total_amount) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-6 border-t-2 border-slate-200">
                    <button type="submit"
                        class="bg-blue-600 px-6 py-2.5 text-white rounded-lg text-lg font-bold hover:bg-blue-700 shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ app()->getLocale() === 'ar' ? 'حفظ التعديلات' : 'Save Changes' }}
                    </button>
                    <a href="{{ route('invoices.show', $invoice) }}"
                        class="bg-slate-200 text-slate-700 px-6 py-2.5 rounded-lg text-lg font-bold hover:bg-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                </div>
            </form>

            {{-- ===== قسم تنفيذ الخدمات (خارج الفورم الرئيسي) ===== --}}
            @can('invoices.edit')
            <div class="mt-8 border-2 border-emerald-300 rounded-lg p-6 bg-gradient-to-br from-emerald-50 to-slate-50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-slate-800">
                        ▶ {{ app()->getLocale() === 'ar' ? 'تنفيذ الخدمات' : 'Execute Services' }}
                    </h3>
                    @php
                        $completedCount = $invoice->items->where('status', 'completed')->count();
                        $totalCount = $invoice->items->count();
                    @endphp
                    <span class="text-sm font-semibold px-3 py-1 rounded-full {{ $completedCount === $totalCount && $totalCount > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                        {{ $completedCount }}/{{ $totalCount }} {{ app()->getLocale() === 'ar' ? 'منفذة' : 'executed' }}
                    </span>
                </div>

                @if($errors->has('error'))
                    <div class="mb-3 p-3 rounded-lg bg-red-50 border border-red-300 text-red-800 text-sm font-medium">
                        ⚠️ {{ $errors->first('error') }}
                    </div>
                @endif

                <div class="overflow-x-auto border-2 border-slate-300 rounded-lg bg-white">
                    <table class="w-full border-collapse" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                        <thead>
                            <tr class="bg-slate-100 border-b-2 border-slate-300">
                                <th class="border border-slate-300 px-3 py-2 text-center text-sm font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الرمز' : 'Code' }}</th>
                                <th class="border border-slate-300 px-3 py-2 text-center text-sm font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الخدمة' : 'Service' }}</th>
                                <th class="border border-slate-300 px-3 py-2 text-center text-sm font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                <th class="border border-slate-300 px-3 py-2 text-center text-sm font-bold text-slate-700">{{ app()->getLocale() === 'ar' ? 'التنفيذ' : 'Execution' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                <tr class="border-b border-slate-200 {{ $item->isCompleted() ? 'bg-emerald-50/50' : '' }}">
                                    <td class="border border-slate-200 px-2 py-2 text-center text-sm text-slate-600">{{ $item->service?->code ?? '—' }}</td>
                                    <td class="border border-slate-200 px-2 py-2 text-sm font-medium text-slate-800">
                                        {{ app()->getLocale() === 'ar' && $item->service?->name_ar ? $item->service->name_ar : ($item->service?->name ?? '—') }}
                                    </td>
                                    <td class="border border-slate-200 px-2 py-2 text-center text-sm">{{ $item->quantity }}</td>
                                    <td class="border border-slate-200 px-2 py-2 text-center text-sm">
                                        @if($item->isCompleted())
                                            <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-xs font-semibold px-2 py-1 rounded-full border border-emerald-300">
                                                ✅ {{ app()->getLocale() === 'ar' ? 'منفذ' : 'Done' }}
                                            </span>
                                            @if($item->execution_date)
                                                <span class="block text-xs text-slate-500 mt-0.5">{{ $item->execution_date->format('Y-m-d') }}</span>
                                            @endif
                                            @if($item->completedByUser)
                                                <span class="block text-xs text-slate-400">{{ $item->completedByUser->name ?? $item->completedByUser->username }}</span>
                                            @endif
                                        @else
                                            <button type="button"
                                                onclick="openExecuteModal({{ $item->id }}, '{{ route('invoices.execute-service', [$invoice, $item]) }}', '{{ addslashes(app()->getLocale() === 'ar' && $item->service?->name_ar ? $item->service->name_ar : ($item->service?->name ?? '')) }}')"
                                                class="inline-flex items-center gap-1 bg-blue-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-700 shadow-sm">
                                                ▶ {{ app()->getLocale() === 'ar' ? 'تنفيذ' : 'Execute' }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endcan

            {{-- Execute Service Modal --}}
            <div id="execute-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-1">▶ {{ app()->getLocale() === 'ar' ? 'تنفيذ الخدمة' : 'Execute Service' }}</h3>
                    <p id="execute-modal-service-name" class="text-slate-600 text-sm mb-4"></p>
                    <form id="execute-modal-form" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                {{ app()->getLocale() === 'ar' ? 'تاريخ التنفيذ' : 'Execution Date' }} *
                            </label>
                            <input type="date" name="execution_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full rounded-lg border-2 border-slate-400 px-3 py-2 text-slate-900 font-medium focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2.5 rounded-lg font-bold hover:bg-blue-700 shadow">
                                ✅ {{ app()->getLocale() === 'ar' ? 'تأكيد التنفيذ' : 'Confirm Execution' }}
                            </button>
                            <button type="button" onclick="closeExecuteModal()"
                                class="px-4 py-2.5 bg-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-300">
                                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                function openExecuteModal(itemId, actionUrl, serviceName) {
                    document.getElementById('execute-modal-form').action = actionUrl;
                    document.getElementById('execute-modal-service-name').textContent = serviceName;
                    document.getElementById('execute-modal').classList.remove('hidden');
                }
                function closeExecuteModal() {
                    document.getElementById('execute-modal').classList.add('hidden');
                }
                document.getElementById('execute-modal').addEventListener('click', function(e) {
                    if (e.target === this) closeExecuteModal();
                });
            </script>
        </div>
    </div>


    <script>
        const searchUrl = '{{ route('invoices.services-search') }}';
        const isArabic = '{{ app()->getLocale() }}' === 'ar';
        let serviceRowIndex = {{ $invoice->items->count() }};

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        function addServiceRow(service) {
            const tbody = document.getElementById('services-container');
            const index = serviceRowIndex++;
            const name = (isArabic && service.name_ar) ? service.name_ar : service.name;
            const code = service.code || '—';
            const qty = service.is_multi_session && service.session_count ? service.session_count : 1;
            const price = service.default_price || 0;
            const total = (qty * price).toFixed(2);

            const tr = document.createElement('tr');
            tr.className = 'service-row border-b border-slate-400 hover:bg-slate-50';
            tr.innerHTML = `
                <td class="border border-slate-400 px-2 py-2 text-center text-sm font-medium text-slate-800">${escapeHtml(code)}</td>
                <td class="border border-slate-400 px-2 py-2">
                    <input type="hidden" name="services[${index}][service_id]" value="${service.id}">
                    <div class="text-sm font-medium text-slate-900">${escapeHtml(name)}</div>
                    <input type="text" name="services[${index}][description]"
                           placeholder="${isArabic ? 'ملاحظات (اختياري)' : 'Notes (optional)'}"
                           class="mt-1 w-full rounded border border-slate-300 px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                </td>
                <td class="border border-slate-400 px-2 py-2">
                    <input type="number" name="services[${index}][quantity]" value="${qty}" min="1" step="1" required
                           onchange="calculateRowTotal(this)"
                           class="w-full rounded border border-slate-400 px-2 py-2 text-sm text-center focus:ring-2 focus:ring-blue-500">
                </td>
                <td class="border border-slate-400 px-2 py-2">
                    <input type="number" name="services[${index}][unit_price]" value="${price}" step="0.01" min="0" required
                           onchange="calculateRowTotal(this)"
                           class="w-full rounded border border-slate-400 px-2 py-2 text-sm text-center focus:ring-2 focus:ring-blue-500">
                </td>
                <td class="border border-slate-400 px-2 py-2">
                    <input type="number" name="services[${index}][total_price]" value="${total}" step="0.01" readonly
                           class="w-full rounded border border-slate-400 px-2 py-2 text-sm text-center bg-slate-100 font-bold text-slate-800">
                </td>
                <td class="border border-slate-400 px-1 py-2 text-center">
                    <button type="button" onclick="removeServiceRow(this)" class="text-red-600 hover:text-red-800 p-1">
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            calculateGrandTotal();
        }

        function calculateRowTotal(input) {
            const row = input.closest('tr.service-row');
            if (!row) return;
            const qty = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
            row.querySelector('input[name*="[total_price]"]').value = (qty * price).toFixed(2);
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            const totalInputs = document.querySelectorAll('#services-container input[name*="[total_price]"]');
            let grand = 0;
            totalInputs.forEach(inp => { grand += parseFloat(inp.value) || 0; });
            document.getElementById('grand-total').textContent = grand.toFixed(2);
        }

        function removeServiceRow(btn) {
            const row = btn.closest('tr.service-row');
            if (row) row.remove();
            calculateGrandTotal();
        }

        document.addEventListener('DOMContentLoaded', function () {
            calculateGrandTotal();

            const searchInput = document.getElementById('service-search-input');
            const searchBtn = document.getElementById('service-search-btn');
            const resultsDiv = document.getElementById('service-search-results');

            function doSearch() {
                const q = (searchInput.value || '').trim();
                if (q.length < 1) { resultsDiv.classList.add('hidden'); return; }
                fetch(searchUrl + '?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(list => {
                        resultsDiv.innerHTML = '';
                        if (!list.length) {
                            resultsDiv.innerHTML = '<p class="p-4 text-slate-700 font-medium">' + (isArabic ? 'لا توجد نتائج' : 'No results') + '</p>';
                        } else {
                            list.forEach(s => {
                                const name = (isArabic && s.name_ar) ? s.name_ar : s.name;
                                const el = document.createElement('button');
                                el.type = 'button';
                                el.className = 'w-full text-left px-4 py-3 hover:bg-blue-100 border-b border-slate-200 last:border-0 text-base font-medium text-slate-800';
                                el.textContent = name + (s.code ? ' (' + s.code + ')' : '') + ' — ' + s.default_price;
                                el.addEventListener('click', function () {
                                    addServiceRow(s);
                                    resultsDiv.classList.add('hidden');
                                    resultsDiv.innerHTML = '';
                                    searchInput.value = '';
                                });
                                resultsDiv.appendChild(el);
                            });
                        }
                        resultsDiv.classList.remove('hidden');
                    })
                    .catch(() => {
                        resultsDiv.innerHTML = '<p class="p-4 text-red-700">' + (isArabic ? 'خطأ في البحث' : 'Search error') + '</p>';
                        resultsDiv.classList.remove('hidden');
                    });
            }

            searchBtn.addEventListener('click', doSearch);
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
            });
            searchInput.addEventListener('input', function () {
                clearTimeout(this._t);
                this._t = setTimeout(doSearch, 300);
            });
        });
    </script>
@endsection
