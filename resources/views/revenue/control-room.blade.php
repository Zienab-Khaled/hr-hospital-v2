@extends('layouts.app')

@section('content')
<style>
    .font-cairo { font-family: 'Cairo', sans-serif !important; }
    .premium-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }
    .btn-match {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-match:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .btn-reject {
        background: #ffffff;
        color: #ef4444;
        border: 2px solid #fee2e2;
        transition: all 0.3s ease;
    }
    .btn-reject:hover {
        background: #fef2f2;
        border-color: #fecaca;
        transform: translateY(-2px);
    }
    .btn-ready {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        transition: all 0.3s ease;
    }
    .btn-ready:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    .cycle-section {
        scroll-margin-top: 1.5rem;
    }
</style>

<div class="px-4 sm:px-6 lg:px-8 py-8 bg-slate-50 min-h-screen font-cairo">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">
                {{ app()->getLocale() === 'ar' ? 'غرفة التحكم في دورة الإيراد' : 'Revenue Control Room' }}
            </h1>
            <div class="flex items-center gap-2 text-slate-500 font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ app()->getLocale() === 'ar' ? 'نظام تدقيق العمليات والإيداع المتقدم' : 'Advanced Audit & Deposit System' }}
            </div>
        </div>

        <form action="{{ route('revenue.control-room') }}" method="GET" class="premium-card p-5 rounded-3xl flex flex-wrap items-center gap-6">
            <div class="flex flex-col gap-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ app()->getLocale() === 'ar' ? 'تاريخ العمليات' : 'Operation Date' }}</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="block w-44 rounded-2xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold py-2.5">
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ app()->getLocale() === 'ar' ? 'المناوبة' : 'Shift' }}</label>
                <select name="shift_id" class="block w-44 rounded-2xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold py-2.5">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'كل المناوبات' : 'All Shifts' }}</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" {{ $shiftId == $shift->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? ($shift->name_ar ?? $shift->name) : $shift->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end self-end mb-0.5">
                <button type="submit" class="inline-flex items-center px-6 text-white py-2.5 bg-slate-900 text-sm font-black rounded-2xl hover:bg-indigo-600 transition-all shadow-lg">
                    {{ app()->getLocale() === 'ar' ? 'تحديث البيانات' : 'Sync Data' }}
                    <span class="ml-2">🔄</span>
                </button>
            </div>
        </form>
    </div>

    @if(session('warning'))
        <div class="mb-8 p-4 bg-amber-50 border border-amber-100 text-amber-700 rounded-2xl flex items-center gap-3 shadow-sm font-bold">
            <span class="text-xl">⚠️</span> {{ session('warning') }}
        </div>
    @endif
    @if(session('success'))
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm font-bold">
            <span class="text-xl">✅</span> {{ session('success') }}
        </div>
    @endif

    {{-- ملخص اليوم --}}
    <div class="mb-10 flex flex-wrap gap-4">
        <div class="premium-card rounded-2xl p-5 border-2 border-slate-200 flex-1 min-w-[180px]">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي فواتير اليوم' : 'Today\'s Invoices' }}</p>
            <p class="text-2xl font-black text-slate-800">{{ $controlRoomStats['total_count'] }}</p>
            <p class="text-sm font-bold text-slate-600 mt-1">{{ number_format($controlRoomStats['total_amount'], 2) }} {{ app()->getLocale() === 'ar' ? 'ريال سعودي' : 'SAR' }}</p>
        </div>
        <div class="premium-card rounded-2xl p-5 border-2 border-emerald-200 bg-emerald-50/50 flex-1 min-w-[180px]">
            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-0.5">{{ app()->getLocale() === 'ar' ? 'تم تأكيده (مطابق)' : 'Confirmed (Matched)' }}</p>
            <p class="text-2xl font-black text-emerald-800">{{ $controlRoomStats['matched_count'] }}</p>
            <p class="text-sm font-bold text-emerald-700 mt-1">{{ number_format($controlRoomStats['matched_amount'], 2) }} {{ app()->getLocale() === 'ar' ? 'ريال سعودي' : 'SAR' }}</p>
        </div>
        <div class="premium-card rounded-2xl p-5 border-2 border-red-200 bg-red-50/50 flex-1 min-w-[180px]">
            <p class="text-[10px] font-black text-red-600 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'تم رفضه' : 'Rejected' }}</p>
            <p class="text-2xl font-black text-red-800">{{ $controlRoomStats['rejected_count'] }}</p>
            <p class="text-sm font-bold text-red-700 mt-1">{{ number_format($controlRoomStats['rejected_amount'], 2) }} {{ app()->getLocale() === 'ar' ? 'ريال سعودي' : 'SAR' }}</p>
        </div>
        <div class="premium-card rounded-2xl p-5 border-2 border-amber-200 bg-amber-50/50 flex-1 min-w-[180px]">
            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'قيد المراجعة' : 'Under Review' }}</p>
            <p class="text-2xl font-black text-amber-800">{{ $controlRoomStats['pending_count'] }}</p>
            <p class="text-sm font-bold text-amber-700 mt-1">{{ number_format($controlRoomStats['pending_amount'], 2) }} {{ app()->getLocale() === 'ar' ? 'ريال سعودي' : 'SAR' }}</p>
        </div>
    </div>

    @if(!empty($showFullCycle) && $showFullCycle)
        {{-- دورة الإيراد كاملة عمودياً (مساعد المدير / الإدارة) --}}
        <nav class="sticky top-4 z-20 mb-8 max-w-5xl mx-auto">
            <div class="premium-card rounded-2xl p-3 flex flex-wrap items-center justify-center gap-2 border border-slate-200/80 shadow-lg">
                <a href="#collector-ops" class="px-4 py-2 rounded-xl text-sm font-black bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                    1. {{ app()->getLocale() === 'ar' ? 'عمليات المحصل' : 'Collector' }}
                    <span class="ms-1 opacity-80">({{ $collectorInvoices->count() }})</span>
                </a>
                <span class="text-slate-300 font-black hidden sm:inline">↓</span>
                <a href="#accountant-ops" class="px-4 py-2 rounded-xl text-sm font-black bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                    2. {{ app()->getLocale() === 'ar' ? 'عمليات المحاسب' : 'Accountant' }}
                    <span class="ms-1 opacity-80">({{ $accountantInvoices->count() }})</span>
                </a>
                <span class="text-slate-300 font-black hidden sm:inline">↓</span>
                <a href="#cashier-ops" class="px-4 py-2 rounded-xl text-sm font-black bg-amber-600 text-white hover:bg-amber-700 transition-colors">
                    3. {{ app()->getLocale() === 'ar' ? 'عمليات أمين الصندوق' : 'Cashier' }}
                    <span class="ms-1 opacity-80">({{ $matchedInvoices->count() + $readyForDepositInvoices->count() + $managerConfirmedInvoices->count() + $depositedInvoices->count() }})</span>
                </a>
            </div>
        </nav>

        <div class="space-y-14 max-w-5xl mx-auto">

            {{-- 1) عمليات المحصل --}}
            <section id="collector-ops" class="cycle-section space-y-6 rounded-3xl border-2 border-indigo-100 bg-indigo-50/30 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xl font-black text-slate-800 flex items-center gap-3">
                        <span class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center shadow-inner font-black text-sm">1</span>
                        {{ app()->getLocale() === 'ar' ? 'عمليات المحصل' : 'Collector Operations' }}
                        <span class="px-3 py-1 rounded-full text-xs font-black text-white bg-indigo-600 shadow-md">
                            {{ $collectorInvoices->count() }}
                        </span>
                    </h2>
                </div>
                @forelse($collectorInvoices as $invoice)
                    @include('revenue.partials.control-room-invoice-card', [
                        'invoice' => $invoice,
                        'date' => $date,
                        'showAuditActions' => false,
                        'cardPrefix' => 'collector',
                    ])
                @empty
                    <div class="premium-card rounded-[3rem] p-12 text-center border-2 border-dashed border-slate-200">
                        <p class="text-slate-400 font-black">{{ app()->getLocale() === 'ar' ? 'لا توجد عمليات محصل لهذا التاريخ' : 'No collector operations for this date' }}</p>
                    </div>
                @endforelse
            </section>

            {{-- 2) عمليات المحاسب --}}
            <section id="accountant-ops" class="cycle-section space-y-6 rounded-3xl border-2 border-emerald-100 bg-emerald-50/30 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xl font-black text-slate-800 flex items-center gap-3">
                        <span class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner font-black text-sm">2</span>
                        {{ app()->getLocale() === 'ar' ? 'عمليات المحاسب' : 'Accountant Operations' }}
                        <span class="px-3 py-1 rounded-full text-xs font-black text-white bg-emerald-600 shadow-md">
                            {{ $accountantInvoices->count() }}
                        </span>
                    </h2>
                </div>
                @forelse($accountantInvoices as $invoice)
                    @include('revenue.partials.control-room-invoice-card', [
                        'invoice' => $invoice,
                        'date' => $date,
                        'showAuditActions' => true,
                        'cardPrefix' => 'accountant',
                    ])
                @empty
                    <div class="premium-card rounded-[3rem] p-12 text-center border-2 border-dashed border-slate-200">
                        <p class="text-slate-400 font-black">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير بانتظار المطابقة' : 'No invoices awaiting audit' }}</p>
                    </div>
                @endforelse
            </section>

            {{-- 3) عمليات أمين الصندوق --}}
            <section id="cashier-ops" class="cycle-section space-y-6 rounded-3xl border-2 border-amber-100 bg-amber-50/30 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xl font-black text-slate-800 flex items-center gap-3">
                        <span class="w-10 h-10 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center shadow-inner font-black text-sm">3</span>
                        {{ app()->getLocale() === 'ar' ? 'عمليات أمين الصندوق' : 'Cashier / Treasury Operations' }}
                        <span class="px-3 py-1 rounded-full text-xs font-black text-white bg-amber-600 shadow-md">
                            {{ $matchedInvoices->count() + $readyForDepositInvoices->count() + $managerConfirmedInvoices->count() + $depositedInvoices->count() }}
                        </span>
                    </h2>
                </div>
                @include('revenue.partials.control-room-treasury-section', [
                    'matchedInvoices' => $matchedInvoices,
                    'readyForDepositInvoices' => $readyForDepositInvoices,
                    'managerConfirmedInvoices' => $managerConfirmedInvoices,
                    'depositedInvoices' => $depositedInvoices,
                ])
            </section>
        </div>
    @else
        {{-- عرض المحاسب: قائمة واحدة + ملخص جانبي --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <div class="space-y-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xl font-black text-slate-800 flex items-center gap-3">
                        <span class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center shadow-inner">💻</span>
                        {{ app()->getLocale() === 'ar' ? 'عمليات المحصل' : 'Collector Operations' }}
                        <span class="px-3 py-1 rounded-full text-xs font-black text-white bg-indigo-600 shadow-md">
                            {{ $invoices->count() }}
                        </span>
                    </h2>
                </div>
                @forelse($invoices as $invoice)
                    @include('revenue.partials.control-room-invoice-card', [
                        'invoice' => $invoice,
                        'date' => $date,
                        'showAuditActions' => true,
                        'cardPrefix' => 'invoice',
                    ])
                @empty
                    <div class="premium-card rounded-[3rem] p-16 text-center border-2 border-dashed border-slate-200 bg-transparent">
                        <div class="text-5xl mb-4">📭</div>
                        <p class="text-slate-400 font-black">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير معلقة حالياً' : 'No pending invoices at the moment' }}</p>
                    </div>
                @endforelse
            </div>

            <div class="space-y-6">
                <h2 class="text-xl font-black text-slate-800 flex items-center gap-3">
                    <span class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">🧾</span>
                    {{ app()->getLocale() === 'ar' ? 'تقرير التدقيق الموازي' : 'Parallel Audit Summary' }}
                </h2>
                <div class="premium-card rounded-[3rem] overflow-hidden sticky top-28 border-2 border-white/50">
                    <div class="p-8 bg-slate-900 overflow-hidden relative">
                        <h3 class="font-black text-[10px] tracking-widest uppercase text-indigo-400 mb-6">
                            {{ app()->getLocale() === 'ar' ? 'الملخص المالي للفترة' : 'Financial Period Summary' }}
                        </h3>
                        <div class="grid grid-cols-2 gap-10">
                            <div>
                                <p class="text-[10px] text-white font-black uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي المحصلات' : 'Gross Collected' }}</p>
                                <p class="text-4xl font-black tracking-tighter text-white">{{ number_format($totalCollectedToday, 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-white font-black uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'عدد العمليات' : 'Trans Count' }}</p>
                                <p class="text-4xl font-black text-emerald-400 tracking-tighter">{{ $invoices->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'حالة المطابقة' : 'Match Progress' }}</h4>
                            <span class="text-[10px] font-black text-emerald-500">{{ $invoices->where('audit_status', 'matched')->count() }}/{{ $invoices->count() }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 p-0.5 border border-slate-200">
                            <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full" style="width: {{ $invoices->count() > 0 ? ($invoices->whereIn('audit_status', ['matched','ready_for_deposit','manager_confirmed','deposited'])->count() / $invoices->count() * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function toggleRejectionForm(key) {
        const form = document.getElementById(`rejection-form-${key}`);
        const actions = document.getElementById(`actions-${key}`);
        if (!form || !actions) return;

        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
            actions.classList.add('opacity-30', 'pointer-events-none');
            const card = document.getElementById(`accountant-card-${key.replace('accountant-', '')}`)
                || document.getElementById(`invoice-card-${key.replace('invoice-', '')}`)
                || form.closest('.premium-card');
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            form.classList.add('hidden');
            actions.classList.remove('opacity-30', 'pointer-events-none');
        }
    }
</script>
@endsection
