@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تفاصيل الجمعية الخيرية' : 'Charity Entity Details')
@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-slate-800">{{ $charity_entity->name_ar ?: $charity_entity->name }}</h2>
            <a href="{{ route('charity-entities.index') }}" class="text-slate-500 hover:text-slate-700">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a>
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</label>
                    <p class="mt-1 text-slate-800 font-medium">{{ $charity_entity->name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                    <p class="mt-1 text-slate-800 font-medium">{{ $charity_entity->name_ar ?: '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'جهة الاتصال' : 'Contact Person' }}</label>
                    <p class="mt-1 text-slate-800 font-medium">{{ $charity_entity->contact_person ?: '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</label>
                    <p class="mt-1 text-slate-800 font-medium">{{ $charity_entity->phone ?: '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                    <p class="mt-1 text-slate-800 font-medium">{{ $charity_entity->email ?: '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الفاكس' : 'Fax' }}</label>
                    <p class="mt-1 text-slate-800 font-medium">{{ $charity_entity->fax ?: '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</label>
                    <p class="mt-1 text-slate-800 font-medium">{{ $charity_entity->address ?: '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</label>
                    <p class="mt-1 text-slate-800 font-medium">{{ $charity_entity->notes ?: '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <span class="mt-1 inline-block px-2 py-1 text-xs rounded-full {{ $charity_entity->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $charity_entity->is_active ? (app()->getLocale() === 'ar' ? 'نشط' : 'Active') : (app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive') }}
                    </span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'عدد المرضى' : 'Patients' }}</label>
                    <p class="mt-1 text-slate-800 font-medium">{{ $charity_entity->patients_count }}</p>
                </div>
            </div>
            <div class="p-6 bg-slate-50 flex justify-end gap-3">
                @can('charity_entities.manage')
                    <a href="{{ route('charity-entities.edit', $charity_entity) }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a>
                @endcan
            </div>
        </div>
    </div>
@endsection
