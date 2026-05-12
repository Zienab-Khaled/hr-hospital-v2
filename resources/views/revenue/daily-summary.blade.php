@extends('layouts.app')

@section('content')
@php
    $fontCairo = 'font-family: \'Cairo\', \'Tajawal\', sans-serif;';
@endphp
<style>
    .font-cairo { {{ $fontCairo }} }
    .daily-summary-wrap { {{ $fontCairo }} direction: rtl; text-align: right; max-width: 900px; margin: 0 auto; }
    .daily-summary-wrap .form-block { border: 1px solid #1a1a1a; padding: 16px 20px; margin-bottom: 16px; }
    .daily-summary-wrap table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    .daily-summary-wrap th, .daily-summary-wrap td { border: 1px solid #333; padding: 8px 10px; }
    .daily-summary-wrap th { background: #f1f5f9; font-weight: 700; }
    .daily-summary-wrap .amount-cell { text-align: left; }
    .daily-summary-wrap .handover-row { display: flex; gap: 24px; margin-top: 12px; flex-wrap: wrap; }
    .daily-summary-wrap .handover-box { flex: 1; min-width: 200px; border: 1px solid #333; padding: 10px; }
    .revenue-tabs { display: flex; gap: 4px; margin-bottom: 16px; flex-wrap: wrap; }
    .revenue-tabs a { padding: 10px 18px; border-radius: 10px; font-weight: 700; text-decoration: none; background: #e2e8f0; color: #475569; transition: all 0.2s; }
    .revenue-tabs a:hover { background: #cbd5e1; }
    .revenue-tabs a.active { background: #4f46e5; color: white; }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; }
    .total-line { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px; }
    .total-line .part-right { text-align: right; }
    .total-line .part-left { text-align: left; color: #dc2626; font-weight: 600; }
    .fillable-red { color: #dc2626 !important; }
    .daily-summary-wrap .handover-box .fillable-red { color: #dc2626 !important; }
    .daily-summary-wrap input[type="text"] { font-family: inherit; }
    @media print {
        /* طباعة التقرير فقط — إخفاء السايدبار والهيدر وكل ما عدا صندوق التقرير */
        body * { visibility: hidden; }
        .main-content-area,
        .main-content-area * { visibility: visible !important; }
        .daily-summary-wrap {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 12px !important;
            background: white !important;
            box-shadow: none !important;
        }
        .no-print { display: none !important; }
        .revenue-tabs { display: none !important; }
        .tab-pane { display: none !important; }
        .tab-pane.active { display: block !important; }
        .form-block { break-inside: avoid; }
        .fillable-red, .part-left { color: #b91c1c !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .daily-summary-wrap input[type="text"] { border-bottom-color: #333 !important; }
        @page { margin: 10mm; }
    }
</style>

<div class="revenue-summary-root px-4 sm:px-6 lg:px-8 py-8 bg-slate-50 min-h-screen font-cairo">
    <div class="no-print flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
        <h1 class="text-2xl font-black text-slate-900">
            {{ app()->getLocale() === 'ar' ? 'ملخصات الإيرادات اليومية' : 'Daily Revenue Summaries' }}
        </h1>
        <form action="{{ route('revenue.daily-summary') }}" method="GET" class="flex items-center gap-4 flex-wrap">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            @if(in_array($activeTab, ['monthly', 'monthly-stats']))
            <label class="text-sm font-bold text-slate-600">{{ app()->getLocale() === 'ar' ? 'الشهر:' : 'Month:' }}</label>
            <input type="month" name="month" value="{{ $monthInput }}"
                class="rounded-xl border-slate-300 bg-white px-4 py-2 text-sm font-bold shadow-sm">
            @else
            <label class="text-sm font-bold text-slate-600">{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }}</label>
            <input type="date" name="date" value="{{ $date }}"
                class="rounded-xl border-slate-300 bg-white px-4 py-2 text-sm font-bold shadow-sm">
            @endif
            <button type="submit" style="background-color: #7a7f86;" class="rounded-xl bg-slate-800 px-5 py-2 text-sm font-bold hover:bg-slate-700">
                {{ app()->getLocale() === 'ar' ? 'عرض الملخص' : 'Show Summary' }}
            </button>
            <button type="button" onclick="window.print()" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold  hover:bg-indigo-700 shadow-md inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                {{ app()->getLocale() === 'ar' ? 'طباعة' : 'Print' }}
            </button>
        </form>
    </div>

    <div class="no-print revenue-tabs mb-6">
        @foreach($tabs as $tabKey => $tab)
        <a href="{{ route('revenue.daily-summary', array_filter(['date' => $date, 'month' => $monthInput ?? null, 'tab' => $tabKey])) }}"
           class="{{ $activeTab === $tabKey ? 'active' : '' }}">
            {{ app()->getLocale() === 'ar' ? $tab['label_ar'] : $tab['label_en'] }}
        </a>
        @endforeach
    </div>

    <div class="no-print mb-4">
        <button type="button" onclick="window.print()" class="rounded-xl bg-red-600 px-6 py-2.5 text-sm font-bold  hover:bg-red-700 shadow-lg inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            {{ app()->getLocale() === 'ar' ? 'طباعة التقرير' : 'Print Report' }}
        </button>
    </div>

    <div class="daily-summary-wrap bg-white p-6 rounded-2xl shadow-sm print:shadow-none">
        {{-- Tab: موارد - 4 (ثلاث فترات) --}}
        <div class="tab-pane {{ $activeTab === 'moarad-4' ? 'active' : '' }}" data-tab="moarad-4">
            @include('components.report-header')
            <p class="text-center text-sm text-slate-500 mt-2 mb-1">الدليل التنظيمي للخدمات الصحية بمقابل</p>
            <p class="text-center font-bold text-slate-800 mb-4">نموذج رقم (موارد - 4)</p>
            <h2 class="text-center text-xl font-black text-slate-900 mb-6">خلاصة الإيرادات اليومية</h2>
            <p class="mb-6">
                يوم <strong>{{ $dayName }}</strong>
                وتاريخ <strong>{{ $hijri }}</strong> هـ الموافق <strong>{{ $gregorian }}</strong> م
            </p>

            @foreach($summary as $key => $period)
            <div class="form-block">
                <p class="font-bold text-slate-800 mb-2">{{ $key }} - {{ $period['label_ar'] }}</p>
                <p class="text-sm mb-1 total-line">
                    <span class="part-right">إجمالي إيرادات الفترة ({{ number_format($period['total'], 2) }}) فقط</span>
                    <span class="part-left fillable-red">مبلغ وقدره: {{ \App\Helpers\CurrencyHelper::amountInArabicWords($period['total']) }}</span>
                </p>
                <p class="text-sm mb-3">حسب سندات التحصيل من رقم <strong>{{ $period['receipt_from'] ?? '—' }}</strong> إلى <strong>{{ $period['receipt_to'] ?? '—' }}</strong></p>

                <table>
                    <thead>
                        <tr>
                            <th>البيان</th>
                            <th colspan="2">المبلغ (ريال / هللة)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>إيرادات بشيكات مصدقة</td>
                            <td class="amount-cell">{{ number_format($period['certified_checks'], 2) }}</td>
                            <td class="amount-cell" style="width:80px">—</td>
                        </tr>
                        <tr>
                            <td>إيرادات الفترة من نقاط البيع</td>
                            <td class="amount-cell">{{ number_format($period['pos'], 2) }}</td>
                            <td class="amount-cell">—</td>
                        </tr>
                        <tr>
                            <td>إيرادات الفترة النقدية</td>
                            <td class="amount-cell">{{ number_format($period['cash'], 2) }}</td>
                            <td class="amount-cell">—</td>
                        </tr>
                        <tr>
                            <td>إيرادات التأمين</td>
                            <td class="amount-cell">{{ number_format($period['insurance'] ?? 0, 2) }}</td>
                            <td class="amount-cell">—</td>
                        </tr>
                        <tr>
                            <td>إيرادات الجمعية</td>
                            <td class="amount-cell">{{ number_format($period['charity'] ?? 0, 2) }}</td>
                            <td class="amount-cell">—</td>
                        </tr>
                    </tbody>
                </table>

                <p class="text-sm mt-2">بلغ إجمالي الإيرادات النقدية للفترة مبلغ <span class="fillable-red">({{ number_format($period['cash'], 2) }})</span> فقط</p>
                <p class="text-xs text-slate-600 mt-2">وتم تسليمها لمحصل الفترة التالية بالإضافة إلى الشيكات المصدقة الموضح قيمتها أعلاه وأبواق إيصالات التحصيل.</p>

                <div class="handover-row mt-4">
                    <div class="handover-box">
                        <p class="text-xs font-bold mb-1">محصل الفترة {{ $key }} (تسليم)</p>
                        <p class="text-xs">الاسم: <span class="fillable-red">{{ !empty($period['handover_names']) ? implode(' ، ', $period['handover_names']) : '...............................' }}</span></p>
                        <p class="text-xs">التوقيع: <span class="fillable-red">...............................</span></p>
                    </div>
                    @if($key < 3)
                    <div class="handover-box">
                        <p class="text-xs font-bold mb-1">محصل الفترة {{ $key + 1 }} (استلام)</p>
                        <p class="text-xs">الاسم: <span class="fillable-red">{{ !empty($period['receiver_names']) ? implode(' ، ', $period['receiver_names']) : '...............................' }}</span></p>
                        <p class="text-xs">التوقيع: <span class="fillable-red">...............................</span></p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            <div class="form-block mt-6 bg-slate-50 border-2 border-slate-300">
                <p class="text-base font-black text-slate-800 mb-2">إجمالي نهار اليوم (من سجل الدفعات)</p>
                <p class="text-sm mb-1 total-line">
                    <span class="part-right">المجموع الكلي لجميع الفترات ({{ number_format($dayTotals['total'], 2) }}) ريال فقط</span>
                    <span class="part-left fillable-red">مبلغ وقدره: {{ \App\Helpers\CurrencyHelper::amountInArabicWords($dayTotals['total']) }}</span>
                </p>
            </div>
        </div>

        {{-- Tab: ملخص حسب طريقة التحصيل (يوم واحد) --}}
        <div class="tab-pane {{ $activeTab === 'by-method' ? 'active' : '' }}" data-tab="by-method">
            @include('components.report-header')
            <p class="text-center font-bold text-slate-800 mt-4 mb-2">ملخص حسب طريقة التحصيل</p>
            <p class="text-center text-sm text-slate-600 mb-6">
                يوم <strong>{{ $dayName }}</strong> — {{ $gregorian }}
            </p>
            <p class="text-sm mb-3">حسب سندات التحصيل من رقم <strong>{{ $dayReceiptNumbers->first() ?? '—' }}</strong> إلى <strong>{{ $dayReceiptNumbers->last() ?? '—' }}</strong></p>

            <div class="form-block">
                <table>
                    <thead>
                        <tr>
                            <th>البيان</th>
                            <th colspan="2">المبلغ (ريال / هللة)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>إيرادات بشيكات مصدقة</td>
                            <td class="amount-cell">{{ number_format($dayTotals['certified_checks'], 2) }}</td>
                            <td class="amount-cell" style="width:80px">—</td>
                        </tr>
                        <tr>
                            <td>إيرادات من نقاط البيع</td>
                            <td class="amount-cell">{{ number_format($dayTotals['pos'], 2) }}</td>
                            <td class="amount-cell">—</td>
                        </tr>
                        <tr>
                            <td>إيرادات نقدية</td>
                            <td class="amount-cell">{{ number_format($dayTotals['cash'], 2) }}</td>
                            <td class="amount-cell">—</td>
                        </tr>
                        <tr>
                            <td>إيرادات التأمين</td>
                            <td class="amount-cell">{{ number_format($dayTotals['insurance'] ?? 0, 2) }}</td>
                            <td class="amount-cell">—</td>
                        </tr>
                        <tr>
                            <td>إيرادات الجمعية</td>
                            <td class="amount-cell">{{ number_format($dayTotals['charity'] ?? 0, 2) }}</td>
                            <td class="amount-cell">—</td>
                        </tr>
                        <tr class="font-bold bg-slate-100">
                            <td>الإجمالي</td>
                            <td class="amount-cell">{{ number_format($dayTotals['total'], 2) }}</td>
                            <td>—</td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-sm mt-3">بلغ إجمالي الإيرادات النقدية مبلغ <span class="fillable-red">({{ number_format($dayTotals['cash'], 2) }})</span> فقط</p>
                <div class="handover-row mt-4">
                    <div class="handover-box">
                        <p class="text-xs font-bold mb-1">محصلو اليوم (تسليم)</p>
                        <p class="text-xs">الاسم: <span class="fillable-red">{{ !empty($dayCollectorNames) ? implode(' ، ', $dayCollectorNames) : '...............................' }}</span></p>
                        <p class="text-xs">التوقيع: <span class="fillable-red">...............................</span></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: خلاصة الإيرادات الشهرية (موارد - 11) --}}
        <div class="tab-pane {{ $activeTab === 'monthly' ? 'active' : '' }}" data-tab="monthly">
            @include('components.report-header')
            <p class="text-center text-sm text-slate-500 mt-2 mb-1">الدليل التنظيمي للخدمات الصحية بمقابل</p>
            <p class="text-center font-bold text-slate-800 mb-2">نموذج رقم (موارد - 11)</p>
            <h2 class="text-center text-xl font-black text-slate-900 mb-4 underline">خلاصة الإيرادات الشهرية</h2>
            <p class="text-center text-sm mb-2">تقرير شهري لإيرادات الخدمة الصحية بمقابل</p>
            <p class="text-center text-sm mb-4">في <span class="fillable-red">{{ $hospitalName }}</span> — لشهر <span class="fillable-red">{{ app()->getLocale() === 'ar' ? $monthYearAr : $monthYearEn }}</span></p>

            <p class="font-bold text-slate-800 mb-2">أولاً: إجمالي المبالغ المحصلة من المرضى</p>
            <table class="mb-6">
                <thead>
                    <tr>
                        <th>التسلسل</th>
                        <th>رقم البوك</th>
                        <th colspan="2">أرقام الإيصالات</th>
                        <th>المبلغ المحصل</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>من</th>
                        <th>إلى</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyWeeks as $idx => $w)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="fillable-red">—</td>
                        <td class="fillable-red">{{ $w['receipt_from'] ?? '—' }}</td>
                        <td class="fillable-red">{{ $w['receipt_to'] ?? '—' }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($w['collected'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td>1</td>
                        <td class="fillable-red">—</td>
                        <td class="fillable-red">—</td>
                        <td class="fillable-red">—</td>
                        <td class="amount-cell fillable-red">0.00</td>
                    </tr>
                    @endforelse
                    <tr class="font-bold bg-slate-100">
                        <td colspan="4">الإجمالي</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyTotalCollected, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <p class="font-bold text-slate-800 mb-2">ثانياً: إيرادات البرنامج لهذا الشهر لكل أسبوع على حده (يبدأ الأسبوع يوم الأحد وينتهي بنهاية يوم السبت)</p>
            <table class="mb-6">
                <thead>
                    <tr>
                        <th>الأسابيع</th>
                        <th>من تاريخ</th>
                        <th>إلى تاريخ</th>
                        <th>قيمة الإيراد المحصل</th>
                        <th>قيمة الإيرادات المودعة</th>
                        <th>الفرق (إن وجد)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyWeeks as $w)
                    <tr>
                        <td>{{ $w['label'] }}</td>
                        <td class="fillable-red">{{ $w['from_date'] }}</td>
                        <td class="fillable-red">{{ $w['to_date'] }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($w['collected'], 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($w['deposited'], 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($w['difference'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500">لا توجد بيانات لهذا الشهر</td>
                    </tr>
                    @endforelse
                    <tr class="font-bold bg-slate-100">
                        <td>الإجمالي</td>
                        <td colspan="2">—</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyTotalCollected, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyTotalDeposited, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyTotalDiff, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <p class="font-bold text-slate-800 mb-2 mt-6">ثالثاً: توزيع الإيرادات المحصلة (نقدي / تأمين / جمعية)</p>
            <table class="mb-6">
                <thead>
                    <tr>
                        <th>الأسابيع</th>
                        <th>من تاريخ</th>
                        <th>إلى تاريخ</th>
                        <th>نقدي (كاش / شيكات / بطاقات)</th>
                        <th>تأمين</th>
                        <th>جمعية</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyWeeks as $w)
                    <tr>
                        <td>{{ $w['label'] }}</td>
                        <td class="fillable-red">{{ $w['from_date'] }}</td>
                        <td class="fillable-red">{{ $w['to_date'] }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($w['collected_cash'] ?? 0, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($w['collected_insurance'] ?? 0, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($w['collected_charity'] ?? 0, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($w['collected'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-500">لا توجد بيانات لهذا الشهر</td>
                    </tr>
                    @endforelse
                    <tr class="font-bold bg-slate-100">
                        <td>الإجمالي</td>
                        <td colspan="2">—</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyTotalCash ?? 0, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyTotalInsurance ?? 0, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyTotalCharity ?? 0, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyTotalCollected, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="form-block text-sm space-y-3">
                <p>تم إيداع جميع المبالغ في الحساب المخصص للمستشفى رقم <span class="fillable-red">....................................</span> في الأوقات المحددة حسب دليل الخدمة الصحية بمقابل ومرفق صور من إشعارات الإيداع.</p>
                <p>أو: لم يتم إيداع جميع المبالغ في الحساب المخصص للمستشفى حسب المحضر المرفق (يعد محضر بالمخالفات الموجودة).</p>
                <div class="handover-row mt-4 flex-wrap">
                    <div class="handover-box"><p class="text-xs font-bold">المحاسب</p><p class="text-xs fillable-red">الاسم / التوقيع: .....................</p></div>
                    <div class="handover-box"><p class="text-xs font-bold">مدير الموارد الذاتية</p><p class="text-xs fillable-red">الاسم / التوقيع: .....................</p></div>
                    <div class="handover-box"><p class="text-xs font-bold">مندوب إدارة الموارد الذاتية بالمديرية</p><p class="text-xs fillable-red">الاسم / التوقيع: .....................</p></div>
                    <div class="handover-box"><p class="text-xs font-bold">مدير المرفق</p><p class="text-xs fillable-red">الاسم / التوقيع: .....................</p></div>
                </div>
            </div>
        </div>

        {{-- Tab: نموذج إحصائية الشهرية (موارد - 9) --}}
        <div class="tab-pane {{ $activeTab === 'monthly-stats' ? 'active' : '' }}" data-tab="monthly-stats">
            @include('components.report-header')
            <p class="text-center text-sm text-slate-500 mt-2 mb-1">الدليل التنظيمي للخدمات الصحية بمقابل</p>
            <p class="text-center font-bold text-slate-800 mb-2">نموذج رقم (موارد - 9)</p>
            <h2 class="text-center text-xl font-black text-slate-900 mb-4 underline">نموذج إحصائية الشهرية</h2>
            <p class="text-sm mb-4">إحصائية شهر <span class="fillable-red">({{ app()->getLocale() === 'ar' ? $monthYearAr : $monthYearEn }})</span></p>

            <table class="mb-6">
                <thead>
                    <tr>
                        <th>رقم الملف</th>
                        <th>إجمالي المبلغ</th>
                        <th>المبلغ المسدد عند الدخول</th>
                        <th>الدفعة المقدمة للأقساط</th>
                        <th>المبلغ المتبقي</th>
                    </tr>
                </thead>
                <tbody>
                    @php $statsRows = $monthlyStatsRows ?? []; $emptyRows = max(0, 10 - count($statsRows)); @endphp
                    @foreach($statsRows as $row)
                    <tr>
                        <td class="fillable-red">{{ $row['file_number'] }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($row['total_amount'], 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($row['paid_at_entry'], 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($row['advance_installments'], 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($row['remaining_amount'], 2) }}</td>
                    </tr>
                    @endforeach
                    @for($i = 0; $i < $emptyRows; $i++)
                    <tr>
                        <td class="fillable-red">—</td>
                        <td class="amount-cell fillable-red">—</td>
                        <td class="amount-cell fillable-red">—</td>
                        <td class="amount-cell fillable-red">—</td>
                        <td class="amount-cell fillable-red">—</td>
                    </tr>
                    @endfor
                    <tr class="font-bold bg-slate-100">
                        <td>الإجمالي</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyStatsTotals['total_amount'] ?? 0, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyStatsTotals['paid_at_entry'] ?? 0, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyStatsTotals['advance_installments'] ?? 0, 2) }}</td>
                        <td class="amount-cell fillable-red">{{ number_format($monthlyStatsTotals['remaining_amount'] ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="handover-row mt-6 flex-wrap">
                <div class="handover-box"><p class="text-xs font-bold">المحاسب</p><p class="text-xs fillable-red">الاسم / التوقيع: .....................</p></div>
                <div class="handover-box"><p class="text-xs font-bold">مدير المرفق</p><p class="text-xs fillable-red">الاسم / التوقيع: .....................</p></div>
            </div>
        </div>

        {{-- Tab: أمر قبض (موارد - 5) — حقول قابلة للملء --}}
        <div class="tab-pane {{ $activeTab === 'receipt-order' ? 'active' : '' }}" data-tab="receipt-order">
            @include('components.report-header')
            <p class="text-center text-sm text-slate-500 mt-2 mb-1">الدليل التنظيمي للخدمات الصحية بمقابل</p>
            <p class="text-center font-bold text-slate-800 mb-4">نموذج رقم (موارد - 5)</p>
            <h2 class="text-center text-xl font-black text-slate-900 mb-8 underline">أمر قبض</h2>

            <div class="form-block">
                <p class="text-sm mb-2">إلى أمين الصندوق / <input type="text" class="border-0 border-b-2 border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 min-w-[200px] px-1 py-0.5" placeholder=""></p>
                <p class="text-sm mb-2">القبض من / <input type="text" class="border-0 border-b-2 border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 min-w-[200px] px-1 py-0.5" placeholder=""></p>
                <p class="text-sm mb-6">لحساب : <input type="text" class="border-0 border-b-2 border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 min-w-[220px] px-1 py-0.5" placeholder=""></p>

                <div class="border-t border-slate-300 pt-4 mb-4">
                    <div class="grid grid-cols-2 gap-6 mb-4">
                        <div>
                            <p class="text-xs font-bold text-slate-500 mb-1">المبلغ</p>
                            <input type="text" class="w-full border-0 border-b-2 border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 px-1 py-1" placeholder="">
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 mb-1">البيان</p>
                            <input type="text" class="w-full border-0 border-b-2 border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 px-1 py-1" placeholder="">
                        </div>
                    </div>
                    <p class="text-sm">فقط وقدره : <input type="text" class="border-0 border-b-2 border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 w-full max-w-md inline-block px-1 py-0.5" placeholder=""></p>
                </div>

                <div class="grid grid-cols-2 gap-8 mt-8 mb-6">
                    <div class="border border-slate-300 p-4 rounded-lg">
                        <p class="text-xs font-bold text-slate-700 mb-2">المحاسب</p>
                        <p class="text-xs">الأسم : <input type="text" class="border-0 border-b border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 min-w-[120px] px-1 py-0.5" placeholder=""></p>
                        <p class="text-xs mt-1">التوقيع : <input type="text" class="border-0 border-b border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 min-w-[120px] px-1 py-0.5" placeholder=""></p>
                    </div>
                    <div class="border border-slate-300 p-4 rounded-lg">
                        <p class="text-xs font-bold text-slate-700 mb-2">مدير الموارد الذاتية</p>
                        <p class="text-xs">الأسم : <input type="text" class="border-0 border-b border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 min-w-[120px] px-1 py-0.5" placeholder=""></p>
                        <p class="text-xs mt-1">التوقيع : <input type="text" class="border-0 border-b border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 min-w-[120px] px-1 py-0.5" placeholder=""></p>
                    </div>
                </div>

                <p class="text-xs border border-slate-200 bg-slate-50 p-3 rounded-lg mb-4">
                    تم إستلام المبلغ أعلاه واستخرج عنه إيصال الإستلام رقم : <input type="text" class="border-0 border-b border-slate-400 bg-transparent focus:ring-0 w-24 px-1 py-0.5 inline-block" placeholder=""> بتاريخ : <input type="text" class="border-0 border-b border-slate-400 bg-transparent focus:ring-0 w-28 px-1 py-0.5 inline-block" placeholder="">
                </p>
                <div class="border border-slate-300 p-4 rounded-lg">
                    <p class="text-xs font-bold text-slate-700 mb-2">أمين الصندوق</p>
                    <p class="text-xs">الأسم : <input type="text" class="border-0 border-b border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 min-w-[120px] px-1 py-0.5" placeholder=""></p>
                    <p class="text-xs mt-1">التوقيع : <input type="text" class="border-0 border-b border-slate-400 bg-transparent focus:ring-0 focus:border-indigo-500 min-w-[120px] px-1 py-0.5" placeholder=""></p>
                </div>
            </div>
        </div>
        @include('components.report-footer')
    </div>
</div>
@endsection
