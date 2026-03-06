@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تعديل قسم' : 'Edit Department')
@section('content')
    <div class="max-w-xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'تعديل قسم' : 'Edit Department' }}</h2>
            <a href="{{ route('departments.index') }}" class="text-slate-600 hover:text-slate-800 text-sm">{{ app()->getLocale() === 'ar' ? '← العودة' : '← Back' }}</a>
        </div>
        <form action="{{ route('departments.update', $department) }}" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6">
                <!-- Name (En) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $department->name) }}" required class="w-full rounded border border-slate-300 px-3 py-2 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Name (Ar) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $department->name_ar) }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('name_ar') border-red-500 @enderror">
                    @error('name_ar')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Code -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</label>
                    <input type="text" name="code" value="{{ old('code', $department->code) }}" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: ACCOUNTING' : 'e.g. ACCOUNTING' }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('code') border-red-500 @enderror">
                    @error('code')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'التصنيف' : 'Category' }} <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full rounded border border-slate-300 px-3 py-2 bg-white @error('category') border-red-500 @enderror">
                        <option value="medical" {{ old('category', $department->category) == 'medical' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'طبي' : 'Medical' }}</option>
                        <option value="administrative" {{ old('category', $department->category) == 'administrative' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'إداري' : 'Administrative' }}</option>
                    </select>
                    @error('category')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Entry fee (كشفية الدخول) — للأقسام الطبية فقط -->
                <div id="entry_fee_wrap" class="{{ old('category', $department->category) === 'medical' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'كشفية الدخول (ريال) — اختياري' : 'Entry fee (SAR) — optional' }}</label>
                    <input type="number" name="entry_fee" id="entry_fee_input" value="{{ old('entry_fee', $department->entry_fee) }}" min="0" step="0.01" placeholder="0" class="w-full rounded border border-slate-300 px-3 py-2 @error('entry_fee') border-red-500 @enderror" {{ old('category', $department->category) !== 'medical' ? 'disabled' : '' }}>
                    <p class="text-xs text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'إذا حددت مبلغاً، سيظهر هذا القسم في «دخول قسم» عند إنشاء زيارة.' : 'If set, this department appears in "Department entry" when creating a visit.' }}</p>
                    @error('entry_fee')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Manager -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'مدير القسم' : 'Department Manager' }}</label>
                    <select name="manager_id" class="w-full rounded border border-slate-300 px-3 py-2 bg-white">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المدير' : 'Select Manager' }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('manager_id', $department->manager_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="is_active" class="ms-2 text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</label>
                </div>
            </div>

            <div class="mt-6 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'حفظ التعديلات' : 'Update Department' }}</button>
                <a href="{{ route('departments.index') }}" class="bg-slate-200 text-slate-700 px-4 py-2 rounded hover:bg-slate-300">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            </div>
        </form>
    </div>
    <script>
        (function() {
            var cat = document.querySelector('select[name="category"]');
            var wrap = document.getElementById('entry_fee_wrap');
            var input = document.getElementById('entry_fee_input');
            function toggle() {
                if (!wrap) return;
                if (cat && cat.value === 'medical') {
                    wrap.classList.remove('hidden');
                    if (input) { input.removeAttribute('disabled'); }
                } else {
                    wrap.classList.add('hidden');
                    if (input) { input.setAttribute('disabled', 'disabled'); input.value = ''; }
                }
            }
            if (cat) { cat.addEventListener('change', toggle); }
        })();
    </script>
@endsection
