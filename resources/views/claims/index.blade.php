@extends('layouts.app')
@section('title', __('Claims'))
@section('content')
    <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ app()->getLocale() === 'ar' ? 'مطالبات التأمين والجمعيات' : 'Insurance & Charity Claims' }}</h2>

    <div class="mb-6">
        <h3 class="text-lg font-medium text-slate-700 mb-3">{{ app()->getLocale() === 'ar' ? 'مطالبات التأمين' : 'Insurance Claims' }}</h3>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b"><tr><th class="text-start p-3">{{ __("Patients") }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الشركة' : 'Company' }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'تاريخ الإرسال' : 'Sent' }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved amount' }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th></tr></thead>
                <tbody>
                    @forelse($insuranceClaims as $c)
                        <tr class="border-b hover:bg-slate-50"><td class="p-3">{{ $c->invoice?->patient?->name }}</td><td class="p-3">{{ $c->insuranceCompany?->name }}</td><td class="p-3">{{ $c->sent_date?->format('Y-m-d') }}</td><td class="p-3">@currency($c->approved_amount ?? 0)</td><td class="p-3">{{ $c->status ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مطالبات تأمين' : 'No insurance claims' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($insuranceClaims->hasPages())<div class="p-3 border-t">{{ $insuranceClaims->links() }}</div>@endif
        </div>
    </div>

    <div>
        <h3 class="text-lg font-medium text-slate-700 mb-3">{{ app()->getLocale() === 'ar' ? 'مطالبات الجمعيات الخيرية' : 'Charity Claims' }}</h3>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b"><tr><th class="text-start p-3">{{ __("Patients") }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الجمعية' : 'Entity' }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'تاريخ الإرسال' : 'Sent' }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'المبلغ المعتمد' : 'Approved amount' }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th></tr></thead>
                <tbody>
                    @forelse($charityClaims as $c)
                        <tr class="border-b hover:bg-slate-50"><td class="p-3">{{ $c->invoice?->patient?->name }}</td><td class="p-3">{{ $c->charityEntity?->name_ar ?? $c->charityEntity?->name ?? '—' }}</td><td class="p-3">{{ $c->sent_date?->format('Y-m-d') }}</td><td class="p-3">@currency($c->approved_amount ?? 0)</td><td class="p-3">{{ $c->status ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مطالبات جمعيات' : 'No charity claims' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($charityClaims->hasPages())<div class="p-3 border-t">{{ $charityClaims->links() }}</div>@endif
        </div>
    </div>
@endsection
