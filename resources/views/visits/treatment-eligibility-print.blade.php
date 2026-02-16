@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إحقاق علاج' : 'Treatment Eligibility')
@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-4">{{ app()->getLocale() === 'ar' ? 'إحقاق علاج' : 'Treatment Eligibility' }}</h2>
        <p class="text-slate-600 mb-2">{{ app()->getLocale() === 'ar' ? 'المريض:' : 'Patient:' }} {{ $visit->patient->name ?? '—' }}</p>
        <p class="text-slate-600 mb-2">{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }} {{ $visit->visit_date?->format('Y-m-d') ?? '—' }}</p>
        <p class="text-slate-600 mb-4">{{ app()->getLocale() === 'ar' ? 'القسم:' : 'Department:' }} {{ $visit->department ? (app()->getLocale() === 'ar' && $visit->department->name_ar ? $visit->department->name_ar : $visit->department->name) : '—' }}</p>
        <p class="text-amber-700 font-medium">{{ app()->getLocale() === 'ar' ? '(تفاصيل ونص الطباعة سيتم إضافتها لاحقاً)' : '(Print content and details to be added later)' }}</p>
        <div class="mt-6">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700">
                {{ app()->getLocale() === 'ar' ? 'طباعة' : 'Print' }}
            </button>
            <a href="{{ route('visits.create', ['patient_id' => $visit->patient_id, 'visit_id' => $visit->id]) }}" class="ml-2 px-4 py-2 border border-slate-400 rounded-lg text-slate-700 hover:bg-slate-100">{{ app()->getLocale() === 'ar' ? 'رجوع' : 'Back' }}</a>
        </div>
    </div>
@endsection
