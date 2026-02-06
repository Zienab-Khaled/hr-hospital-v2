@extends('layouts.app')
@section('title', __('Add Patient'))
@section('content')
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ __('Add Patient') }}</h2>
    <form action="{{ route('patients.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 max-w-xl">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File number' }}</label>
            <input type="text" name="file_number" value="{{ old('file_number') }}" required class="w-full rounded border border-slate-300 px-3 py-2 @error('file_number') border-red-500 @enderror">
            @error('file_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-slate-300 px-3 py-2">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم بالعربية' : 'Name (Arabic)' }}</label>
            <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الهوية' : 'ID number' }}</label>
            <input type="text" name="id_number" value="{{ old('id_number') }}" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment type' }}</label>
            <select name="payment_type" id="payment_type" required class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="cash" {{ old('payment_type', 'cash') === 'cash' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نقدي' : 'Cash' }}</option>
                <option value="insurance" {{ old('payment_type') === 'insurance' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}</option>
                <option value="charity" {{ old('payment_type') === 'charity' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'جمعية خيرية' : 'Charity' }}</option>
            </select>
        </div>
        <div class="mb-4" id="insurance_field">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance company' }}</label>
            <select name="insurance_company_id" class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}</option>
                @foreach($insuranceCompanies as $c)<option value="{{ $c->id }}" {{ old('insurance_company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="mb-4" id="charity_field">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الجمعية الخيرية' : 'Charity entity' }}</label>
            <select name="charity_entity_id" class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر --' : '-- Select --' }}</option>
                @foreach($charityEntities as $e)<option value="{{ $e->id }}" {{ old('charity_entity_id') == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>@endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</label>
            <textarea name="notes" rows="2" class="w-full rounded border border-slate-300 px-3 py-2">{{ old('notes') }}</textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
        <a href="{{ route('patients.index') }}" class="mr-2 text-slate-600 hover:underline">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
    </form>
    <script>
        document.getElementById('payment_type').addEventListener('change', function() {
            var v = this.value;
            document.getElementById('insurance_field').style.display = v === 'insurance' ? 'block' : 'none';
            document.getElementById('charity_field').style.display = v === 'charity' ? 'block' : 'none';
        });
        document.getElementById('payment_type').dispatchEvent(new Event('change'));
    </script>
@endsection
