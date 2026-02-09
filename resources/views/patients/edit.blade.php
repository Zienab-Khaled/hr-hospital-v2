@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تعديل مريض' : 'Edit Patient')
@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">
                {{ app()->getLocale() === 'ar' ? '👤 تعديل مريض' : '👤 Edit Patient' }}
            </h2>
            
            <form action="{{ route('patients.update', $patient) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                {{-- Identity: Type + Value --}}
                <div class="border-b pb-4">
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
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}</option>
                                @foreach(\App\Models\Patient::identityTypeOptions() as $key => $labels)
                                    <option value="{{ $key }}" {{ old('identity_type', $patient->identity_type) === $key ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'ar' ? $labels['ar'] : $labels['en'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('identity_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'رقم الهوية' : 'Identity Number' }} *
                            </label>
                            <input type="text" name="identity_value" id="identity_value" value="{{ old('identity_value', $patient->identity_value) }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2 @error('identity_value') border-red-500 @enderror">
                            @error('identity_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Personal Information --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? '👤 المعلومات الشخصية' : '👤 Personal Information' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} *
                            </label>
                            <input type="text" name="name" value="{{ old('name', $patient->name) }}" required
                                   class="w-full rounded border border-slate-300 px-3 py-2 @error('name') border-red-500 @enderror">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}
                            </label>
                            <input type="text" name="name_ar" value="{{ old('name_ar', $patient->name_ar) }}" dir="rtl"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File Number' }} *
                            </label>
                            <input type="text" name="file_number" value="{{ old('file_number', $patient->file_number) }}" required
                                   class="w-full rounded border border-slate-300 px-3 py-2 @error('file_number') border-red-500 @enderror">
                            @error('file_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'العمر' : 'Age' }}
                            </label>
                            <input type="number" name="age" value="{{ old('age', $patient->age) }}" min="0" max="150"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الجنس' : 'Gender' }}
                            </label>
                            <select name="gender" class="w-full rounded border border-slate-300 px-3 py-2">
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}</option>
                                <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }}
                                </option>
                                <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'البلد الأصلي' : 'Country of Origin' }}
                            </label>
                            <input type="text" name="country_of_origin" value="{{ old('country_of_origin', $patient->country_of_origin) }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الموقع الحالي' : 'Current Location' }}
                            </label>
                            <input type="text" name="current_location" value="{{ old('current_location', $patient->current_location) }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div id="sponsor_name_field" style="display: none;">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'اسم الكفيل' : 'Sponsor Name' }} <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="sponsor_name" id="sponsor_name" value="{{ old('sponsor_name', $patient->sponsor_name) }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                            <p class="mt-1 text-xs text-blue-600">
                                {{ app()->getLocale() === 'ar' ? 'مطلوب لحاملي الإقامة' : 'Required for Iqama holders' }}
                            </p>
                        </div>
                        <div id="sponsor_phone_field" style="display: none;">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'هاتف الكفيل' : 'Sponsor Phone' }} <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="sponsor_phone" id="sponsor_phone" value="{{ old('sponsor_phone', $patient->sponsor_phone) }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                            <p class="mt-1 text-xs text-blue-600">
                                {{ app()->getLocale() === 'ar' ? 'مطلوب لحاملي الإقامة' : 'Required for Iqama holders' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Payment Information --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? '💳 معلومات الدفع' : '💳 Payment Information' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }} *
                            </label>
                            <select name="payment_type" id="payment_type" required class="w-full rounded border border-slate-300 px-3 py-2">
                                <option value="cash" {{ old('payment_type', $patient->payment_type) === 'cash' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'نقدي' : 'Cash' }}
                                </option>
                                <option value="insurance" {{ old('payment_type', $patient->payment_type) === 'insurance' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}
                                </option>
                                <option value="charity" {{ old('payment_type', $patient->payment_type) === 'charity' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'جمعية خيرية' : 'Charity' }}
                                </option>
                            </select>
                        </div>
                        <div id="insurance_field">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}
                            </label>
                            <select name="insurance_company_id" id="insurance_company_id" class="w-full rounded border border-slate-300 px-3 py-2">
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}</option>
                                @foreach($insuranceCompanies as $c)
                                    <option value="{{ $c->id }}" {{ old('insurance_company_id', $patient->insurance_company_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="insurance_check_box" class="mt-2 p-3 rounded-lg border border-slate-200 bg-slate-50 text-sm">
                                <p class="text-slate-600 mb-2">
                                    {{ app()->getLocale() === 'ar' ? 'أدخل نوع الهوية ورقم الهوية أعلاه ثم اضغط للتحقق من سجلاتنا أو من موقع مجلس الضمان الصحي.' : 'Enter identity type and number above, then click to check our records or the official CHI portal.' }}
                                </p>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" id="btn_check_insurance" class="bg-emerald-600 text-white px-3 py-1.5 rounded text-sm font-medium hover:bg-emerald-700">
                                        {{ app()->getLocale() === 'ar' ? '🔍 التحقق من السجلات' : '🔍 Check our records' }}
                                    </button>
                                    <a href="https://www.chi.gov.sa/ServicesDirectory/Pages/Eservices-CheckInsurance.aspx" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline text-sm">
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
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}</option>
                                @foreach($charityEntities as $e)
                                    <option value="{{ $e->id }}" {{ old('charity_entity_id', $patient->charity_entity_id) == $e->id ? 'selected' : '' }}>
                                        {{ $e->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Document Upload --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? '📎 المستندات' : '📎 Documents' }}
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'رفع مستندات إضافية' : 'Upload additional documents' }}
                        </label>
                        <input type="file" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full rounded border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}
                    </label>
                    <textarea name="notes" rows="3" class="w-full rounded border border-slate-300 px-3 py-2">{{ old('notes', $patient->notes) }}</textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 shadow">
                        {{ app()->getLocale() === 'ar' ? '💾 حفظ التعديلات' : '💾 Save Changes' }}
                    </button>
                    <a href="{{ route('patients.show', $patient) }}" class="bg-slate-200 text-slate-700 px-6 py-3 rounded-lg font-semibold hover:bg-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('payment_type').addEventListener('change', function() {
            var v = this.value;
            document.getElementById('insurance_field').style.display = v === 'insurance' ? 'block' : 'none';
            document.getElementById('charity_field').style.display = v === 'charity' ? 'block' : 'none';
        });
        document.getElementById('payment_type').dispatchEvent(new Event('change'));

        var btnCheckInsurance = document.getElementById('btn_check_insurance');
        var insuranceCheckResult = document.getElementById('insurance_check_result');
        var identityValueInput = document.getElementById('identity_value');
        function showInsuranceResult(msg, isSuccess) {
            insuranceCheckResult.textContent = msg;
            insuranceCheckResult.classList.remove('hidden');
            insuranceCheckResult.className = 'mt-2 ' + (isSuccess ? 'text-emerald-700' : 'text-amber-700');
        }
        if (btnCheckInsurance) {
            btnCheckInsurance.addEventListener('click', function() {
                var identityValue = (identityValueInput && identityValueInput.value) ? identityValueInput.value.trim() : '';
                if (!identityValue) {
                    showInsuranceResult('{{ app()->getLocale() === "ar" ? "يرجى إدخال رقم الهوية أولاً." : "Please enter identity number first." }}', false);
                    return;
                }
                btnCheckInsurance.disabled = true;
                insuranceCheckResult.classList.add('hidden');
                var url = '{{ route("patients.check-insurance") }}?identity_value=' + encodeURIComponent(identityValue);
                fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.has_insurance && data.insurance_company_id) {
                            var sel = document.getElementById('insurance_company_id');
                            if (sel) { sel.value = data.insurance_company_id; showInsuranceResult((data.message || '') + ' {{ app()->getLocale() === "ar" ? "تم تعبئة شركة التأمين." : "Insurance company filled." }}', true); }
                        } else {
                            showInsuranceResult(data.message || (data.found ? '{{ app()->getLocale() === "ar" ? "لا يوجد تأمين مسجل." : "No insurance on record." }}' : '{{ app()->getLocale() === "ar" ? "لا يوجد مريض بهذا الرقم." : "No patient with this identity." }}'), false);
                        }
                    })
                    .catch(function() { showInsuranceResult('{{ app()->getLocale() === "ar" ? "حدث خطأ أثناء التحقق." : "Check failed." }}', false); })
                    .finally(function() { btnCheckInsurance.disabled = false; });
            });
        }

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
@endsection
