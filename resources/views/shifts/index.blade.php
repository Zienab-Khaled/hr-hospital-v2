@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'إدارة الورديات (الشيفتات)' : 'Shifts Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'الورديات المسجلة' : 'Registered Shifts' }}</h2>
        <p class="text-sm text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'إدارة أوقات العمل والورديات الرسمية في المستشفى' : 'Manage official work times and shifts in the hospital' }}</p>
    </div>
    <a href="{{ route('shifts.create') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        <span>{{ app()->getLocale() === 'ar' ? 'إضافة وردية جديدة' : 'Add New Shift' }}</span>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider text-center">#</th>
                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">{{ app()->getLocale() === 'ar' ? 'الاسم (EN)' : 'Name (EN)' }}</th>
                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider text-center">{{ app()->getLocale() === 'ar' ? 'وقت البدء' : 'Start Time' }}</th>
                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider text-center">{{ app()->getLocale() === 'ar' ? 'وقت النهاية' : 'End Time' }}</th>
                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider text-center">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider text-right">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($shifts as $shift)
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4 text-sm text-slate-600 text-center font-medium">{{ $shift->sort_order }}</td>
                <td class="px-6 py-4 text-sm font-semibold text-slate-800">{{ $shift->name_ar }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $shift->name }}</td>
                <td class="px-6 py-4 text-sm text-slate-600 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                        {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600 text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                        {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    @if($shift->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                            {{ app()->getLocale() === 'ar' ? 'معطل' : 'Inactive' }}
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                    <a href="{{ route('shifts.edit', $shift) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="{{ __('Edit') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('shifts.destroy', $shift) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذه الوردية؟' : 'Are you sure you want to delete this shift?' }}')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="{{ __('Delete') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد ورديات مسجلة حالياً' : 'No shifts registered yet' }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
