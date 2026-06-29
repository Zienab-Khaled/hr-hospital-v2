@php
    use App\Helpers\CurrencyHelper;
    use App\Models\PaymentReceipt;
    $splitLines = $receipt->splitsForDisplay();
    $nSplitLines = $splitLines->count();
    $cashFromPatient = ($nSplitLines <= 1 && $receipt->patient_cash_amount && (float) $receipt->patient_cash_amount > 0)
        ? (float) $receipt->patient_cash_amount
        : null;
    $displayAmount = $cashFromPatient !== null ? $cashFromPatient : (float) $receipt->amount;
    $amountWords = CurrencyHelper::amountInArabicWords($displayAmount);
    $patient = $receipt->patient;
    $anyCash = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'cash');
    $anyCard = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'card');
    $anyLoyaltyPoints = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'loyalty_points');
    $anyBank = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'bank_transfer');
    $anyCheque = $splitLines->contains(fn ($l) => ($l->payment_method ?? null) === 'cheque');
    $isCash = $anyCash;
    $isCard = $anyCard;
    $isLoyaltyPoints = $anyLoyaltyPoints;
    $isBank = $anyBank;
    $isCheque = $anyCheque;
    $chequeLine = $splitLines->first(fn ($l) => ($l->payment_method ?? null) === 'cheque');
    $chequeRefFromSplit = $chequeLine ? (string) ($chequeLine->reference_number ?? '') : '';
    $bankLine = $splitLines->first(fn ($l) => ($l->payment_method ?? null) === 'bank_transfer');
    $bankRefFromSplit = $bankLine ? (string) ($bankLine->reference_number ?? '') : '';
    $s = $settingsData ?? [];
    $ministryNumber = $receipt->ministry_receipt_number ?? $receipt->receipt_number;
    $rowsForTable = isset($displaySelectedItems) && is_array($displaySelectedItems) && count($displaySelectedItems) > 0
        ? $displaySelectedItems
        : ($receipt->selected_items ?? []);
    $remainingToCollect = round((float) ($receipt->invoice_snapshot_remaining ?? 0), 2);
    $snapInvoiceTotal = round((float) ($receipt->invoice_snapshot_total ?? 0), 2);
    $snapPaidThisReceipt = round((float) ($receipt->amount ?? 0), 2);
    $snapPaidCumulative = round((float) ($receipt->invoice_snapshot_paid ?? 0), 2);
    $printRowCount = count($rowsForTable) + $nSplitLines;
    $printDensityClass = $printRowCount > 14 ? 'print-density-tight' : ($printRowCount > 9 ? 'print-density-compact' : '');
