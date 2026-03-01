@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'مرضى الأقسام' : 'Patients by Department')
@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'مرضى الأقسام' : 'Patients by Department' }}</h2>
            <p class="text-sm text-slate-600 mt-1">{{ app()->getLocale() === 'ar' ? 'اختر القسم لعرض المرضى التابعين له (بمن فيهم من تم تحويلهم من هذا القسم)' : 'Select a department to view its patients (including those transferred from it)' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <ul class="divide-y divide-slate-200">
                @forelse($departments as $dept)
                    <li>
                        <a href="{{ route('patients.by-department', $dept) }}" class="flex items-center justify-between px-4 py-3 hover:bg-slate-50 transition-colors">
                            <span class="font-medium text-slate-800">{{ app()->getLocale() === 'ar' && $dept->name_ar ? $dept->name_ar : $dept->name }}</span>
                            <span class="text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'عرض المرضى' : 'View patients' }} →</span>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد أقسام نشطة' : 'No active departments' }}</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
