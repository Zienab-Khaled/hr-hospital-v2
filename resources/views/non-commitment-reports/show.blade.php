@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'محضر رفض توقيع' : 'Refusal-to-Sign Report')
@section('content')
    @php
        $user = auth()->user();
        $canAdvance = $report->canAdvance($user);
        $hasESign = ! empty($user?->signature);
    @endphp
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('non-commitment-reports.index') }}" class="text-sm text-slate-500 hover:text-slate-800">
                    ← {{ app()->getLocale() === 'ar' ? 'العودة للقائمة' : 'Back to list' }}
                </a>
                <h1 class="text-2xl font-black text-slate-900 mt-2">
                    {{ app()->getLocale() === 'ar' ? 'محضر رفض توقيع' : 'Refusal-to-Sign Report' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1 font-bold">
                    {{ app()->getLocale() === 'ar' ? 'مثل الفواتير: إرسال ← إشعار ← فتح ← إرسال للمرحلة التالية' : 'Like invoices: send → notify → open → send next' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('non-commitment-reports.print', $report) }}" target="_blank"
                    class="inline-flex items-center gap-2 bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-800">
                    🖨️ {{ app()->getLocale() === 'ar' ? 'طباعة' : 'Print' }}
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

        {{-- زر الإرسال أولاً مثل إجراءات الفاتورة --}}
        @if ($canAdvance)
            <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-5 space-y-3">
                <div class="text-sm font-black text-emerald-900">
                    {{ app()->getLocale() === 'ar' ? 'إجراء مطلوب منك الآن' : 'Action required from you' }}
                </div>
                <p class="text-xs font-bold text-emerald-800">{{ $report->nextRoleNotifyHint() }}</p>
                @if ($hasESign)
                    <div class="flex items-center gap-3 text-xs font-bold text-slate-600">
                        <img src="{{ asset('storage/' . ltrim($user->signature, '/')) }}" alt="توقيع" class="max-h-10 max-w-[120px] object-contain bg-white rounded border px-2 py-1">
                        <span>{{ app()->getLocale() === 'ar' ? 'سيتم تسجيل توقيعك الإلكتروني مع وقت الإرسال من النظام' : 'Your e-signature and system time will be recorded' }}</span>
                    </div>
                @else
                    <p class="text-xs font-bold text-amber-800">
                        {{ app()->getLocale() === 'ar' ? 'ملاحظة: لا توجد صورة توقيع في ملفك — سيُسجَّل اسمك ووقت الإرسال من النظام.' : 'No signature image on file — your name and system time will still be recorded.' }}
                    </p>
                @endif
                <form method="POST" action="{{ route('non-commitment-reports.advance', $report) }}"
                    onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'تأكيد الإرسال للمرحلة التالية؟ سيصلهم إشعار.' : 'Confirm send to next stage? They will get a notification.' }}');">
                    @csrf
                    <button type="submit"
                        class="w-full py-4 bg-emerald-600 text-white text-base font-black rounded-2xl hover:bg-emerald-700 shadow-lg">
                        📤 {{ $report->nextStageLabel() }}
                    </button>
                </form>
            </div>
        @elseif ($report->workflow_status === 'completed')
            <div class="py-4 px-4 bg-emerald-50 text-emerald-800 text-sm font-black rounded-2xl text-center border border-emerald-200">
                {{ app()->getLocale() === 'ar' ? 'اكتمل المسار — وصل للمدير وتم الاعتماد' : 'Completed — reached manager and approved' }}
            </div>
        @else
            <div class="py-4 px-4 bg-amber-50 text-amber-900 text-sm font-bold rounded-2xl text-center border border-amber-200">
                {{ $report->workflowStatusLabel() }} —
                {{ app()->getLocale() === 'ar' ? 'بانتظار الدور المختص (سيصلهم إشعار)' : 'Awaiting the assigned role (they get a notification)' }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow border border-slate-100 p-6 space-y-5">
            <div class="flex flex-wrap gap-3 items-center justify-between">
                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-black
                    @if ($report->workflow_status === 'completed') bg-emerald-100 text-emerald-800
                    @elseif ($report->workflow_status === 'draft') bg-slate-100 text-slate-700
                    @elseif ($report->workflow_status === 'pending_manager') bg-violet-100 text-violet-800
                    @elseif ($report->workflow_status === 'pending_accountant') bg-amber-100 text-amber-800
                    @else bg-blue-100 text-blue-800 @endif">
                    {{ $report->workflowStatusLabel() }}
                </span>
                <div class="text-sm text-slate-600 font-bold">
                    {{ app()->getLocale() === 'ar' ? 'وقت النظام:' : 'System time:' }}
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
                    <dt class="text-slate-400 font-bold text-xs">{{ app()->getLocale() === 'ar' ? 'الفاتورة' : 'Invoice' }}</dt>
                    <dd class="font-bold text-slate-800">{{ $report->invoice?->invoice_number ?? '—' }}</dd>
                </div>
            </dl>

            <div class="border-t border-slate-100 pt-4">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">
                    {{ app()->getLocale() === 'ar' ? 'مسار الإرسال' : 'Send trail' }}
                </h3>
                <div class="space-y-3">
                    @foreach ([
                        ['label' => app()->getLocale() === 'ar' ? '1. المحصل' : '1. Collector', 'user' => $report->collector, 'at' => $report->reported_at, 'done' => (bool) $report->collector_id && $report->workflow_status !== 'draft'],
                        ['label' => app()->getLocale() === 'ar' ? '2. فني متابعة المرضى' : '2. Follow-up', 'user' => $report->followUpUser, 'at' => $report->follow_up_at, 'done' => (bool) $report->follow_up_id],
                        ['label' => app()->getLocale() === 'ar' ? '3. المحاسب' : '3. Accountant', 'user' => $report->accountant, 'at' => $report->accountant_at, 'done' => (bool) $report->accountant_id],
                        ['label' => app()->getLocale() === 'ar' ? '4. المدير' : '4. Manager', 'user' => $report->manager, 'at' => $report->manager_at, 'done' => (bool) $report->manager_id],
                    ] as $step)
                        <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-xl border {{ $step['done'] ? 'border-emerald-200 bg-emerald-50/50' : 'border-slate-100 bg-slate-50' }}">
                            <div>
                                <div class="text-sm font-black {{ $step['done'] ? 'text-emerald-800' : 'text-slate-500' }}">{{ $step['label'] }}</div>
                                <div class="text-xs font-bold text-slate-600 mt-0.5">
                                    {{ $step['user']?->name ?? (app()->getLocale() === 'ar' ? 'بانتظار الإرسال' : 'Awaiting') }}
                                    @if ($step['at'] && $step['done'])
                                        · {{ western_digits($step['at']->format('Y-m-d H:i')) }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-left">
                                @if ($step['done'] && $step['user']?->signature)
                                    <img src="{{ asset('storage/' . ltrim($step['user']->signature, '/')) }}" alt="توقيع"
                                        class="max-h-12 max-w-[140px] object-contain">
                                @elseif ($step['done'])
                                    <span class="text-[10px] font-bold text-emerald-700">{{ app()->getLocale() === 'ar' ? 'تم الإرسال' : 'Sent' }}</span>
                                @else
                                    <span class="text-[10px] font-bold text-slate-400">—</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
