<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إيصال تحصيل - {{ $receipt->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #000;
            font-size: 14px;
            padding: 30px 40px;
            position: relative;
            min-height: 100vh;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            @page { margin: 1cm; size: A4; }
        }
        .no-print {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: center;
        }
        .btn {
            padding: 9px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-dark { background: #1a1a2e; color: #fff; }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        .header-logo img { max-width: 90px; max-height: 90px; }
        .header-info { text-align: center; flex: 1; padding: 0 20px; }
        .header-info .hospital-name-ar { font-size: 18px; font-weight: bold; margin-bottom: 3px; }
        .header-info .dept-name { font-size: 14px; font-weight: bold; margin-top: 5px; color: #555; }
        .form-title {
            text-align: center;
            font-size: 28px;
            font-weight: 900;
            margin: 20px 0 30px;
            text-decoration: underline;
        }
        .receipt-container {
            border: 2px solid #000;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            margin-bottom: 15px;
            font-size: 16px;
            line-height: 1.6;
        }
        .info-label { font-weight: bold; width: 140px; shrink: 0; }
        .info-value { border-bottom: 1px dotted #666; flex: 1; padding: 0 10px; }
        .financial-summary {
            margin-top: 30px;
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 20px;
        }
        .summary-box {
            border: 1px solid #333;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .summary-title { font-weight: bold; font-size: 14px; margin-bottom: 10px; color: #444; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 15px; }
        .summary-label { font-weight: bold; }
        .summary-value { font-weight: 900; }
        .footer-sigs {
            margin-top: 60px;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        .sig-box { width: 220px; }
        .sig-title { font-weight: bold; margin-bottom: 40px; }
        .sig-line { border-top: 1px solid #000; padding-top: 5px; }
        .sig-img img { max-width: 150px; max-height: 60px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn btn-dark" onclick="window.print()">🖨️ طباعة الإيصال (ق-1)</button>
        <a href="{{ route('invoices.show', $invoice) }}" style="text-decoration: none; color: #666;">← العودة</a>
    </div>

    <div class="page-header">
        <div class="header-logo">
            @if($settings && $settings->logo)
                <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo">
            @endif
        </div>
        <div class="header-info">
            <div class="hospital-name-ar">مستشفى {{ $settings->hospital_name ?? 'الأمير متعب بن عبدالعزيز' }}</div>
            <div class="dept-name">إدارة تنمية الإيرادات - قسم التحصيل للمرضى</div>
        </div>
        <div class="header-spacer" style="width: 90px;"></div>
    </div>

    <div class="form-title">إيصال تحصيل رقم: {{ $receipt->receipt_number }}</div>

    <div class="receipt-container">
        <div class="info-row">
            <span class="info-label">وصلنا من السيد:</span>
            <span class="info-value">{{ $receipt->patient->name_ar ?? $receipt->patient->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">مبلغا وقدره:</span>
            <span class="info-value" style="font-weight: bold; font-size: 18px;">{{ number_format($receipt->amount, 2) }} ريال سعودي</span>
        </div>
        <div class="info-row">
            <span class="info-label">وذلك عن فاتورة:</span>
            <span class="info-value">{{ $invoice->invoice_number }} بتاريخ {{ $invoice->invoice_date?->format('Y/m/d') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">طريقة الدفع:</span>
            <span class="info-value">{{ $receipt->payment_method_label }} {{ $receipt->reference_number ? ' (مرجع: '.$receipt->reference_number.')' : '' }}</span>
        </div>

        <div class="financial-summary">
            <div class="summary-box">
                <div class="summary-title">تفاصيل الفاتورة عند التحصيل</div>
                <div class="summary-row">
                    <span class="summary-label">إجمالي الفاتورة:</span>
                    <span class="summary-value">{{ number_format($receipt->invoice_snapshot_total ?? $invoice->total_amount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">تاريخ التحصيل:</span>
                    <span class="summary-value">{{ $receipt->collected_at?->format('Y/m/d H:i') }}</span>
                </div>
            </div>
            <div class="summary-box" style="border-left: 5px solid #22c55e;">
                <div class="summary-title" style="color: #166534;">الحالة المالية</div>
                <div class="summary-row">
                    <span class="summary-label">المسدد (بما فيه هذا الإيصال):</span>
                    <span class="summary-value">{{ number_format($receipt->invoice_snapshot_paid ?? $invoice->paid_amount, 2) }}</span>
                </div>
                <div class="summary-row" style="color: #dc2626; border-top: 1px dashed #ccc; padding-top: 5px; margin-top: 5px;">
                    <span class="summary-label">المتبقي (الرصيد):</span>
                    <span class="summary-value">{{ number_format($receipt->invoice_snapshot_remaining ?? $invoice->remaining_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($receipt->notes)
    <div style="margin-top: 10px; font-style: italic; color: #555;">
        <strong>ملاحظات:</strong> {{ $receipt->notes }}
    </div>
    @endif

    <div class="footer-sigs">
        <div class="sig-box">
            <div class="sig-title">المستلم (المحصل)</div>
            <div class="sig-img">
                @if($receipt->collectedBy && $receipt->collectedBy->signature)
                    <img src="{{ asset('storage/' . $receipt->collectedBy->signature) }}" alt="Sig">
                @endif
            </div>
            <div class="sig-line">{{ $receipt->collectedBy->name ?? '________________' }}</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">يعتمد / مدير الإيرادات</div>
            <div class="sig-img">
                @if($manager && $manager->signature)
                    <img src="{{ asset('storage/' . $manager->signature) }}" alt="Sig">
                @endif
            </div>
            <div class="sig-line">{{ $manager->name ?? '________________' }}</div>
        </div>
    </div>

    <div style="position: fixed; bottom: 20px; left: 0; width: 100%; text-align: center; font-size: 11px; color: #888;">
        تم استخراج هذا الإيصال إلكترونياً من نظام المستشفى - {{ date('Y/m/d H:i') }}
    </div>
</body>
</html>
