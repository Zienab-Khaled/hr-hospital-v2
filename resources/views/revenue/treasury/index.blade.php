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
    .btn-ready {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        transition: all 0.3s ease;
    }
    .btn-ready:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }
    .btn-deposited {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transition: all 0.3s ease;
    }
    .btn-deposited:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3); }
</style>

<div class="px-4 sm:px-6 lg:px-8 py-8 bg-slate-50 min-h-screen font-cairo">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">
                {{ app()->getLocale() === 'ar' ? 'أمين الصندوق' : 'Treasury' }}
            </h1>
            <div class="flex items-center gap-2 text-slate-500 font-bold">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                {{ app()->getLocale() === 'ar' ? 'قائمة الواردة من المحصل + جاهز للتوريد للبنك' : 'Transferred from collector & ready for bank deposit' }}
            </div>
        </div>

        <form action="{{ route('revenue.treasury.index') }}" method="GET" class="premium-card p-5 rounded-3xl flex items-center gap-6">
            <div class="flex flex-col gap-1.5">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ app()->getLocale() === 'ar' ? 'تاريخ العمليات' : 'Operation Date' }}</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="block w-44 rounded-2xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-bold py-2.5">
            </div>
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-slate-900 text-white text-sm font-black rounded-2xl hover:bg-slate-800">
                {{ app()->getLocale() === 'ar' ? 'تحديث' : 'Sync' }}
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm font-bold">
            <span class="text-xl">✨</span> {{ session('success') }}
        </div>
    @endif

    {{-- ملخص أمين الصندوق — جنب بعض (مرتبط بفلتر التاريخ) --}}
    <div class="mb-10 flex flex-wrap gap-4">
        <div class="premium-card rounded-2xl p-5 border-2 border-blue-200 bg-blue-50/50 flex-1 min-w-[200px]">
            <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'الواردة من المحصل' : 'From Collector' }}</p>
            <p class="text-2xl font-black text-blue-800">{{ $treasuryStats['matched_count'] }}</p>
            <p class="text-sm font-bold text-blue-700 mt-1">{{ number_format($treasuryStats['matched_amount'], 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}</p>
        </div>
        <div class="premium-card rounded-2xl p-5 border-2 border-amber-200 bg-amber-50/50 flex-1 min-w-[200px]">
            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'جاهزة للتوريد للبنك' : 'Ready for Deposit' }}</p>
            <p class="text-2xl font-black text-amber-800">{{ $treasuryStats['ready_count'] }}</p>
            <p class="text-sm font-bold text-amber-700 mt-1">{{ number_format($treasuryStats['ready_amount'], 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}</p>
        </div>
        <div class="premium-card rounded-2xl p-5 border-2 border-emerald-200 bg-emerald-50/50 flex-1 min-w-[200px]">
            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'تم التقفيل والتوريد' : 'Deposited (Closed)' }}</p>
            <p class="text-2xl font-black text-emerald-800">{{ $treasuryStats['deposited_count'] }}</p>
            <p class="text-sm font-bold text-emerald-700 mt-1">{{ number_format($treasuryStats['deposited_amount'], 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}</p>
        </div>
    </div>

    {{-- القسم 1: وردت من المحصل (matched) — زرار "جاهز للتوريد للبنك" --}}
    <div class="mb-12">
        <h2 class="text-xl font-black text-slate-800 flex items-center gap-3 mb-6">
            <span class="w-10 h-10 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">📥</span>
            {{ app()->getLocale() === 'ar' ? 'الواردة من المحصل (بعد المطابقة)' : 'Transferred from Collector (Matched)' }}
            <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-600 text-white">{{ $matchedInvoices->count() }}</span>
        </h2>

        @forelse($matchedInvoices as $invoice)
            @php $fileLinks = $invoice->getAllRelatedMediaUrls(); @endphp
            <div class="premium-card rounded-[2.5rem] p-6 mb-6 border-2 border-transparent hover:shadow-xl transition-all">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl">
                            {{ $invoice->patient->gender === 'female' ? '👩' : '👨' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">#{{ $invoice->invoice_number }}</span>
                                <a href="{{ route('invoices.show', $invoice) }}" target="_blank" class="text-[10px] font-black text-indigo-600 hover:underline">
                                    {{ app()->getLocale() === 'ar' ? 'عرض الفاتورة' : 'View' }}
                                </a>
                            </div>
                            <h3 class="text-lg font-black text-slate-900">{{ $invoice->patient->name_ar ?? $invoice->patient->name }}</h3>
                            <p class="text-sm font-bold text-slate-600 mt-1">@currency($invoice->paid_amount ?? 0) {{ app()->getLocale() === 'ar' ? 'محصل' : 'collected' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @if(!empty($fileLinks))
                            <div class="flex flex-wrap gap-2 justify-end">
                                @foreach($fileLinks as $name => $url)
                                    <a href="{{ $url }}" target="_blank" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-xl text-xs font-bold border border-indigo-100 hover:bg-indigo-100">
                                        📄 {{ Str::limit($name, 18) }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        <form action="{{ route('revenue.invoices.ready', $invoice) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-ready inline-flex items-center justify-center px-6 py-3 text-white text-xs font-black rounded-2xl gap-2">
                                <span>🏦</span> {{ app()->getLocale() === 'ar' ? 'جاهز للتوريد للبنك' : 'Ready for Bank Deposit' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="premium-card rounded-3xl p-12 text-center border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير واردة من المحصل بهذا التاريخ' : 'No matched invoices for this date' }}</p>
            </div>
        @endforelse
    </div>

    {{-- القسم 2: جاهزة للتوريد (ready_for_deposit) — زرار "تم التوريد" --}}
    <div>
        <h2 class="text-xl font-black text-slate-800 flex items-center gap-3 mb-6">
            <span class="w-10 h-10 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center">🏦</span>
            {{ app()->getLocale() === 'ar' ? 'جاهزة للتوريد للبنك' : 'Ready for Bank Deposit' }}
            <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-600 text-white">{{ $readyForDepositInvoices->count() }}</span>
        </h2>

        @forelse($readyForDepositInvoices as $invoice)
            @php $fileLinks = $invoice->getAllRelatedMediaUrls(); @endphp
            <div class="premium-card rounded-[2.5rem] p-6 mb-6 border-2 border-amber-100 hover:shadow-xl transition-all">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-2xl">💰</div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">#{{ $invoice->invoice_number }}</span>
                                <a href="{{ route('invoices.show', $invoice) }}" target="_blank" class="text-[10px] font-black text-amber-700 hover:underline">
                                    {{ app()->getLocale() === 'ar' ? 'عرض الفاتورة' : 'View' }}
                                </a>
                            </div>
                            <h3 class="text-lg font-black text-slate-900">{{ $invoice->patient->name_ar ?? $invoice->patient->name }}</h3>
                            <p class="text-sm font-bold text-slate-600 mt-1">@currency($invoice->paid_amount ?? 0)</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @if(!empty($fileLinks))
                            <div class="flex flex-wrap gap-2 justify-end">
                                @foreach($fileLinks as $name => $url)
                                    <a href="{{ $url }}" target="_blank" class="px-3 py-1.5 bg-amber-50 text-amber-700 rounded-xl text-xs font-bold border border-amber-200 hover:bg-amber-100">
                                        📄 {{ Str::limit($name, 18) }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        <form action="{{ route('revenue.invoices.deposited', $invoice) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2 items-end" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'تأكيد تسجيل التوريد وإقفال المعاملة؟' : 'Confirm deposit and close transaction?' }}');">
                            @csrf
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-600 w-full justify-end">
                                <span>{{ app()->getLocale() === 'ar' ? 'صورة إيداع (اختياري):' : 'Deposit slip image (optional):' }}</span>
                                <input type="file" name="deposit_slip" accept="image/*" class="rounded border border-slate-300 text-[10px] max-w-[180px]">
                            </label>
                            <button type="submit" class="btn-deposited inline-flex items-center justify-center px-6 py-3 text-white text-xs font-black rounded-2xl gap-2">
                                <span>✅</span> {{ app()->getLocale() === 'ar' ? 'تم التوريد (إقفال)' : 'Deposited (Close)' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="premium-card rounded-3xl p-12 text-center border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير جاهزة للتوريد بهذا التاريخ' : 'No invoices ready for deposit for this date' }}</p>
            </div>
        @endforelse
    </div>

    {{-- القسم 3: تم التوريد (اتدفعت) — للعرض فقط --}}
    <div class="mt-12">
        <h2 class="text-xl font-black text-slate-800 flex items-center gap-3 mb-6">
            <span class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center">✅</span>
            {{ app()->getLocale() === 'ar' ? 'تم التوريد (اتدفعت)' : 'Deposited' }}
            <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-600 text-white">{{ $depositedInvoices->count() }}</span>
        </h2>

        @forelse($depositedInvoices as $invoice)
            @php $fileLinks = $invoice->getAllRelatedMediaUrls(); @endphp
            <div class="premium-card rounded-[2.5rem] p-6 mb-6 border-2 border-emerald-100 hover:shadow-xl transition-all bg-emerald-50/30">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center text-2xl">✅</div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-800">#{{ $invoice->invoice_number }}</span>
                                <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-emerald-600 text-white">{{ app()->getLocale() === 'ar' ? 'اتدفعت' : 'Deposited' }}</span>
                                <a href="{{ route('invoices.show', $invoice) }}" target="_blank" class="text-[10px] font-black text-emerald-700 hover:underline">
                                    {{ app()->getLocale() === 'ar' ? 'عرض الفاتورة' : 'View' }}
                                </a>
                            </div>
                            <h3 class="text-lg font-black text-slate-900">{{ $invoice->patient->name_ar ?? $invoice->patient->name }}</h3>
                            <p class="text-sm font-bold text-slate-600 mt-1">@currency($invoice->paid_amount ?? 0)</p>
                            @if($invoice->deposited_at)
                                <p class="text-xs font-bold text-emerald-600 mt-1">{{ app()->getLocale() === 'ar' ? 'تاريخ التوريد:' : 'Deposited at:' }} {{ $invoice->deposited_at->translatedFormat('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    @php $depositMedia = $invoice->getMedia('bank_deposit'); @endphp
                    @if($depositMedia->isNotEmpty())
                        <div class="flex flex-wrap gap-2 justify-end items-center">
                            <span class="text-[10px] font-bold text-emerald-700">{{ app()->getLocale() === 'ar' ? 'صورة الإيداع:' : 'Deposit slip:' }}</span>
                            @foreach($depositMedia as $media)
                                <a href="{{ $media->getUrl() }}" target="_blank" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold border border-emerald-200 hover:bg-emerald-100">
                                    🖼️ {{ Str::limit($media->file_name, 20) }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($fileLinks))
                        <div class="flex flex-wrap gap-2 justify-end">
                            @foreach($fileLinks as $name => $url)
                                <a href="{{ $url }}" target="_blank" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold border border-emerald-200 hover:bg-emerald-100">
                                    📄 {{ Str::limit($name, 18) }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="premium-card rounded-3xl p-12 text-center border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-bold">{{ app()->getLocale() === 'ar' ? 'لا توجد فواتير تم توريدها بهذا التاريخ' : 'No deposited invoices for this date' }}</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
