@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تسجيل مريض جديد' : 'Register New Patient')
@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">
                {{ app()->getLocale() === 'ar' ? '👤 تسجيل مريض جديد' : '👤 Register New Patient' }}
            </h2>
            
            <form action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                {{-- Identity Documents Section --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold text-slate-700 mb-4">
                        {{ app()->getLocale() === 'ar' ? '📋 وثائق الهوية' : '📋 Identity Documents' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الرقم القومي' : 'National ID' }}
                            </label>
                            <input type="text" name="id_number" value="{{ old('id_number', request('identity')) }}" 
                                   class="w-full rounded border border-slate-300 px-3 py-2 @error('id_number') border-red-500 @enderror">
                            @error('id_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'رقم الإقامة' : 'Iqama Number' }}
                            </label>
                            <input type="text" name="iqama_number" value="{{ old('iqama_number') }}" 
                                   class="w-full rounded border border-slate-300 px-3 py-2 @error('iqama_number') border-red-500 @enderror">
                            @error('iqama_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'رقم جواز السفر' : 'Passport Number' }}
                            </label>
                            <input type="text" name="passport_number" value="{{ old('passport_number') }}" 
                                   class="w-full rounded border border-slate-300 px-3 py-2 @error('passport_number') border-red-500 @enderror">
                            @error('passport_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">
                        {{ app()->getLocale() === 'ar' ? '* يجب إدخال رقم هوية واحد على الأقل' : '* At least one identity document is required' }}
                    </p>
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
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full rounded border border-slate-300 px-3 py-2 @error('name') border-red-500 @enderror">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}
                            </label>
                            <input type="text" name="name_ar" value="{{ old('name_ar') }}" dir="rtl"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File Number' }} *
                            </label>
                            <input type="text" name="file_number" value="{{ old('file_number', 'F-' . date('Ymd') . '-' . rand(1000, 9999)) }}" required
                                   class="w-full rounded border border-slate-300 px-3 py-2 @error('file_number') border-red-500 @enderror">
                            @error('file_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'العمر' : 'Age' }}
                            </label>
                            <input type="number" name="age" value="{{ old('age') }}" min="0" max="150"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الجنس' : 'Gender' }}
                            </label>
                            <select name="gender" class="w-full rounded border border-slate-300 px-3 py-2">
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}</option>
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
                                {{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}
                            </label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'البلد الأصلي' : 'Country of Origin' }}
                            </label>
                            <input type="text" name="country_of_origin" value="{{ old('country_of_origin') }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الموقع الحالي' : 'Current Location' }}
                            </label>
                            <input type="text" name="current_location" value="{{ old('current_location') }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'اسم الكفيل' : 'Sponsor Name' }}
                            </label>
                            <input type="text" name="sponsor_name" value="{{ old('sponsor_name') }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'هاتف الكفيل' : 'Sponsor Phone' }}
                            </label>
                            <input type="text" name="sponsor_phone" value="{{ old('sponsor_phone') }}"
                                   class="w-full rounded border border-slate-300 px-3 py-2">
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
                                <option value="cash" {{ old('payment_type', 'cash') === 'cash' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'نقدي' : 'Cash' }}
                                </option>
                                <option value="insurance" {{ old('payment_type') === 'insurance' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}
                                </option>
                                <option value="charity" {{ old('payment_type') === 'charity' ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? 'جمعية خيرية' : 'Charity' }}
                                </option>
                            </select>
                        </div>
                        <div id="insurance_field">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance Company' }}
                            </label>
                            <select name="insurance_company_id" class="w-full rounded border border-slate-300 px-3 py-2">
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}</option>
                                @foreach($insuranceCompanies as $c)
                                    <option value="{{ $c->id }}" {{ old('insurance_company_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="charity_field">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ app()->getLocale() === 'ar' ? 'الجمعية الخيرية' : 'Charity Entity' }}
                            </label>
                            <select name="charity_entity_id" class="w-full rounded border border-slate-300 px-3 py-2">
                                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}</option>
                                @foreach($charityEntities as $e)
                                    <option value="{{ $e->id }}" {{ old('charity_entity_id') == $e->id ? 'selected' : '' }}>
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
                            {{ app()->getLocale() === 'ar' ? 'رفع المستندات (صور/PDFs)' : 'Upload Documents (Images/PDFs)' }}
                        </label>
                        <input type="file" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full rounded border border-slate-300 px-3 py-2">
                        <p class="text-xs text-slate-500 mt-1">
                            {{ app()->getLocale() === 'ar' ? 'يمكنك رفع عدة ملفات (PDF, JPG, PNG)' : 'You can upload multiple files (PDF, JPG, PNG)' }}
                        </p>
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
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 shadow">
                        {{ app()->getLocale() === 'ar' ? '💾 حفظ' : '💾 Save' }}
                    </button>
                    <a href="{{ route('patients.search') }}" class="bg-slate-200 text-slate-700 px-6 py-3 rounded-lg font-semibold hover:bg-slate-300">
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
    </script>
@endsection
