@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'إضافة خدمة' : 'Add Service')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'إضافة خدمة جديدة' : 'Add New Service' }}</h2>
        <a href="{{ route('services.index') }}" class="text-slate-500 hover:text-slate-700">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('services.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                <!-- Name (En) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</label>
                    <input type="text" name="name" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Name (Ar) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</label>
                    <input type="text" name="name_ar" class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Code -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'كود الخدمة' : 'Service Code' }}</label>
                    <input type="text" name="code" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'السعر الافتراضي' : 'Default Price' }}</label>
                    <input type="number" name="default_price" step="0.01" min="0" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</label>
                    <select name="department_id" required class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر القسم' : 'Select Department' }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ app()->getLocale() === 'ar' ? ($dept->name_ar ?: $dept->name) : $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">
                    {{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
