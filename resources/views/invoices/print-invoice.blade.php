@php
    use App\Helpers\CurrencyHelper;
    use App\Models\PaymentReceipt;
    $s = $settingsData ?? [];
    $patient = $invoice->patient;
    $rows = $invoice->items ?? collect();
    $total = round((float) ($invoice->total_amount ?? 0), 2);
    $paid = round((float) ($invoice->paid_amount ?? 0), 2);
    $remaining = round((float) ($invoice->remaining_amount ?? max(0, $total - $paid)), 2);
    $printRowCount = $rows->count();
    $printDensityClass = $printRowCount > 14 ? 'print-density-tight' : ($printRowCount > 9 ? 'print-density-compact' : '');
    $paymentTypeLabel = $invoice->payment_type
        ? (\App\Models\Patient::paymentTypeOptions()[$invoice->payment_type]['ar'] ?? $invoice->payment_type)
        : ($patient->payment_type_label ?? '—');

    $splitLines = collect();
    foreach ($invoice->payments ?? [] as $pay) {
        $splitLines->push((object) [
            'payment_method' => $pay->payment_type ?? 'cash',
            'amount' => (float) ($pay->amount ?? 0),
            'reference_number' => $pay->reference_no ?? null,
        ]);
    }
    $showPaymentSplits = $splitLines->isNotEmpty() && $paid > 0;
    $isCash = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'cash')
        || ($splitLines->isEmpty() && ($invoice->payment_type ?? 'cash') === 'cash');
    $isCard = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'card');
    $isLoyaltyPoints = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'loyalty_points');
    $isBank = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'bank_transfer');
    $isCheque = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'cheque');
    $chequeRef = (string) ($splitLines->first(fn ($l) => ($l->payment_method ?? null) === 'cheque')?->reference_number ?? '');
    $bankRef = (string) ($splitLines->first(fn ($l) => ($l->payment_method ?? null) === 'bank_transfer')?->reference_number ?? '');
