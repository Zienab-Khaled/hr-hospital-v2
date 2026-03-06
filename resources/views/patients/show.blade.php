@extends('layouts.app')

@section('title', (app()->getLocale() === 'ar' ? 'ملف المريض' : 'Patient Profile') . ' - ' . ($patient->name_ar ?? $patient->name))

@section('content')
<style>
    .font-cairo { font-family: 'Cairo', sans-serif !important; }
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .hover-row:hover {
        background-color: rgba(248, 250, 252, 0.8) !important;
    }
</style>

<div class="max-w-6xl mx-auto px-4 py-8 font-cairo">
    {{-- Header Action Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-indigo-100">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-800">{{ $patient->name_ar ?? $patient->name }}</h1>
                <div class="flex items-center gap-3 mt-1 text-slate-500 font-bold">
                    <span>#{{ $patient->file_number }}</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                    <span>{{ ucfirst($patient->payment_type) }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @can('invoices.create')
                <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}"
                    class="flex items-center gap-2 px-5 py-3 bg-emerald-600 text-white rounded-xl font-black text-sm hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'تسجيل زيارة' : 'Register Visit' }}
                </a>
                <a href="{{ route('invoices.create', ['patient_id' => $patient->id]) }}"
                    class="flex items-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-xl font-black text-sm hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'تقديم خدمة' : 'Add Service' }}
                </a>
            @endcan
            @can('patients.edit')
                <a href="{{ route('patients.edit', $patient) }}"
                    class="flex items-center gap-2 px-5 py-3 bg-white text-violet-600 border border-violet-100 rounded-xl font-black text-sm hover:bg-violet-50 transition-all shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ app()->getLocale() === 'ar' ? 'تعديل الملف' : 'Edit Profile' }}
                </a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="mb-8 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 font-bold flex items-center gap-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Left: Details --}}
        <div class="lg:col-span-4 space-y-8">
            {{-- Identity Card --}}
            <div class="glass-card rounded-3xl p-6 ring-1 ring-slate-100">
                <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                    <div class="w-2 h-6 bg-indigo-500 rounded-full"></div>
                    {{ app()->getLocale() === 'ar' ? 'بيانات الهوية' : 'Identity Info' }}
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                        <span class="text-sm text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'الرقم الضريبي' : 'Identity Value' }}</span>
                        <span class="text-sm font-black text-slate-700">{{ $patient->identity_value }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                        <span class="text-sm text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'نوع الهوية' : 'Identity Type' }}</span>
                        <span class="text-sm font-black text-slate-700">{{ $patient->identity_type_label ?? $patient->identity_type }}</span>
                    </div>
                </div>
            </div>

            {{-- Personal Details Card --}}
            <div class="glass-card rounded-3xl p-6 ring-1 ring-slate-100">
                <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                    <div class="w-2 h-6 bg-blue-500 rounded-full"></div>
                    {{ app()->getLocale() === 'ar' ? 'المعلومات الشخصية' : 'Personal Details' }}
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                        <span class="text-sm text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'العمر' : 'Age' }}</span>
                        <span class="text-sm font-black text-slate-700">{{ $patient->age ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                        <span class="text-sm text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'الجنس' : 'Gender' }}</span>
                        <span class="text-sm font-black text-slate-700">
                            @if ($patient->gender === 'male') {{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }}
                            @elseif($patient->gender === 'female') {{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }}
                            @else — @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                        <span class="text-sm text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</span>
                        <span class="text-sm font-black text-slate-700" dir="ltr">{{ $patient->phone ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                        <span class="text-sm text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'القسم الحالي' : 'Department' }}</span>
                        <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-black">
                            {{ $patient->department ? ($patient->department->name_ar ?? $patient->department->name) : '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Insurance / Charity --}}
            @if($patient->payment_type !== 'cash')
            <div class="glass-card rounded-3xl p-6 ring-1 ring-slate-100 border-t-4 border-indigo-500">
                <h3 class="text-lg font-black text-slate-800 mb-4">
                    {{ $patient->payment_type === 'charity' ? (app()->getLocale() === 'ar' ? 'الجمعية الخيرية' : 'Charity') : (app()->getLocale() === 'ar' ? 'شركة التأمين' : 'Insurance') }}
                </h3>
                <div class="bg-indigo-50/50 p-4 rounded-2xl flex items-center gap-4">
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-indigo-600">
                        @if($patient->payment_type === 'charity')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        @endif
                    </div>
                    <p class="font-black text-slate-800">
                        {{ $patient->payment_type === 'charity' ? ($patient->charityEntity?->name_ar ?? $patient->charityEntity?->name ?? '—') : ($patient->insuranceCompany?->name_ar ?? $patient->insuranceCompany?->name ?? '—') }}
                    </p>
                </div>
            </div>
            @endif

            {{-- أين توجه المريض: أقسام تم تنفيذ خدمات فيها (عيادات، أشعة، مختبر، مركز أورام، ...) --}}
            @if(isset($completedDepartments) && $completedDepartments->isNotEmpty())
            <div class="glass-card rounded-3xl p-6 ring-1 ring-slate-100 border-t-4 border-emerald-500">
                <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                    <span class="text-emerald-600">📍</span>
                    {{ app()->getLocale() === 'ar' ? 'توجه المريض (أقسام تم تنفيذ خدمات فيها)' : 'Where patient received services' }}
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($completedDepartments as $dept)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm font-bold">
                            {{ app()->getLocale() === 'ar' ? ($dept->name_ar ?? $dept->name) : $dept->name }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($patient->notes)
            <div class="glass-card rounded-3xl p-6 ring-1 ring-slate-100">
                <span class="text-xs text-slate-400 font-black uppercase tracking-widest block mb-2">{{ app()->getLocale() === 'ar' ? 'ملاحظات خلفية' : 'Background Notes' }}</span>
                <p class="text-slate-600 text-sm leading-relaxed">{{ $patient->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Right: Activities --}}
        <div class="lg:col-span-8 space-y-8 mt-1.5">
            {{-- Visits List --}}
            <div class="glass-card rounded-3xl overflow-hidden ring-1 ring-slate-100">
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="text-xl font-black text-slate-800 flex items-center gap-2">
                        <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                        {{ app()->getLocale() === 'ar' ? 'سجل الزيارات' : 'Visit History' }}
                    </h3>
                    <span class="bg-indigo-50 text-indigo-600 px-4 py-1.5 rounded-xl font-black text-sm">{{ $visits->total() }}</span>
                </div>

                @if($visits->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'لا توجد زيارات مسجلة' : 'No visits recorded yet' }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400">
                                    <th class="px-6 py-4 text-left {{ app()->getLocale() === 'ar' ? 'text-right' : '' }} font-black">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                                    <th class="px-6 py-4 text-left {{ app()->getLocale() === 'ar' ? 'text-right' : '' }} font-black">{{ app()->getLocale() === 'ar' ? 'القسم الطبي' : 'Medical Dept' }}</th>
                                    <th class="px-6 py-4 text-left {{ app()->getLocale() === 'ar' ? 'text-right' : '' }} font-black">{{ app()->getLocale() === 'ar' ? 'نوع الحالة' : 'Case' }}</th>
                                    <th class="px-6 py-4 text-center font-black">{{ app()->getLocale() === 'ar' ? 'الإجراء' : 'Action' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($visits as $visit)
                                <tr class="hover-row transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-700">
                                        {{ $visit->visit_date?->format('Y-m-d') ?? $visit->created_at->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-medium">
                                        {{ $visit->department ? (app()->getLocale() === 'ar' ? ($visit->department->name_ar ?? $visit->department->name) : $visit->department->name) : '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($visit->case_type === 'emergency')
                                            <span class="text-rose-600 font-black">{{ app()->getLocale() === 'ar' ? '🚨 طوارئ' : '🚨 Emergency' }}</span>
                                        @else
                                            <span class="text-emerald-600 font-bold">{{ app()->getLocale() === 'ar' ? '🏥 عيادات' : '🏥 Clinics' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('visits.show', $visit) }}"
                                            title="{{ app()->getLocale() === 'ar' ? 'عرض تفاصيل الزيارة' : 'View visit details' }}"
                                            class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg font-black text-xs hover:bg-indigo-600 hover:text-white transition-all">
                                            <span>{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        @can('invoices.create')
                                            <a href="{{ route('visits.create', ['patient_id' => $visit->patient_id, 'visit_id' => $visit->id, 'registered' => 1]) }}"
                                                title="{{ app()->getLocale() === 'ar' ? 'فتح شاشة الخدمات والفاتورة' : 'Open services screen' }}"
                                                class="inline-flex items-center gap-1 ms-1 px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg font-bold text-xs hover:bg-emerald-600 hover:text-white transition-all">
                                                {{ app()->getLocale() === 'ar' ? 'خدمات' : 'Services' }}
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50">
                        {{ $visits->links() }}
                    </div>
                @endif
            </div>

            {{-- Invoices List --}}
            <div class="glass-card rounded-3xl overflow-hidden ring-1 ring-slate-100">
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="text-xl font-black text-slate-800 flex items-center gap-2">
                        <div class="w-2 h-6 bg-blue-500 rounded-full"></div>
                        {{ app()->getLocale() === 'ar' ? 'سجل الفواتير' : 'Invoice History' }}
                    </h3>
                    <span class="bg-blue-50 text-blue-600 px-4 py-1.5 rounded-xl font-black text-sm">{{ $invoices->total() }}</span>
                </div>

                @if($invoices->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير مسجلة' : 'No invoices recorded yet' }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400">
                                    <th class="px-6 py-4 text-left {{ app()->getLocale() === 'ar' ? 'text-right' : '' }} font-black">{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة' : 'Invoice #' }}</th>
                                    <th class="px-6 py-4 text-left {{ app()->getLocale() === 'ar' ? 'text-right' : '' }} font-black">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                                    <th class="px-6 py-4 text-left {{ app()->getLocale() === 'ar' ? 'text-right' : '' }} font-black">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                                    <th class="px-6 py-4 text-center font-black">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                                    <th class="px-6 py-4 text-center font-black">{{ app()->getLocale() === 'ar' ? 'الإجراء' : 'Action' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($invoices as $invoice)
                                <tr class="hover-row transition-colors">
                                    <td class="px-6 py-4 font-black text-slate-700">#{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 text-slate-500 font-bold">{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4 font-black text-slate-900">{{ number_format($invoice->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="status-badge {{ $invoice->status === 'paid' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                            {{ app()->getLocale() === 'ar' ? ($invoice->status === 'paid' ? 'تم السداد' : 'بانتظار') : ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-blue-50 text-blue-600 rounded-lg font-black text-xs hover:bg-blue-600 hover:text-white transition-all">
                                            <span>{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
