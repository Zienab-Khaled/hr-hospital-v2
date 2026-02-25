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
    .btn-verify {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-verify:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
</style>

<div class="px-4 sm:px-6 lg:px-8 py-8 bg-slate-50 min-h-screen font-cairo">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">
                {{ app()->getLocale() === 'ar' ? 'صندوق التحصيل' : 'Cashier Deposit Box' }}
            </h1>
            <div class="flex items-center gap-2 text-slate-500 font-bold">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                {{ app()->getLocale() === 'ar' ? 'استلام المبالغ وتوريدها للبنك (بواسطة OTP)' : 'Receive & deposit funds (Via OTP)' }}
            </div>
        </div>

        {{-- Date Filter --}}
        <form action="{{ route('cashier.index') }}" method="GET" class="premium-card p-5 rounded-3xl flex items-center gap-6">
            <div class="flex flex-col gap-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ app()->getLocale() === 'ar' ? 'تاريخ العمليات' : 'Operation Date' }}</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="block w-44 rounded-2xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold py-2.5">
            </div>
            <div class="flex items-end self-end mb-0.5">
                <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-slate-900 text-white text-sm font-black rounded-2xl hover:bg-slate-800 transition-all">
                    {{ app()->getLocale() === 'ar' ? 'تحديث' : 'Sync' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="premium-card p-6 rounded-[2.5rem] bg-indigo-600 text-white relative overflow-hidden">
             <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
             <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي بانتظار الاستلام' : 'Total Pending Receipt' }}</p>
             <p class="text-3xl font-black">{{ number_format($invoices->sum('total_amount'), 2) }} <span class="text-xs opacity-70">SR</span></p>
        </div>
        <div class="premium-card p-6 rounded-[2.5rem] bg-emerald-600 text-white relative overflow-hidden">
             <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
             <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">{{ app()->getLocale() === 'ar' ? 'عدد العمليات الجاهزة' : 'Ready Operations' }}</p>
             <p class="text-3xl font-black">{{ $invoices->count() }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm font-bold animate-in fade-in slide-in-from-top-4">
            <span class="text-xl">✨</span> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-8 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl flex items-center gap-3 shadow-sm font-bold animate-in fade-in slide-in-from-top-4">
            <span class="text-xl">❌</span> {{ session('error') }}
        </div>
    @endif

    {{-- Main List --}}
    <div class="space-y-6 max-w-4xl mx-auto">
        @forelse($invoices as $invoice)
            <div class="premium-card rounded-[2.5rem] p-8 transition-all hover:scale-[1.01] hover:shadow-xl group border-2 border-transparent">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex gap-6 items-center">
                         <div class="w-20 h-20 rounded-3xl bg-indigo-50 flex items-center justify-center text-4xl shadow-inner text-indigo-600">
                            🏦
                         </div>
                         <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[10px] font-black px-3 py-1 rounded-full uppercase bg-indigo-100 text-indigo-700">
                                    {{ $invoice->patient->payment_type_label }}
                                </span>
                                <span class="text-slate-400 text-[10px] font-black tracking-widest">#{{ $invoice->invoice_number }}</span>
                            </div>
                            <h3 class="text-2xl font-black text-slate-900">{{ $invoice->patient->name_ar ?? $invoice->patient->name }}</h3>
                            <div class="flex items-center gap-4 mt-2">
                                <span class="text-xs text-slate-500 font-bold">🏢 {{ $invoice->visit?->shift ? (app()->getLocale() === 'ar' ? ($invoice->visit->shift->name_ar ?? $invoice->visit->shift->name) : $invoice->visit->shift->name) : '—' }}</span>
                                <span class="text-xs text-slate-500 font-bold">💰 {{ number_format($invoice->total_amount, 2) }} SR</span>
                            </div>
                         </div>
                    </div>

                    {{-- Action Form --}}
                    <div class="w-full md:w-auto">
                        <form action="{{ route('cashier.receive', $invoice) }}" method="POST" class="flex flex-col gap-3">
                            @csrf
                            <div class="relative group/input">
                                <input type="text" name="otp" required maxlength="6"
                                    class="w-full md:w-48 text-center text-2xl font-black tracking-[0.5em] rounded-2xl border-2 border-slate-100 bg-slate-50 p-3 focus:border-indigo-500 focus:ring-0 transition-all placeholder:text-slate-300 placeholder:tracking-normal"
                                    placeholder="000 000">
                                <div class="absolute inset-x-0 -bottom-1 h-0.5 bg-indigo-500 scale-x-0 group-focus-within/input:scale-x-100 transition-transform duration-500 rounded-full"></div>
                            </div>
                            <button type="submit" class="btn-verify w-full px-8 py-3.5 text-white text-xs font-black rounded-2xl shadow-xl flex items-center justify-center gap-3">
                                <span>🤝</span> {{ app()->getLocale() === 'ar' ? 'تأكيد الاستلام' : 'Confirm Receipt' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="premium-card rounded-[3rem] p-24 text-center border-2 border-dashed border-slate-200 bg-transparent opacity-60">
                <div class="text-6xl mb-6 grayscale">💰</div>
                <h3 class="text-xl font-black text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير جاهزة للتوريد حالياً' : 'No invoices ready for deposit' }}</h3>
                <p class="text-sm text-slate-300 font-bold mt-2">{{ app()->getLocale() === 'ar' ? 'بانتظار اعتماد المحاسب المالي' : 'Awaiting financial accountant approval' }}</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
