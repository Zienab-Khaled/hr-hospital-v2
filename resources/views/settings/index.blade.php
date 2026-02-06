@extends('layouts.app')
@section('title', __('Settings'))
@section('content')
    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ __('Settings') }}</h2>
    <form action="{{ route('settings.update') }}" method="POST" class="bg-white rounded-lg shadow p-6 max-w-xl">
        @csrf
        <div class="mb-4">
            <label
                class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم المستشفى' : 'Hospital name' }}</label>
            <input type="text" name="hospital_name" value="{{ old('hospital_name', $hospitalName) }}"
                class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <div class="mb-4">
            <label
                class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اسم المدير (ثابت للتقارير والطباعة)' : 'Manager name (fixed for reports & print)' }}</label>
            <input type="text" name="manager_name" value="{{ old('manager_name', $managerName) }}"
                class="w-full rounded border border-slate-300 px-3 py-2">
        </div>
        <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
    </form>
@endsection
