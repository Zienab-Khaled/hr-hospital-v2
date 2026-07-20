@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إضافة محضر رفض توقيع' : 'Add Refusal-to-Sign Report')
@section('content')
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ app()->getLocale() === 'ar' ? 'إضافة محضر رفض توقيع' : 'Add Refusal-to-Sign Report' }}</h2>
    <form action="{{ route('non-commitment-reports.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 max-w-xl">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __("Patients") }}</label>
            <select name="patient_id" required class="w-full rounded border border-slate-300 px-3 py-2 @error('patient_id') border-red-500 @enderror">
                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر المريض --' : '-- Select --' }}</option>
                @foreach($patients as $p)<option value="{{ $p->id }}" {{ old('patient_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->file_number }})</option>@endforeach
            </select>
            @error('patient_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم المحضر (اختياري — كتابة يدوية)' : 'Report number (optional, manual)' }}</label>
            <input type="text" name="report_number" value="{{ old('report_number') }}" class="w-full rounded border border-slate-300 px-3 py-2" placeholder="{{ app()->getLocale() === 'ar' ? 'رقم المحضر' : 'Report number' }}">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ المحضر' : 'Report date' }}</label>
            <input type="date" name="report_date" value="{{ old('report_date', date('Y-m-d')) }}" required class="w-full rounded border border-slate-300 px-3 py-2">
            <p class="mt-1 text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'الوقت يُسجَّل تلقائياً من النظام عند الحفظ.' : 'Time is recorded automatically from the system on save.' }}</p>
            @error('report_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</label>
            <textarea name="notes" rows="3" class="w-full rounded border border-slate-300 px-3 py-2">{{ old('notes') }}</textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'حفظ وإحالة لمتابعة المرضى' : 'Save & send to follow-up' }}</button>
        <a href="{{ route('non-commitment-reports.index') }}" class="mr-2 text-slate-600 hover:underline">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
    </form>
@endsection
