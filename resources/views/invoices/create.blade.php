@extends('layouts.app')
@section('title', isset($patient) ? (app()->getLocale() === 'ar' ? 'تقديم خدمة' : 'Add Service') : (app()->getLocale() === 'ar' ? 'إنشاء فاتورة جديدة' : 'Create New Invoice'))

@section('content')
    @php
        $inputClass = 'w-full rounded-lg border-2 border-slate-400 bg-white px-3 py-2 text-slate-900 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
    @endphp
    <div class="max-w-6xl mx-auto">
        <div class=" rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">
                @if(isset($patient))
                    {{ app()->getLocale() === 'ar' ? '🧾 تقديم خدمة' : '🧾 Add Service' }}
                @else
                    {{ app()->getLocale() === 'ar' ? '🧾 إنشاء فاتورة جديدة' : '🧾 Create New Invoice' }}
                @endif
            </h2>

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

            <form action="{{ route('invoices.store') }}" method="POST" id="invoice-form" class="space-y-6">
                @csrf

                @if (isset($patient))
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                @endif

                {{-- Patient block: نفس التصميم سواء مريض محدد مسبقاً أو اختيار من القائمة (تقديم خدمة من مريض) --}}
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-lg p-5 mb-6 shadow-sm">
                    <h3 class="font-bold text-blue-900 mb-3 text-lg">
                        {{ app()->getLocale() === 'ar' ? '👤 معلومات المريض' : '👤 Patient Information' }}
                    </h3>

                @if (isset($patient))
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold  mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم:' : 'Name:' }}</label>
                                <input type="text" name="patient_name" value="{{ old('patient_name', $patient->name) }}"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي):' : 'Name (Arabic):' }}</label>
                                <input type="text" name="patient_name_ar"
                                    value="{{ old('patient_name_ar', $patient->name_ar) }}" dir="rtl"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الملف:' : 'File No:' }}</label>
                                <input type="text" name="patient_file_number"
                                    value="{{ old('patient_file_number', $patient->file_number) }}"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'نوع الدفع:' : 'Payment type:' }}</label>
                                <select name="patient_payment_type" id="patient_payment_type" class="{{ $inputClass }}">
                                    <option value="cash" {{ old('patient_payment_type', $patient->payment_type) === 'cash' ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? 'نقدي (كاش)' : 'Cash' }}
                                    </option>
                                    <option value="insurance" {{ old('patient_payment_type', $patient->payment_type) === 'insurance' ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? 'تأمين (شركة)' : 'Insurance' }}
                                    </option>
                                    <option value="charity" {{ old('patient_payment_type', $patient->payment_type) === 'charity' ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? 'جمعية' : 'Charity' }}
                                    </option>
                                </select>
                            </div>
                            <div id="patient_insurance_wrap" class="{{ old('patient_payment_type', $patient->payment_type) === 'insurance' ? '' : 'hidden' }}">
                                <label class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'شركة التأمين:' : 'Insurance company:' }}</label>
                                <select name="patient_insurance_company_id" class="{{ $inputClass }}">
                                    <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر الشركة --' : '-- Select company --' }}</option>
                                    @foreach ($insuranceCompanies ?? [] as $company)
                                        <option value="{{ $company->id }}" {{ old('patient_insurance_company_id', $patient->insurance_company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="patient_charity_wrap" class="{{ old('patient_payment_type', $patient->payment_type) === 'charity' ? '' : 'hidden' }}">
                                <label class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'الجمعية:' : 'Charity:' }}</label>
                                <select name="patient_charity_entity_id" class="{{ $inputClass }}">
                                    <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر الجمعية --' : '-- Select charity --' }}</option>
                                    @foreach ($charityEntities ?? [] as $entity)
                                        <option value="{{ $entity->id }}" {{ old('patient_charity_entity_id', $patient->charity_entity_id) == $entity->id ? 'selected' : '' }}>{{ $entity->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'نوع الهوية:' : 'Identity Type:' }}</label>
                                <select name="patient_identity_type" class="{{ $inputClass }}">
                                    <option value="">{{ app()->getLocale() === 'ar' ? '—' : '—' }}</option>
                                    @foreach (\App\Models\Patient::identityTypeOptions() as $key => $labels)
                                        <option value="{{ $key }}"
                                            {{ old('patient_identity_type', $patient->identity_type) === $key ? 'selected' : '' }}>
                                            {{ app()->getLocale() === 'ar' ? $labels['ar'] : $labels['en'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الهوية:' : 'Identity No:' }}</label>
                                <input type="text" name="patient_identity_value"
                                    value="{{ old('patient_identity_value', $patient->identity_value) }}"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'الهاتف:' : 'Phone:' }}</label>
                                <input type="text" name="patient_phone"
                                    value="{{ old('patient_phone', $patient->phone) }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'العمر:' : 'Age:' }}</label>
                                <input type="number" name="patient_age" value="{{ old('patient_age', $patient->age) }}"
                                    min="0" max="150" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'الجنس:' : 'Gender:' }}</label>
                                <select name="patient_gender" class="{{ $inputClass }}">
                                    <option value="">{{ app()->getLocale() === 'ar' ? '—' : '—' }}</option>
                                    <option value="male"
                                        {{ old('patient_gender', $patient->gender) === 'male' ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }}</option>
                                    <option value="female"
                                        {{ old('patient_gender', $patient->gender) === 'female' ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }}</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'بلد المنشأ:' : 'Country of Origin:' }}</label>
                                <input type="text" name="patient_country_of_origin"
                                    value="{{ old('patient_country_of_origin', $patient->country_of_origin) }}"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'اسم الكفيل:' : 'Sponsor Name:' }}</label>
                                <input type="text" name="patient_sponsor_name"
                                    value="{{ old('patient_sponsor_name', $patient->sponsor_name) }}"
                                    class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'هاتف الكفيل:' : 'Sponsor Phone:' }}</label>
                                <input type="text" name="patient_sponsor_phone"
                                    value="{{ old('patient_sponsor_phone', $patient->sponsor_phone) }}"
                                    class="{{ $inputClass }}">
                            </div>
                        </div>
                @else
                    {{-- بحث عن المريض: اسم / رقم هوية / فيزا / رقم ملف / هاتف — ثم اختياره لتحميل بياناته --}}
                    <div>
                        <label class="block text-blue-700 font-semibold text-sm mb-2">
                            {{ app()->getLocale() === 'ar' ? 'بحث عن المريض' : 'Search for patient' }} *
                        </label>
                        <p class="text-slate-600 text-sm mb-2">
                            {{ app()->getLocale() === 'ar' ? 'ابحث بالاسم أو رقم الهوية أو رقم الفيزا أو رقم الملف أو الهاتف، ثم اختر المريض لتحميل بياناته.' : 'Search by name, ID number, visa, file number or phone, then select the patient to load their data.' }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <input type="text" id="patient-search-input"
                                placeholder="{{ app()->getLocale() === 'ar' ? 'اسم، رقم هوية، فيزا، رقم ملف، هاتف...' : 'Name, ID, visa, file no, phone...' }}"
                                class="flex-1 min-w-[200px] {{ $inputClass }}">
                            <button type="button" id="patient-search-btn"
                                class="bg-blue-600 px-5 text-white py-3 rounded-lg font-bold text-base hover:bg-blue-700 shadow">
                                {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                            </button>
                        </div>
                        <div id="patient-search-results" class="mt-3 hidden border-2 border-slate-300 rounded-lg bg-white max-h-64 overflow-y-auto shadow-inner"></div>
                        <input type="hidden" name="patient_id" id="invoice_patient_id" value="">
                    </div>
                @endif
                </div>

                {{-- Invoice Date & Referral Number --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'تاريخ الفاتورة' : 'Invoice Date' }} *
                        </label>
                        <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required
                            class="{{ $inputClass}}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'رقم الإحالة (من مستوصف / جهة أخرى)' : 'Referral No. (from clinic / other)' }}
                            <span
                                class="text-slate-500 font-normal">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'optional' }})</span>
                        </label>
                        <input type="text" name="referral_number" value="{{ old('referral_number') }}" maxlength="100"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: إحالة من مستوصف' : 'e.g. referral from clinic' }}"
                            class="{{ $inputClass }}">
                    </div>
                </div>

                {{-- Services Section (جدول الخدمات المقدمة - شكل نموذج وزارة الصحة) --}}
                <div class="border-2 border-blue-300 rounded-lg p-6 bg-gradient-to-br from-blue-50 to-slate-50">
                    <h3 class="text-xl font-bold text-slate-800 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'الخدمات المقدمة' : 'Provided Services' }}
                    </h3>
                    {{-- Search by name or code --}}
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-800 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'بحث عن خدمة (بالاسم أو الكود)' : 'Search for a service (by name or code)' }}
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <input type="text" id="service-search-input"
                                placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب اسم الخدمة أو الكود ثم اضغط بحث' : 'Type service name or code then click Search' }}"
                                class="flex-1 min-w-[200px]
                                {{ $inputClass }}">
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
                            </tbody>
                        </table>
                    </div>

                    {{-- Total Section --}}
                    <div class="mt-6 pt-6 border-t-2 border-slate-300">
                        <div
                            class="flex justify-between items-center bg-gradient-to-r from-slate-800 to-slate-700 p-4 rounded-lg shadow-lg">
                            <span class="text-xl font-bold">
                                {{ app()->getLocale() === 'ar' ? 'المجموع الإجمالي:' : 'Total Amount:' }}
                            </span>
                            <span id="grand-total" class="text-3xl font-bold">0.00</span>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}
                    </label>
                    <textarea name="notes" rows="3"
                        class="{{ $inputClass}}">{{ old('notes') }}</textarea>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-6 border-t-2 border-slate-200">
                    <button type="submit"
                        class="bg-blue-600 px-3 py-2 text-white rounded-lg text-lg font-bold hover:bg-blue-700 shadow-lg flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ app()->getLocale() === 'ar' ? 'إنشاء الفاتورة' : 'Create Invoice' }}
                    </button>
                    <a href="{{ route('invoices.index') }}"
                        class="bg-slate-200 text-slate-700 px-3 py-2 rounded-lg text-lg font-bold hover:bg-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const searchUrl = '{{ route('invoices.services-search') }}';
        const patientSearchUrl = '{{ route('invoices.patients-search') }}';
        const invoiceCreateUrl = '{{ route('invoices.create') }}';
        const isArabic = '{{ app()->getLocale() }}' === 'ar';
        let serviceRowIndex = 0;

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
                <td class="border border-slate-400 px-2 py-2 align-top text-center text-sm font-medium text-slate-800">${escapeHtml(code)}</td>
                <td class="border border-slate-400 px-2 py-2 align-top">
                    <input type="hidden" form="invoice-form" name="services[${index}][service_id]" value="${service.id}">
                    <div class="text-sm font-medium text-slate-900 whitespace-pre-wrap">${escapeHtml(name)}</div>
                    <input type="text" form="invoice-form" name="services[${index}][description]" placeholder="${isArabic ? 'ملاحظات (اختياري)' : 'Notes (optional)'}" class="mt-1 w-full rounded border border-slate-300 px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                </td>
                <td class="border border-slate-400 px-2 py-2 align-top">
                    <input type="number" form="invoice-form" name="services[${index}][quantity]" value="${qty}" min="1" step="1" required onchange="calculateRowTotal(${index})" class="w-full rounded border border-slate-400 px-2 py-2 text-sm text-center focus:ring-2 focus:ring-blue-500">
                </td>
                <td class="border border-slate-400 px-2 py-2 align-top">
                    <input type="number" form="invoice-form" name="services[${index}][unit_price]" value="${price}" step="0.01" min="0" required onchange="calculateRowTotal(${index})" class="w-full rounded border border-slate-400 px-2 py-2 text-sm text-center focus:ring-2 focus:ring-blue-500">
                </td>
                <td class="border border-slate-400 px-2 py-2 align-top">
                    <input type="number" form="invoice-form" name="services[${index}][total_price]" value="${total}" step="0.01" readonly class="w-full rounded border border-slate-400 px-2 py-2 text-sm text-center bg-slate-100 font-bold text-slate-800">
                </td>
                <td class="border border-slate-400 px-1 py-2 align-middle text-center">
                    <button type="button" onclick="removeServiceRow(this)" class="text-red-600 hover:text-red-800 p-1" title="${isArabic ? 'حذف' : 'Remove'}">
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </td>
            `;
            tbody.appendChild(tr);
            calculateGrandTotal();
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        function calculateRowTotal(index) {
            const tbody = document.getElementById('services-container');
            const row = tbody.querySelector('.service-row input[name="services[' + index + '][quantity]"]')?.closest('tr');
            if (!row) return;

            const qty = parseFloat(row.querySelector('input[name="services[' + index + '][quantity]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="services[' + index + '][unit_price]"]').value) || 0;
            const total = qty * price;

            row.querySelector('input[name="services[' + index + '][total_price]"]').value = total.toFixed(2);
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            const container = document.getElementById('services-container');
            const totalInputs = container.querySelectorAll('input[name*="[total_price]"]');
            let grandTotal = 0;

            totalInputs.forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });

            document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
        }

        function removeServiceRow(button) {
            const row = button.closest('tr.service-row');
            if (row) row.remove();
            calculateGrandTotal();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const paymentTypeEl = document.getElementById('patient_payment_type');
            const insuranceWrap = document.getElementById('patient_insurance_wrap');
            const charityWrap = document.getElementById('patient_charity_wrap');
            if (paymentTypeEl && insuranceWrap && charityWrap) {
                function togglePaymentFields() {
                    const v = paymentTypeEl.value;
                    insuranceWrap.classList.toggle('hidden', v !== 'insurance');
                    charityWrap.classList.toggle('hidden', v !== 'charity');
                }
                paymentTypeEl.addEventListener('change', togglePaymentFields);
                togglePaymentFields();
            }

            const searchInput = document.getElementById('service-search-input');
            const searchBtn = document.getElementById('service-search-btn');
            const resultsDiv = document.getElementById('service-search-results');

            function doSearch() {
                const q = (searchInput.value || '').trim();
                if (q.length < 1) {
                    resultsDiv.classList.add('hidden');
                    return;
                }
                fetch(searchUrl + '?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(list => {
                        resultsDiv.innerHTML = '';
                        if (list.length === 0) {
                            resultsDiv.innerHTML = '<p class="p-4 text-slate-700 font-medium text-base">' + (
                                isArabic ? 'لا توجد نتائج' : 'No results') + '</p>';
                        } else {
                            list.forEach(s => {
                                const name = (isArabic && s.name_ar) ? s.name_ar : s.name;
                                const code = s.code ? ' (' + s.code + ')' : '';
                                const price = s.default_price;
                                const el = document.createElement('button');
                                el.type = 'button';
                                el.className =
                                    'w-full text-left px-4 py-3 hover:bg-blue-100 border-b border-slate-200 last:border-0 text-base font-medium text-slate-800';
                                el.textContent = name + code + ' - ' + price;
                                el.addEventListener('click', function() {
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
                        resultsDiv.innerHTML = '<p class="p-4 text-red-700 font-medium text-base">' + (
                            isArabic ? 'خطأ في البحث' : 'Search error') + '</p>';
                        resultsDiv.classList.remove('hidden');
                    });
            }

            searchBtn.addEventListener('click', doSearch);
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    doSearch();
                }
            });

            // Patient search (عند عدم وجود مريض محدد: بحث باسم / هوية / فيزا / ملف / هاتف)
            const patientSearchInput = document.getElementById('patient-search-input');
            const patientSearchBtn = document.getElementById('patient-search-btn');
            const patientResultsDiv = document.getElementById('patient-search-results');
            if (patientSearchInput && patientSearchBtn && patientResultsDiv) {
                function doPatientSearch() {
                    const q = (patientSearchInput.value || '').trim();
                    if (q.length < 1) {
                        patientResultsDiv.classList.add('hidden');
                        return;
                    }
                    fetch(patientSearchUrl + '?q=' + encodeURIComponent(q))
                        .then(r => r.json())
                        .then(list => {
                            patientResultsDiv.innerHTML = '';
                            if (list.length === 0) {
                                patientResultsDiv.innerHTML = '<p class="p-4 text-slate-700 font-medium text-base">' + (isArabic ? 'لا توجد نتائج' : 'No results') + '</p>';
                            } else {
                                list.forEach(function(p) {
                                    const name = (isArabic && p.name_ar) ? p.name_ar : p.name;
                                    const extra = [p.file_number, p.identity_value, p.phone].filter(Boolean).join(' · ');
                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className = 'w-full text-left px-4 py-3 hover:bg-blue-100 border-b border-slate-200 last:border-0 text-base font-medium text-slate-800';
                                    btn.innerHTML = '<span class="font-semibold">' + (name || p.name) + '</span>' + (extra ? '<br><span class="text-slate-600 text-sm">' + extra + '</span>' : '');
                                    btn.addEventListener('click', function() {
                                        window.location.href = invoiceCreateUrl + '?patient_id=' + p.id;
                                    });
                                    patientResultsDiv.appendChild(btn);
                                });
                            }
                            patientResultsDiv.classList.remove('hidden');
                        })
                        .catch(function() {
                            patientResultsDiv.innerHTML = '<p class="p-4 text-red-700 font-medium text-base">' + (isArabic ? 'خطأ في البحث' : 'Search error') + '</p>';
                            patientResultsDiv.classList.remove('hidden');
                        });
                }
                patientSearchBtn.addEventListener('click', doPatientSearch);
                patientSearchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        doPatientSearch();
                    }
                });
            }
        });

        // Form validation
        document.getElementById('invoice-form').addEventListener('submit', function(e) {
            const patientIdInput = document.getElementById('invoice_patient_id');
            if (patientIdInput && !patientIdInput.value) {
                e.preventDefault();
                alert('{{ app()->getLocale() === 'ar' ? 'يرجى البحث عن المريض واختياره أولاً' : 'Please search and select a patient first' }}');
                return false;
            }
            const container = document.getElementById('services-container');
            const rows = container.querySelectorAll('.service-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('{{ app()->getLocale() === 'ar' ? 'يجب إضافة خدمة واحدة على الأقل' : 'Please add at least one service' }}');
                return false;
            }
        });
    </script>
@endsection
