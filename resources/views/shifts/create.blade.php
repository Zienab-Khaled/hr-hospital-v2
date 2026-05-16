@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'إضافة وردية جديدة' : 'Add New Shift')

@section('content')
<div class="mb-6 flex items-center gap-4">
    <a href="{{ route('shifts.index') }}" class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ app()->getLocale() === 'ar' ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7' }}"/></svg>
    </a>
    <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'إضافة وردية جديدة' : 'Add New Shift' }}</h2>
</div>

<div class="max-w-xl bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <form action="{{ route('shifts.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم الوردية (عربي)' : 'Shift Name (AR)' }}</label>
                <input type="text" name="name_ar" value="{{ old('name_ar') }}" placeholder="مثال: الوردية الصباحية" required
                    class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                @error('name_ar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم الوردية (En)' : 'Shift Name (EN)' }}</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Morning Shift" required
                    class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'وقت البدء' : 'Start Time' }}</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}" required
                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                    @error('start_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'وقت النهاية' : 'End Time' }}</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}" required
                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                    @error('end_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الترتيب' : 'Sort Order' }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                        class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
                </div>
                <div class="flex items-center pt-6">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-slate-700">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</span>
                    </label>
                </div>
            </div>

            @include('shifts.partials.staff-assign', ['staffUsers' => $staffUsers])

            <div class="pt-4 border-t border-slate-100 flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    {{ app()->getLocale() === 'ar' ? 'حفظ الـ Shift' : 'Save Shift' }}
                </button>
                <a href="{{ route('shifts.index') }}" class="bg-slate-100 text-slate-600 px-6 py-2 rounded-lg hover:bg-slate-200 transition-colors font-medium text-center">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
