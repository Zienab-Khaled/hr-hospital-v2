@extends('layouts.app')
@section('title', __('Activity Log'))
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-slate-800">{{ __('Activity Log') }}</h2>
    </div>
    <p class="text-sm text-slate-600 mb-4">{{ app()->getLocale() === 'ar' ? 'جميع الإجراءات التي تمت في النظام (إنشاء، تعديل، حذف، رفع ملفات، إلخ).' : 'All actions performed in the system (create, update, delete, uploads, etc.).' }}</p>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'التاريخ والوقت' : 'Date & Time' }}</th>
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'User' }}</th>
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الإجراء' : 'Action' }}</th>
                        <th class="text-start p-3">{{ app()->getLocale() === 'ar' ? 'الوصف' : 'Description' }}</th>
                        @if(app()->getLocale() === 'ar')
                            <th class="text-start p-3">IP</th>
                        @else
                            <th class="text-start p-3">IP</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="p-3 whitespace-nowrap text-slate-600">
                                {{ $log->created_at->format('Y-m-d') }}<br>
                                <span class="text-xs">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                            <td class="p-3">
                                {{ $log->user?->employee?->name ?? $log->user?->name ?? $log->user?->username ?? '-' }}
                            </td>
                            <td class="p-3">
                                <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                    @if(str_contains($log->action, 'created')) bg-green-100 text-green-800
                                    @elseif(str_contains($log->action, 'updated') || str_contains($log->action, 'uploaded')) bg-blue-100 text-blue-800
                                    @elseif(str_contains($log->action, 'deleted')) bg-red-100 text-red-800
                                    @else bg-slate-100 text-slate-800
                                    @endif">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="p-3 max-w-md">{{ $log->description ?? '-' }}</td>
                            <td class="p-3 text-xs text-slate-500">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد سجلات نشاط حتى الآن.' : 'No activity records yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
