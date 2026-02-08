@extends('layouts.app')
@section('title', __('Services'))
@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-slate-800">{{ __('Services') }}</h2>
        @can('services.manage')
            <a href="{{ route('services.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+
                {{ app()->getLocale() === 'ar' ? 'إضافة خدمة' : 'Add Service' }}</a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <x-index-filters :action="route('services.index')" :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم الخدمة، الكود...' : 'Service name, code...'">
        <div class="w-40">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                {{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}
            </label>
            <select name="department_id"
                class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' && $dept->name_ar ? $dept->name_ar : $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-index-filters>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</th>
                    @can('services.manage')
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($services as $s)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">
                            <div class="font-medium text-slate-800">
                                {{ app()->getLocale() === 'ar' ? ($s->name_ar ?: $s->name) : $s->name }}</div>
                            @if (!$s->is_active)
                                <span
                                    class="text-xs text-red-500">{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $s->code }}</td>
                        <td class="p-3">{{ number_format($s->default_price, 2) }}</td>
                        <td class="p-3">
                            {{ app()->getLocale() === 'ar' ? ($s->department?->name_ar ?: $s->department?->name) : $s->department?->name ?? '-' }}
                        </td>
                        @can('services.manage')
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('services.show', $s) }}" class="text-blue-600 hover:text-blue-800"
                                        title="{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('services.edit', $s) }}" class="text-green-600 hover:text-green-800"
                                        title="{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('services.destroy', $s) }}" method="POST" class="inline"
                                        onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذه الخدمة؟' : 'Are you sure you want to delete this service?' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800"
                                            title="{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('services.manage') ? '5' : '4' }}"
                            class="p-6 text-center text-slate-500">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد خدمات' : 'No services yet' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($services->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $services->links() }}
            </div>
        @endif
    </div>
@endsection
