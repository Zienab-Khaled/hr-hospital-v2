@extends('layouts.app')
@section('title', __('Users'))
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'الموظفين والصلاحيات' : 'Employees & Permissions' }}</h2>
        @can('users.manage')
            <a href="{{ route('users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">+ {{ app()->getLocale() === 'ar' ? 'إضافة موظف' : 'Add Employee' }}</a>
        @endcan
    </div>
    
    {{-- Search and Filter using Global Component --}}
    <x-index-filters 
        :action="route('users.index')"
        :searchPlaceholder="app()->getLocale() === 'ar' ? 'اسم المستخدم، البريد، الاسم...' : 'Username, email, name...'">
        <div class="w-40">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">
                {{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}
            </label>
            <select name="department_id" class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                @foreach($departments as $dept)
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
                    <th class="text-start p-3">{{ __('Username') }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الدور' : 'Role' }}</th>
                    <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-3">{{ $u->username }}</td>
                        <td class="p-3">{{ $u->employee?->name ?? '-' }}</td>
                        <td class="p-3">{{ $u->employee?->department?->name ?? '-' }}</td>
                        <td class="p-3">{{ $u->getRoleNames()->join(', ') ?: '-' }}</td>
                        <td class="p-3">
                            <div class="flex gap-2">
                                @can('users.manage')
                                    <a href="{{ route('users.show', $u) }}" class="text-blue-600 hover:text-blue-800" title="{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('users.edit', $u) }}" class="text-green-600 hover:text-green-800" title="{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('users.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا الموظف؟' : 'Are you sure you want to delete this employee?' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا يوجد مستخدمون' : 'No users' }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
