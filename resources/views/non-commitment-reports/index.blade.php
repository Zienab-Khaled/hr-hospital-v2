@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'محاضر رفض التوقيع' : 'Refusal-to-Sign Reports')
@section('content')
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">
                {{ app()->getLocale() === 'ar' ? 'محاضر رفض التوقيع' : 'Refusal-to-Sign Reports' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                {{ app()->getLocale() === 'ar' ? 'المسار: محصل → متابعة المرضى → محاسب → مدير' : 'Flow: Collector → Follow-up → Accountant → Manager' }}
            </p>
        </div>
        @can('procedures.non_commitment')
            <a href="{{ route('non-commitment-reports.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+
                {{ app()->getLocale() === 'ar' ? 'إضافة محضر' : 'Add' }}</a>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'رقم المحضر' : 'Report #' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'التاريخ / الوقت' : 'Date / Time' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الفاتورة' : 'Invoice' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'إجراء' : 'Action' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                    <tr class="border-b hover:bg-slate-50">
                        <td class="p-3 font-bold">{{ $r->report_number ?: '—' }}</td>
                        <td class="p-3">
                            @if ($r->reported_at)
                                {{ western_digits($r->reported_at->format('Y-m-d H:i')) }}
                            @elseif ($r->report_date)
                                {{ $r->report_date->format('Y-m-d') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-3">{{ $r->patient?->fullArabicName() ?? $r->patient?->name ?? '—' }}</td>
                        <td class="p-3">
                            @if ($r->invoice)
                                <a href="{{ route('invoices.show', $r->invoice) }}" class="text-indigo-600 hover:underline font-bold">
                                    {{ $r->invoice->invoice_number }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="inline-flex px-2 py-1 rounded-full text-[11px] font-bold
                                @if ($r->workflow_status === 'completed') bg-emerald-100 text-emerald-800
                                @elseif ($r->workflow_status === 'draft') bg-slate-100 text-slate-700
                                @elseif ($r->workflow_status === 'pending_manager') bg-violet-100 text-violet-800
                                @elseif ($r->workflow_status === 'pending_accountant') bg-amber-100 text-amber-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ $r->workflowStatusLabel() }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('non-commitment-reports.show', $r) }}"
                                    class="text-blue-600 hover:underline font-semibold">
                                    {{ app()->getLocale() === 'ar' ? 'فتح' : 'Open' }}
                                </a>
                                @if ($r->canAdvance(auth()->user()))
                                    <form method="POST" action="{{ route('non-commitment-reports.advance', $r) }}" class="inline"
                                        onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'تأكيد الإرسال؟' : 'Confirm send?' }}');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-black hover:bg-emerald-700">
                                            📤 {{ $r->nextStageLabel() }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-500">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد محاضر في مرحلتك الحالية' : 'No reports in your current queue' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($reports->hasPages())
            <div class="p-3 border-t">{{ $reports->links() }}</div>
        @endif
    </div>
@endsection