@endphp
<!DOCTYPE html>
<html lang="ar-SA-u-nu-latn" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إيصال تحصيل - {{ $receipt->receipt_number }}</title>
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
            html, body {
                height: auto;
                overflow: visible;
                margin: 0;
                padding: 0;
            }
            body { padding: 4px 8px; font-size: 11px; }
            @page { margin: 0.4cm; size: A4 portrait; }
            .moh-form {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .moh-form.print-fit-scale {
                transform-origin: top center;
            }
        }
        @media print {
            .moh-form.print-density-compact .moh-header { margin-bottom: 8px; padding-bottom: 6px; }
            .moh-form.print-density-compact .moh-header-right .moh-main-title { font-size: 20px; }
            .moh-form.print-density-compact .services-table { font-size: 10px; margin-bottom: 8px; }
            .moh-form.print-density-compact .services-table th,
            .moh-form.print-density-compact .services-table td { padding: 4px 5px; }
            .moh-form.print-density-compact .receipt-money-summary { margin: 6px 0 8px; padding: 6px 8px; font-size: 10px; line-height: 1.5; }
            .moh-form.print-density-compact .total-section .total-num { font-size: 15px; }
            .moh-form.print-density-compact .footer-sigs { margin-top: 12px; }
            .moh-form.print-density-compact .footer-sigs .col-content { min-height: 40px; padding: 4px; }
            .moh-form.print-density-tight .moh-header { margin-bottom: 6px; padding-bottom: 4px; }
            .moh-form.print-density-tight .moh-header-right .moh-main-title { font-size: 18px; }
            .moh-form.print-density-tight .moh-header-center .form-title-box { padding: 6px 14px; font-size: 14px; }
            .moh-form.print-density-tight .services-table { font-size: 9px; margin-bottom: 6px; }
            .moh-form.print-density-tight .services-table th,
            .moh-form.print-density-tight .services-table td { padding: 3px 4px; }
            .moh-form.print-density-tight .receipt-money-summary { margin: 4px 0 6px; padding: 5px 6px; font-size: 9px; line-height: 1.4; }
            .moh-form.print-density-tight .total-section .total-num { font-size: 14px; }
            .moh-form.print-density-tight .total-section .total-words { font-size: 10px; }
            .moh-form.print-density-tight .payment-type-section { margin-bottom: 10px; }
            .moh-form.print-density-tight .footer-sigs { margin-top: 8px; }
            .moh-form.print-density-tight .footer-sigs .col { min-width: 100px; max-width: 150px; }
            .moh-form.print-density-tight .footer-sigs .col-content { min-height: 34px; padding: 3px; }
        }
        .no-print {
            margin-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            justify-content: center;
        }
        .no-print .hint { font-size: 12px; color: #555; width: 100%; text-align: center; margin-top: 4px; }
        .editable-print-input { background: #fefce8; border: 1px solid #ca8a04; }
        @media print {
            .editable-print-input { background: transparent; border-bottom: 1px solid #000; border-top: none; border-left: none; border-right: none; }
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

        /* —— تصميم نموذج وزارة الصحة (ق-1) حرفياً —— */
        .moh-form {
            max-width: 100%;
            margin: 0 auto;
        }
        .moh-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a1a1a;
            gap: 12px;
        }
        /* يمين: وزارة الصحة + الإدارة + المديرية + المرفق + المدة + نوع الإيراد */
        .moh-header-right {
            text-align: right;
            flex: 1;
            min-width: 0;
        }
        .moh-header-right .moh-main-title { font-size: 24px; font-weight: 900; margin-bottom: 6px; font-family: 'Tajawal', sans-serif; }
        .moh-header-right .moh-sub { font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .moh-header-right .moh-line { font-size: 12px; margin-bottom: 2px; }
        .moh-header-right .moh-line label { font-weight: 700; }
        .moh-header-right .moh-line .underline { display: inline-block; min-width: 120px; border-bottom: 1px solid #000; margin-right: 4px; }
        .moh-header-right .revenue-type-label { font-weight: 700; font-size: 12px; margin-top: 6px; margin-bottom: 4px; }
        .moh-header-right .moh-checkboxes { display: flex; flex-wrap: wrap; gap: 8px 16px; font-size: 11px; }
        .moh-header-right .moh-checkboxes label { display: inline-flex; align-items: center; gap: 4px; font-weight: 400; }
        .moh-header-right .moh-checkboxes input { width: 14px; height: 14px; }

        /* وسط: شعار + Ministry of Health + وزارة الصحة + صندوق ايصال تحصيل */
        .moh-header-center {
            text-align: center;
            flex: 0 0 160px;
            padding: 0 8px;
        }
        .moh-header-center .logo-wrap { margin-bottom: 6px; }
        .moh-header-center .logo-wrap img { max-width: 70px; max-height: 70px; display: block; margin: 0 auto; }
        .moh-header-center .logo-placeholder { width: 70px; height: 70px; margin: 0 auto; border: 1px solid #999; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #555; }
        .moh-header-center .moh-en { font-size: 12px; font-weight: 700; margin-bottom: 2px; }
        .moh-header-center .moh-ar { font-size: 13px; font-weight: 700; margin-bottom: 8px; }
        .form-title-box {
            background: #157347;
            color: #fff !important;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            display: inline-block;
            font-size: 16px;
            font-weight: 700;
        }

        /* يسار: نموذج (ق-1) + رقم / [ترقيم الوزارة] + الرقم الإلكتروني + بتاريخ + رقم الملف + رقم الهوية */
        .moh-header-left {
            flex: 1;
            text-align: left;
            font-size: 12px;
            min-width: 0;
        }
        .moh-header-left .form-meta { margin-bottom: 6px; line-height: 1.7; }
        .moh-header-left .form-meta label { font-weight: 700; }
        .moh-header-left .form-meta .val { margin-right: 6px; font-weight: 600; }
        .moh-header-left .ministry-num { font-size: 15px; font-weight: 700; color: #0f172a; letter-spacing: 1px; }

        .form-number-row { margin-bottom: 6px; }
        .form-number-row label { font-weight: 700; }

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

        .total-section { margin-bottom: 14px; }
        .total-section .total-inline { display: flex; align-items: baseline; gap: 14px; flex-wrap: wrap; margin-bottom: 4px; }
        .total-section .total-num { font-size: 17px; font-weight: 700; }
        .total-section .total-words { font-size: 12px; line-height: 1.6; }
        .total-section .total-extra { margin-top: 4px; font-size: 10px; color: #555; }
        .receipt-money-summary {
            margin: 10px 0 12px;
            padding: 10px 12px;
            border: 2px solid #0f172a;
            border-radius: 6px;
            background: #f8fafc;
            font-size: 12px;
            line-height: 1.85;
        }
        .receipt-money-summary div { font-weight: 600; }
        .receipt-money-summary strong { font-weight: 800; color: #0f172a; }

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

        /* توقيعات: المحصل، التوقيع، الختم (بدون تسمية نسخ) */
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
        <button class="btn" onclick="window.print()">🖨️ طباعة الإيصال (ق-1)</button>
        <a href="{{ route('invoices.show', $invoice) }}" style="text-decoration: none; color: #666;">← العودة للفاتورة</a>
        <p class="hint">يمكنك تعديل حقول «شيك رقم» و«إيداع في الحساب» و«على بنك» و«فرع» أدناه قبل الطباعة.</p>
    </div>

    <div class="moh-form {{ $printDensityClass }}" id="receipt-print-root">
        <div class="moh-header">
            {{-- يمين: وزارة الصحة + الإدارة + المديرية + المرفق + المدة + نوع الإيراد --}}
            <div class="moh-header-right">
                <div class="moh-main-title">وزارة الصحة</div>
                <div class="moh-sub">الإدارة العامة لتنمية ومتابعة الإيرادات</div>
                <div class="moh-line">المديرية/التجمع : <span class="underline">{{ $s['health_cluster_name'] ?? '—' }}</span></div>
                <div class="moh-line">المرفق الصحي : <span class="underline">{{ $s['hospital_name'] ?? '—' }}</span></div>
                <div class="moh-line">المدة : <span class="underline"></span></div>
                <div class="revenue-type-label">نوع الإيراد :</div>
                <div class="moh-checkboxes">
                    <label><input type="checkbox" checked disabled> الخدمات الصحية بمقابل</label>
                    <label><input type="checkbox" disabled> التدريب بأجر</label>
                    <label><input type="checkbox" disabled> استثمار</label>
                    <label><input type="checkbox" disabled> أخرى</label>
                </div>
            </div>

            {{-- وسط: شعار + Ministry of Health + وزارة الصحة + ايصال تحصيل --}}
            <div class="moh-header-center">
                <div class="logo-wrap">
                    @if(!empty($s['logo']))
                        <img src="{{ asset('storage/' . $s['logo']) }}" alt="شعار وزارة الصحة">
                    @else
                        <div class="logo-placeholder">شعار وزارة الصحة</div>
                    @endif
                </div>
                <div class="moh-en">Ministry of Health</div>
                <div class="moh-ar">وزارة الصحة</div>
                <div class="form-title-box">ايصال تحصيل</div>
            </div>

            {{-- يسار: نموذج (ق-1) + رقم / [ترقيم الوزارة] + الرقم الإلكتروني + بتاريخ + رقم الملف + رقم الهوية --}}
            <div class="moh-header-left">
                <div class="form-meta">نموذج (ق-1)</div>
                <div class="form-number-row">
                    <label>رقم /</label>
                    <span class="val ministry-num">{{ $ministryNumber }}</span>
                </div>
                <div class="form-meta"><label>الرقم الإلكتروني:</label> <span class="val">{{ $receipt->id }}</span></div>
                <div class="form-meta"><label>بتاريخ :</label> <span class="val">{{ $receipt->collected_at?->format('d-m-Y') }}</span></div>
                <div class="form-meta"><label>رقم الملف :</label> <span class="val">{{ $patient->file_number ?? '—' }}</span></div>
                <div class="form-meta"><label>رقم الهوية :</label> <span class="val">{{ $patient->identity_value ?? '—' }}</span></div>
            </div>
        </div>

        {{-- جدول الخدمات — ترتيب الأعمدة كالإيصال الورقي: البيان ثم الكمية ثم السعر ثم المبلغ ثم الرمز (يسار) --}}
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
                @if(!empty($rowsForTable))
                    @foreach($rowsForTable as $item)
                    <tr>
                        <td class="col-desc">{{ $item['name'] ?? '—' }}</td>
                        <td class="col-qty">{{ \App\Helpers\NumeralHelper::toWesternDigits((string) (int) ($item['qty'] ?? 0)) }}</td>
                        <td class="col-unit">{{ \App\Helpers\CurrencyHelper::formatAmountDecimal((float) ($item['unit_price'] ?? 0)) }}</td>
                        <td class="col-amount">{{ \App\Helpers\CurrencyHelper::formatAmountDecimal((float) ($item['total'] ?? 0)) }}</td>
                        <td class="col-code">{{ $item['code'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align:center;padding:12px;">—</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="receipt-money-summary">
            <div><strong>إجمالي الفاتورة:</strong> {{ \App\Helpers\CurrencyHelper::formatAmountDecimal($snapInvoiceTotal) }} ريال</div>
            <div><strong>المدفوع في هذا الإيصال:</strong> {{ \App\Helpers\CurrencyHelper::formatAmountDecimal($snapPaidThisReceipt) }} ريال</div>
            <div><strong>إجمالي المسدد على الفاتورة بعد هذا الإيصال:</strong> {{ \App\Helpers\CurrencyHelper::formatAmountDecimal($snapPaidCumulative) }} ريال</div>
            <div><strong>المتبقي للتحصيل على الفاتورة:</strong> {{ \App\Helpers\CurrencyHelper::formatAmountDecimal($remainingToCollect) }} ريال</div>
        </div>

        <div class="total-section">
            <div class="total-inline">
                <span class="total-num">{{ \App\Helpers\CurrencyHelper::formatAmountDecimal((float) $displayAmount) }} ريال</span>
                <span class="total-words">{{ $amountWords }}</span>
            </div>
            @if(($receipt->total_payment_amount ?? 0) > 0 && (float)$receipt->total_payment_amount !== (float)$displayAmount)
            <div class="total-extra">إجمالي دفعة الفاتورة (كامل المبلغ المسجل): {{ \App\Helpers\CurrencyHelper::formatAmountDecimal((float) $receipt->total_payment_amount) }} ريال</div>
            @endif
            @if ($nSplitLines >= 1 && (float) $receipt->amount > 0)
                <table class="services-table" style="margin-top: 10px;">
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
                                <td class="col-amount">{{ \App\Helpers\CurrencyHelper::formatAmountDecimal((float) ($line->amount ?? 0)) }}</td>
                                <td class="col-code">{{ $line->reference_number ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- نوع السداد + على بنك + فرع --}}
        <div class="payment-type-section">
            <div class="section-label">نوع السداد</div>
            <div class="payment-row">
                <label class="payment-opt"><input type="checkbox" {{ $isCash ? 'checked' : '' }} disabled> نقدي</label>
                <label class="payment-opt inline-field">شيك رقم <input type="text" class="editable-print-input" id="cheque-no" value="{{ $chequeRefFromSplit !== '' ? $chequeRefFromSplit : ($isCheque ? ($receipt->reference_number ?? '') : '') }}" placeholder="—"></label>
                <label class="payment-opt"><input type="checkbox" {{ $isCard ? 'checked' : '' }} disabled> نقطة بيع / شبكة</label>
                <label class="payment-opt"><input type="checkbox" {{ $isLoyaltyPoints ? 'checked' : '' }} disabled> نقاط بيع</label>
                <label class="payment-opt inline-field">إيداع في الحساب رقم : <input type="text" class="editable-print-input" id="deposit-account" value="{{ $bankRefFromSplit !== '' ? $bankRefFromSplit : ($isBank ? ($receipt->reference_number ?? '') : '') }}" placeholder="—"></label>
            </div>
            <div class="bank-row">
                <span>على بنك : <input type="text" class="editable-print-input" id="bank-name" value="" placeholder="—"></span>
                <span>فرع <input type="text" class="editable-print-input" id="bank-branch" value="" placeholder="—"></span>
            </div>
        </div>

        {{-- المحصل، التوقيع، الختم — بدون تسمية (أصل للمسدد / صورة المحصل / صورة للملف) --}}
        <div class="footer-sigs">
            <div class="col">
                <div class="col-title">المحصل</div>
                <div class="col-content">
                    @if($receipt->collectedBy && $receipt->collectedBy->signature)
                        <div class="sig-img"><img src="{{ asset('storage/' . ltrim($receipt->collectedBy->signature ?? '', '/')) }}" alt="توقيع المحصل"></div>
                    @else
                        <div style="min-height:36px;"></div>
                    @endif
                    <div class="sig-line">{{ $receipt->collectedBy->name_ar ?? $receipt->collectedBy->name ?? '________________' }}</div>
                </div>
            </div>
            <div class="col">
                <div class="col-title">التوقيع</div>
                <div class="col-content" style="min-height:50px;"></div>
            </div>
            <div class="col">
                <div class="col-title">الختم</div>
                <div class="col-content">
                    @if(!empty($s['stamp']))
                        <img src="{{ asset('storage/' . $s['stamp']) }}" alt="ختم" style="max-width:80px;max-height:40px;display:block;margin:0 auto;">
                    @else
                        <div style="min-height:40px;"></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="receipt-print-footer print-only" style="text-align: center; font-size: 9px; color: #888; margin-top: 6px;">
            تم استخراج هذا الإيصال إلكترونياً — {{ now()->format('Y/m/d H:i') }}
        </div>
    </div>

    <div class="no-print" style="position: fixed; bottom: 8px; left: 0; width: 100%; text-align: center; font-size: 9px; color: #888;">
        تم استخراج هذا الإيصال إلكترونياً من نظام المستشفى — {{ now()->format('Y/m/d H:i') }}
    </div>

    @include('components.report-footer')

    <script>
        (function() {
            var root = document.getElementById('receipt-print-root');
            if (!root) return;

            function fitReceiptToOnePage() {
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

            function resetReceiptPrintScale() {
                root.classList.remove('print-fit-scale');
                root.style.transform = '';
                root.style.width = '';
            }

            window.addEventListener('beforeprint', fitReceiptToOnePage);
            window.addEventListener('afterprint', resetReceiptPrintScale);
        })();
    </script>
</body>

</html>
