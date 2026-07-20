@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'محضر رفض توقيع' : 'Refusal-to-Sign Report')
@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('non-commitment-reports.index') }}" class="text-sm text-slate-500 hover:text-slate-800">
                    ← {{ app()->getLocale() === 'ar' ? 'العودة للقائمة' : 'Back to list' }}
                </a>
                <h1 class="text-2xl font-black text-slate-900 mt-2">
                    {{ app()->getLocale() === 'ar' ? 'محضر رفض توقيع' : 'Refusal-to-Sign Report' }}
                </h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('non-commitment-reports.print', $report) }}" target="_blank"
                    class="inline-flex items-center gap-2 bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-800">
                    🖨️ {{ app()->getLocale() === 'ar' ? 'طباعة المحضر' : 'Print' }}
                </a>
                @if ($report->invoice)
                    <a href="{{ route('invoices.show', $report->invoice) }}"
                        class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-50">
                        {{ app()->getLocale() === 'ar' ? 'الفاتورة' : 'Invoice' }}
                    </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow border border-slate-100 p-6 space-y-4">
            <div class="flex flex-wrap gap-3 items-center justify-between">
                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-black
                    @if ($report->workflow_status === 'completed') bg-emerald-100 text-emerald-800
                    @elseif ($report->workflow_status === 'pending_manager') bg-violet-100 text-violet-800
                    @elseif ($report->workflow_status === 'pending_accountant') bg-amber-100 text-amber-800
                    @else bg-blue-100 text-blue-800 @endif">
                    {{ $report->workflowStatusLabel() }}
                </span>
                <div class="text-sm text-slate-600 font-bold">
                    {{ app()->getLocale() === 'ar' ? 'التاريخ والوقت (من النظام):' : 'System date/time:' }}
                    {{ $report->reported_at ? western_digits($report->reported_at->format('Y-m-d H:i')) : '—' }}
                </div>
            </div>

            <form method="POST" action="{{ route('non-commitment-reports.update-report-number', $report) }}" class="flex flex-wrap items-end gap-3">
                @csrf
                @method('PATCH')
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-black text-slate-500 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'رقم المحضر (كتابة يدوية)' : 'Report number (manual)' }}
                    </label>
                    <input type="text" name="report_number" value="{{ $report->report_number }}"
                        class="w-full rounded-xl border-slate-200 text-sm font-bold"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب رقم المحضر' : 'Enter report number' }}">
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700">
                    {{ app()->getLocale() === 'ar' ? 'حفظ الرقم' : 'Save number' }}
                </button>
            </form>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-400 font-bold text-xs">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</dt>
                    <dd class="font-black text-slate-900">{{ $report->patient?->fullArabicName() ?? $report->patient?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 font-bold text-xs">{{ app()->getLocale() === 'ar' ? 'رقم الإقامة' : 'ID / Iqama' }}</dt>
                    <dd class="font-black text-slate-900">{{ $report->patient?->identity_value ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 font-bold text-xs">{{ app()->getLocale() === 'ar' ? 'المحصل' : 'Collector' }}</dt>
                    <dd class="font-bold text-slate-800">{{ $report->collector?->name ?? $report->createdByUser?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 font-bold text-xs">{{ app()->getLocale() === 'ar' ? 'الفاتورة' : 'Invoice' }}</dt>
                    <dd class="font-bold text-slate-800">{{ $report->invoice?->invoice_number ?? '—' }}</dd>
                </div>
            </dl>

            <div class="border-t border-slate-100 pt-4">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">
                    {{ app()->getLocale() === 'ar' ? 'مسار الإحالة' : 'Workflow trail' }}
                </h3>
                <ol class="space-y-2 text-sm">
                    <li class="flex justify-between gap-2 {{ $report->collector_id ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                        <span>1. {{ app()->getLocale() === 'ar' ? 'المحصل' : 'Collector' }}</span>
                        <span>{{ $report->collector?->name ?? '—' }} @if($report->reported_at) · {{ western_digits($report->reported_at->format('Y-m-d H:i')) }} @endif</span>
                    </li>
                    <li class="flex justify-between gap-2 {{ $report->follow_up_id ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                        <span>2. {{ app()->getLocale() === 'ar' ? 'متابعة المرضى' : 'Patient follow-up' }}</span>
                        <span>{{ $report->followUpUser?->name ?? '—' }} @if($report->follow_up_at) · {{ western_digits($report->follow_up_at->format('Y-m-d H:i')) }} @endif</span>
                    </li>
                    <li class="flex justify-between gap-2 {{ $report->accountant_id ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                        <span>3. {{ app()->getLocale() === 'ar' ? 'المحاسب' : 'Accountant' }}</span>
                        <span>{{ $report->accountant?->name ?? '—' }} @if($report->accountant_at) · {{ western_digits($report->accountant_at->format('Y-m-d H:i')) }} @endif</span>
                    </li>
                    <li class="flex justify-between gap-2 {{ $report->manager_id ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                        <span>4. {{ app()->getLocale() === 'ar' ? 'المدير' : 'Manager' }}</span>
                        <span>{{ $report->manager?->name ?? '—' }} @if($report->manager_at) · {{ western_digits($report->manager_at->format('Y-m-d H:i')) }} @endif</span>
                    </li>
                </ol>
            </div>

            @if ($report->canAdvance(auth()->user()))
                <form method="POST" action="{{ route('non-commitment-reports.advance', $report) }}"
                    onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'تأكيد وإحالة للمرحلة التالية؟' : 'Confirm and forward to next stage?' }}');">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 bg-emerald-600 text-white text-sm font-black rounded-2xl hover:bg-emerald-700 shadow-lg">
                        @if ($report->workflow_status === 'pending_follow_up')
                            {{ app()->getLocale() === 'ar' ? 'تأكيد وإحالة للمحاسب' : 'Confirm & forward to accountant' }}
                        @elseif ($report->workflow_status === 'pending_accountant')
                            {{ app()->getLocale() === 'ar' ? 'تأكيد وإحالة للمدير' : 'Confirm & forward to manager' }}
                        @else
                            {{ app()->getLocale() === 'ar' ? 'اعتماد المدير (إقفال)' : 'Manager approve (close)' }}
                        @endif
                    </button>
                </form>
            @elseif ($report->workflow_status !== 'completed')
                <div class="py-3 px-4 bg-slate-50 text-slate-500 text-sm font-bold rounded-2xl text-center border border-slate-100">
                    {{ app()->getLocale() === 'ar' ? 'عرض فقط — بانتظار الدور المختص في هذه المرحلة' : 'View only — awaiting the role for this stage' }}
                </div>
            @endif
        </div>
    </div>
@endsection
