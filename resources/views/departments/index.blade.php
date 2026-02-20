@extends('layouts.app')
@section('title', __('Departments'))
@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-slate-800">{{ __('Departments') }}</h2>
        @can('departments.manage')
            <a href="{{ route('departments.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+
                {{ app()->getLocale() === 'ar' ? 'إضافة قسم' : 'Add Department' }}</a>
        @endcan
    </div>

    {{-- Search and Filter --}}
    <x-index-filters :action="route('departments.index')" :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم القسم، الكود...' : 'Department name, code...'">
    </x-index-filters>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>

                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'مدير القسم' : 'Manager' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'عدد الموظفين' : 'Employees' }}</th>
                    @can('departments.manage')
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @foreach ($departments as $d)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ app()->getLocale() === 'ar' ? ($d->name_ar ?: $d->name) : $d->name }}</td>

                        <td class="p-3">
                            @if($d->manager)
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-800">{{ $d->manager->name }}</span>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $d->users_count }}</td>
                        @can('departments.manage')
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('departments.show', $d) }}" class="text-blue-600 hover:text-blue-800"
                                        title="{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('departments.edit', $d) }}" class="text-green-600 hover:text-green-800"
                                        title="{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('departments.destroy', $d) }}" method="POST" class="inline"
                                        onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا القسم؟' : 'Are you sure you want to delete this department?' }}')">
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
                @endforeach
            </tbody>
        </table>
        @if ($departments->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $departments->links() }}
            </div>
        @endif
    </div>
@endsection
