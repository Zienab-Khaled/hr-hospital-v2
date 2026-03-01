@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'محضر عدم تعهد' : 'Non-Commitment Reports')
@section('content')
    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">
            {{ app()->getLocale() === 'ar' ? 'محضر عدم تعهد' : 'Non-Commitment Reports' }}</h2>
        @can('procedures.non_commitment')
            <a href="{{ route('non-commitment-reports.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+
                {{ app()->getLocale() === 'ar' ? 'إضافة محضر' : 'Add' }}</a>
        @endcan
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                    <th class="text-start p-3">{{ __('Patients') }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                    <tr class="border-b hover:bg-slate-50">
                        <td class="p-3">{{ $r->report_date->format('Y-m-d') }}</td>
                        <td class="p-3">{{ $r->patient?->name }}</td>
                        <td class="p-3">{{ Str::limit($r->notes, 50) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-6 text-center text-slate-500">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($reports->hasPages())
            <div class="p-3 border-t">{{ $reports->links() }}</div>
        @endif
    </div>
@endsection
