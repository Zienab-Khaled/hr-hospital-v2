@extends('layouts.app')
@section('title', isset($patient) ? (app()->getLocale() === 'ar' ? 'تقديم خدمة' : 'Add Service') : (app()->getLocale()
    === 'ar' ? 'إنشاء فاتورة جديدة' : 'Create New Invoice'))

@section('content')
    @php
        $inputClass =
            'w-full rounded-lg border-2 border-slate-400 bg-white px-3 py-2 text-slate-900 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
        $patientHasPartyCoverage = isset($patient) && ($patient->payment_type ?? '') === 'insurance';
        $patientPartyLabelAr = (isset($patient) && ($patient->payment_type ?? '') === 'charity') ? 'الجمعية' : 'التأمين';
        $patientPartyLabelEn = (isset($patient) && ($patient->payment_type ?? '') === 'charity') ? 'Charity' : 'Insurance';
    @endphp
    <div class="max-w-6xl mx-auto">
        <div class=" rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">
                @if (isset($patient))
                    {{ app()->getLocale() === 'ar' ? '🧾 تقديم خدمة' : '🧾 Add Service' }}
                @else
                    {{ app()->getLocale() === 'ar' ? '🧾 إنشاء فاتورة جديدة' : '🧾 Create New Invoice' }}
                @endif
            </h2>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg border-2 border-red-400 bg-red-50 text-red-800">
                    <p class="font-bold mb-2">
                        {{ app()->getLocale() === 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please fix the following errors:' }}
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('invoices.store') }}" method="POST" id="invoice-form" class="space-y-6"
                enctype="multipart/form-data">
                @csrf

                @if (isset($patient))
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    @if (isset($visit) && $visit)
                        <input type="hidden" name="visit_id" value="{{ $visit->id }}">
                    @endif
                @endif

                {{-- Patient block: نفس التصميم سواء مريض محدد مسبقاً أو اختيار من القائمة (تقديم خدمة من مريض) --}}
                <div
                    class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-lg p-5 mb-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <h3 class="font-bold text-blue-900 text-lg">
                            {{ app()->getLocale() === 'ar' ? '👤 معلومات المريض' : '👤 Patient Information' }}
                        </h3>
                        @if (isset($patient) && auth()->user()?->can('patients.create'))
                            <a href="{{ route('patients.create') }}"
                                class="inline-flex items-center gap-1.5 shrink-0 bg-emerald-600 text-white px-3 py-1.5 rounded-md font-semibold text-xs hover:bg-emerald-700">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                <span>{{ app()->getLocale() === 'ar' ? 'مريض جديد' : 'New patient' }}</span>
                            </a>
                        @endif
                    </div>

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
                                    value="{{ old('patient_name_ar', $patient->fullArabicName()) }}" dir="rtl"
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
                                    @foreach (\App\Models\Patient::paymentTypeOptions() as $value => $labels)
                                        <option value="{{ $value }}"
                                            {{ old('patient_payment_type', $patient->payment_type) === $value ? 'selected' : '' }}>
                                            {{ app()->getLocale() === 'ar' ? $labels['ar'] : $labels['en'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="patient_insurance_wrap"
                                class="{{ old('patient_payment_type', $patient->payment_type) === 'insurance' ? '' : 'hidden' }}">
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'شركة التأمين:' : 'Insurance company:' }}</label>
                                <select name="patient_insurance_company_id" class="{{ $inputClass }}">
                                    <option value="">
                                        {{ app()->getLocale() === 'ar' ? '-- اختر الشركة --' : '-- Select company --' }}
                                    </option>
                                    @foreach ($insuranceCompanies ?? [] as $company)
                                        <option value="{{ $company->id }}"
                                            {{ old('patient_insurance_company_id', $patient->insurance_company_id) == $company->id ? 'selected' : '' }}>
                                            {{ app()->getLocale() === 'ar' ? ($company->name_ar ?? $company->name) : $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="patient_charity_wrap"
                                class="{{ old('patient_payment_type', $patient->payment_type) === 'charity' ? '' : 'hidden' }}">
                                <label
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'الجمعية:' : 'Charity:' }}</label>
                                <select name="patient_charity_entity_id" class="{{ $inputClass }}">
                                    <option value="">
                                        {{ app()->getLocale() === 'ar' ? '-- اختر الجمعية --' : '-- Select charity --' }}
                                    </option>
                                    @foreach ($charityEntities ?? [] as $entity)
                                        <option value="{{ $entity->id }}"
                                            {{ old('patient_charity_entity_id', $patient->charity_entity_id) == $entity->id ? 'selected' : '' }}>
                                            {{ $entity->name_ar ?: $entity->name }}</option>
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
                                    class="block text-blue-700 font-semibold text-sm mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ الميلاد:' : 'Date of birth:' }}</label>
                                <input type="date" name="patient_date_of_birth"
                                    value="{{ old('patient_date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="{{ $inputClass }}">
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

                        @php
                            $patientDocuments = $patient
                                ->getMedia('documents')
                                ->merge($patient->getMedia('medical-reports'));
                        @endphp
                        @if ($patientDocuments->isNotEmpty())
                            <div class="mt-4 pt-4 border-t border-blue-200">
                                <h4 class="font-bold text-blue-900 text-sm mb-2">
                                    {{ app()->getLocale() === 'ar' ? '📎 مستندات المريض — اختر ما يُطبع مع الفاتورة' : '📎 Patient documents — select which to print with invoice' }}
                                </h4>
                                <div class="flex flex-wrap gap-3">
                                    @foreach ($patientDocuments as $media)
                                        <div
                                            class="inline-flex items-center gap-2 p-2 rounded-lg border border-slate-300 bg-white">
                                            <input type="checkbox" name="print_media_ids[]" value="{{ $media->id }}"
                                                id="print_media_{{ $media->id }}"
                                                {{ in_array($media->id, old('print_media_ids', [])) ? 'checked' : '' }}
                                                class="rounded border-slate-400 text-blue-600 focus:ring-blue-500 shrink-0">
                                            <label for="print_media_{{ $media->id }}"
                                                class="cursor-pointer text-sm font-medium text-slate-800 truncate max-w-[160px]"
                                                title="{{ $media->file_name }}">
                                                {{ $media->file_name ?? ($media->name ?? 'File #' . $media->id) }}
                                            </label>
                                            <span
                                                class="text-xs text-slate-500 shrink-0">({{ strtoupper($media->extension) }})</span>
                                            <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener noreferrer"
                                                class="shrink-0 text-blue-600 hover:text-blue-800 text-xs font-semibold whitespace-nowrap">
                                                {{ app()->getLocale() === 'ar' ? 'عرض' : 'Preview' }}
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- بحث عن المريض أو إنشاء مريض جديد — ثم إنشاء فاتورة له --}}
                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <label class="block text-blue-700 font-semibold text-sm">
                                    {{ app()->getLocale() === 'ar' ? 'بحث عن المريض' : 'Search for patient' }} *
                                </label>
                                @can('patients.create')
                                    <a href="{{ route('patients.create') }}"
                                        class="inline-flex items-center gap-1.5 bg-emerald-600 text-white px-3 py-1.5 rounded-md font-semibold text-xs hover:bg-emerald-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        {{ app()->getLocale() === 'ar' ? 'إنشاء مريض جديد' : 'Create new patient' }}
                                    </a>
                                @endcan
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <input type="text" id="patient-search-input"
                                    placeholder="{{ app()->getLocale() === 'ar' ? 'اسم، رقم هوية، فيزا، رقم ملف، هاتف...' : 'Name, ID, visa, file no, phone...' }}"
                                    class="flex-1 min-w-[200px] {{ $inputClass }}">
                                <button type="button" id="patient-search-btn"
                                    class="bg-blue-600 px-5 text-white py-3 rounded-lg font-bold text-base hover:bg-blue-700 shadow">
                                    {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                                </button>
                            </div>
                            <div id="patient-search-results"
                                class="mt-3 hidden border-2 border-slate-300 rounded-lg bg-white max-h-64 overflow-y-auto shadow-inner">
                            </div>
                            <input type="hidden" name="patient_id" id="invoice_patient_id" value="">
                        </div>
                    @endif

                    <div id="charity-treatment-invoice-wrap" class="hidden mt-4 rounded-lg border-2 border-teal-200 bg-teal-50/80 p-4">
                        <p class="text-sm font-bold text-teal-900 mb-2">
                            {{ app()->getLocale() === 'ar' ? '🤝 مريض جمعية — حالة هذه الزيارة' : '🤝 Charity patient — this visit' }}
                        </p>
                        @php
                            $oldCharityMode = old('charity_treatment_invoice_mode', 'ahli_eligibility');
                            if (in_array($oldCharityMode, ['eligibility', 'no_eligibility', ''], true)) {
                                $oldCharityMode = 'ahli_eligibility';
                            }
                        @endphp
                        <div class="space-y-3">
                            <label class="flex items-start gap-2 cursor-pointer font-medium text-slate-800">
                                <input type="radio" name="charity_treatment_invoice_mode" value="ahli_eligibility"
                                    {{ $oldCharityMode === 'ahli_eligibility' ? 'checked' : '' }}
                                    class="mt-1 text-teal-600 focus:ring-teal-500 charity-treatment-mode">
                                <span>
                                    <span class="block font-bold">{{ app()->getLocale() === 'ar' ? 'أحقية علاج أهلي' : 'Charity treatment eligibility' }}</span>
                                    <span class="block text-xs text-teal-800">{{ app()->getLocale() === 'ar' ? 'لديه جمعية — المريض لا يدفع (حصة المريض = 0)' : 'Has charity coverage — patient pays 0' }}</span>
                                </span>
                            </label>
                            <label class="flex items-start gap-2 cursor-pointer font-medium text-slate-800">
                                <input type="radio" name="charity_treatment_invoice_mode" value="no_charity_now"
                                    {{ $oldCharityMode === 'no_charity_now' ? 'checked' : '' }}
                                    class="mt-1 text-teal-600 focus:ring-teal-500 charity-treatment-mode">
                                <span>
                                    <span class="block font-bold">{{ app()->getLocale() === 'ar' ? 'لا يوجد جمعية حالياً' : 'No charity currently' }}</span>
                                    <span class="block text-xs text-teal-800">{{ app()->getLocale() === 'ar' ? 'يُحسب على المريض — نقدي أو تأمين جديد لهذه الزيارة' : 'Billed to patient — cash or new insurance for this visit' }}</span>
                                </span>
                            </label>
                        </div>
                        <div id="charity-fallback-wrap" class="hidden mt-4 mr-6 pr-4 border-r-4 border-amber-300 space-y-3">
                            <p class="text-xs font-bold text-amber-900">
                                {{ app()->getLocale() === 'ar' ? 'طريقة الدفع لهذه الزيارة:' : 'Payment for this visit:' }}
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-slate-800">
                                    <input type="radio" name="charity_fallback_payment" value="cash"
                                        {{ old('charity_fallback_payment', 'cash') === 'cash' ? 'checked' : '' }}
                                        class="text-amber-600 focus:ring-amber-500 charity-fallback-mode">
                                    <span>{{ app()->getLocale() === 'ar' ? 'نقدي — المريض يدفع' : 'Cash — patient pays' }}</span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer font-medium text-slate-800">
                                    <input type="radio" name="charity_fallback_payment" value="insurance"
                                        {{ old('charity_fallback_payment') === 'insurance' ? 'checked' : '' }}
                                        class="text-amber-600 focus:ring-amber-500 charity-fallback-mode">
                                    <span>{{ app()->getLocale() === 'ar' ? 'تأمين جديد' : 'New insurance' }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Invoice Date & Referral Number --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'تاريخ الفاتورة' : 'Invoice Date' }} *
                        </label>
                        <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}"
                            required class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'رقم الإحالة (من مستوصف / جهة أخرى)' : 'Referral No. (from clinic / other)' }}
                            <span
                                class="text-slate-500 font-normal">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'optional' }})</span>
                        </label>
                        <input type="text" name="referral_number" value="{{ old('referral_number') }}"
                            maxlength="100"
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
                                    <th
                                        class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 w-24">
                                        {{ app()->getLocale() === 'ar' ? 'الرمز' : 'Code' }}</th>
                                    <th
                                        class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 min-w-[200px]">
                                        {{ app()->getLocale() === 'ar' ? 'البيان' : 'Description' }}</th>
                                    <th
                                        class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 w-20">
                                        {{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                                    <th
                                        class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 w-28">
                                        {{ app()->getLocale() === 'ar' ? 'السعر الافرادي' : 'Unit Price' }}</th>
                                    <th
                                        class="border border-slate-500 px-3 py-2 text-center text-sm font-bold text-slate-800 w-28">
                                        {{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                    <th id="th-coverage-type"
                                        class="insurance-coverage-th border border-slate-500 px-2 py-2 text-center text-sm font-bold text-slate-800 w-32 {{ $patientHasPartyCoverage ? '' : 'hidden' }}">
                                        {{ app()->getLocale() === 'ar' ? 'نوع التغطية' : 'Coverage type' }}</th>
                                    <th id="th-coverage-value"
                                        class="insurance-coverage-th border border-slate-500 px-2 py-2 text-center text-sm font-bold text-slate-800 w-36 {{ $patientHasPartyCoverage ? '' : 'hidden' }}">
                                        {{ app()->getLocale() === 'ar' ? 'قيمة التغطية / الخصم' : 'Coverage / discount' }}
                                    </th>
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
                            class="flex justify-between items-center bg-gradient-to-r from-slate-800 to-slate-700 p-4 rounded-lg shadow-lg text-white">
                            <span class="text-xl font-bold text-white">
                                {{ app()->getLocale() === 'ar' ? 'المجموع الإجمالي:' : 'Total Amount:' }}
                            </span>
                            <span id="grand-total" class="text-3xl font-bold text-white">0.00</span>
                        </div>
                        <div id="insurance-totals-wrap"
                            class="mt-3 space-y-2 {{ $patientHasPartyCoverage ? '' : 'hidden' }}">
                            <div
                                class="flex justify-between items-center bg-emerald-100 border border-emerald-300 p-3 rounded-lg">
                                <span id="party-covered-label"
                                    class="font-semibold text-emerald-800">{{ app()->getLocale() === 'ar' ? 'إجمالي المبلغ المغطى (' . $patientPartyLabelAr . '):' : $patientPartyLabelEn . ' covered total:' }}</span>
                                <span id="insurance-covered-total" class="text-xl font-bold text-emerald-800">0.00</span>
                            </div>
                            <div
                                class="flex justify-between items-center bg-amber-50 border border-amber-300 p-3 rounded-lg">
                                <span
                                    class="font-semibold text-amber-900">{{ app()->getLocale() === 'ar' ? 'المتبقي على المريض:' : 'Patient share:' }}</span>
                                <span id="patient-share-total" class="text-xl font-bold text-amber-900">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- التقرير الطبي: رفع أو مسح بالكاميرا (مثل إنشاء المريض) --}}
                <div class="border-2 border-slate-300 rounded-lg p-4 bg-slate-50">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? '📋 التقرير الطبي (اختياري)' : '📋 Medical report (optional)' }}
                    </label>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <button type="button" id="btn_inv_mode_upload"
                            class="px-4 py-2 rounded-lg text-sm font-semibold border-2 border-blue-600 bg-blue-600 text-white hover:bg-blue-700">
                            {{ app()->getLocale() === 'ar' ? '📁 رفع ملف' : '📁 Upload file' }}
                        </button>
                        <button type="button" id="btn_inv_mode_scan"
                            class="px-4 py-2 rounded-lg text-sm font-semibold border-2 border-slate-400 bg-slate-100 text-slate-800 hover:bg-slate-200">
                            {{ app()->getLocale() === 'ar' ? '📷 مسح بالكاميرا' : '📷 Scan with camera' }}
                        </button>
                    </div>
                    <div id="inv_medical_upload_box">
                        <input type="file" name="medical_reports[]" id="medical_reports_upload"
                            accept=".pdf,image/jpeg,image/png,image/jpg" multiple
                            class="invoice-medical-input w-full rounded-lg border-2 border-slate-400 bg-white px-3 py-2 text-slate-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-2 file:border-blue-500 file:bg-blue-600 file:text-white file:font-semibold file:cursor-pointer hover:file:bg-blue-700">
                    </div>
                    <div id="inv_medical_scan_box" class="hidden">
                        <div id="inv_scan_pdf_pages" class="flex flex-wrap gap-2 mb-3 min-h-[60px]"></div>
                        <div class="flex flex-wrap gap-2 items-center">
                            <button type="button" id="btn_inv_add_scan_page"
                                class="border-2 border-emerald-600 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700">
                                {{ app()->getLocale() === 'ar' ? '➕ إضافة صفحة (مسح بالكاميرا)' : '➕ Add page (scan with camera)' }}
                            </button>
                            <button type="button" id="btn_inv_generate_pdf"
                                class="border-2 border-slate-500 bg-slate-600  px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-700 disabled:opacity-50"
                                disabled>
                                {{ app()->getLocale() === 'ar' ? '📄 إنشاء PDF ومرفق' : '📄 Generate PDF & attach' }}
                            </button>
                        </div>
                        <p id="inv_scanned_pdf_status" class="mt-2 text-sm text-slate-600 hidden"></p>
                        <input type="file" name="medical_reports[]" id="medical_report_scanned_file" accept=".pdf"
                            class="hidden">
                    </div>
                </div>
                {{-- مودال الكاميرا للتقرير الطبي --}}
                <div id="inv_camera_modal"
                    class="hidden fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl max-w-lg w-full p-4">
                        <p id="inv_camera_step_label" class="font-semibold text-slate-800 mb-2"></p>
                        <video id="inv_camera_video" autoplay playsinline class="w-full rounded border bg-slate-900"
                            style="max-height: 50vh;"></video>
                        <div class="flex gap-2 mt-3">
                            <button type="button" id="btn_inv_take_photo"
                                class="flex-1 border-2 border-blue-600 bg-blue-600 py-2.5 rounded-lg font-semibold text-white hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'التقاط' : 'Capture' }}</button>
                            <button type="button" id="btn_inv_close_camera"
                                class="px-4 py-2.5 border-2 border-slate-400 bg-slate-100 text-slate-800 rounded-lg font-medium hover:bg-slate-200">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'ملاحظات عامة' : 'General Notes' }}
                        </label>
                        <textarea name="notes" rows="3"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'أي ملاحظات إضافية على الفاتورة...' : 'Any additional notes about the invoice...' }}"
                            class="{{ $inputClass }}">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Financial Collection Section (Digital q-1) --}}
                    <div
                        class="border-2 border-emerald-300 rounded-lg p-5 bg-emerald-50/50 shadow-sm relative overflow-hidden group">
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-emerald-100/50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110">
                        </div>
                        <h3 class="text-lg font-black text-emerald-900 mb-4 flex items-center gap-2 relative z-10">
                            <span>💰</span>
                            {{ app()->getLocale() === 'ar' ? 'تحصيل الرسوم ومستندات (ق-1) الرقمية' : 'Financial Collection & Digital (q-1) Docs' }}
                        </h3>

                        <div class="relative z-10 mb-4">
                            <label
                                class="block text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-2">{{ app()->getLocale() === 'ar' ? 'طرق الدفع (يمكن إضافة أكثر من طريقة — مجموع الأسطر = إجمالي التحصيل)' : 'Payment methods (add multiple lines — sum equals collection total)' }}</label>
                            <div class="overflow-x-auto rounded-xl border-2 border-emerald-200 bg-white">
                                <table class="w-full text-sm" id="create-split-payment-table">
                                    <thead>
                                        <tr class="bg-emerald-50 text-emerald-900">
                                            <th class="p-2 text-start font-bold">{{ app()->getLocale() === 'ar' ? 'الطريقة' : 'Method' }}</th>
                                            <th class="p-2 text-start font-bold w-32">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                            <th class="p-2 text-start font-bold">{{ app()->getLocale() === 'ar' ? 'مرجع / شيك' : 'Ref.' }}</th>
                                            <th class="p-2 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="create-split-rows-body">
                                        <tr data-split-row>
                                            <td class="p-2 align-top">
                                                <select name="split_lines[0][payment_method]"
                                                    class="create-split-method w-full rounded-lg border border-emerald-200 px-2 py-1.5 font-medium">
                                                    <option value="">{{ app()->getLocale() === 'ar' ? '— بدون —' : '— None —' }}</option>
                                                    <option value="cash" {{ old('split_lines.0.payment_method', old('collection_method')) === 'cash' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'كاش (نقدي)' : 'Cash' }}</option>
                                                    <option value="card" {{ old('split_lines.0.payment_method', old('collection_method')) === 'card' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'شبكة / POS' : 'POS / Card' }}</option>
                                                    <option value="bank_transfer" {{ old('split_lines.0.payment_method', old('collection_method')) === 'bank_transfer' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'تحويل بنكي' : 'Bank transfer' }}</option>
                                                    <option value="cheque" {{ old('split_lines.0.payment_method', old('collection_method')) === 'cheque' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'شيك' : 'Cheque' }}</option>
                                                    <option value="loyalty_points" {{ old('split_lines.0.payment_method') === 'loyalty_points' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نقاط بيع' : 'Loyalty points' }}</option>
                                                    <option value="insurance" class="create-split-party-opt {{ in_array(old('patient_payment_type', $patient?->payment_type ?? ''), ['insurance']) ? '' : 'hidden' }}">{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</option>
                                                    <option value="charity" class="create-split-party-opt {{ in_array(old('patient_payment_type', $patient?->payment_type ?? ''), ['charity']) ? '' : 'hidden' }}">{{ app()->getLocale() === 'ar' ? 'جمعية' : 'Charity' }}</option>
                                                </select>
                                            </td>
                                            <td class="p-2 align-top">
                                                <input type="number" name="split_lines[0][amount]" step="0.01" min="0"
                                                    value="{{ old('split_lines.0.amount', old('collection_amount')) }}"
                                                    class="create-split-amount w-full rounded-lg border border-emerald-200 px-2 py-1.5 font-bold text-emerald-800">
                                            </td>
                                            <td class="p-2 align-top">
                                                <input type="text" name="split_lines[0][reference_number]"
                                                    value="{{ old('split_lines.0.reference_number', old('collection_reference')) }}"
                                                    placeholder="{{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }}"
                                                    class="w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs">
                                            </td>
                                            <td class="p-2 align-top text-center">
                                                <button type="button"
                                                    class="create-btn-remove-split text-red-600 font-bold text-lg leading-none hidden"
                                                    title="{{ app()->getLocale() === 'ar' ? 'حذف السطر' : 'Remove' }}">×</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="create-btn-add-split-row"
                                class="mt-2 text-sm font-bold text-emerald-800 hover:text-emerald-950">
                                + {{ app()->getLocale() === 'ar' ? 'إضافة طريقة دفع' : 'Add payment method' }}
                            </button>
                            <p id="create-split-sum-warning" class="mt-2 text-xs text-red-600 hidden"></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative z-10">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي المبلغ المحصل' : 'Total Collected Amount' }}</label>
                                <input type="number" name="collection_amount" id="collection_amount" step="0.01"
                                    value="{{ old('collection_amount') }}" readonly
                                    class="w-full rounded-xl border-2 border-emerald-200 bg-emerald-50/80 px-3 py-2 text-lg font-black text-emerald-800">
                                <p class="text-[11px] text-emerald-800/90 mt-1 leading-snug">{{ app()->getLocale() === 'ar' ? 'يُحدَّث تلقائياً من مجموع أسطر طرق الدفع. يمكن أن يكون أقل من إجمالي الفاتورة (تحصيل جزئي)؛ اترك الأسطر فارغة إن لم يُحصّل شيء.' : 'Updates automatically from payment lines. Can be less than invoice total (partial); leave lines empty if no collection at creation.' }}</p>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black text-rose-700 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الترقيم من الوزارة (يُطبع على إيصال التحصيل)' : 'Ministry receipt number (printed on q-1)' }}</label>
                                <input type="text" name="ministry_receipt_number"
                                    value="{{ old('ministry_receipt_number') }}"
                                    class="w-full rounded-xl border-2 border-rose-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-rose-500"
                                    placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: 5267859' : 'e.g. 5267859' }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 relative z-10">
                            <div class="p-3 bg-white rounded-xl border border-emerald-100">
                                <label
                                    class="block text-[10px] font-black text-indigo-700 mb-1 uppercase tracking-tight">{{ app()->getLocale() === 'ar' ? '📁 إيصال التحصيل (ق-1)' : '📁 Physical Receipt (q-1)' }}</label>
                                <input type="file" name="physical_receipt" accept="image/*,.pdf"
                                    class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                            </div>
                            <div class="p-3 bg-white rounded-xl border border-emerald-100">
                                <label
                                    class="block text-[10px] font-black text-slate-600 mb-1 uppercase tracking-tight">{{ app()->getLocale() === 'ar' ? '📸 شاشة المحصل' : '📸 Collector Screenshot' }}</label>
                                <input type="file" name="collector_screenshot" accept="image/*"
                                    class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-slate-600 file:text-white hover:file:bg-slate-700">
                            </div>
                        </div>
                    </div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        const searchUrl = '{{ route('invoices.services-search') }}';
        const patientSearchUrl = '{{ route('invoices.patients-search') }}';
        const invoiceCreateUrl = '{{ route('invoices.create') }}';
        const isArabic = '{{ app()->getLocale() }}' === 'ar';
        let serviceRowIndex = 0;
        window.invoicePatientHasPartyCoverage = @json($patientHasPartyCoverage);
        window.invoicePatientIsCharity = @json(isset($patient) && ($patient->payment_type ?? '') === 'charity');

        function updatePartyCoverageLabel() {
            const label = document.getElementById('party-covered-label');
            if (!label) return;
            const isCharity = !!window.invoicePatientIsCharity;
            label.textContent = isArabic
                ? ('إجمالي المبلغ المغطى (' + (isCharity ? 'الجمعية' : 'التأمين') + '):')
                : ((isCharity ? 'Charity' : 'Insurance') + ' covered total:');
        }

        function addServiceRow(service) {
            const tbody = document.getElementById('services-container');
            const index = serviceRowIndex++;
            const name = (isArabic && service.name_ar) ? service.name_ar : service.name;
            const code = service.code || '—';
            const qty = service.is_multi_session && service.session_count ? service.session_count : 1;
            const price = service.default_price || 0;
            const total = (qty * price).toFixed(2);
            const covTypeLabelPct = isArabic ? 'نسبة %' : 'Percentage %';
            const covTypeLabelFixed = isArabic ? 'قيمة ثابتة' : 'Fixed amount';
            const defaultCovType = '';
            const defaultCovVal = '';
            const selPctDefault = '';
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
                <td class="border border-slate-400 px-2 py-2 align-top insurance-coverage-cell">
                    <select form="invoice-form" name="services[${index}][insurance_coverage_type]" class="w-full rounded border border-slate-400 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 insurance-coverage-type" onchange="updateInsuranceTotals()">
                        <option value="">—</option>
                        <option value="percentage" ${selPctDefault}>${covTypeLabelPct}</option>
                        <option value="fixed">${covTypeLabelFixed}</option>
                    </select>
                </td>
                <td class="border border-slate-400 px-2 py-2 align-top insurance-coverage-cell">
                    <input type="number" form="invoice-form" name="services[${index}][insurance_coverage_value]" step="0.01" min="0" placeholder="0" value="${defaultCovVal}" class="w-full rounded border border-slate-400 px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500" onchange="updateInsuranceTotals()" oninput="updateInsuranceTotals()">
                </td>
                <td class="border border-slate-400 px-1 py-2 align-middle text-center">
                    <button type="button" onclick="removeServiceRow(this)" class="text-red-600 hover:text-red-800 p-1" title="${isArabic ? 'حذف' : 'Remove'}">
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </td>
            `;
            tbody.appendChild(tr);
            toggleInsuranceColumns();
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

            updateInsuranceTotals();
            if (typeof window.validateCreateSplitSum === 'function') {
                window.validateCreateSplitSum();
            }
        }

        function getCharityInvoiceMode() {
            const checked = document.querySelector('input[name="charity_treatment_invoice_mode"]:checked');
            return checked ? checked.value : 'ahli_eligibility';
        }

        function getCharityFallbackPayment() {
            const checked = document.querySelector('input[name="charity_fallback_payment"]:checked');
            return checked ? checked.value : 'cash';
        }

        function isCharityPatientFreeMode() {
            return window.invoicePatientIsCharity && getCharityInvoiceMode() === 'ahli_eligibility';
        }

        function shouldShowCharityFallbackWrap() {
            const paymentTypeEl = document.getElementById('patient_payment_type');
            return paymentTypeEl && paymentTypeEl.value === 'charity' && getCharityInvoiceMode() === 'no_charity_now';
        }

        function shouldShowInsuranceWrapForCharity() {
            return shouldShowCharityFallbackWrap() && getCharityFallbackPayment() === 'insurance';
        }

        function refreshPartyCoverageFlags() {
            const paymentTypeEl = document.getElementById('patient_payment_type');
            const v = paymentTypeEl ? paymentTypeEl.value : '';
            window.invoicePatientIsCharity = (v === 'charity');
            window.invoicePatientHasPartyCoverage = (v === 'insurance') || shouldShowInsuranceWrapForCharity();
        }

        function syncCharityVisitUi() {
            const fallbackWrap = document.getElementById('charity-fallback-wrap');
            const insuranceWrap = document.getElementById('patient_insurance_wrap');
            const charityWrap = document.getElementById('patient_charity_wrap');
            const paymentTypeEl = document.getElementById('patient_payment_type');
            const isCharity = paymentTypeEl && paymentTypeEl.value === 'charity';
            const showFallback = shouldShowCharityFallbackWrap();

            if (fallbackWrap) fallbackWrap.classList.toggle('hidden', !showFallback);
            if (charityWrap) charityWrap.classList.toggle('hidden', !isCharity || showFallback);
            if (insuranceWrap) {
                const showInsurance = (paymentTypeEl && paymentTypeEl.value === 'insurance') || shouldShowInsuranceWrapForCharity();
                insuranceWrap.classList.toggle('hidden', !showInsurance);
            }
            applyCharityLineCoverageMode();
            if (typeof toggleInsuranceColumns === 'function') toggleInsuranceColumns();
        }

        function applyCharityLineCoverageMode() {
            const container = document.getElementById('services-container');
            if (!container) return;
            const freeMode = window.invoicePatientIsCharity && isCharityPatientFreeMode();
            container.querySelectorAll('tr.service-row').forEach(function(tr) {
                const typeSel = tr.querySelector('select[name*="[insurance_coverage_type]"]');
                const valInp = tr.querySelector('input[name*="[insurance_coverage_value]"]');
                if (!typeSel || !valInp) return;
                if (freeMode) {
                    typeSel.value = 'percentage';
                    valInp.value = '100';
                } else if (window.invoicePatientIsCharity && getCharityInvoiceMode() === 'no_charity_now' && getCharityFallbackPayment() === 'cash') {
                    typeSel.value = '';
                    valInp.value = '';
                }
            });
            updateInsuranceTotals();
        }

        function toggleInsuranceColumns() {
            refreshPartyCoverageFlags();
            const hasPartyCoverage = !!window.invoicePatientHasPartyCoverage;
            document.querySelectorAll('.insurance-coverage-th').forEach(function(el) {
                el.classList.toggle('hidden', !hasPartyCoverage);
            });
            document.querySelectorAll('.insurance-coverage-cell').forEach(function(el) {
                el.classList.toggle('hidden', !hasPartyCoverage);
            });
            const wrap = document.getElementById('insurance-totals-wrap');
            if (wrap) wrap.classList.toggle('hidden', !hasPartyCoverage);
            updatePartyCoverageLabel();
            updateInsuranceTotals();
        }

        function updateInsuranceTotals() {
            if (!window.invoicePatientHasPartyCoverage) return;
            const container = document.getElementById('services-container');
            const rows = container.querySelectorAll('tr.service-row');
            let covered = 0;
            rows.forEach(function(tr) {
                const totalInp = tr.querySelector('input[name*="[total_price]"]');
                const typeSel = tr.querySelector('select[name*="[insurance_coverage_type]"]');
                const valInp = tr.querySelector('input[name*="[insurance_coverage_value]"]');
                const total = parseFloat(totalInp && totalInp.value ? totalInp.value : 0) || 0;
                const type = typeSel ? typeSel.value : '';
                const val = parseFloat(valInp && valInp.value ? valInp.value : 0) || 0;
                if (!type || total <= 0) return;
                if (type === 'percentage') covered += total * Math.min(100, Math.max(0, val)) / 100;
                else if (type === 'fixed') covered += Math.min(val, total);
            });
            const totalInputs = container.querySelectorAll('input[name*="[total_price]"]');
            let grandTotal = 0;
            totalInputs.forEach(function(input) {
                grandTotal += parseFloat(input.value) || 0;
            });
            const patientShare = Math.max(0, grandTotal - covered);
            const elCovered = document.getElementById('insurance-covered-total');
            const elPatient = document.getElementById('patient-share-total');
            if (elCovered) elCovered.textContent = covered.toFixed(2);
            if (elPatient) elPatient.textContent = patientShare.toFixed(2);
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
            const charityTreatmentWrap = document.getElementById('charity-treatment-invoice-wrap');
            if (paymentTypeEl && insuranceWrap && charityWrap) {
                function togglePaymentFields() {
                    const v = paymentTypeEl.value;
                    refreshPartyCoverageFlags();
                    if (charityTreatmentWrap) {
                        charityTreatmentWrap.classList.toggle('hidden', v !== 'charity');
                        if (v !== 'charity') {
                            const def = charityTreatmentWrap.querySelector('input.charity-treatment-mode[value="ahli_eligibility"]');
                            if (def) def.checked = true;
                        }
                    }
                    syncCharityVisitUi();
                }
                paymentTypeEl.addEventListener('change', togglePaymentFields);
                togglePaymentFields();
                document.querySelectorAll('input.charity-treatment-mode').forEach(function(radio) {
                    radio.addEventListener('change', syncCharityVisitUi);
                });
                document.querySelectorAll('input.charity-fallback-mode').forEach(function(radio) {
                    radio.addEventListener('change', syncCharityVisitUi);
                });
            } else {
                if (typeof toggleInsuranceColumns === 'function') toggleInsuranceColumns();
            }

            const collectionInp = document.getElementById('collection_amount');

            function getCreateMaxCollection() {
                const patientShareEl = document.getElementById('patient-share-total');
                const grandEl = document.getElementById('grand-total');
                if (patientShareEl && patientShareEl.textContent) {
                    const ps = parseFloat(patientShareEl.textContent.replace(/,/g, '')) || 0;
                    if (ps > 0) return ps;
                }
                if (grandEl && grandEl.textContent) {
                    return parseFloat(grandEl.textContent.replace(/,/g, '')) || 0;
                }
                return 0;
            }

            function reindexCreateSplitRows() {
                const body = document.getElementById('create-split-rows-body');
                if (!body) return;
                const rows = body.querySelectorAll('[data-split-row]');
                rows.forEach(function(row, index) {
                    row.querySelectorAll('select, input').forEach(function(el) {
                        const n = el.getAttribute('name');
                        if (n && n.indexOf('split_lines[') === 0) {
                            el.setAttribute('name', n.replace(/split_lines\[\d+]/, 'split_lines[' + index + ']'));
                        }
                    });
                    const rm = row.querySelector('.create-btn-remove-split');
                    if (rm) rm.classList.toggle('hidden', rows.length <= 1);
                });
            }

            function sumCreateSplitAmounts() {
                let s = 0;
                document.querySelectorAll('#create-split-rows-body .create-split-amount').forEach(function(inp) {
                    s += parseFloat(inp.value || 0) || 0;
                });
                return Math.round(s * 100) / 100;
            }

            function syncCreateCollectionTotal() {
                if (!collectionInp) return;
                const sum = sumCreateSplitAmounts();
                collectionInp.value = sum > 0 ? sum.toFixed(2) : '';
            }

            function validateCreateSplitSum() {
                const warn = document.getElementById('create-split-sum-warning');
                if (!warn) return true;
                const maxC = getCreateMaxCollection();
                const sum = sumCreateSplitAmounts();
                const hasMethod = Array.from(document.querySelectorAll('#create-split-rows-body .create-split-method')).some(function(sel) {
                    return sel.value && parseFloat(sel.closest('tr')?.querySelector('.create-split-amount')?.value || 0) > 0;
                });

                if (sum > maxC + 0.02 && maxC > 0) {
                    warn.textContent = isArabic
                        ? ('مجموع أسطر الدفع (' + sum.toFixed(2) + ') يتجاوز إجمالي الفاتورة/حصة المريض (' + maxC.toFixed(2) + ').')
                        : ('Sum of payment lines (' + sum.toFixed(2) + ') exceeds invoice/patient share (' + maxC.toFixed(2) + ').');
                    warn.classList.remove('hidden');
                    return false;
                }
                if (hasMethod && sum <= 0) {
                    warn.textContent = isArabic ? 'أدخل مبلغاً في أسطر طرق الدفع.' : 'Enter an amount in payment lines.';
                    warn.classList.remove('hidden');
                    return false;
                }
                warn.classList.add('hidden');
                return true;
            }

            function onCreateSplitAmountInput() {
                syncCreateCollectionTotal();
                validateCreateSplitSum();
            }

            function toggleCreatePartySplitOptions() {
                const v = paymentTypeEl ? paymentTypeEl.value : '';
                document.querySelectorAll('.create-split-party-opt').forEach(function(opt) {
                    if (opt.value === 'insurance') opt.classList.toggle('hidden', v !== 'insurance');
                    if (opt.value === 'charity') opt.classList.toggle('hidden', v !== 'charity');
                });
            }

            function addCreateSplitRow() {
                const body = document.getElementById('create-split-rows-body');
                const first = body && body.querySelector('[data-split-row]');
                if (!body || !first) return;
                const clone = first.cloneNode(true);
                clone.querySelectorAll('input').forEach(function(i) { i.value = ''; });
                clone.querySelectorAll('select.create-split-method').forEach(function(s) { s.selectedIndex = 0; });
                body.appendChild(clone);
                reindexCreateSplitRows();
                toggleCreatePartySplitOptions();
                clone.querySelectorAll('.create-split-amount').forEach(function(el) {
                    el.addEventListener('input', onCreateSplitAmountInput);
                    el.addEventListener('change', onCreateSplitAmountInput);
                });
                clone.querySelectorAll('.create-split-method').forEach(function(el) {
                    el.addEventListener('change', validateCreateSplitSum);
                });
                validateCreateSplitSum();
            }

            const createAddSplitBtn = document.getElementById('create-btn-add-split-row');
            if (createAddSplitBtn) createAddSplitBtn.addEventListener('click', addCreateSplitRow);
            const createSplitBody = document.getElementById('create-split-rows-body');
            if (createSplitBody) {
                createSplitBody.addEventListener('click', function(e) {
                    if (e.target.classList.contains('create-btn-remove-split')) {
                        const rows = createSplitBody.querySelectorAll('[data-split-row]');
                        if (rows.length <= 1) return;
                        e.target.closest('[data-split-row]').remove();
                        reindexCreateSplitRows();
                        onCreateSplitAmountInput();
                    }
                });
                createSplitBody.querySelectorAll('.create-split-amount').forEach(function(el) {
                    el.addEventListener('input', onCreateSplitAmountInput);
                    el.addEventListener('change', onCreateSplitAmountInput);
                });
                createSplitBody.querySelectorAll('.create-split-method').forEach(function(el) {
                    el.addEventListener('change', validateCreateSplitSum);
                });
            }
            if (paymentTypeEl) {
                paymentTypeEl.addEventListener('change', toggleCreatePartySplitOptions);
                toggleCreatePartySplitOptions();
            }
            syncCreateCollectionTotal();
            validateCreateSplitSum();

            window.validateCreateSplitSum = validateCreateSplitSum;

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
                                patientResultsDiv.innerHTML =
                                    '<p class="p-4 text-slate-700 font-medium text-base">' + (isArabic ?
                                        'لا توجد نتائج' : 'No results') + '</p>';
                            } else {
                                list.forEach(function(p) {
                                    const name = (isArabic && p.name_ar) ? p.name_ar : p.name;
                                    const extra = [p.file_number, p.identity_value, p.phone].filter(
                                        Boolean).join(' · ');
                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className =
                                        'w-full text-left px-4 py-3 hover:bg-blue-100 border-b border-slate-200 last:border-0 text-base font-medium text-slate-800';
                                    btn.innerHTML = '<span class="font-semibold">' + (name || p.name) +
                                        '</span>' + (extra ?
                                            '<br><span class="text-slate-600 text-sm">' + extra +
                                            '</span>' : '');
                                    btn.addEventListener('click', function() {
                                        window.location.href = invoiceCreateUrl +
                                            '?patient_id=' + p.id;
                                    });
                                    patientResultsDiv.appendChild(btn);
                                });
                            }
                            patientResultsDiv.classList.remove('hidden');
                        })
                        .catch(function() {
                            patientResultsDiv.innerHTML = '<p class="p-4 text-red-700 font-medium text-base">' +
                                (isArabic ? 'خطأ في البحث' : 'Search error') + '</p>';
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

        // --- التقرير الطبي: رفع ملف | مسح بالكاميرا ---
        (function() {
            var btnUpload = document.getElementById('btn_inv_mode_upload');
            var btnScan = document.getElementById('btn_inv_mode_scan');
            var boxUpload = document.getElementById('inv_medical_upload_box');
            var boxScan = document.getElementById('inv_medical_scan_box');
            if (!btnUpload || !btnScan || !boxUpload || !boxScan) return;

            function setMedicalMode(mode) {
                var isUpload = mode === 'upload';
                boxUpload.classList.toggle('hidden', !isUpload);
                boxScan.classList.toggle('hidden', isUpload);
                btnUpload.className = 'px-4 py-2 rounded-lg text-sm font-semibold border-2 ' + (isUpload ?
                    'border-blue-600 bg-blue-600 text-white hover:bg-blue-700' :
                    'border-slate-400 bg-slate-100 text-slate-800 hover:bg-slate-200');
                btnScan.className = 'px-4 py-2 rounded-lg text-sm font-semibold border-2 ' + (!isUpload ?
                    'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700' :
                    'border-slate-400 bg-slate-100 text-slate-800 hover:bg-slate-200');
            }
            btnUpload.addEventListener('click', function() {
                setMedicalMode('upload');
            });
            btnScan.addEventListener('click', function() {
                setMedicalMode('scan');
            });
            setMedicalMode('upload');

            var invCameraModal = document.getElementById('inv_camera_modal');
            var invCameraVideo = document.getElementById('inv_camera_video');
            var invCameraLabel = document.getElementById('inv_camera_step_label');
            var invScanPages = document.getElementById('inv_scan_pdf_pages');
            var invScannedStatus = document.getElementById('inv_scanned_pdf_status');
            var invScannedFile = document.getElementById('medical_report_scanned_file');
            var invStream = null;
            var invScanPagesArray = [];
            var invCanvas = document.createElement('canvas');
            var invCtx = invCanvas.getContext('2d');

            function invStopCamera() {
                if (invStream) {
                    invStream.getTracks().forEach(function(t) {
                        t.stop();
                    });
                    invStream = null;
                }
                if (invCameraModal) invCameraModal.classList.add('hidden');
            }

            function invOpenCamera() {
                if (invCameraLabel) invCameraLabel.textContent = isArabic ? 'وجّه الكاميرا إلى المستند ثم اضغط التقاط' :
                    'Point camera at document, then press Capture';
                if (invCameraModal) invCameraModal.classList.remove('hidden');
                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'environment'
                        }
                    })
                    .then(function(stream) {
                        invStream = stream;
                        if (invCameraVideo) invCameraVideo.srcObject = stream;
                    })
                    .catch(function() {
                        navigator.mediaDevices.getUserMedia({
                            video: true
                        }).then(function(stream) {
                            invStream = stream;
                            if (invCameraVideo) invCameraVideo.srcObject = stream;
                        }).catch(function() {
                            if (invScannedStatus) {
                                invScannedStatus.textContent = isArabic ? 'تعذر الوصول للكاميرا.' :
                                    'Camera access denied.';
                                invScannedStatus.classList.remove('hidden');
                            }
                            invStopCamera();
                        });
                    });
            }

            function invCapturePhoto() {
                if (!invStream || !invCameraVideo) return;
                invCanvas.width = invCameraVideo.videoWidth;
                invCanvas.height = invCameraVideo.videoHeight;
                invCtx.drawImage(invCameraVideo, 0, 0);
                invCanvas.toBlob(function(blob) {
                    invScanPagesArray.push({
                        blob: blob,
                        url: URL.createObjectURL(blob)
                    });
                    invRenderThumbnails();
                    var btnGen = document.getElementById('btn_inv_generate_pdf');
                    if (btnGen) btnGen.disabled = false;
                    invStopCamera();
                }, 'image/jpeg', 0.92);
            }

            function invRenderThumbnails() {
                if (!invScanPages) return;
                invScanPages.innerHTML = '';
                invScanPagesArray.forEach(function(item, index) {
                    var wrap = document.createElement('div');
                    wrap.className = 'relative inline-block';
                    var img = document.createElement('img');
                    img.src = item.url;
                    img.alt = 'Page ' + (index + 1);
                    img.className = 'h-20 w-auto rounded border border-slate-300 object-cover';
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className =
                        'absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs hover:bg-red-600';
                    btn.textContent = '×';
                    btn.addEventListener('click', function() {
                        invScanPagesArray.splice(index, 1);
                        invRenderThumbnails();
                        if (invScanPagesArray.length === 0) {
                            var b = document.getElementById('btn_inv_generate_pdf');
                            if (b) b.disabled = true;
                        }
                    });
                    wrap.appendChild(img);
                    wrap.appendChild(btn);
                    invScanPages.appendChild(wrap);
                });
            }

            function blobToBase64(blob) {
                return new Promise(function(resolve, reject) {
                    var r = new FileReader();
                    r.onload = function() {
                        resolve(r.result);
                    };
                    r.onerror = reject;
                    r.readAsDataURL(blob);
                });
            }

            document.getElementById('btn_inv_add_scan_page').addEventListener('click', invOpenCamera);
            document.getElementById('btn_inv_take_photo').addEventListener('click', invCapturePhoto);
            document.getElementById('btn_inv_close_camera').addEventListener('click', invStopCamera);

            document.getElementById('btn_inv_generate_pdf').addEventListener('click', function() {
                if (invScanPagesArray.length === 0) return;
                var JsPDF = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : (typeof jspdf !==
                    'undefined' && jspdf.jsPDF) ? jspdf.jsPDF : null;
                if (!JsPDF) {
                    if (invScannedStatus) {
                        invScannedStatus.textContent = isArabic ? 'مكتبة PDF غير محمّلة.' :
                            'PDF library not loaded.';
                        invScannedStatus.classList.remove('hidden');
                    }
                    return;
                }
                var doc = new JsPDF();
                var w = doc.internal.pageSize.getWidth();
                var h = doc.internal.pageSize.getHeight();
                Promise.all(invScanPagesArray.map(function(p) {
                    return blobToBase64(p.blob);
                })).then(function(base64arr) {
                    base64arr.forEach(function(base64, i) {
                        if (i > 0) doc.addPage();
                        doc.addImage(base64, 'JPEG', 0, 0, w, h);
                    });
                    var pdfBlob = doc.output('blob');
                    var file = new File([pdfBlob], 'scanned-medical-report.pdf', {
                        type: 'application/pdf'
                    });
                    var dt = new DataTransfer();
                    dt.items.add(file);
                    invScannedFile.files = dt.files;
                    if (invScannedStatus) {
                        invScannedStatus.textContent = (isArabic ? 'تم مرفق PDF (' : 'PDF attached (') +
                            invScanPagesArray.length + (isArabic ? ' صفحة).' : ' pages).');
                        invScannedStatus.classList.remove('hidden');
                        invScannedStatus.classList.add('text-emerald-600');
                    }
                });
            });
        })();

        // Form validation
        document.getElementById('invoice-form').addEventListener('submit', function(e) {
            const patientIdInput = document.getElementById('invoice_patient_id');
            if (patientIdInput && !patientIdInput.value) {
                e.preventDefault();
                alert(
                    '{{ app()->getLocale() === 'ar' ? 'يرجى البحث عن المريض واختياره أولاً' : 'Please search and select a patient first' }}'
                    );
                return false;
            }
            const container = document.getElementById('services-container');
            const rows = container.querySelectorAll('.service-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert(
                    '{{ app()->getLocale() === 'ar' ? 'يجب إضافة خدمة واحدة على الأقل' : 'Please add at least one service' }}'
                    );
                return false;
            }
            if (typeof window.validateCreateSplitSum === 'function' && !window.validateCreateSplitSum()) {
                e.preventDefault();
                return false;
            }
        });
    </script>
@endsection
