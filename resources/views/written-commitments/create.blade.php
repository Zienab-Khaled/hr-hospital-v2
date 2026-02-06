@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إضافة تمهيد خطي' : 'Add Written Commitment')
@section('content')
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ app()->getLocale() === 'ar' ? 'إضافة تمهيد خطي' : 'Add Written Commitment' }}</h2>
    <form action="{{ route('written-commitments.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 max-w-xl">
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
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المبلغ (ريال)' : 'Amount (SAR)' }}</label>
            <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required class="w-full rounded border border-slate-300 px-3 py-2">
            @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ التعهد' : 'Commitment date' }}</label>
            <input type="date" name="commitment_date" value="{{ old('commitment_date', date('Y-m-d')) }}" required class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
            <select name="status" class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'قيد الانتظار' : 'Pending' }}</option>
                <option value="signed" {{ old('status') === 'signed' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'موقع' : 'Signed' }}</option>
                <option value="fulfilled" {{ old('status') === 'fulfilled' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'منفذ' : 'Fulfilled' }}</option>
                <option value="breached" {{ old('status') === 'breached' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مخالف' : 'Breached' }}</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</label>
            <textarea name="notes" rows="3" class="w-full rounded border border-slate-300 px-3 py-2">{{ old('notes') }}</textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
        <a href="{{ route('written-commitments.index') }}" class="mr-2 text-slate-600 hover:underline">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
    </form>
@endsection
