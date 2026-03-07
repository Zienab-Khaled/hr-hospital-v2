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

        {{-- Filters --}}
        <form action="{{ route('revenue.control-room') }}" method="GET" class="premium-card p-5 rounded-3xl flex flex-wrap items-center gap-6">
            <div class="flex flex-col gap-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ app()->getLocale() === 'ar' ? 'تاريخ العمليات' : 'Operation Date' }}</label>
                <div class="relative">
                    <input type="date" name="date" value="{{ $date }}"
                        class="block w-44 rounded-2xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold py-2.5">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ app()->getLocale() === 'ar' ? 'الوردية' : 'Shift' }}</label>
                <select name="shift_id" class="block w-44 rounded-2xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold py-2.5">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'كل الشيفتات' : 'All Shifts' }}</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" {{ $shiftId == $shift->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? ($shift->name_ar ?? $shift->name) : $shift->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end self-end mb-0.5">
                <button type="submit" class="inline-flex items-center px-6 text-white py-2.5 bg-slate-900  text-sm font-black rounded-2xl hover:bg-indigo-600 transition-all shadow-lg hover:shadow-indigo-200 group">
                    {{ app()->getLocale() === 'ar' ? 'تحديث البيانات' : 'Sync Data' }}
                    <span class="ml-2 group-hover:rotate-180 transition-transform duration-500">🔄</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Alert Messages --}}
    @if(session('warning'))
        <div class="mb-8 p-4 bg-amber-50 border border-amber-100 text-amber-700 rounded-2xl flex items-center gap-3 shadow-sm font-bold">
            <span class="text-xl">⚠️</span> {{ session('warning') }}
        </div>
    @endif

    {{-- ملخص اليوم — جنب بعض (مرتبط بفلتر التاريخ والوردية) --}}
    <div class="mb-10 flex flex-wrap gap-4">
        <div class="premium-card rounded-2xl p-5 border-2 border-slate-200 flex-1 min-w-[180px]">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي فواتير اليوم' : 'Today\'s Invoices' }}</p>
            <p class="text-2xl font-black text-slate-800">{{ $controlRoomStats['total_count'] }}</p>
            <p class="text-sm font-bold text-slate-600 mt-1">{{ number_format($controlRoomStats['total_amount'], 2) }} {{ app()->getLocale() === 'ar' ? 'ريال سعودي' : 'SAR' }}</p>
        </div>
        <div class="premium-card rounded-2xl p-5 border-2 border-emerald-200 bg-emerald-50/50 flex-1 min-w-[180px]">
            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-0.5">{{ app()->getLocale() === 'ar' ? 'تم تأكيده (مطابق)' : 'Confirmed (Matched)' }}</p>
            {{-- <p class="text-[9px] text-emerald-600/80 font-bold mb-1">{{ app()->getLocale() === 'ar' ? 'اللي اتبعتت لأمين الصندوق' : 'Sent to treasury' }}</p> --}}
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

    {{-- Split Screen Container --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

        {{-- Right Side: Collector Operations --}}
        <div class="space-y-6">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xl font-black text-slate-800 flex items-center gap-3">
                    <span class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center shadow-inner">💻</span>
                    {{ app()->getLocale() === 'ar' ? 'عمليات المحصل' : 'Collector Operations' }}
                    <span class="px-3 py-1 rounded-full text-xs font-black text-white bg-indigo-600  shadow-md">
                        {{ $invoices->count() }}
                    </span>
                </h2>
            </div>

            @forelse($invoices as $invoice)
                <div class="premium-card rounded-[2.5rem] p-6 transition-all hover:scale-[1.01] hover:shadow-xl group border-2 {{ $invoice->audit_status === 'rejected' ? 'border-red-100 bg-red-50/30' : 'border-transparent' }}" id="invoice-card-{{ $invoice->id }}">
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex gap-4">
                             <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center text-2xl shadow-inner border border-white">
                                {{ $invoice->patient->gender === 'female' ? '👩' : '👨' }}
                             </div>
                             <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="text-[10px] font-black px-3 py-1 rounded-full uppercase {{ $invoice->patient->payment_type === 'cash' ? 'bg-emerald-100 text-emerald-700' : ($invoice->patient->payment_type === 'insurance' ? 'bg-amber-100 text-amber-700' : 'bg-purple-100 text-purple-700') }}">
                                        {{ $invoice->patient->payment_type_label }}
                                    </span>
                                    <span class="text-slate-400 text-[10px] font-black tracking-widest">INV-{{ $invoice->invoice_number }}</span>
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                       target="_blank"
                                       class="bg-blue-600  text-[10px] font-black px-2 text-white py-0.5 rounded-full hover:bg-blue-700 transition-colors uppercase">
                                        {{ app()->getLocale() === 'ar' ? 'عرض الفاتورة' : 'View Invoice' }}
                                    </a>
                                </div>
                                <h3 class="text-xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $invoice->patient->name_ar ?? $invoice->patient->name }}</h3>
                                <div class="flex items-center gap-3 mt-1.5">
                                    <span class="text-xs text-slate-500 font-bold flex items-center gap-1">
                                        <span class="opacity-50">🕒</span> {{ $invoice->created_at->format('H:i') }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-bold flex items-center gap-1">
                                        <span class="opacity-50">🏢</span> {{ $invoice->visit?->shift ? (app()->getLocale() === 'ar' ? ($invoice->visit->shift->name_ar ?? $invoice->visit->shift->name) : $invoice->visit->shift->name) : '—' }}
                                    </span>
                                </div>
                             </div>
                        </div>
                        <div class="text-right">
                            @php
                                $totalCollectedOnDate = $invoice->payments->filter(fn($p) => $p->received_date?->isSameDay($date))->sum('amount');
                            @endphp
                            <div class="text-2xl font-black text-indigo-600 tracking-tight">{{ number_format($totalCollectedOnDate, 2) }} <span class="text-xs text-slate-400 font-medium">SR</span></div>
                            <div class="text-[10px] text-slate-500 font-bold mt-1">
                                {{ app()->getLocale() === 'ar' ? 'إجمالي الفاتورة:' : 'Invoice Total:' }} {{ number_format($invoice->total_amount, 2) }}
                                @if($invoice->remaining_amount > 0)
                                    | {{ app()->getLocale() === 'ar' ? 'المتبقي:' : 'Remaining:' }} {{ number_format($invoice->remaining_amount, 2) }}
                                @endif
                            </div>
                                <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase @if($invoice->audit_status === 'under_review') bg-amber-100 text-amber-700 @elseif($invoice->audit_status === 'matched') bg-blue-100 text-blue-700 @elseif($invoice->audit_status === 'rejected') bg-red-100 text-red-700 @elseif($invoice->audit_status === 'ready_for_deposit') bg-amber-100 text-amber-700 @elseif($invoice->audit_status === 'manager_confirmed') bg-violet-100 text-violet-700 @elseif($invoice->audit_status === 'deposited') bg-emerald-100 text-emerald-700 @else bg-slate-100 text-slate-700 @endif">
                                    <span class="w-1.5 h-1.5 rounded-full @if($invoice->audit_status === 'under_review') bg-amber-500 @elseif($invoice->audit_status === 'matched') bg-blue-500 @elseif($invoice->audit_status === 'rejected') bg-red-500 @elseif($invoice->audit_status === 'ready_for_deposit') bg-amber-500 @elseif($invoice->audit_status === 'manager_confirmed') bg-violet-500 @elseif($invoice->audit_status === 'deposited') bg-emerald-500 @else bg-slate-500 @endif"></span>
                                    {{ $invoice->status_label }}
                                </div>
                                {{-- Audit Documents --}}
                                @php $fileLinks = $invoice->getAllRelatedMediaUrls(); @endphp
                                @if(!empty($fileLinks))
                                    <div class="mt-2 flex justify-end flex-wrap gap-2">
                                        @foreach($fileLinks as $name => $url)
                                            <a href="{{ $url }}" target="_blank"
                                               class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] shadow-sm border border-indigo-100 hover:bg-indigo-100 font-bold transition-all"
                                               title="{{ $name }}">
                                               @if(str_contains($name, 'Receipt')) 🧾 @elseif(str_contains($name, 'Approval')) 💎 @else 📄 @endif
                                               {{ Str::limit($name, 12) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    {{-- Today's Payments Details --}}
                    @php
                        $todayPayments = $invoice->payments->filter(fn($p) => $p->received_date?->isSameDay($date));
                    @endphp
                    @if($todayPayments->isNotEmpty())
                        <div class="mb-6 p-4 bg-emerald-50/50 rounded-3xl border border-emerald-100">
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-3">💰 {{ app()->getLocale() === 'ar' ? 'المبالغ المحصلة بتاريخ اليوم' : 'Payments Collected Today' }}</p>
                            @foreach($todayPayments as $payment)
                                <div class="flex justify-between items-center text-sm py-1 border-b border-emerald-100/50 last:border-0">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-white px-2 py-0.5 rounded-full {{ $payment->payment_type === 'charity' ? 'bg-purple-600' : ($payment->payment_type === 'insurance' ? 'bg-amber-600' : 'bg-emerald-600') }}">{{ $payment->payment_type_label }}</span>
                                            <span class="text-slate-600 text-xs">{{ $payment?->receipt?->receipt_number }}</span>
                                        </div>
                                        @if($payment->receipt && !empty($payment->receipt->selected_items))
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($payment->receipt->selected_items as $si)
                                                    <span class="text-[9px] bg-white border border-emerald-200 text-emerald-700 px-1.5 py-0.5 rounded-md font-bold">
                                                        {{ $si['name'] }} ({{ $si['qty'] }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-emerald-700 font-black">@currency($payment->amount)</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($invoice->rejection_reason)
                        <div class="mb-6 p-4 bg-red-50 text-red-700 text-xs rounded-2xl border border-red-100 font-bold relative overflow-hidden group/reason">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-red-100/30 rounded-full -mr-12 -mt-12 transition-transform group-hover/reason:scale-150"></div>
                            <div class="relative z-10 flex gap-3">
                                <span class="text-lg">🚫</span>
                                <div>
                                    <div class="uppercase text-[10px] opacity-70 mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظة الرفض السابقة' : 'Previous Rejection Note' }}</div>
                                    <div class="leading-relaxed">{{ $invoice->rejection_reason }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Local Rejection Form (Inline) --}}
                    <div id="rejection-form-{{ $invoice->id }}" class="hidden mb-6 p-6 bg-red-50 rounded-3xl border-2 border-red-200 animate-in fade-in slide-in-from-top-4 duration-300">
                        <form action="{{ route('revenue.invoices.reject', $invoice) }}" method="POST">
                            @csrf
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-black text-red-800">{{ app()->getLocale() === 'ar' ? 'كتابة أسباب الرفض' : 'Write Rejection Reasons' }}</h4>
                                <button type="button" onclick="toggleRejectionForm('{{ $invoice->id }}')" class="text-red-400 hover:text-red-600 transition-colors text-xl font-bold">&times;</button>
                            </div>
                            <textarea name="rejection_reason" required rows="3"
                                class="block w-full rounded-2xl border-red-200 bg-white shadow-sm focus:border-red-500 focus:ring-red-500 text-sm p-4 mb-4 font-bold"
                                placeholder="{{ app()->getLocale() === 'ar' ? 'وضح للمحصل ما يجب تعديله هنا...' : 'Explain what needs correction here...' }}"></textarea>
                            <div class="flex gap-3">
                                <button type="submit"  class="flex-1 py-3 bg-red-600 text-xs font-black rounded-2xl hover:bg-red-700 transition-all shadow-lg hover:shadow-red-200 uppercase">
                                    {{ app()->getLocale() === 'ar' ? 'تأكيد الرفض' : 'Confirm Rejection' }}
                                </button>
                                <button type="button" onclick="toggleRejectionForm('{{ $invoice->id }}')" class="px-6 py-3 bg-white text-slate-500 text-xs font-black rounded-2xl hover:bg-slate-100 transition-all border border-slate-200">
                                    {{ app()->getLocale() === 'ar' ? 'تراجع' : 'Cancel' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Main Actions --}}
                    <div class="flex items-center gap-4 pt-6 border-t border-slate-100" id="actions-{{ $invoice->id }}">
                        @if($invoice->audit_status === 'under_review' || $invoice->audit_status === 'rejected')
                            <form action="{{ route('revenue.invoices.match', $invoice) }}" method="POST" class="flex-[2]">
                                @csrf
                                <button type="submit" class="btn-match w-full inline-flex items-center justify-center px-6 py-4 text-white text-xs font-black rounded-2xl shadow-xl gap-3">
                                    <span>✅</span> {{ app()->getLocale() === 'ar' ? 'مطابقة (صحيح)' : 'Match (Verified)' }}
                                </button>
                            </form>

                            <button type="button" onclick="toggleRejectionForm('{{ $invoice->id }}')" class="btn-reject flex-1 inline-flex items-center justify-center px-6 py-4 text-xs font-black rounded-2xl gap-3">
                                <span>❌</span> {{ app()->getLocale() === 'ar' ? 'رفض' : 'Reject' }}
                            </button>
                        @elseif($invoice->audit_status === 'matched')
                            <div class="w-full py-4 bg-blue-50 text-blue-700 text-xs font-black rounded-2xl text-center border-2 border-blue-100 shadow-inner flex flex-col items-center justify-center gap-3">
                                <span>📤</span>
                                {{ app()->getLocale() === 'ar' ? 'تم التحويل لأمين الصندوق' : 'Transferred to Treasury' }}
                                <a href="{{ route('revenue.treasury.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                                    {{ app()->getLocale() === 'ar' ? 'عرض تبويب أمين الصندوق' : 'Open Treasury' }}
                                </a>
                            </div>
                        @elseif($invoice->audit_status === 'ready_for_deposit')
                            <div class="w-full flex flex-col gap-3">
                                <p class="text-amber-700 text-xs font-black text-center">{{ app()->getLocale() === 'ar' ? 'أمين الصندوق سجّل جاهزية الإيداع. يرجى التأكيد أن الاستلام تم.' : 'Cashier marked ready for deposit. Please confirm receipt.' }}</p>
                                <form action="{{ route('revenue.invoices.manager-confirmed', $invoice) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-4 bg-violet-600 text-white text-xs font-black rounded-2xl shadow-xl hover:bg-violet-700 flex items-center justify-center gap-2">
                                        <span>✓</span> {{ app()->getLocale() === 'ar' ? 'تأكيد من المدير (أمين الصندوق استلم)' : 'Manager Confirm (Cashier Received)' }}
                                    </button>
                                </form>
                            </div>
                        @elseif($invoice->audit_status === 'manager_confirmed')
                            <div class="w-full py-4 bg-violet-50 text-violet-700 text-xs font-black rounded-2xl text-center border-2 border-violet-100 shadow-inner flex flex-col items-center justify-center gap-3">
                                <span>✓</span>
                                {{ app()->getLocale() === 'ar' ? 'تم التأكيد من المدير — أمين الصندوق يمكنه تسجيل الإيداع في البنك' : 'Manager confirmed — Cashier can record bank deposit' }}
                                <a href="{{ route('revenue.treasury.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 text-white rounded-xl hover:bg-violet-700">
                                    {{ app()->getLocale() === 'ar' ? 'تبويب أمين الصندوق' : 'Treasury' }}
                                </a>
                            </div>
                        @elseif($invoice->audit_status === 'deposited')
                            <div class="w-full py-4 bg-emerald-50 text-emerald-700 text-xs font-black rounded-2xl text-center border-2 border-emerald-100 shadow-inner flex items-center justify-center gap-3">
                                <span>✅</span> {{ app()->getLocale() === 'ar' ? 'تم الإيداع في البنك (إقفال)' : 'Deposited at Bank (Closed)' }}
                            </div>
                        @else
                           <div class="w-full py-4 bg-slate-50 text-slate-600 text-xs font-black rounded-2xl text-center border-2 border-slate-100 shadow-inner flex items-center justify-center gap-3">
                               <span>💎</span> {{ $invoice->getStatusLabelAttribute() }}
                           </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="premium-card rounded-[3rem] p-16 text-center border-2 border-dashed border-slate-200 bg-transparent">
                    <div class="text-5xl mb-4">📭</div>
                    <p class="text-slate-400 font-black">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير معلقة حالياً' : 'No pending invoices at the moment' }}</p>
                </div>
            @endforelse
        </div>

        {{-- Left Side: Accountant Audit --}}
        <div class="space-y-6">
            <h2 class="text-xl font-black text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">🧾</span>
                {{ app()->getLocale() === 'ar' ? 'تقرير التدقيق الموازي' : 'Parallel Audit Audit' }}
            </h2>

            <div class="premium-card rounded-[3rem] overflow-hidden sticky top-28 border-2 border-white/50">
                <div class="p-8 bg-slate-900 overflow-hidden relative">
                    <div class="absolute -right-8 -top-8 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
                    <div class="absolute -left-8 -bottom-8 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl"></div>

                    <h3 class="font-black text-[10px] tracking-widest uppercase text-indigo-400 mb-6 flex items-center gap-2">
                         <span class="w-2 h-0.5 text-white bg-indigo-500"></span>
                         {{ app()->getLocale() === 'ar' ? 'الملخص المالي للفترة' : 'Financial Period Summary' }}
                    </h3>

                    <div class="grid grid-cols-2 gap-10">
                        <div>
                            <p class="text-[10px] text-white font-black uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي المحصلات' : 'Gross Collected' }}</p>
                            <p class="text-4xl font-black  tracking-tighter text-white">{{ number_format($totalCollectedToday, 2) }}</p>
                        </div>
                        <div class="text-right">
                             <p class="text-[10px] text-white font-black uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'عدد العمليات' : 'Trans Count' }}</p>
                             <p class="text-4xl font-black text-emerald-400 tracking-tighter">{{ $invoices->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 space-y-8">
                    <div class="flex items-center justify-between p-6 bg-slate-50 rounded-[2rem] border border-slate-100 group cursor-default">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white text-indigo-600 rounded-2xl shadow-sm flex items-center justify-center font-black group-hover:scale-110 transition-transform">
                                <span class="text-xl">📊</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-white">{{ app()->getLocale() === 'ar' ? 'دقة مطابقة السندات' : 'Voucher Accuracy' }}</h4>
                                <p class="text-[10px] text-slate-500 font-bold">{{ app()->getLocale() === 'ar' ? 'نسبة التحقق من المطالبات اليدوية' : 'Manual vs System verified ratio' }}</p>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="text-xs font-black text-white">{{ $invoices->where('audit_status', 'matched')->count() }}/{{ $invoices->count() }}</span>
                            <div class="text-[8px] font-black text-slate-300 uppercase tracking-tighter">verified</div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center mb-1">
                            <h4 class="text-[10px] font-black text-white uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'حالة الإيداع البنكي' : 'Bank Deposit Progress' }}</h4>
                            <span class="text-[10px] font-black text-emerald-500">{{ round($invoices->count() > 0 ? ($invoices->where('audit_status', 'ready_for_deposit')->count() / $invoices->count() * 100) : 0) }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 p-0.5 border border-slate-200">
                            <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full transition-all duration-1000" style="width: {{ $invoices->count() > 0 ? ($invoices->where('audit_status', 'ready_for_deposit')->count() / $invoices->count() * 100) : 0 }}%"></div>
                        </div>
                        <div class="flex justify-between text-[9px] font-black text-slate-400 uppercase">
                            <span>0 READY</span>
                            <span>{{ $invoices->where('audit_status', 'ready_for_deposit')->count() }} DEPOSITED</span>
                            <span>TOTAL {{ $invoices->count() }}</span>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-slate-100">
                        <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                            <p class="text-[10px] text-indigo-900 font-bold leading-relaxed italic">
                                {{ app()->getLocale() === 'ar' ? 'ملاحظة: عند الضغط على "مطابقة"، تنتقل الفاتورة إلى عهدة المحاسب المركزية قبل تحويلها للإيداع البنكي النهائي.' : 'Note: matching validates the operation, moving it to central audit before final bank deposit clearance.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRejectionForm(invoiceId) {
        const form = document.getElementById(`rejection-form-${invoiceId}`);
        const actions = document.getElementById(`actions-${invoiceId}`);

        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
            actions.classList.add('opacity-30', 'pointer-events-none');
            // Scroll to the card
            document.getElementById(`invoice-card-${invoiceId}`).scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            form.classList.add('hidden');
            actions.classList.remove('opacity-30', 'pointer-events-none');
        }
    }
</script>
@endsection
