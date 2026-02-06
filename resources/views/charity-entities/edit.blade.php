@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تعديل جمعية خيرية' : 'Edit Charity Entity')
@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'تعديل جمعية خيرية' : 'Edit Charity Entity' }}</h2>
            <a href="{{ route('charity-entities.index') }}" class="text-slate-600 hover:text-slate-800 text-sm">{{ app()->getLocale() === 'ar' ? '← العودة' : '← Back' }}</a>
        </div>
        <form action="{{ route('charity-entities.update', $charity_entity) }}" method="POST" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $charity_entity->name) }}" required class="w-full rounded border border-slate-300 px-3 py-2 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $charity_entity->name_ar) }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('name_ar') border-red-500 @enderror">
                    @error('name_ar')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'جهة الاتصال' : 'Contact Person' }}</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $charity_entity->contact_person) }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('contact_person') border-red-500 @enderror">
                    @error('contact_person')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $charity_entity->phone) }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('phone') border-red-500 @enderror">
                    @error('phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                    <input type="email" name="email" value="{{ old('email', $charity_entity->email) }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('email') border-red-500 @enderror">
                    @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الفاكس' : 'Fax' }}</label>
                    <input type="text" name="fax" value="{{ old('fax', $charity_entity->fax) }}" class="w-full rounded border border-slate-300 px-3 py-2 @error('fax') border-red-500 @enderror">
                    @error('fax')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</label>
                    <textarea name="address" rows="2" class="w-full rounded border border-slate-300 px-3 py-2 @error('address') border-red-500 @enderror">{{ old('address', $charity_entity->address) }}</textarea>
                    @error('address')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</label>
                    <textarea name="notes" rows="2" class="w-full rounded border border-slate-300 px-3 py-2 @error('notes') border-red-500 @enderror">{{ old('notes', $charity_entity->notes) }}</textarea>
                    @error('notes')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2 flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $charity_entity->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                    <label for="is_active" class="ms-2 text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</label>
                </div>
            </div>
            <div class="mt-6 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'تحديث' : 'Update' }}</button>
                <a href="{{ route('charity-entities.index') }}" class="bg-slate-200 text-slate-700 px-4 py-2 rounded hover:bg-slate-300">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            </div>
        </form>
    </div>
@endsection
