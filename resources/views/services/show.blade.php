@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل الخدمة' : 'Service Details')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'تفاصيل الخدمة' : 'Service Details' }}</h2>
        <a href="{{ route('services.index') }}" class="text-slate-500 hover:text-slate-700">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? ($service->name_ar ?: $service->name) : $service->name }}</h3>
            <p class="text-sm text-slate-500">{{ $service->code }}</p>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</label>
                <p class="mt-1 text-slate-800 font-medium">{{ $service->name }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                <p class="mt-1 text-slate-800 font-medium">{{ $service->name_ar ?: '-' }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</label>
                <p class="mt-1 text-slate-800 font-bold">{{ number_format($service->default_price, 2) }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</label>
                <p class="mt-1 text-slate-800 font-medium">{{ app()->getLocale() === 'ar' ? ($service->department?->name_ar ?: $service->department?->name) : $service->department?->name }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                <span class="mt-1 inline-block px-2 py-1 text-xs rounded-full {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $service->is_active ? (app()->getLocale() === 'ar' ? 'نشط' : 'Active') : (app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive') }}
                </span>
            </div>
        </div>
        <div class="p-6 bg-slate-50 flex justify-end gap-3">
            @can('services.manage')
                <a href="{{ route('services.edit', $service) }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                    {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection
