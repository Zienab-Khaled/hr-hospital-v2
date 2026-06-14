@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تسجيل مريض جديد' : 'Register New Patient')
@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">
                {{ app()->getLocale() === 'ar' ? '👤 تسجيل مريض جديد' : '👤 Register New Patient' }}
            </h2>

            <form action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                id="patient_create_form">
                @csrf
                @if (request('redirect_to') === 'visits.create')
                    <input type="hidden" name="redirect_to" value="visits.create">
                @endif

                {{-- Scan / Upload identity document: camera (face + back) OR upload file --}}
                <div style="background:#e6eaf0" class="mb-6 p-4 rounded-xl border-2">
                    <h3 class="text-lg font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? '📷 مسح أو رفع وثيقة الهوية' : '📷 Scan or upload identity document' }}
                    </h3>

                    {{-- Toggle: Scan with camera | Upload file --}}
                    <div class="flex gap-2 mb-4">
                        <button type="button" id="btn_mode_scan"
                            class="patient-form-btn patient-form-btn-primary px-4 py-2.5 rounded-lg text-sm font-semibold border-2 border-violet-600 bg-violet-600 text-white hover:bg-violet-700 hover:border-violet-700 shadow-sm transition-colors">
                            {{ app()->getLocale() === 'ar' ? '📷 مسح بالكاميرا (وجه + ظهر)' : '📷 Scan with camera (front + back)' }}
                        </button>
                        <button type="button" id="btn_mode_upload"
                            class="patient-form-btn patient-form-btn-secondary px-4 py-2.5 rounded-lg text-sm font-semibold border-2 border-slate-400 bg-slate-100 text-slate-800 hover:bg-slate-200 hover:border-slate-500 shadow-sm transition-colors">
                            {{ app()->getLocale() === 'ar' ? '📁 رفع ملف' : '📁 Upload file' }}
                        </button>
                    </div>

                    {{-- Mode: Scan with camera --}}
                    <div id="mode_scan_box" class="space-y-4">
                        <p class="text-sm text-slate-600">
                            {{ app()->getLocale() === 'ar' ? 'الخطوة 1: وجه الوثيقة (الأمام)' : 'Step 1: Front of document' }}
                        </p>
                        <div class="flex flex-wrap gap-3 items-start">
                            <button type="button" id="btn_capture_front"
                                class="border-2 border-violet-600 bg-violet-600  px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-violet-700 hover:border-violet-700 shadow-sm transition-colors">
                                {{ app()->getLocale() === 'ar' ? 'فتح الكاميرا والتقاط الوجه' : 'Open camera & capture front' }}
                            </button>
                            <div id="preview_front" class="hidden">
                                <img id="img_front" src="" alt="Front"
                                    class="max-h-32 rounded border border-slate-300">
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ app()->getLocale() === 'ar' ? 'تم التقاط الوجه' : 'Front captured' }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 pt-2">
                            {{ app()->getLocale() === 'ar' ? 'الخطوة 2: ظهر الوثيقة' : 'Step 2: Back of document' }}</p>
                        <div class="flex flex-wrap gap-3 items-start">
                            <button type="button" id="btn_capture_back"
                                class="border-2 border-violet-600 bg-violet-600  px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-violet-700 hover:border-violet-700 shadow-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                                disabled>
                                {{ app()->getLocale() === 'ar' ? 'فتح الكاميرا والتقاط الظهر' : 'Open camera & capture back' }}
                            </button>
                            <div id="preview_back" class="hidden">
                                <img id="img_back" src="" alt="Back"
                                    class="max-h-32 rounded border border-slate-300">
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ app()->getLocale() === 'ar' ? 'تم التقاط الظهر' : 'Back captured' }}</p>
                            </div>
                        </div>
                        {{-- Camera modal: video + capture --}}
                        <div id="camera_modal"
                            class="hidden fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">
                            <div class="bg-white rounded-xl max-w-lg w-full p-4">
                                <p id="camera_step_label" class="font-semibold text-slate-800 mb-2"></p>
                                <video id="camera_video" autoplay playsinline class="w-full rounded border bg-slate-900"
                                    style="max-height: 50vh;"></video>
                                <div class="flex gap-2 mt-3">
                                    <button type="button" id="btn_take_photo"
                                        class="flex-1 border-2 border-violet-600 bg-violet-600  py-2.5 rounded-lg font-semibold hover:bg-violet-700 transition-colors">
                                        {{ app()->getLocale() === 'ar' ? 'التقاط' : 'Capture' }}</button>
                                    <button type="button" id="btn_close_camera"
                                        class="px-4 py-2.5 border-2 border-slate-400 bg-slate-100 text-slate-800 rounded-lg font-medium hover:bg-slate-200">
                                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mode: Upload file --}}
                    <div id="mode_upload_box" class="hidden">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'صورة الوثيقة (JPG, PNG, WebP)' : 'Document image (JPG, PNG, WebP)' }}
                                </label>
                                <input type="file" name="documents[]" id="identity_document_file" multiple
                                    accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                                    class="patient-form-file-input w-full text-sm text-slate-700 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-2 file:border-violet-500 file:bg-violet-600 file:text-white file:font-semibold file:cursor-pointer hover:file:bg-violet-700 hover:file:border-violet-600">
                            </div>
                            <button type="button" id="btn_extract_identity"
                                class="patient-form-btn-primary border-2 border-violet-600 bg-violet-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-violet-700 hover:border-violet-700 shadow-sm transition-colors">
                                {{ app()->getLocale() === 'ar' ? '🔍 استخراج البيانات' : '🔍 Extract data' }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <button type="button" id="btn_extract_after_scan"
                            class="hidden border-2 border-violet-600 bg-violet-600  px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-violet-700 hover:border-violet-700 shadow-sm transition-colors">
                            {{ app()->getLocale() === 'ar' ? '🔍 استخراج البيانات من صورة الوجه' : '🔍 Extract data from front image' }}
                        </button>
                    </div>
                    <p id="extract_status" class="mt-2 text-sm hidden"></p>
                    <p id="scan_status" class="mt-2 text-sm text-slate-600 hidden"></p>
                </div>

                {{-- Identity: Type + Value --}}
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? '📋 وثائق الهوية' : '📋 Identity Documents' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'نوع الهوية' : 'Identity Type' }} *
                            </label>
                            <select name="identity_type" id="identity_type" required
                                class="w-full rounded border border-slate-300 px-3 py-2 @error('identity_type') border-red-500 @enderror">
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}
                                </option>
                                @foreach (\App\Models\Patient::identityTypeOptions() as $key => $labels)
                                    <option value="{{ $key }}"
                                        {{ old('identity_type') === $key ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? $labels['ar'] : $labels['en'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('identity_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'رقم الهوية' : 'Identity Number' }} *
                            </label>
                            <input type="text" name="identity_value" id="identity_value"
                                value="{{ old('identity_value', request('identity')) }}"
                                class="w-full rounded border border-slate-300 px-3 py-2 @error('identity_value') border-red-500 @enderror"
                                placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل الرقم' : 'Enter number' }}">
                            @error('identity_value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>


                {{-- Personal Information --}}
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? '👤 المعلومات الشخصية' : '👤 Personal Information' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} *
                            </label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full rounded border border-slate-300 px-3 py-2 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'الاسم الأول (عربي)' : 'First name (Arabic)' }} *
                                </label>
                                <input type="text" name="name_ar_first" value="{{ old('name_ar_first') }}" dir="rtl" required
                                    class="w-full rounded border border-slate-300 px-3 py-2 @error('name_ar_first') border-red-500 @enderror">
                                @error('name_ar_first')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'اسم الأب (عربي)' : 'Father name (Arabic)' }}
                                    <span class="text-slate-400 font-normal text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'optional' }})</span>
                                </label>
                                <input type="text" name="name_ar_father" value="{{ old('name_ar_father') }}" dir="rtl"
                                    class="w-full rounded border border-slate-300 px-3 py-2 @error('name_ar_father') border-red-500 @enderror">
                                @error('name_ar_father')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ app()->getLocale() === 'ar' ? 'الاسم الأخير / العائلة (عربي)' : 'Last / family name (Arabic)' }} *
                                </label>
                                <input type="text" name="name_ar_family" value="{{ old('name_ar_family') }}" dir="rtl" required
                                    class="w-full rounded border border-slate-300 px-3 py-2 @error('name_ar_family') border-red-500 @enderror">
                                @error('name_ar_family')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File Number' }} *
                            </label>
                            <input type="text" name="file_number"
                                value="{{ old('file_number', 'F-' . date('Ymd') . '-' . rand(1000, 9999)) }}" required
                                class="w-full rounded border border-slate-300 px-3 py-2 @error('file_number') border-red-500 @enderror">
                            @error('file_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'تاريخ الميلاد' : 'Date of birth' }} *
                            </label>
                            @php
                                $monthNames = app()->getLocale() === 'ar'
                                    ? ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
                                    : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                $dobSelectClass = 'w-full rounded border border-slate-300 px-2 py-2 ' . ($errors->has('date_of_birth') ? 'border-red-500' : '');
                            @endphp
                            <div class="grid grid-cols-3 gap-2">
                                <select name="dob_day" required class="{{ $dobSelectClass }}">
                                    <option value="">{{ app()->getLocale() === 'ar' ? 'يوم' : 'Day' }}</option>
                                    @for ($d = 1; $d <= 31; $d++)
                                        <option value="{{ $d }}" {{ (string) old('dob_day') === (string) $d ? 'selected' : '' }}>{{ $d }}</option>
                                    @endfor
                                </select>
                                <select name="dob_month" required class="{{ $dobSelectClass }}">
                                    <option value="">{{ app()->getLocale() === 'ar' ? 'شهر' : 'Month' }}</option>
                                    @foreach ($monthNames as $i => $monthName)
                                        <option value="{{ $i + 1 }}" {{ (string) old('dob_month') === (string) ($i + 1) ? 'selected' : '' }}>{{ $monthName }}</option>
                                    @endforeach
                                </select>
                                <select name="dob_year" required class="{{ $dobSelectClass }}">
                                    <option value="">{{ app()->getLocale() === 'ar' ? 'سنة' : 'Year' }}</option>
                                    @for ($y = now()->year; $y >= 1900; $y--)
                                        <option value="{{ $y }}" {{ (string) old('dob_year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            @error('date_of_birth')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الجنس' : 'Gender' }} *
                            </label>
                            <select name="gender" required class="w-full rounded border border-slate-300 px-3 py-2">
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}
                                </option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }}
                                </option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }} *
                            </label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required
                                class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الجنسية' : 'Nationality' }} *
                            </label>
                            <input type="text" name="country_of_origin" value="{{ old('country_of_origin') }}" required
                                class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div id="sponsor_name_field" style="display: none;">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'اسم الكفيل' : 'Sponsor Name' }} <span
                                    class="text-red-600">*</span>
                            </label>
                            <input type="text" name="sponsor_name" id="sponsor_name"
                                value="{{ old('sponsor_name') }}"
                                class="w-full rounded border border-slate-300 px-3 py-2">
                            <p class="mt-1 text-xs text-blue-600">
                                {{ app()->getLocale() === 'ar' ? 'مطلوب لحاملي الإقامة' : 'Required for Iqama holders' }}
                            </p>
                        </div>
                        <div id="sponsor_phone_field" style="display: none;">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'هاتف الكفيل' : 'Sponsor Phone' }} <span
                                    class="text-red-600">*</span>
                            </label>
                            <input type="text" name="sponsor_phone" id="sponsor_phone"
                                value="{{ old('sponsor_phone') }}"
                                class="w-full rounded border border-slate-300 px-3 py-2">
                            <p class="mt-1 text-xs text-blue-600">
                                {{ app()->getLocale() === 'ar' ? 'مطلوب لحاملي الإقامة' : 'Required for Iqama holders' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Payment Information --}}
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? '💳 معلومات الدفع' : '💳 Payment Information' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }} *
                            </label>
                            <select name="payment_type" id="payment_type" required
                                class="w-full rounded border border-slate-300 px-3 py-2">
                                @foreach (\App\Models\Patient::paymentTypeOptions() as $value => $labels)
                                    <option value="{{ $value }}"
                                        {{ old('payment_type', 'cash') === $value ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? $labels['ar'] : $labels['en'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ app()->getLocale() === 'ar' ? '«' . \App\Models\Patient::paymentTypeLabel('treatment_eligibility') . '» للخادم والسائق — تُحفظ مع ملف المريض.' : '«' . \App\Models\Patient::paymentTypeLabel('treatment_eligibility', 'en') . '» is for servants and drivers — saved on the patient record.' }}
                            </p>
                        </div>
                        <div id="insurance_field">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}
                            </label>
                            <select name="insurance_company_id" id="insurance_company_id"
                                class="w-full rounded border border-slate-300 px-3 py-2">
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}
                                </option>
                                @foreach ($insuranceCompanies as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('insurance_company_id') == $c->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? ($c->name_ar ?? $c->name) : $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- Dynamic check: search our records by identity to suggest insurance (CHI official verification link below) --}}
                            <div id="insurance_check_box"
                                class="mt-2 p-3 rounded-lg border border-slate-200 bg-slate-50 text-sm">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" id="btn_check_insurance"
                                        class="border-2 border-emerald-600 bg-emerald-600 text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700 hover:border-emerald-700 shadow-sm transition-colors">
                                        {{ app()->getLocale() === 'ar' ? '🔍 التحقق من السجلات' : '🔍 Check our records' }}
                                    </button>
                                    <a href="https://www.chi.gov.sa/ServicesDirectory/Pages/Eservices-CheckInsurance.aspx"
                                        target="_blank" rel="noopener noreferrer"
                                        class="text-blue-600 hover:underline text-sm">
                                        {{ app()->getLocale() === 'ar' ? 'التحقق من موقع مجلس الضمان الصحي (CHI)' : 'Verify on CHI portal (chi.gov.sa)' }}
                                    </a>
                                </div>
                                <p id="insurance_check_result" class="mt-2 hidden"></p>
                            </div>
                        </div>
                        <div id="charity_field">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الجمعية الخيرية' : 'Charity Entity' }}
                            </label>
                            <select name="charity_entity_id" class="w-full rounded border border-slate-300 px-3 py-2">
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}
                                </option>
                                @foreach ($charityEntities as $e)
                                    <option value="{{ $e->id }}"
                                        {{ old('charity_entity_id') == $e->id ? 'selected' : '' }}>
                                        {{ $e->name_ar ?: $e->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Document Upload --}}
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? '📎 المستندات' : '📎 Documents' }}
                    </h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'رفع المستندات (صور/PDFs)' : 'Upload Documents (Images/PDFs)' }}
                        </label>
                        <input type="file" name="documents[]" id="documents_upload" multiple
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="patient-form-file-input w-full rounded-lg border-2 border-slate-300 px-3 py-2 text-slate-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-2 file:border-slate-500 file:bg-slate-600 file:text-white file:font-medium file:cursor-pointer hover:file:bg-slate-700">
                        <p class="text-xs text-slate-500 mt-1">
                            {{ app()->getLocale() === 'ar' ? 'يمكنك رفع عدة ملفات (PDF, JPG, PNG)' : 'You can upload multiple files (PDF, JPG, PNG)' }}
                        </p>
                    </div>
                    {{-- Scan with phone → attach as PDF --}}
                    <div class="p-4 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50">
                        <h4 class="text-sm font-semibold text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? '📱 مسح من الجوال ومرفق كـ PDF' : '📱 Scan with phone & attach as PDF' }}
                        </h4>
                        <div id="scan_pdf_pages" class="flex flex-wrap gap-2 mb-3 min-h-[60px]"></div>
                        <div class="flex flex-wrap gap-2 items-center">
                            <button type="button" id="btn_add_scan_page"
                                class="border-2 border-emerald-600 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition-colors">
                                {{ app()->getLocale() === 'ar' ? '➕ إضافة صفحة (مسح بالكاميرا)' : '➕ Add page (scan with camera)' }}
                            </button>
                            <button type="button" id="btn_generate_pdf"
                                class="border-2 border-slate-500 bg-slate-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-700 transition-colors disabled:opacity-50"
                                disabled>
                                {{ app()->getLocale() === 'ar' ? '📄 إنشاء PDF ومرفق' : '📄 Generate PDF & attach' }}
                            </button>
                        </div>
                        <p id="scanned_pdf_status" class="mt-2 text-sm text-slate-600 hidden"></p>
                        <input type="file" name="documents[]" id="scanned_pdf_file" accept=".pdf" class="hidden">
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}
                    </label>
                    <textarea name="notes" rows="3" class="w-full rounded border border-slate-300 px-3 py-2">{{ old('notes') }}</textarea>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="patient-form-btn-primary border-2 border-blue-600 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 hover:border-blue-700 shadow-md transition-colors">
                        {{ app()->getLocale() === 'ar' ? '💾 حفظ' : '💾 Save' }}
                    </button>
                    <a href="{{ route('patients.search') }}"
                        class="border-2 border-slate-400 bg-slate-100 text-slate-800 px-6 py-3 rounded-lg font-semibold hover:bg-slate-200 hover:border-slate-500 shadow-sm transition-colors inline-block">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Payment type change handler
        document.getElementById('payment_type').addEventListener('change', function() {
            var v = this.value;
            document.getElementById('insurance_field').style.display = v === 'insurance' ? 'block' : 'none';
            document.getElementById('charity_field').style.display = v === 'charity' ? 'block' : 'none';
        });
        document.getElementById('payment_type').dispatchEvent(new Event('change'));

        // --- Mode: Scan with camera vs Upload file ---
        var modeScanBox = document.getElementById('mode_scan_box');
        var modeUploadBox = document.getElementById('mode_upload_box');
        var btnModeScan = document.getElementById('btn_mode_scan');
        var btnModeUpload = document.getElementById('btn_mode_upload');

        function setMode(mode) {
            var isScan = mode === 'scan';
            modeScanBox.classList.toggle('hidden', !isScan);
            modeUploadBox.classList.toggle('hidden', isScan);
            btnModeScan.className = isScan ?
                'patient-form-btn patient-form-btn-primary px-4 py-2.5 rounded-lg text-sm font-semibold border-2 border-violet-600 bg-violet-600 text-white hover:bg-violet-700 hover:border-violet-700 shadow-sm transition-colors' :
                'patient-form-btn patient-form-btn-secondary px-4 py-2.5 rounded-lg text-sm font-semibold border-2 border-slate-400 bg-slate-100 text-slate-800 hover:bg-slate-200 hover:border-slate-500 shadow-sm transition-colors';
            btnModeUpload.className = !isScan ?
                'patient-form-btn patient-form-btn-primary px-4 py-2.5 rounded-lg text-sm font-semibold border-2 border-violet-600 bg-violet-600 text-white hover:bg-violet-700 hover:border-violet-700 shadow-sm transition-colors' :
                'patient-form-btn patient-form-btn-secondary px-4 py-2.5 rounded-lg text-sm font-semibold border-2 border-slate-400 bg-slate-100 text-slate-800 hover:bg-slate-200 hover:border-slate-500 shadow-sm transition-colors';
            if (!isScan) {
                var pf = document.getElementById('preview_front');
                var pb = document.getElementById('preview_back');
                var bcb = document.getElementById('btn_capture_back');
                if (pf) pf.classList.add('hidden');
                if (pb) pb.classList.add('hidden');
                if (bcb) bcb.disabled = true;
                capturedFrontBlob = capturedBackBlob = null;
                var beas = document.getElementById('btn_extract_after_scan');
                var ss = document.getElementById('scan_status');
                if (beas) beas.classList.add('hidden');
                if (ss) ss.classList.add('hidden');
            } else {
                var ido = document.getElementById('identity_document_file');
                if (ido) ido.value = '';
                var beas2 = document.getElementById('btn_extract_after_scan');
                if (beas2) beas2.classList.add('hidden');
            }
        }
        if (btnModeScan) btnModeScan.addEventListener('click', function() {
            setMode('scan');
        });
        if (btnModeUpload) btnModeUpload.addEventListener('click', function() {
            setMode('upload');
        });

        // --- Camera: capture front + back ---
        var cameraModal = document.getElementById('camera_modal');
        var cameraVideo = document.getElementById('camera_video');
        var cameraStepLabel = document.getElementById('camera_step_label');
        var btnCaptureFront = document.getElementById('btn_capture_front');
        var btnCaptureBack = document.getElementById('btn_capture_back');
        var btnTakePhoto = document.getElementById('btn_take_photo');
        var btnCloseCamera = document.getElementById('btn_close_camera');
        var previewFront = document.getElementById('preview_front');
        var previewBack = document.getElementById('preview_back');
        var imgFront = document.getElementById('img_front');
        var imgBack = document.getElementById('img_back');
        var scanStatus = document.getElementById('scan_status');
        var btnExtractAfterScan = document.getElementById('btn_extract_after_scan');
        var identityDocInput = document.getElementById('identity_document_file');

        setMode('scan');

        var cameraStream = null;
        var currentCaptureStep = null;
        var capturedFrontBlob = null;
        var capturedBackBlob = null;
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        var scanPagesArray = [];

        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(t) {
                    t.stop();
                });
                cameraStream = null;
            }
            cameraModal.classList.add('hidden');
        }

        function openCameraFor(step) {
            currentCaptureStep = step;
            if (step === 'scanpage') {
                cameraStepLabel.textContent =
                    '{{ app()->getLocale() === 'ar' ? 'وجّه الكاميرا إلى المستند ثم اضغط التقاط (صفحة للمسح كـ PDF)' : 'Point camera at document, then press Capture (page for scan-as-PDF)' }}';
            } else {
                cameraStepLabel.textContent = step === 'front' ?
                    '{{ app()->getLocale() === 'ar' ? 'وجّه الكاميرا إلى وجه الوثيقة (الأمام) ثم اضغط التقاط' : 'Point camera at front of document, then press Capture' }}' :
                    '{{ app()->getLocale() === 'ar' ? 'وجّه الكاميرا إلى ظهر الوثيقة ثم اضغط التقاط' : 'Point camera at back of document, then press Capture' }}';
            }
            cameraModal.classList.remove('hidden');
            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment'
                }
            }).then(function(stream) {
                cameraStream = stream;
                cameraVideo.srcObject = stream;
            }).catch(function() {
                navigator.mediaDevices.getUserMedia({
                    video: true
                }).then(function(stream) {
                    cameraStream = stream;
                    cameraVideo.srcObject = stream;
                }).catch(function() {
                    if (scanStatus) scanStatus.textContent =
                        '{{ app()->getLocale() === 'ar' ? 'تعذر الوصول للكاميرا.' : 'Camera access denied.' }}';
                    scanStatus.classList.remove('hidden');
                    cameraModal.classList.add('hidden');
                });
            });
        }

        function capturePhoto() {
            if (!cameraStream || !currentCaptureStep) return;
            canvas.width = cameraVideo.videoWidth;
            canvas.height = cameraVideo.videoHeight;
            ctx.drawImage(cameraVideo, 0, 0);
            canvas.toBlob(function(blob) {
                if (currentCaptureStep === 'scanpage') {
                    var url = URL.createObjectURL(blob);
                    scanPagesArray.push({
                        blob: blob,
                        url: url
                    });
                    renderScanPdfThumbnails();
                    document.getElementById('btn_generate_pdf').disabled = false;
                    stopCamera();
                    currentCaptureStep = null;
                    return;
                }
                if (currentCaptureStep === 'front') {
                    capturedFrontBlob = blob;
                    imgFront.src = URL.createObjectURL(blob);
                    previewFront.classList.remove('hidden');
                    btnCaptureBack.disabled = false;
                    stopCamera();
                } else {
                    capturedBackBlob = blob;
                    imgBack.src = URL.createObjectURL(blob);
                    previewBack.classList.remove('hidden');
                    stopCamera();
                    var dt = new DataTransfer();
                    dt.items.add(new File([capturedFrontBlob], 'document-front.jpg', {
                        type: 'image/jpeg'
                    }));
                    dt.items.add(new File([capturedBackBlob], 'document-back.jpg', {
                        type: 'image/jpeg'
                    }));
                    identityDocInput.files = dt.files;
                    if (scanStatus) scanStatus.textContent =
                        '{{ app()->getLocale() === 'ar' ? 'تم مسح وجه وظهر الوثيقة. يمكنك استخراج البيانات من صورة الوجه أدناه.' : 'Front and back captured. You can extract data from the front image below.' }}';
                    scanStatus.classList.remove('hidden');
                    if (btnExtractAfterScan) btnExtractAfterScan.classList.remove('hidden');
                }
            }, 'image/jpeg', 0.92);
        }

        if (btnCaptureFront) btnCaptureFront.addEventListener('click', function() {
            openCameraFor('front');
        });
        if (btnCaptureBack) btnCaptureBack.addEventListener('click', function() {
            openCameraFor('back');
        });
        if (btnTakePhoto) btnTakePhoto.addEventListener('click', capturePhoto);
        if (btnCloseCamera) btnCloseCamera.addEventListener('click', stopCamera);

        function renderScanPdfThumbnails() {
            var container = document.getElementById('scan_pdf_pages');
            if (!container) return;
            container.innerHTML = '';
            scanPagesArray.forEach(function(item, index) {
                var wrap = document.createElement('div');
                wrap.className = 'relative inline-block';
                var img = document.createElement('img');
                img.src = item.url;
                img.alt = 'Page ' + (index + 1);
                img.className = 'h-20 w-auto rounded border border-slate-300 object-cover';
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className =
                    'absolute -top-1 -right-1 w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold hover:bg-red-600';
                btn.textContent = '×';
                btn.addEventListener('click', function() {
                    URL.revokeObjectURL(item.url);
                    scanPagesArray.splice(index, 1);
                    renderScanPdfThumbnails();
                    if (scanPagesArray.length === 0) document.getElementById('btn_generate_pdf').disabled =
                        true;
                });
                wrap.appendChild(img);
                wrap.appendChild(btn);
                container.appendChild(wrap);
            });
        }

        function blobToBase64(blob) {
            return new Promise(function(resolve, reject) {
                var r = new FileReader();
                r.onload = function() {
                    resolve(r.result.split(',')[1]);
                };
                r.onerror = reject;
                r.readAsDataURL(blob);
            });
        }

        document.getElementById('btn_add_scan_page').addEventListener('click', function() {
            openCameraFor('scanpage');
        });

        document.getElementById('btn_generate_pdf').addEventListener('click', function() {
            if (scanPagesArray.length === 0) return;
            var JsPDF = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : (typeof jspdf !== 'undefined' &&
                jspdf.jsPDF) ? jspdf.jsPDF : null;
            if (!JsPDF) {
                document.getElementById('scanned_pdf_status').textContent =
                    '{{ app()->getLocale() === 'ar' ? 'مكتبة PDF غير محمّلة.' : 'PDF library not loaded.' }}';
                document.getElementById('scanned_pdf_status').classList.remove('hidden');
                return;
            }
            var doc = new JsPDF();
            var w = doc.internal.pageSize.getWidth();
            var h = doc.internal.pageSize.getHeight();
            var promises = scanPagesArray.map(function(p) {
                return blobToBase64(p.blob);
            });
            Promise.all(promises).then(function(base64arr) {
                base64arr.forEach(function(base64, i) {
                    if (i > 0) doc.addPage();
                    doc.addImage(base64, 'JPEG', 0, 0, w, h);
                });
                var pdfBlob = doc.output('blob');
                var file = new File([pdfBlob], 'scanned-document.pdf', {
                    type: 'application/pdf'
                });
                var dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('scanned_pdf_file').files = dt.files;
                var status = document.getElementById('scanned_pdf_status');
                status.textContent =
                    '{{ app()->getLocale() === 'ar' ? 'تم مرفق PDF (' : 'PDF attached (' }}' +
                    scanPagesArray.length +
                    ' {{ app()->getLocale() === 'ar' ? 'صفحة) مع الطلب.' : ' pages) with the request.' }}';
                status.classList.remove('hidden');
                status.classList.add('text-emerald-600');
            });
        });

        function doExtractFromFile(file) {
            var extractStatus = document.getElementById('extract_status');
            extractStatus.textContent = '{{ app()->getLocale() === 'ar' ? 'جاري الاستخراج...' : 'Extracting...' }}';
            extractStatus.classList.remove('hidden', 'text-emerald-600', 'text-red-600', 'text-amber-600');
            extractStatus.classList.add('text-slate-600');
            var formData = new FormData();
            formData.append('document', file);
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            fetch('{{ route('patients.extract-identity-document') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function(r) {
                    return r.text().then(function(text) {
                        try {
                            var data = text ? JSON.parse(text) : {};
                            return {
                                ok: r.ok,
                                status: r.status,
                                data: data
                            };
                        } catch (e) {
                            return {
                                ok: false,
                                status: r.status,
                                data: {
                                    message: r.status === 500 ? 'Server error. Check Tesseract OCR setup.' :
                                        text ? text.substring(0, 120) : 'Request failed.'
                                }
                            };
                        }
                    });
                })
                .then(function(result) {
                    var res = result.data;
                    extractStatus.textContent = (res && res.message) ? res.message : (result.ok ? '' :
                        'Request failed (' + result.status + ').');
                    if (result.ok && res && res.success && res.data) {
                        extractStatus.classList.remove('text-slate-600', 'text-red-600', 'text-amber-600');
                        extractStatus.classList.add('text-emerald-600');
                        var d = res.data;
                        if (d.name) document.querySelector('input[name="name"]').value = d.name;
                        if (d.name_ar) {
                            var inf = document.querySelector('input[name="name_ar_first"]');
                            if (inf) inf.value = d.name_ar;
                        }
                        if (d.identity_value) document.getElementById('identity_value').value = d.identity_value;
                        if (d.identity_type) {
                            var typeSelect = document.getElementById('identity_type');
                            if (typeSelect) typeSelect.value = d.identity_type;
                        }
                        if (document.getElementById('identity_type')) document.getElementById('identity_type')
                            .dispatchEvent(new Event('change'));
                    } else {
                        extractStatus.classList.remove('text-emerald-600', 'text-slate-600');
                        extractStatus.classList.add(result.ok ? 'text-amber-600' : 'text-red-600');
                    }
                })
                .catch(function(err) {
                    extractStatus.textContent = (err && err.message) ? err.message :
                        '{{ app()->getLocale() === 'ar' ? 'حدث خطأ أثناء الاستخراج. تحقق من الاتصال وتثبيت Tesseract.' : 'Extraction error. Check connection and Tesseract OCR setup.' }}';
                    extractStatus.classList.remove('text-amber-600', 'text-emerald-600');
                    extractStatus.classList.add('text-red-600');
                });
        }

        if (btnExtractAfterScan) {
            btnExtractAfterScan.addEventListener('click', function() {
                var file = identityDocInput.files && identityDocInput.files[0];
                if (file) doExtractFromFile(file);
            });
        }

        // Extract identity data from uploaded document (OCR) - upload mode
        var btnExtract = document.getElementById('btn_extract_identity');
        var extractStatus = document.getElementById('extract_status');
        if (btnExtract && identityDocInput) {
            btnExtract.addEventListener('click', function() {
                var file = identityDocInput.files && identityDocInput.files[0];
                if (!file) {
                    extractStatus.textContent =
                        '{{ app()->getLocale() === 'ar' ? 'اختر صورة الوثيقة أولاً.' : 'Please select a document image first.' }}';
                    extractStatus.classList.remove('hidden');
                    extractStatus.className = 'mt-2 text-sm text-amber-600';
                    return;
                }
                btnExtract.disabled = true;
                doExtractFromFile(file);
                setTimeout(function() {
                    btnExtract.disabled = false;
                }, 2000);
            });
        }

        // Check insurance by identity (our system) — when payment type is insurance
        const btnCheckInsurance = document.getElementById('btn_check_insurance');
        const insuranceCheckResult = document.getElementById('insurance_check_result');
        const identityValueInput = document.getElementById('identity_value');

        function showInsuranceResult(msg, isSuccess) {
            insuranceCheckResult.textContent = msg;
            insuranceCheckResult.classList.remove('hidden');
            insuranceCheckResult.className = 'mt-2 ' + (isSuccess ? 'text-emerald-700' : 'text-amber-700');
        }

        if (btnCheckInsurance) {
            btnCheckInsurance.addEventListener('click', function() {
                var identityValue = (identityValueInput && identityValueInput.value) ? identityValueInput.value
                    .trim() : '';
                if (!identityValue) {
                    showInsuranceResult(
                        '{{ app()->getLocale() === 'ar' ? 'يرجى إدخال رقم الهوية أولاً.' : 'Please enter identity number first.' }}',
                        false);
                    return;
                }
                btnCheckInsurance.disabled = true;
                insuranceCheckResult.classList.add('hidden');
                var url = '{{ route('patients.check-insurance') }}?identity_value=' + encodeURIComponent(
                    identityValue);
                fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.has_insurance && data.insurance_company_id) {
                            var sel = document.getElementById('insurance_company_id');
                            if (sel) {
                                sel.value = data.insurance_company_id;
                                showInsuranceResult((data.message || '') +
                                    ' {{ app()->getLocale() === 'ar' ? 'تم تعبئة شركة التأمين.' : 'Insurance company filled.' }}',
                                    true);
                            } else {
                                showInsuranceResult(data.message || '', true);
                            }
                        } else {
                            showInsuranceResult(data.message || (data.found ?
                                '{{ app()->getLocale() === 'ar' ? 'لا يوجد تأمين مسجل.' : 'No insurance on record.' }}' :
                                '{{ app()->getLocale() === 'ar' ? 'لا يوجد مريض بهذا الرقم.' : 'No patient with this identity.' }}'
                            ), false);
                        }
                    })
                    .catch(function() {
                        showInsuranceResult(
                            '{{ app()->getLocale() === 'ar' ? 'حدث خطأ أثناء التحقق.' : 'Check failed.' }}',
                            false);
                    })
                    .finally(function() {
                        btnCheckInsurance.disabled = false;
                    });
            });
        }

        // Identity type change: show sponsor fields when Iqama is selected
        const identityTypeSelect = document.getElementById('identity_type');
        const sponsorNameField = document.getElementById('sponsor_name_field');
        const sponsorPhoneField = document.getElementById('sponsor_phone_field');
        const sponsorNameInput = document.getElementById('sponsor_name');
        const sponsorPhoneInput = document.getElementById('sponsor_phone');

        function toggleSponsorFields() {
            const isIqama = identityTypeSelect.value === 'iqama';

            if (isIqama) {
                sponsorNameField.style.display = 'block';
                sponsorPhoneField.style.display = 'block';
                sponsorNameInput.setAttribute('required', 'required');
                sponsorPhoneInput.setAttribute('required', 'required');
            } else {
                sponsorNameField.style.display = 'none';
                sponsorPhoneField.style.display = 'none';
                sponsorNameInput.removeAttribute('required');
                sponsorPhoneInput.removeAttribute('required');
            }
        }

        identityTypeSelect.addEventListener('change', toggleSponsorFields);
        toggleSponsorFields();
    </script>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-in-out;
        }

        /* Ensure buttons always have visible contrast (fallback if Tailwind is overridden) */
        .patient-form-btn-primary,
        .patient-form-btn-primary:hover {
            background-color: #7c3aed !important;
            color: #ffffff !important;
            border-color: #7c3aed !important;
        }

        .patient-form-btn-primary:hover {
            background-color: #6d28d9 !important;
            border-color: #6d28d9 !important;
        }

        .patient-form-btn-secondary,
        .patient-form-btn-secondary:hover {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-color: #94a3b8 !important;
        }

        .patient-form-btn-secondary:hover {
            background-color: #e2e8f0 !important;
            border-color: #64748b !important;
        }

        /* File input "Choose file" button - ensure visible */
        .patient-form-file-input::-webkit-file-upload-button {
            background-color: #7c3aed;
            color: #ffffff;
            border: 2px solid #6d28d9;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
        }

        .patient-form-file-input::-webkit-file-upload-button:hover {
            background-color: #6d28d9;
        }

        .patient-form-file-input::file-selector-button {
            background-color: #7c3aed;
            color: #ffffff;
            border: 2px solid #6d28d9;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
        }

        .patient-form-file-input::file-selector-button:hover {
            background-color: #6d28d9;
        }
    </style>
@endsection
