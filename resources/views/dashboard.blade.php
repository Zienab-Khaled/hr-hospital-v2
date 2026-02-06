@extends('layouts.app')

@section('title', __('Dashboard'))

@section('tabs')
    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-t bg-blue-600 text-white text-sm font-medium">{{ __("Patient File") }}</a>
    <a href="{{ route('invoices.index') }}" class="px-4 py-2 rounded-t bg-slate-100 text-slate-600 text-sm hover:bg-slate-200">{{ __("Invoices") }}</a>
    <a href="{{ route('authorizations.index') }}" class="px-4 py-2 rounded-t bg-slate-100 text-slate-600 text-sm hover:bg-slate-200">{{ __("Authorizations") }}</a>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <span>📋</span> {{ __("Patients") }}
            </h2>
            @can('patients.create')
                <a href="{{ route('patients.create') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ __("Add Patient") }}</a>
            @endcan
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead><tr class="border-b"><th class="text-start py-2">{{ __("Patients") }}</th><th class="text-start py-2">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment' }}</th></tr></thead>
                    <tbody>
                        @forelse($recentPatients as $p)
                            <tr class="border-b border-slate-100"><td class="py-2">{{ $p->name }}</td><td class="py-2">{{ $p->payment_type }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا يوجد مرضى' : 'No patients yet' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('patients.section.followup') }}" class="mt-3 text-sm text-blue-600 hover:underline">{{ __("View All") }}</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <span>📄</span> {{ app()->getLocale() === 'ar' ? 'تمهيد اتصال للدفع' : 'Payment contact' }}
            </h2>
            @can('procedures.contact_report')
                <a href="{{ route('contact-reports.create') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة محضر اتصال' : 'Add contact report' }}</a>
            @endcan
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead><tr class="border-b"><th class="text-start py-2">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th><th class="text-start py-2">{{ app()->getLocale() === 'ar' ? 'النتيجة' : 'Result' }}</th></tr></thead>
                    <tbody>
                        @forelse($recentContactReports as $r)
                            <tr class="border-b border-slate-100"><td class="py-2">{{ $r->contact_date->format('Y-m-d') }}</td><td class="py-2">{{ Str::limit($r->result ?? $r->notes, 20) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('contact-reports.index') }}" class="mt-3 text-sm text-blue-600 hover:underline">{{ __("View All") }}</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <span>📑</span> {{ app()->getLocale() === 'ar' ? 'تمهيد خطي للدفع' : 'Written commitment' }}
            </h2>
            @can('procedures.written_commitment')
                <a href="{{ route('written-commitments.create') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة تعهد خطي' : 'Add commitment' }}</a>
            @endcan
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead><tr class="border-b"><th class="text-start py-2">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th><th class="text-start py-2">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th></tr></thead>
                    <tbody>
                        @forelse($recentWrittenCommitments as $w)
                            <tr class="border-b border-slate-100"><td class="py-2">@currency($w->amount)</td><td class="py-2">{{ $w->status }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('written-commitments.index') }}" class="mt-3 text-sm text-blue-600 hover:underline">{{ __("View All") }}</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <span>⚠️</span> {{ app()->getLocale() === 'ar' ? 'توقيع محضر عدم التعهد' : 'Non-commitment report' }}
            </h2>
            @can('procedures.non_commitment')
                <a href="{{ route('non-commitment-reports.create') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة محضر عدم تعهد' : 'Add report' }}</a>
            @endcan
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead><tr class="border-b"><th class="text-start py-2">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th><th class="text-start py-2">{{ __("Patients") }}</th></tr></thead>
                    <tbody>
                        @forelse($recentNonCommitmentReports as $n)
                            <tr class="border-b border-slate-100"><td class="py-2">{{ $n->report_date->format('Y-m-d') }}</td><td class="py-2">{{ $n->patient?->name }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('non-commitment-reports.index') }}" class="mt-3 text-sm text-blue-600 hover:underline">{{ __("View All") }}</a>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <span>📊</span> {{ app()->getLocale() === 'ar' ? 'حصر ديون' : 'Debt inventory' }}
            </h2>
            @can('procedures.debt_inventory')
                <a href="{{ route('debt-inventories.create') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'حصر ديون' : 'Inventory' }}</a>
            @endcan
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm text-slate-600">
                    <thead><tr class="border-b"><th class="text-start py-2">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th><th class="text-start py-2">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th></tr></thead>
                    <tbody>
                        @forelse($recentDebtInventories as $d)
                            <tr class="border-b border-slate-100"><td class="py-2">{{ $d->inventory_date->format('Y-m-d') }}</td><td class="py-2">@currency($d->total_debt)</td></tr>
                        @empty
                            <tr><td colspan="2" class="py-4 text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('debt-inventories.index') }}" class="mt-3 text-sm text-blue-600 hover:underline">{{ __("View All") }}</a>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ app()->getLocale() === 'ar' ? 'ملخص مالي' : 'Financial summary' }}</h2>
            <div class="space-y-2 text-sm">
                <p class="flex justify-between"><span>{{ app()->getLocale() === 'ar' ? 'إجمالي الفواتير' : 'Total invoiced' }}</span> <span class="font-medium">@currency($totalInvoiced)</span></p>
                <p class="flex justify-between"><span>{{ app()->getLocale() === 'ar' ? 'المحصل' : 'Collected' }}</span> <span class="font-medium text-green-600">@currency($totalCollected)</span></p>
                <p class="flex justify-between"><span>{{ app()->getLocale() === 'ar' ? 'المتبقي' : 'Remaining' }}</span> <span class="font-medium text-orange-500">@currency($totalRemaining)</span></p>
            </div>
            <p class="mt-2 text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'تأمين / كاش / جمعيات' : 'Insurance / Cash / Charity' }}</p>
            <a href="{{ route('payments.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">{{ __("View Details") }}</a>
        </div>
    </div>
@endsection
