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
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم المستشفى' : 'Hospital name' }}</label>
            <input type="text" name="hospital_name" value="{{ old('hospital_name', $hospitalName) }}"
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
