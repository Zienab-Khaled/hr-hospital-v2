@extends('layouts.app')
@section('title', __('Settings'))
@section('content')
    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ __('Settings') }}</h2>
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 max-w-xl">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم التجمع الصحي (اختياري، يظهر في عرض السعر)' : 'Health cluster name (optional, shown on price offer)' }}</label>
            <input type="text" name="health_cluster_name" value="{{ old('health_cluster_name', $healthClusterName ?? '') }}"
                placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: تجمع الجوف الصحي' : 'e.g. Aljouf Health Cluster' }}"
                class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم التجمع الصحي (إنجليزي، اختياري)' : 'Health cluster name (English, optional)' }}</label>
            <input type="text" name="health_cluster_name_en" value="{{ old('health_cluster_name_en', $healthClusterNameEn ?? '') }}"
                placeholder="e.g. Aljouf Health Cluster"
                class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم المستشفى' : 'Hospital name' }}</label>
            <input type="text" name="hospital_name" value="{{ old('hospital_name', $hospitalName) }}"
                class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم المستشفى (إنجليزي، اختياري)' : 'Hospital name (English, optional)' }}</label>
            <input type="text" name="hospital_name_en" value="{{ old('hospital_name_en', $hospitalNameEn ?? '') }}"
                class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم المدير (ثابت للتقارير والطباعة)' : 'Manager name (fixed for reports & print)' }}</label>
            <input type="text" name="manager_name" value="{{ old('manager_name', $managerName) }}"
                class="w-full rounded border border-slate-300 px-3 py-2">
        </div>

        <div class="mb-4 pt-4 border-t border-slate-200">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الشعار' : 'Logo' }}</label>
            @if($logoPath)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" class="h-16 object-contain border border-slate-200 rounded p-1 bg-white">
                    <span class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الشعار الحالي' : 'Current logo' }}</span>
                </div>
            @endif
            <input type="file" name="logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml"
                class="w-full rounded border border-slate-300 px-3 py-2 text-sm file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('logo') border-red-500 @enderror">
            @error('logo')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'اتركه فارغًا للإبقاء على الشعار الحالي. PNG, JPG, GIF, WebP, SVG حتى 2 ميجا.' : 'Leave empty to keep current logo. PNG, JPG, GIF, WebP, SVG up to 2MB.' }}</p>
        </div>

        <div class="mb-4 pt-4 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ app()->getLocale() === 'ar' ? 'البيانات البنكية' : 'Bank details' }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم البنك' : 'Bank name' }}</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $bankName ?? '') }}"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: البنك الأهلي' : 'e.g. Al Ahli Bank' }}"
                        class="w-full rounded border border-slate-300 px-3 py-2 @error('bank_name') border-red-500 @enderror">
                    @error('bank_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الحساب' : 'Account number' }}</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $accountNumber) }}"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'رقم الحساب البنكي' : 'Bank account number' }}"
                        class="w-full rounded border border-slate-300 px-3 py-2 @error('account_number') border-red-500 @enderror">
                    @error('account_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الآيبان' : 'IBAN' }}</label>
                    <input type="text" name="iban_number" value="{{ old('iban_number', $ibanNumber) }}"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: SA00 0000 0000 0000 0000 0000' : 'e.g. SA00 0000 0000 0000 0000 0000' }}"
                        class="w-full rounded border border-slate-300 px-3 py-2 @error('iban_number') border-red-500 @enderror">
                    @error('iban_number')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="mb-4 pt-4 border-t border-slate-200">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'صورة الختم' : 'Stamp image' }}</label>
            @if($stampPath)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ asset('storage/' . $stampPath) }}" alt="Stamp" class="h-20 object-contain border border-slate-200 rounded p-1 bg-white">
                    <span class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الختم الحالي' : 'Current stamp' }}</span>
                </div>
            @endif
            <input type="file" name="stamp" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml"
                class="w-full rounded border border-slate-300 px-3 py-2 text-sm file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('stamp') border-red-500 @enderror">
            @error('stamp')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'اتركه فارغًا للإبقاء على الختم الحالي. PNG, JPG, GIF, WebP, SVG حتى 2 ميجا.' : 'Leave empty to keep current stamp. PNG, JPG, GIF, WebP, SVG up to 2MB.' }}</p>
        </div>

        <div class="mb-4 pt-4 border-t border-slate-200">
            <x-signature-pad
                name="manager_signature_data"
                :current-image="$managerSignaturePath"
                :label="(app()->getLocale() === 'ar' ? 'التوقيع الإلكتروني لمدير إدارة تنمية الإيرادات' : 'Revenue Development Manager electronic signature')"
            />
            @error('manager_signature_data')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4 pt-4 border-t border-slate-200">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم مدير الإدارة' : 'Department manager name' }}</label>
            <input type="text" name="department_manager_name" value="{{ old('department_manager_name', $departmentManagerName ?? '') }}"
                placeholder="{{ app()->getLocale() === 'ar' ? 'للظهور في عرض السعر المُرسل للتأمين/الجمعية' : 'Shown on price offer sent to insurance/charity' }}"
                class="w-full rounded border border-slate-300 px-3 py-2 @error('department_manager_name') border-red-500 @enderror">
            @error('department_manager_name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            <x-signature-pad
                name="department_manager_signature_data"
                :current-image="$departmentManagerSignaturePath ?? ''"
                :label="(app()->getLocale() === 'ar' ? 'التوقيع الإلكتروني لمدير الإدارة' : 'Department manager electronic signature')"
            />
            @error('department_manager_signature_data')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>


        <div class="mb-4 pt-4 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ app()->getLocale() === 'ar' ? 'بيانات الاتصال بالشركة' : 'Company contact' }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</label>
                    <input type="text" name="company_phone" value="{{ old('company_phone', $companyPhone) }}"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: +966 11 234 5678' : 'e.g. +966 11 234 5678' }}"
                        class="w-full rounded border border-slate-300 px-3 py-2 @error('company_phone') border-red-500 @enderror">
                    @error('company_phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                    <input type="email" name="company_email" value="{{ old('company_email', $companyEmail) }}"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'info@hospital.com' : 'info@hospital.com' }}"
                        class="w-full rounded border border-slate-300 px-3 py-2 @error('company_email') border-red-500 @enderror">
                    @error('company_email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</label>
                    <textarea name="company_address" rows="2" placeholder="{{ app()->getLocale() === 'ar' ? 'العنوان الكامل للمستشفى' : 'Full hospital address' }}"
                        class="w-full rounded border border-slate-300 px-3 py-2 @error('company_address') border-red-500 @enderror">{{ old('company_address', $companyAddress) }}</textarea>
                    @error('company_address')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
    </form>
@endsection
