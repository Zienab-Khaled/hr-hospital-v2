@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'التمهيد الخطي' : 'Written Commitments')
@section('content')
    @if(session('success'))<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>@endif
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'التمهيد الخطي' : 'Written Commitments' }}</h2>
        @can('procedures.written_commitment')
            <a href="{{ route('written-commitments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة تمهيد خطي' : 'Add' }}</a>
        @endcan
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b"><tr><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th><th class="text-start p-3">{{ __("Patients") }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th><th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th></tr></thead>
            <tbody>
                @forelse($items as $r)
                    <tr class="border-b hover:bg-slate-50"><td class="p-3">{{ $r->commitment_date->format('Y-m-d') }}</td><td class="p-3">{{ $r->patient?->name }}</td><td class="p-3">@currency($r->amount)</td><td class="p-3">{{ $r->status ?? '—' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records' }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($items->hasPages())<div class="p-3 border-t">{{ $items->links() }}</div>@endif
    </div>
@endsection
