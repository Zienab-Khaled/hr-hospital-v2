@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل القسم' : 'Department Details')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'تفاصيل القسم' : 'Department Details' }}</h2>
        <a href="{{ route('departments.index') }}" class="text-slate-500 hover:text-slate-700">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? ($department->name_ar ?: $department->name) : $department->name }}</h3>
            <p class="text-sm text-slate-500">{{ $department->code ?: '-' }}</p>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</label>
                <p class="mt-1 text-slate-800 font-medium">{{ $department->name }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                <p class="mt-1 text-slate-800 font-medium">{{ $department->name_ar ?: '-' }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</label>
                <p class="mt-1 text-slate-800 font-medium">{{ $department->code ?: '-' }}</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                <span class="mt-1 inline-block px-2 py-1 text-xs rounded-full {{ $department->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $department->is_active ? (app()->getLocale() === 'ar' ? 'نشط' : 'Active') : (app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive') }}
                </span>
            </div>
        </div>
        <div class="p-6 bg-slate-50 flex justify-end gap-3">
            @can('departments.manage')
                <a href="{{ route('departments.edit', $department) }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                    {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection
