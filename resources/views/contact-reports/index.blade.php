@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'محاضر الاتصال' : 'Contact Reports')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">
            {{ app()->getLocale() === 'ar' ? 'محاضر الاتصال' : 'Contact Reports' }}</h2>
        @can('procedures.contact_report')
            <a href="{{ route('contact-reports.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+
                {{ app()->getLocale() === 'ar' ? 'إضافة محضر اتصال' : 'Add' }}</a>
        @endcan
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                    <th class="text-start p-3">{{ __('Patients') }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'النتيجة' : 'Result' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                    <tr class="border-b hover:bg-slate-50">
                        <td class="p-3">{{ $r->contact_date->format('Y-m-d') }}</td>
                        <td class="p-3">{{ $r->patient?->name }}</td>
                        <td class="p-3">{{ Str::limit($r->result ?? $r->notes, 40) }}</td>
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
