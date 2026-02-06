@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إضافة حصر ديون' : 'Add Debt Inventory')
@section('content')
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ app()->getLocale() === 'ar' ? 'إضافة حصر ديون' : 'Add Debt Inventory' }}</h2>
    <form action="{{ route('debt-inventories.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 max-w-xl">
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
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ الحصر' : 'Inventory date' }}</label>
            <input type="date" name="inventory_date" value="{{ old('inventory_date', date('Y-m-d')) }}" required class="w-full rounded border border-slate-300 px-3 py-2">
            @error('inventory_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي الدين (ريال)' : 'Total debt (SAR)' }}</label>
            <input type="number" name="total_debt" value="{{ old('total_debt') }}" step="0.01" min="0" required class="w-full rounded border border-slate-300 px-3 py-2">
            @error('total_debt')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'تفاصيل' : 'Details' }}</label>
            <textarea name="details" rows="3" class="w-full rounded border border-slate-300 px-3 py-2">{{ old('details') }}</textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
        <a href="{{ route('debt-inventories.index') }}" class="mr-2 text-slate-600 hover:underline">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
    </form>
@endsection
