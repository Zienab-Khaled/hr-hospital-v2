@extends('layouts.app')
@section('title', __('Departments'))
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ __('Departments') }}</h2>
        @can('departments.manage')
            <a href="{{ route('departments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة قسم' : 'Add Department' }}</a>
        @endcan
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'عدد الموظفين' : 'Employees' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $d)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ app()->getLocale() === 'ar' ? ($d->name_ar ?: $d->name) : $d->name }}</td>
                        <td class="p-3">{{ $d->code }}</td>
                        <td class="p-3">{{ $d->employees_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