@endphp
<!DOCTYPE html>
<html lang="ar-SA-u-nu-latn" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة - {{ $invoice->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Tajawal', 'Amiri', 'Traditional Arabic', 'Arial', sans-serif;
            background: #fff;
            color: #000;
            font-size: 13px;
            padding: 18px 22px;
            line-height: 1.5;
            font-weight: 500;
        }
        .print-only { display: none; }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .report-footer-wrap { display: none !important; }
            html, body { height: auto; overflow: visible; margin: 0; padding: 0; }
            body { padding: 4px 8px; font-size: 11px; }
            @page { margin: 0.5cm 0.4cm 1cm 0.4cm; size: A4 portrait; }
            .invoice-form { page-break-inside: avoid; break-inside: avoid; }
            .invoice-form.print-fit-scale { transform-origin: top center; }
            .editable-print-input { background: transparent; border-bottom: 1px solid #000; border-top: none; border-left: none; border-right: none; }
        }

        @page {
            @bottom-center {
                content: counter(page);
                font-size: 11px;
                color: #333;
            }
        }
        @media print {
            .invoice-form.print-density-compact .doc-header { margin-bottom: 8px; padding-bottom: 6px; }
            .invoice-form.print-density-compact .services-table { font-size: 10px; margin-bottom: 8px; }
            .invoice-form.print-density-compact .services-table th,
            .invoice-form.print-density-compact .services-table td { padding: 4px 5px; }
            .invoice-form.print-density-compact .money-summary { margin: 6px 0 8px; padding: 6px 8px; font-size: 10px; }
            .invoice-form.print-density-tight .doc-header { margin-bottom: 6px; padding-bottom: 4px; }
            .invoice-form.print-density-tight .services-table { font-size: 9px; margin-bottom: 6px; }
            .invoice-form.print-density-tight .services-table th,
            .invoice-form.print-density-tight .services-table td { padding: 3px 4px; }
            .invoice-form.print-density-tight .money-summary { margin: 4px 0 6px; padding: 5px 6px; font-size: 9px; }
            .invoice-form.print-density-tight .footer-sigs { margin-top: 8px; }
        }
        .no-print {
            margin-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            justify-content: center;
        }
        .btn {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            background: #1a1a2e;
            color: #fff;
        }
        .invoice-form { max-width: 100%; margin: 0 auto; }
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a1a1a;
            gap: 12px;
        }
        .header-right { text-align: right; flex: 1; min-width: 0; }
        .header-right .main-title { font-size: 22px; font-weight: 900; margin-bottom: 6px; }
        .header-right .sub { font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .header-right .line { font-size: 12px; margin-bottom: 2px; }
        .header-right .line .underline { display: inline-block; min-width: 120px; border-bottom: 1px solid #000; margin-right: 4px; }
        .header-center { text-align: center; flex: 0 0 160px; padding: 0 8px; }
        .header-center .logo-wrap img { max-width: 70px; max-height: 70px; display: block; margin: 0 auto; }
        .header-center .logo-placeholder { width: 70px; height: 70px; margin: 0 auto; border: 1px solid #999; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #555; }
        .header-center .moh-en { font-size: 12px; font-weight: 700; margin-bottom: 2px; }
        .header-center .moh-ar { font-size: 13px; font-weight: 700; margin-bottom: 8px; }
        .form-title-box {
            background: #1e40af;
            color: #fff !important;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            display: inline-block;
            font-size: 16px;
            font-weight: 700;
        }
        .header-left { flex: 1; text-align: left; font-size: 12px; min-width: 0; }
        .header-left .form-meta { margin-bottom: 6px; line-height: 1.7; }
        .header-left .form-meta label { font-weight: 700; }
        .header-left .form-meta .val { margin-right: 6px; font-weight: 600; }
        .header-left .invoice-num { font-size: 15px; font-weight: 700; color: #0f172a; letter-spacing: 0.5px; }
        .patient-box {
            margin-bottom: 12px;
            padding: 10px 12px;
            border: 1px solid #334155;
            border-radius: 6px;
            background: #f8fafc;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 18px;
            font-size: 12px;
        }
        .patient-box div strong { font-weight: 800; margin-left: 4px; }
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11px;
        }
        .services-table th, .services-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
        }
        .services-table th { background: #e8e8e8; font-weight: 700; }
        .services-table .col-code { width: 12%; }
        .services-table .col-desc { width: 38%; text-align: right; }
        .services-table .col-qty { width: 10%; }
        .services-table .col-unit { width: 14%; }
        .services-table .col-amount { width: 14%; font-weight: 700; }
        .money-summary {
            margin: 10px 0 12px;
            padding: 10px 12px;
            border: 2px solid #0f172a;
            border-radius: 6px;
            background: #f8fafc;
            font-size: 12px;
            line-height: 1.85;
        }
        .money-summary div { font-weight: 600; }
        .money-summary strong { font-weight: 800; color: #0f172a; }
        .money-summary .debt-line {
            margin-top: 4px;
            padding-top: 6px;
            border-top: 1px dashed #94a3b8;
            font-size: 13px;
            font-weight: 800;
            color: #9a3412;
        }
        .money-summary .debt-line strong { color: #9a3412; }
        .money-summary .splits-table { margin-top: 8px; margin-bottom: 0; font-size: 11px; }
        .payment-type-section { margin-bottom: 18px; }
        .payment-type-section .section-label { font-weight: 700; margin-bottom: 6px; font-size: 12px; }
        .payment-type-section .payment-row { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 18px; }
        .payment-type-section .payment-opt { display: flex; align-items: center; gap: 4px; }
        .payment-type-section .payment-opt input[type="checkbox"] { width: 14px; height: 14px; }
        .payment-type-section .payment-opt.inline-field { gap: 4px; }
        .payment-type-section .payment-opt.inline-field input[type="text"] { width: 95px; padding: 2px 5px; border: 1px solid #999; font-size: 11px; }
        .payment-type-section .bank-row { margin-top: 6px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .payment-type-section .bank-row span { font-size: 12px; }
        .payment-type-section .bank-row input { width: 100px; padding: 2px 5px; border: 1px solid #999; font-size: 11px; }
        .editable-print-input { background: #fefce8; border: 1px solid #ca8a04; }
        .footer-sigs { margin-top: 22px; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
        .footer-sigs .col { flex: 1; min-width: 120px; max-width: 180px; text-align: center; }
        .footer-sigs .col-title { font-weight: 700; margin-bottom: 6px; font-size: 12px; }
        .footer-sigs .col-content { min-height: 50px; border: 1px solid #333; padding: 6px; }
        .footer-sigs .sig-img img { max-width: 100px; max-height: 44px; margin-bottom: 2px; display: block; margin-left: auto; margin-right: auto; }
        .footer-sigs .sig-line { border-top: 1px solid #000; padding-top: 3px; font-size: 11px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn" onclick="window.print()">🖨️ طباعة الفاتورة</button>
        <a href="{{ route('invoices.show', $invoice) }}" style="text-decoration: none; color: #666;">← العودة للفاتورة</a>
    </div>

    <div class="invoice-form {{ $printDensityClass }}" id="invoice-print-root">
        <div class="doc-header">
            <div class="header-right">
                <div class="main-title">وزارة الصحة</div>
                <div class="sub">الإدارة العامة لتنمية ومتابعة الإيرادات</div>
                <div class="line">المديرية/التجمع : <span class="underline">{{ $s['health_cluster_name'] ?? '—' }}</span></div>
                <div class="line">المرفق الصحي : <span class="underline">{{ $s['hospital_name'] ?? '—' }}</span></div>
            </div>

            <div class="header-center">
                <div class="logo-wrap">
                    @if (!empty($s['logo']))
                        <img src="{{ asset('storage/' . $s['logo']) }}" alt="شعار">
                    @else
                        <div class="logo-placeholder">شعار</div>
                    @endif
                </div>
                <div class="moh-en">Ministry of Health</div>
                <div class="moh-ar">وزارة الصحة</div>
                <div class="form-title-box">فاتورة</div>
            </div>

            <div class="header-left">
                <div class="form-meta"><label>رقم الفاتورة:</label> <span class="val invoice-num">{{ $invoice->invoice_number }}</span></div>
                <div class="form-meta"><label>بتاريخ :</label> <span class="val">{{ $invoice->invoice_date?->format('d-m-Y') ?? now()->format('d-m-Y') }}</span></div>
                <div class="form-meta"><label>رقم الملف :</label> <span class="val">{{ $patient->file_number ?? '—' }}</span></div>
                <div class="form-meta"><label>رقم الإقامة :</label> <span class="val">{{ $patient->identity_value ?? '—' }}</span></div>
            </div>
        </div>

        <div class="patient-box">
            <div><strong>اسم المريض:</strong> {{ $patient?->fullArabicName() ?: ($patient->name ?? '—') }}</div>
            <div><strong>نوع الدفع:</strong> {{ $paymentTypeLabel }}</div>
            @if ($patient?->name && $patient->name !== $patient->fullArabicName())
                <div><strong>الاسم (إنجليزي):</strong> {{ $patient->name }}</div>
            @endif
            @if ($patient?->phone)
                <div><strong>الهاتف:</strong> <span dir="ltr">{{ $patient->phone }}</span></div>
            @endif
            @if ($patient?->country_of_origin)
                <div><strong>الجنسية:</strong> {{ $patient->country_of_origin }}</div>
            @endif
            @if ($patient?->profession)
                <div><strong>المهنة:</strong> {{ $patient->profession }}</div>
            @endif
            @if ($invoice->visit?->department)
                <div><strong>القسم:</strong> {{ $invoice->visit->department->name_ar ?? $invoice->visit->department->name }}</div>
            @elseif ($patient?->department)
                <div><strong>القسم:</strong> {{ $patient->department->name_ar ?? $patient->department->name }}</div>
            @endif
            @if ($patient?->payment_type === 'insurance' && $patient->insuranceCompany)
                <div><strong>شركة التأمين:</strong> {{ $patient->insuranceCompany->name_ar ?? $patient->insuranceCompany->name }}</div>
            @endif
            @if ($patient?->payment_type === 'charity' && $patient->charityEntity)
                <div><strong>الجمعية:</strong> {{ $patient->charityEntity->name_ar ?: $patient->charityEntity->name }}</div>
            @endif
        </div>

        <table class="services-table">
            <thead>
                <tr>
                    <th class="col-desc">البيان</th>
                    <th class="col-qty">الكمية</th>
                    <th class="col-unit">السعر الافرادي</th>
                    <th class="col-amount">المبلغ</th>
                    <th class="col-code">الرمز</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $item)
                    @php
                        $name = trim((string) ($item->description ?? ''))
                            ?: trim((string) ($item->service?->name_ar ?? ''))
                            ?: trim((string) ($item->service?->name ?? ''))
                            ?: '—';
                    @endphp
                    <tr>
                        <td class="col-desc">{{ $name }}</td>
                        <td class="col-qty">{{ \App\Helpers\NumeralHelper::toWesternDigits((string) (int) $item->quantity) }}</td>
                        <td class="col-unit">{{ CurrencyHelper::formatAmountDecimal((float) $item->unit_price) }}</td>
                        <td class="col-amount">{{ CurrencyHelper::formatAmountDecimal((float) $item->total_price) }}</td>
                        <td class="col-code">{{ $item->service?->code ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:12px;">—</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ملخص مالي داخل المربع فقط (تفاصيل الإيصال: طرق الدفع + الدين) --}}
        <div class="money-summary">
            <div><strong>إجمالي الفاتورة:</strong> {{ CurrencyHelper::formatAmountDecimal($total) }} ريال</div>
            <div><strong>المسدد:</strong> {{ CurrencyHelper::formatAmountDecimal($paid) }} ريال</div>
            @if ($showPaymentSplits)
                <table class="services-table splits-table">
                    <thead>
                        <tr>
                            <th class="col-desc">طريقة الدفع</th>
                            <th class="col-amount">المبلغ</th>
                            <th class="col-code">مرجع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($splitLines as $line)
                            <tr>
                                <td class="col-desc" style="text-align: right;">{{ PaymentReceipt::paymentMethodLabel($line->payment_method ?? '') }}</td>
                                <td class="col-amount">{{ CurrencyHelper::formatAmountDecimal((float) ($line->amount ?? 0)) }}</td>
                                <td class="col-code">{{ $line->reference_number ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <div class="debt-line">
                <strong>الدين (المتبقي):</strong> {{ CurrencyHelper::formatAmountDecimal($remaining) }} ريال
            </div>
        </div>

        <div class="payment-type-section">
            <div class="section-label">نوع السداد</div>
            <div class="payment-row">
                <label class="payment-opt"><input type="checkbox" {{ $isCash ? 'checked' : '' }} disabled> نقدي</label>
                <label class="payment-opt inline-field">شيك رقم <input type="text" class="editable-print-input" value="{{ $chequeRef }}" placeholder="—"></label>
                <label class="payment-opt"><input type="checkbox" {{ $isCard ? 'checked' : '' }} disabled> نقطة بيع / شبكة</label>
                <label class="payment-opt"><input type="checkbox" {{ $isLoyaltyPoints ? 'checked' : '' }} disabled> نقاط بيع</label>
                <label class="payment-opt inline-field">إيداع في الحساب رقم : <input type="text" class="editable-print-input" value="{{ $bankRef }}" placeholder="—"></label>
            </div>
            <div class="bank-row">
                <span>على بنك : <input type="text" class="editable-print-input" value="" placeholder="—"></span>
                <span>فرع <input type="text" class="editable-print-input" value="" placeholder="—"></span>
            </div>
        </div>

        <div class="footer-sigs">
            <div class="col">
                <div class="col-title">الموظف</div>
                <div class="col-content">
                    @if (auth()->check() && auth()->user()->signature)
                        <div class="sig-img"><img src="{{ asset('storage/' . ltrim(auth()->user()->signature ?? '', '/')) }}" alt="توقيع"></div>
                    @else
                        <div style="min-height:36px;"></div>
                    @endif
                    <div class="sig-line">{{ auth()->user()->name ?? '________________' }}</div>
                </div>
            </div>
            <div class="col">
                <div class="col-title">مدير إدارة تنمية الإيرادات</div>
                <div class="col-content">
                    @if (isset($manager) && $manager && $manager->signature)
                        <div class="sig-img"><img src="{{ asset('storage/' . ltrim($manager->signature ?? '', '/')) }}" alt="توقيع المدير"></div>
                    @else
                        <div style="min-height:36px;"></div>
                    @endif
                    <div class="sig-line">{{ isset($manager) && $manager ? ($manager->name_ar ?? $manager->name) : ($s['manager_name'] ?? '________________') }}</div>
                </div>
            </div>
            <div class="col">
                <div class="col-title">الختم</div>
                <div class="col-content">
                    @if (!empty($s['stamp']))
                        <img src="{{ asset('storage/' . $s['stamp']) }}" alt="ختم" style="max-width:80px;max-height:40px;display:block;margin:0 auto;">
                    @else
                        <div style="min-height:40px;"></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="print-only" style="text-align: center; font-size: 9px; color: #888; margin-top: 6px;">
            فاتورة رسمية — {{ now()->format('Y/m/d H:i') }}
        </div>
    </div>

    @include('components.report-footer')

    <script>
        (function () {
            var root = document.getElementById('invoice-print-root');
            if (!root) return;
            function fitToOnePage() {
                root.classList.remove('print-fit-scale');
                root.style.transform = '';
                root.style.width = '';
                var pageHeight = 1122;
                var contentHeight = root.scrollHeight;
                if (contentHeight > pageHeight - 8) {
                    var scale = Math.min(1, (pageHeight - 8) / contentHeight);
                    root.classList.add('print-fit-scale');
                    root.style.transform = 'scale(' + scale + ')';
                    root.style.width = (100 / scale) + '%';
                }
            }
            function resetScale() {
                root.classList.remove('print-fit-scale');
                root.style.transform = '';
                root.style.width = '';
            }
            window.addEventListener('beforeprint', fitToOnePage);
            window.addEventListener('afterprint', resetScale);
        })();
    </script>
</body>
</html>
