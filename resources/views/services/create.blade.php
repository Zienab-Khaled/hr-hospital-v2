@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'إضافة خدمة' : 'Add Service')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'إضافة خدمة جديدة' : 'Add New Service' }}</h2>
        <a href="{{ route('services.index') }}" class="text-slate-600 hover:text-slate-800 text-sm">{{ app()->getLocale() === 'ar' ? '← العودة' : '← Back' }}</a>
    </div>

    <form action="{{ route('services.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="grid grid-cols-1 gap-6">
            <!-- Name (En) -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-slate-300 px-3 py-2 @error('name') border-red-500 @enderror">
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Name (Ar) -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('name_ar') border-red-500 @enderror">
                @error('name_ar')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Code -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'كود الخدمة' : 'Service Code' }} <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" required class="w-full rounded border border-slate-300 px-3 py-2 @error('code') border-red-500 @enderror">
                @error('code')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Price -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'السعر الافتراضي' : 'Default Price' }} <span class="text-red-500">*</span></label>
                <input type="number" name="default_price" value="{{ old('default_price') }}" step="0.01" min="0" required class="w-full rounded border border-slate-300 px-3 py-2 @error('default_price') border-red-500 @enderror">
                @error('default_price')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Department -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }} <span class="text-red-500">*</span></label>
                <select name="department_id" required class="w-full rounded border border-slate-300 px-3 py-2 @error('department_id') border-red-500 @enderror">
                    <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر قسم --' : '-- Select Department --' }}</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? ($dept->name_ar ?: $dept->name) : $dept->name }}</option>
                    @endforeach
                </select>
                @error('department_id')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Multi-Session Service -->
            <div>
                <div class="flex items-center mb-2">
                    <input type="hidden" name="is_multi_session" value="0">
                    <input type="checkbox" name="is_multi_session" id="is_multi_session" value="1" {{ old('is_multi_session') ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" onclick="toggleMultiSession()">
                    <label for="is_multi_session" class="ms-2 text-sm font-medium text-slate-700">{{ app()->getLocale() === 'ar' ? 'خدمة متعددة الجلسات' : 'Multi-Session Service' }}</label>
                </div>
            </div>

            <!-- Multi-Session Fields -->
            <div id="multi_session_fields" class="{{ old('is_multi_session') ? '' : 'hidden' }} grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'عدد الجلسات' : 'Number of Sessions' }}</label>
                    <input type="number" name="session_count" value="{{ old('session_count') }}" min="1" class="w-full rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المدة بين كل جلسة' : 'Wait Time' }}</label>
                    <input type="number" name="session_wait_time" value="{{ old('session_wait_time') }}" min="1" class="w-full rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الوحدة' : 'Unit' }}</label>
                    <select name="session_wait_unit" class="w-full rounded border border-slate-300 px-3 py-2">
                        <option value="days" {{ old('session_wait_unit') == 'days' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'يوم' : 'Days' }}</option>
                        <option value="weeks" {{ old('session_wait_unit') == 'weeks' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'أسبوع' : 'Weeks' }}</option>
                        <option value="months" {{ old('session_wait_unit') == 'months' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'شهر' : 'Months' }}</option>
                    </select>
                </div>
            </div>

            <script>
                function toggleMultiSession() {
                    const isChecked = document.getElementById('is_multi_session').checked;
                    const fields = document.getElementById('multi_session_fields');
                    if (isChecked) {
                        fields.classList.remove('hidden');
                    } else {
                        fields.classList.add('hidden');
                    }
                }
            </script>
        </div>

        <div class="mt-6 flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                {{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}
            </button>
            <a href="{{ route('services.index') }}" class="bg-slate-200 text-slate-700 px-4 py-2 rounded hover:bg-slate-300">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
        </div>
    </form>
</div>
@endsection
