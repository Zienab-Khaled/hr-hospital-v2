{{-- عمليات أمين الصندوق داخل غرفة التحكم --}}
<div class="space-y-8">
    <div>
        <h3 class="text-lg font-black text-slate-700 flex items-center gap-2 mb-4">
            <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-sm">📥</span>
            {{ app()->getLocale() === 'ar' ? 'الواردة بعد المطابقة' : 'Matched (Incoming)' }}
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-600 text-white">{{ $matchedInvoices->count() }}</span>
        </h3>
        @forelse($matchedInvoices as $invoice)
            @php $fileLinks = $invoice->getAllRelatedMediaUrls(); @endphp
            <div class="premium-card rounded-[2rem] p-5 mb-4 border-2 border-transparent hover:shadow-lg">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">#{{ $invoice->invoice_number }}</span>
                            <a href="{{ route('invoices.show', $invoice) }}" target="_blank" class="text-[10px] font-black text-indigo-600 hover:underline">
                                {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                            </a>
                        </div>
                        <h4 class="text-base font-black text-slate-900">{{ $invoice->patient->fullArabicName() }}</h4>
                        <p class="text-sm font-bold text-slate-600 mt-1">@currency($invoice->paid_amount ?? 0)</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @if (!empty($fileLinks))
                            <div class="flex flex-wrap gap-2 justify-end">
                                @foreach ($fileLinks as $name => $url)
                                    <a href="{{ $url }}" target="_blank" class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-bold border border-indigo-100">📄 {{ Str::limit($name, 14) }}</a>
                                @endforeach
                            </div>
                        @endif
                        @if (\App\Support\RoleNav::canOperateTreasury(auth()->user()))
                            <form action="{{ route('revenue.invoices.ready', $invoice) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-ready inline-flex items-center justify-center px-5 py-2.5 text-white text-xs font-black rounded-2xl gap-2">
                                    <span>🏦</span> {{ app()->getLocale() === 'ar' ? 'جاهز للإيداع للبنك' : 'Ready for Bank Deposit' }}
                                </button>
                            </form>
                        @else
                            <span class="text-[10px] font-bold text-slate-400">{{ app()->getLocale() === 'ar' ? 'عرض فقط' : 'View only' }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="premium-card rounded-2xl p-8 text-center border-2 border-dashed border-slate-200 text-slate-400 font-bold text-sm">
                {{ app()->getLocale() === 'ar' ? 'لا توجد فواتير مطابقة حالياً' : 'No matched invoices' }}
            </div>
        @endforelse
    </div>

    <div>
        <h3 class="text-lg font-black text-slate-700 flex items-center gap-2 mb-4">
            <span class="w-8 h-8 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-sm">🏦</span>
            {{ app()->getLocale() === 'ar' ? 'جاهزة للإيداع (بانتظار المدير)' : 'Ready (Awaiting Manager)' }}
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-600 text-white">{{ $readyForDepositInvoices->count() }}</span>
        </h3>
        @forelse($readyForDepositInvoices as $invoice)
            <div class="premium-card rounded-[2rem] p-5 mb-4 border-2 border-amber-100">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">#{{ $invoice->invoice_number }}</span>
                        <h4 class="text-base font-black text-slate-900 mt-1">{{ $invoice->patient->fullArabicName() }}</h4>
                        <p class="text-sm font-bold text-slate-600">@currency($invoice->paid_amount ?? 0)</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <div class="px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-[10px] font-black">
                            {{ app()->getLocale() === 'ar' ? '⏳ بانتظار تأكيد المدير' : 'Awaiting manager confirmation' }}
                        </div>
                        @if (\App\Support\RoleNav::canConfirmAsManager(auth()->user()))
                            <form action="{{ route('revenue.invoices.manager-confirmed', $invoice) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-violet-600 text-white text-xs font-black rounded-2xl gap-2 hover:bg-violet-700">
                                    <span>✓</span> {{ app()->getLocale() === 'ar' ? 'تأكيد من المدير' : 'Manager Confirm' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="premium-card rounded-2xl p-8 text-center border-2 border-dashed border-slate-200 text-slate-400 font-bold text-sm">
                {{ app()->getLocale() === 'ar' ? 'لا توجد فواتير جاهزة للإيداع' : 'No invoices ready for deposit' }}
            </div>
        @endforelse
    </div>

    <div>
        <h3 class="text-lg font-black text-slate-700 flex items-center gap-2 mb-4">
            <span class="w-8 h-8 bg-violet-100 text-violet-600 rounded-xl flex items-center justify-center text-sm">✓</span>
            {{ app()->getLocale() === 'ar' ? 'مؤكدة من المدير (تسجيل الإيداع)' : 'Manager Confirmed' }}
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-violet-600 text-white">{{ $managerConfirmedInvoices->count() }}</span>
        </h3>
        @forelse($managerConfirmedInvoices as $invoice)
            <div class="premium-card rounded-[2rem] p-5 mb-4 border-2 border-violet-100">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-violet-100 text-violet-700">#{{ $invoice->invoice_number }}</span>
                        <h4 class="text-base font-black text-slate-900 mt-1">{{ $invoice->patient->fullArabicName() }}</h4>
                        <p class="text-sm font-bold text-slate-600">@currency($invoice->paid_amount ?? 0)</p>
                    </div>
                    @if (\App\Support\RoleNav::canOperateTreasury(auth()->user()))
                        <form action="{{ route('revenue.invoices.deposited', $invoice) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2 items-end"
                              onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'تأكيد تسجيل الإيداع في البنك؟' : 'Confirm bank deposit?' }}');">
                            @csrf
                            <label class="flex items-center gap-2 text-[10px] font-bold text-slate-600">
                                <span>{{ app()->getLocale() === 'ar' ? 'صورة إيداع:' : 'Slip:' }}</span>
                                <input type="file" name="deposit_slip" accept="image/*" class="rounded border border-slate-300 text-[10px] max-w-[160px]">
                            </label>
                            <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 text-white text-xs font-black rounded-2xl gap-2 hover:bg-emerald-700">
                                <span>✅</span> {{ app()->getLocale() === 'ar' ? 'تم الإيداع في البنك' : 'Deposited at Bank' }}
                            </button>
                        </form>
                    @else
                        <span class="text-[10px] font-bold text-slate-400">{{ app()->getLocale() === 'ar' ? 'عرض فقط' : 'View only' }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="premium-card rounded-2xl p-8 text-center border-2 border-dashed border-slate-200 text-slate-400 font-bold text-sm">
                {{ app()->getLocale() === 'ar' ? 'لا توجد فواتير مؤكدة من المدير' : 'No manager-confirmed invoices' }}
            </div>
        @endforelse
    </div>

    <div>
        <h3 class="text-lg font-black text-slate-700 flex items-center gap-2 mb-4">
            <span class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-sm">✅</span>
            {{ app()->getLocale() === 'ar' ? 'تم الإيداع (إقفال)' : 'Deposited' }}
            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-600 text-white">{{ $depositedInvoices->count() }}</span>
        </h3>
        @forelse($depositedInvoices as $invoice)
            <div class="premium-card rounded-[2rem] p-5 mb-4 border-2 border-emerald-100 bg-emerald-50/30">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-800">#{{ $invoice->invoice_number }}</span>
                        <h4 class="text-base font-black text-slate-900 mt-1">{{ $invoice->patient->fullArabicName() }}</h4>
                        <p class="text-sm font-bold text-slate-600">@currency($invoice->paid_amount ?? 0)</p>
                        @if ($invoice->deposited_at)
                            <p class="text-xs font-bold text-emerald-600 mt-1">
                                {{ app()->getLocale() === 'ar' ? 'تاريخ الإيداع:' : 'Deposited:' }}
                                {{ western_digits($invoice->deposited_at->translatedFormat('d/m/Y H:i')) }}
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('invoices.show', $invoice) }}" target="_blank" class="text-[10px] font-black text-emerald-700 hover:underline">
                        {{ app()->getLocale() === 'ar' ? 'عرض الفاتورة' : 'View' }}
                    </a>
                </div>
            </div>
        @empty
            <div class="premium-card rounded-2xl p-8 text-center border-2 border-dashed border-slate-200 text-slate-400 font-bold text-sm">
                {{ app()->getLocale() === 'ar' ? 'لا توجد إيداعات لهذا التاريخ' : 'No deposits for this date' }}
            </div>
        @endforelse
    </div>
</div>
