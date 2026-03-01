<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تذكير بالسداد</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #1e293b; direction: rtl; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #b45309, #92400e); padding: 24px 32px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; }
        .body { padding: 28px 32px; }
        .reminder-box { background: #fef3c7; border: 2px solid #f59e0b; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .reminder-box h2 { margin: 0 0 12px; font-size: 16px; color: #92400e; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #fde68a; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #78350f; font-weight: 600; }
        .info-value { color: #1e293b; font-weight: 700; }
        .amount-big { font-size: 20px; color: #b45309; }
        .footer { background: #f8fafc; padding: 16px 32px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>💰 تذكير بالسداد</h1>
    </div>

    <div class="body">
        @php
            $charity = $invoice->patient?->charityEntity;
            $charityName = $charity?->name_ar ?? $charity?->name ?? 'الجمعية الخيرية';
            $patientName = $invoice->patient?->name_ar ?? $invoice->patient?->name ?? '—';
        @endphp

        <p style="font-size:15px; color:#334155; line-height:1.7;">
            السادة / {{ $charityName }} المحترمين،<br>
            تحية طيبة،
        </p>

        <p style="font-size:14px; color:#475569; line-height:1.7;">
            نود تذكيركم بالمبلغ المستحق عن الفاتورة رقم <strong>{{ $invoice->invoice_number }}</strong>
            (المريض: {{ $patientName }})، ويرجى التحويل إلى الحساب أدناه.
        </p>

        <div class="reminder-box">
            <h2>بيانات التحويل</h2>
            <div class="info-row">
                <span class="info-label">المبلغ المستحق</span>
                <span class="info-value amount-big">{{ number_format((float)$invoice->total_amount, 2) }} ريال</span>
            </div>
            <div class="info-row">
                <span class="info-label">رقم الفاتورة</span>
                <span class="info-value">{{ $invoice->invoice_number }}</span>
            </div>
            @if(!empty($settings['bank_name'] ?? ''))
                <div class="info-row">
                    <span class="info-label">البنك</span>
                    <span class="info-value">{{ $settings['bank_name'] }}</span>
                </div>
            @endif
            @if(!empty($settings['account_number'] ?? ''))
                <div class="info-row">
                    <span class="info-label">رقم الحساب</span>
                    <span class="info-value" dir="ltr">{{ $settings['account_number'] }}</span>
                </div>
            @endif
            @if(!empty($settings['iban_number'] ?? ''))
                <div class="info-row">
                    <span class="info-label">رقم الآيبان</span>
                    <span class="info-value" dir="ltr">{{ $settings['iban_number'] }}</span>
                </div>
            @endif
            <p style="margin: 14px 0 0; font-size: 13px; color: #92400e;">
                نأمل السداد في أقرب وقت ممكن.
            </p>
        </div>

        <p style="font-size:14px; color:#64748b;">
            {{ $settings['hospital_name'] ?: 'المستشفى' }} — {{ now()->format('Y-m-d H:i') }}
        </p>
    </div>

    <div class="footer">
        هذا البريد أُرسل من نظام إدارة المستشفى
    </div>
</div>
</body>
</html>
