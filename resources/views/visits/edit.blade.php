@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تعديل زيارة' : 'Edit Visit')
@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-6 border-b pb-4">
                {{ app()->getLocale() === 'ar' ? 'تعديل بيانات الزيارة' : 'Edit Visit Details' }}
            </h2>

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('visits.update', $visit) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</label>
                        <input type="text" disabled value="{{ $visit->patient->name }}" class="w-full bg-slate-100 border border-slate-300 rounded px-3 py-2 text-slate-600">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</label>
                        <input type="date" name="visit_date" value="{{ old('visit_date', $visit->visit_date?->format('Y-m-d')) }}" required
                            class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</label>
                        <select name="department_id" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $visit->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' && $dept->name_ar ? $dept->name_ar : $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الشيفت' : 'Shift' }}</label>
                        <select name="shift_id" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id', $visit->shift_id) == $shift->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' && $shift->name_ar ? $shift->name_ar : $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</label>
                        <textarea name="notes" rows="3" class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">{{ old('notes', $visit->notes) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center gap-4      pt-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700">
                        {{ app()->getLocale() === 'ar' ? 'حفظ التعديلات' : 'Save Changes' }}
                    </button>
                    <a href="{{ route('visits.index') }}" class="text-slate-600 hover:text-slate-800 font-medium">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
