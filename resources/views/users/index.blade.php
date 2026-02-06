@extends('layouts.app')
@section('title', __('Users'))
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'المستخدمون والصلاحيات' : 'Users & Permissions' }}</h2>
        @can('users.manage')
            <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة مستخدم' : 'Add User' }}</a>
        @endcan
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-start p-3">{{ __('Username') }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الدور' : 'Role' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ $u->username }}</td>
                        <td class="p-3">{{ $u->employee?->name ?? '-' }}</td>
                        <td class="p-3">{{ $u->employee?->department?->name ?? '-' }}</td>
                        <td class="p-3">{{ $u->getRoleNames()->join(', ') ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا يوجد مستخدمون' : 'No users' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
