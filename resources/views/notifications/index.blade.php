@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'الإشعارات' : 'Notifications')

@section('content')
<style>
    .font-cairo { font-family: 'Cairo', sans-serif !important; }
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }
    .notification-item {
        transition: all 0.2s;
        border-left: 4px solid transparent;
    }
    .notification-item.unread {
        background: #fffbeb; /* amber-50 */
        border-right: 4px solid #f59e0b; /* amber-500 */
    }
    .notification-item:hover {
        background: rgba(248, 250, 252, 1);
    }
</style>

<div class="max-w-4xl mx-auto px-4 py-8 font-cairo">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-amber-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <h1 class="text-2xl font-black text-slate-800">{{ app()->getLocale() === 'ar' ? 'مركز الإشعارات' : 'Notification Center' }}</h1>
        </div>

        @if(auth()->user()->unreadNotifications->isNotEmpty())
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm font-bold text-amber-600 hover:text-amber-700 bg-amber-50 px-4 py-2 rounded-xl transition-all">
                    {{ app()->getLocale() === 'ar' ? 'تحديد الكل كمقروء' : 'Mark all as read' }}
                </button>
            </form>
        @endif
    </div>

    <div class="glass-card rounded-3xl overflow-hidden ring-1 ring-slate-100">
        @forelse ($notifications as $notification)
            @php $data = $notification->data; @endphp
            <div class="notification-item p-6 border-b border-slate-50 {{ $notification->read_at ? '' : 'unread' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-grow">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full {{ $notification->read_at ? 'bg-slate-200' : 'bg-amber-500' }}"></span>
                            <h3 class="font-black text-slate-800">{{ $data['title'] ?? 'Universal notification' }}</h3>
                            @if(!$notification->read_at)
                                <span class="bg-amber-100 text-amber-700 text-[9px] px-1.5 py-0.5 rounded font-black uppercase ml-2">NEW</span>
                            @endif
                        </div>
                        <p class="text-slate-600 text-sm mb-3">{{ $data['message'] ?? '' }}</p>
                        <div class="flex items-center gap-3">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                            </span>
                            @if(isset($data['action_url']) && $data['action_url'] !== '#')
                                <a href="{{ route('notifications.read', $notification) }}" class="text-indigo-600 font-black text-xs hover:underline flex items-center gap-1">
                                    {{ app()->getLocale() === 'ar' ? 'عرض التفاصيل' : 'View Details' }}
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        @php
                            $typeClasses = [
                                'info' => 'bg-blue-50 text-blue-600',
                                'success' => 'bg-emerald-50 text-emerald-600',
                                'warning' => 'bg-amber-50 text-amber-600',
                                'danger' => 'bg-rose-50 text-rose-600',
                            ];
                            $typeClass = $typeClasses[$data['type'] ?? 'info'] ?? $typeClasses['info'];
                        @endphp
                        <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase {{ $typeClass }}">
                            {{ $data['type'] ?? 'info' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-16 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد إشعارات حالياً' : 'No notifications yet' }}</h3>
                <p class="text-slate-400 text-sm mt-1">{{ app()->getLocale() === 'ar' ? 'سيظهر هنا أي تنبيهات جديدة تصلك.' : 'New alerts will appear here.' }}</p>
            </div>
        @endforelse

        @if($notifications->hasPages())
            <div class="p-6 bg-slate-50/50 border-t border-slate-50">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
