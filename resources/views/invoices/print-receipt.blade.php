@php
    use App\Helpers\CurrencyHelper;
    $cashFromPatient = $receipt->patient_cash_amount && (float)$receipt->patient_cash_amount > 0 ? (float)$receipt->patient_cash_amount : null;
    $displayAmount = $cashFromPatient !== null ? $cashFromPatient : (float)$receipt->amount;
    $amountWords = CurrencyHelper::amountInArabicWords($displayAmount);
    $patient = $receipt->patient;
    $isCash = $receipt->payment_method === 'cash';
    $isCard = $receipt->payment_method === 'card';
    $isBank = $receipt->payment_method === 'bank_transfer';
    $isCheque = $receipt->payment_method === 'cheque';
    $s = $settingsData ?? [];
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
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
        @media print {
            .no-print { display: none !important; }
            body { padding: 6px 12px; font-size: 12px; }
            @page { margin: 1cm; size: A4; }
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
        .sig-name-placeholder { min-height: 46px; display: flex; align-items: center; justify-content: center; border: 1px dashed #666; padding: 6px; font-size: 12px; color: #333; margin-bottom: 4px; text-align: center; }
        @media print {
            .sig-name-placeholder { border: 1px solid #999; }
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

        .moh-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a1a1a;
        }
        .moh-header-right {
            text-align: right;
            flex: 1;
            min-width: 0;
        }
        .moh-header-right .moh-main-title { font-size: 18px; font-weight: 700; margin-bottom: 3px; font-family: 'Tajawal', sans-serif; }
        .moh-header-right .moh-sub { font-size: 12px; color: #222; margin-bottom: 1px; }
        .moh-header-right .moh-facility { font-size: 12px; color: #222; margin-top: 5px; margin-bottom: 6px; }
        .moh-header-right .payer-label { font-weight: 700; font-size: 13px; display: block; margin-bottom: 2px; }
        .moh-header-right .payer-name { font-size: 13px; border-bottom: 1px solid #666; display: inline-block; min-width: 160px; padding: 0 4px 2px; }
        .moh-header-right .moh-checkboxes { margin-top: 8px; }
        .moh-header-right .moh-checkboxes label { display: inline-block; margin-left: 14px; font-weight: 400; font-size: 11px; }
        .moh-header-right .moh-checkboxes input { width: 14px; height: 14px; vertical-align: middle; }

        .moh-header-center {
            text-align: center;
            flex: 0 0 175px;
            padding: 0 6px;
        }
        .moh-header-center .logo-wrap { margin-bottom: 4px; }
        .moh-header-center .logo-wrap img { max-width: 62px; max-height: 62px; display: block; margin: 0 auto; }
        .moh-header-center .logo-placeholder { width: 62px; height: 62px; margin: 0 auto; border: 1px solid #999; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #555; }
        .moh-header-center .moh-title-below-logo { font-size: 13px; font-weight: 700; margin-bottom: 6px; }
        .form-title-box {
            background: #157347;
            color: #fff !important;
            border: none;
            padding: 8px 18px;
            display: inline-block;
            font-size: 15px;
            font-weight: 700;
        }

        .moh-header-left {
            flex: 1;
            text-align: left;
            font-size: 12px;
            min-width: 0;
        }
        .moh-header-left .form-meta { margin-bottom: 5px; line-height: 1.6; }
        .moh-header-left .form-meta label { font-weight: 700; }
        .moh-header-left .form-meta .val { margin-right: 6px; }

        .services-heading { font-size: 14px; font-weight: 700; margin-bottom: 8px; padding: 0 2px; }
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11px;
        }
        .services-table th, .services-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            text-align: center;
        }
        .services-table th { background: #e8e8e8; font-weight: 700; }
        .services-table .col-code { width: 11%; }
        .services-table .col-desc { width: 40%; text-align: right; }
        .services-table .col-qty { width: 8%; }
        .services-table .col-unit { width: 14%; }
        .services-table .col-amount { width: 14%; font-weight: 700; }

        .total-section { margin-bottom: 14px; }
        .total-section .total-inline { display: flex; align-items: baseline; gap: 14px; flex-wrap: wrap; margin-bottom: 4px; }
        .total-section .total-num { font-size: 17px; font-weight: 700; }
        .total-section .total-words { font-size: 12px; line-height: 1.6; }
        .total-section .total-extra { margin-top: 4px; font-size: 10px; color: #555; }

        .payment-type-section { margin-bottom: 18px; }
        .payment-type-section .section-label { font-weight: 700; margin-bottom: 6px; font-size: 12px; }
        .payment-type-section .payment-row { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 18px; }
        .payment-type-section .payment-opt { display: flex; align-items: center; gap: 4px; }
        .payment-type-section .payment-opt input[type="checkbox"] { width: 14px; height: 14px; }
        .payment-type-section .payment-opt.inline-field { gap: 4px; }
        .payment-type-section .payment-opt.inline-field input[type="text"] { width: 95px; padding: 2px 5px; border: 1px solid #999; font-size: 11px; }

        .footer-sigs { margin-top: 22px; display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
        .footer-sigs .col { flex: 1; min-width: 130px; max-width: 200px; text-align: center; }
        .footer-sigs .col-title { font-weight: 700; margin-bottom: 6px; font-size: 12px; }
        .footer-sigs .col-content { min-height: 52px; }
        .footer-sigs .sig-img img { max-width: 110px; max-height: 46px; margin-bottom: 2px; display: block; margin-left: auto; margin-right: auto; }
        .footer-sigs .sig-line { border-top: 1px solid #000; padding-top: 3px; font-size: 11px; margin-top: 4px; }
        .footer-sigs .copy-note { font-size: 9px; color: #444; margin-top: 5px; }
        .footer-sigs .stamp-box {
            border: 1px solid #333;
            padding: 8px 6px;
            min-height: 58px;
            font-size: 10px;
            color: #111;
            text-align: center;
            line-height: 1.4;
        }
        .footer-sigs .stamp-box img { max-width: 85px; max-height: 42px; opacity: 0.95; margin-bottom: 4px; display: block; margin-left: auto; margin-right: auto; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ طباعة الإيصال (ق-1)</button>
        <a href="{{ route('invoices.show', $invoice) }}" style="text-decoration: none; color: #666;">← العودة للفاتورة</a>
        <p class="hint">يمكنك تعديل حقول «شيك رقم» و«إيداع في الحساب» و«على بنك» أدناه قبل الطباعة.</p>
    </div>

    @include('components.report-header')

    <div class="moh-header">
        <div class="moh-header-right">
            <div class="moh-main-title">وزارة الصحة</div>
            <div class="moh-sub">الإدارة العامة لتنمية ومتابعة الإيرادات</div>
            <div class="moh-sub">المديرية / {{ $s['health_cluster_name'] ?? 'تجمع الجوف الصحي' }}</div>
            <div class="moh-facility">المرفق الصحي: {{ $s['hospital_name'] ?? 'مستشفى الأمير متعب بن عبدالعزيز' }}</div>
            <div class="payer-label">المسدد:</div>
            <span class="payer-name">{{ $patient->name_ar ?? $patient->name }}</span>
            <div class="moh-checkboxes">
                <label><input type="checkbox" disabled> نوع المرفق الصحي</label>
                <label><input type="checkbox" disabled> خدمات صحية إضافية</label>
                <label><input type="checkbox" disabled> التدريب بأجر</label>
            </div>
        </div>
        <div class="moh-header-center">
            <div class="logo-wrap">
                @if(!empty($s['logo']))
                    <img src="{{ asset('storage/' . $s['logo']) }}" alt="شعار">
                @else
                    <div class="logo-placeholder">شعار</div>
                @endif
            </div>
            <div class="moh-title-below-logo">وزارة الصحة</div>
            <div class="form-title-box">إيصال تحصيل</div>
        </div>
        <div class="moh-header-left">
            <div class="form-meta"><label>نموذج (ق 1)</label></div>
            <div class="form-meta"><label>رقم:</label> <span class="val">{{ $receipt->receipt_number }}</span></div>
            <div class="form-meta"><label>الرقم الإلكتروني:</label> <span class="val">{{ $receipt->id }}</span></div>
            <div class="form-meta"><label>تاريخ:</label> <span class="val">{{ $receipt->collected_at?->format('d-m-Y') }}</span></div>
            <div class="form-meta"><label>رقم ملف:</label> <span class="val">{{ $patient->file_number ?? '—' }}</span></div>
        </div>
    </div>

    <div class="services-heading">الخدمات المقدمة</div>
    <table class="services-table">
        <thead>
            <tr>
                <th class="col-code">الرمز</th>
                <th class="col-desc">البيان</th>
                <th class="col-qty">الكمية</th>
                <th class="col-unit">السعر الافرادي</th>
                <th class="col-amount">المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($receipt->selected_items))
                @foreach($receipt->selected_items as $item)
                <tr>
                    <td class="col-code">{{ $item['code'] ?? '—' }}</td>
                    <td class="col-desc">{{ $item['name'] ?? '—' }}</td>
                    <td class="col-qty">{{ $item['qty'] ?? 0 }}</td>
                    <td class="col-unit">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                    <td class="col-amount">{{ number_format($item['total'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" style="text-align:center;padding:10px;">—</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-inline">
            <span class="total-num">{{ number_format($displayAmount, 2) }}</span>
            <span class="total-words">{{ $amountWords }}</span>
        </div>
        @if(($receipt->total_payment_amount ?? 0) > 0 && (float)$receipt->total_payment_amount !== (float)$displayAmount)
        <div class="total-extra">إجمالي دفعة الفاتورة (كامل المبلغ المسجل): {{ number_format($receipt->total_payment_amount, 2) }} ريال</div>
        @endif
    </div>

    <div class="payment-type-section">
        <div class="section-label">نوع السداد:</div>
        <div class="payment-row">
            <label class="payment-opt"><input type="checkbox" {{ $isCash ? 'checked' : '' }} disabled> نقدي</label>
            <label class="payment-opt inline-field">شيك رقم <input type="text" class="editable-print-input" id="cheque-no" value="{{ $isCheque ? ($receipt->reference_number ?? '') : '' }}" placeholder="—"></label>
            <label class="payment-opt"><input type="checkbox" {{ $isCard ? 'checked' : '' }} disabled> نقطة بيع</label>
            <label class="payment-opt inline-field">إيداع في الحساب رقم <input type="text" class="editable-print-input" id="deposit-account" value="{{ $isBank ? ($receipt->reference_number ?? '') : '' }}" placeholder="—"></label>
            <label class="payment-opt inline-field">على بنك <input type="text" class="editable-print-input" id="bank-name" value="" placeholder="—"></label>
        </div>
    </div>

    {{-- أربعة أعمدة: المحصل (ختم الموظف)، التوقيع، ختم الشركة، ختم مدير النظام --}}
    <div class="footer-sigs">
        <div class="col">
            <div class="col-title">المحصل (ختم الموظف)</div>
            <div class="col-content">
                @if($receipt->collectedBy && $receipt->collectedBy->signature)
                    <div class="sig-img"><img src="{{ asset('storage/' . $receipt->collectedBy->signature) }}" alt="توقيع المحصل"></div>
                @else
                    @php $collectorName = $receipt->collectedBy ? ($receipt->collectedBy->name_ar ?? $receipt->collectedBy->name) : ''; @endphp
                    @if($collectorName)
                        <div class="sig-name-placeholder">الاسم: {{ $collectorName }}</div>
                    @endif
                @endif
                <div class="sig-line">{{ $receipt->collectedBy->name_ar ?? $receipt->collectedBy->name ?? '________________' }}</div>
            </div>
            <div class="copy-note">الأصل للمسدد</div>
            <div class="copy-note">صورة المحصل</div>
        </div>
        <div class="col">
            <div class="col-title">التوقيع</div>
            <div class="col-content" style="min-height:52px;"></div>
            <div class="copy-note">صورة للملف</div>
        </div>
        <div class="col">
            <div class="col-title">الختم (ختم الشركة)</div>
            <div class="col-content">
                <div class="stamp-box">
                    @if(!empty($s['stamp']))
                        <img src="{{ asset('storage/' . $s['stamp']) }}" alt="ختم الشركة">
                    @endif
                    <div>المملكة العربية السعودية</div>
                    <div>{{ $s['hospital_name'] ?? 'مستشفى' }}</div>
                    <div style="font-size:9px;">{{ $s['financial_dept_name'] ?? 'إدارة الموارد المالية' }}</div>
                </div>
            </div>
            <div class="copy-note">صورة للمحاسب</div>
        </div>
        <div class="col">
            <div class="col-title">ختم مدير النظام</div>
            <div class="col-content">
                @php
                    $managerHasSig = ($manager && $manager->signature) || !empty($s['manager_signature']);
                    $managerDisplayName = $manager ? ($manager->name_ar ?? $manager->name) : ($s['manager_name'] ?? '');
                @endphp
                @if($manager && $manager->signature)
                    <div class="sig-img"><img src="{{ asset('storage/' . $manager->signature) }}" alt="توقيع مدير النظام"></div>
                @elseif(!empty($s['manager_signature']))
                    <div class="sig-img"><img src="{{ asset('storage/' . $s['manager_signature']) }}" alt="توقيع مدير النظام"></div>
                @elseif($managerDisplayName)
                    <div class="sig-name-placeholder">الاسم: {{ $managerDisplayName }}</div>
                @endif
                <div class="sig-line">{{ $managerDisplayName ?: '________________' }}</div>
            </div>
        </div>
    </div>

    <div style="position: fixed; bottom: 8px; left: 0; width: 100%; text-align: center; font-size: 9px; color: #888;">
        تم استخراج هذا الإيصال إلكترونياً من نظام المستشفى — {{ now()->format('Y/m/d H:i') }}
    </div>
</body>
</html>
