@extends('layouts.app')
@section('title', __('Services'))
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ __('Services') }}</h2>
        @can('services.manage')
            <a href="{{ route('services.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة خدمة' : 'Add Service' }}</a>
        @endcan
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $s)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ app()->getLocale() === 'ar' ? ($s->name_ar ?: $s->name) : $s->name }}</td>
                        <td class="p-3">{{ $s->code }}</td>
                        <td class="p-3">{{ number_format($s->default_price, 2) }}</td>
                        <td class="p-3">{{ $s->department?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد خدمات' : 'No services yet' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
