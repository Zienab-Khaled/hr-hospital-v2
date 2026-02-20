<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إشعار اكتمال تنفيذ الخدمات</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #1e293b; direction: rtl; }
        .container { max-width: 680px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #059669, #047857); padding: 32px 40px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: #a7f3d0; margin: 8px 0 0; font-size: 14px; }
        .badge { display: inline-block; background: #d1fae5; color: #065f46; border: 2px solid #6ee7b7; border-radius: 999px; padding: 6px 20px; font-size: 15px; font-weight: 700; margin: 20px 0 0; }
        .body { padding: 32px 40px; }
        .greeting { font-size: 16px; margin-bottom: 20px; color: #334155; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 24px; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; font-weight: 600; }
        .info-value { color: #1e293b; font-weight: 700; }
        .services-table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
        .services-table th { background: #f1f5f9; color: #475569; font-weight: 700; padding: 10px 12px; text-align: right; border: 1px solid #e2e8f0; }
        .services-table td { padding: 10px 12px; border: 1px solid #e2e8f0; color: #334155; vertical-align: top; }
        .services-table tr:nth-child(even) td { background: #f8fafc; }
        .done-badge { display: inline-block; background: #d1fae5; color: #065f46; border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 700; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 40px; text-align: center; font-size: 12px; color: #94a3b8; }
        h2 { font-size: 16px; color: #0f172a; margin: 24px 0 12px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>✅ إشعار اكتمال تنفيذ الخدمات</h1>
        <p>تم تنفيذ جميع الخدمات المطلوبة بنجاح</p>
        <div class="badge">✔ مكتمل 100%</div>
    </div>

    <div class="body">
        @php
            $charity = $invoice->patient?->charityEntity;
            $charityName = $charity?->name_ar ?? $charity?->name ?? 'الجمعية الخيرية';
            $patientName = $invoice->patient?->name_ar ?? $invoice->patient?->name ?? '—';
        @endphp

        <p class="greeting">
            السادة / {{ $charityName }} المحترمين،<br>
            تحية طيبة وبعد،
        </p>

        <p style="font-size:15px; color:#334155; line-height:1.7;">
            يسعدنا إحاطتكم علماً بأنه قد تم تنفيذ جميع الخدمات الطبية المقدمة للمريض
            <strong>{{ $patientName }}</strong>
            المرتبطة بالفاتورة رقم <strong>{{ $invoice->invoice_number }}</strong>،
            وذلك على النحو الموضح في الجدول أدناه.
        </p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">رقم الفاتورة</span>
                <span class="info-value">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">اسم المريض</span>
                <span class="info-value">{{ $patientName }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">تاريخ الفاتورة</span>
                <span class="info-value">{{ $invoice->invoice_date?->format('Y-m-d') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">إجمالي المبلغ</span>
                <span class="info-value">{{ number_format((float)$invoice->total_amount, 2) }}</span>
            </div>
        </div>

        <h2>📋 تفاصيل الخدمات المنفذة</h2>
        <table class="services-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الخدمة</th>
                    <th>الكمية</th>
                    <th>تاريخ التنفيذ</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ $item->service?->name_ar ?? $item->service?->name ?? '—' }}
                            @if($item->service?->code)
                                <br><small style="color:#94a3b8;">{{ $item->service->code }}</small>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                        <td style="text-align:center;">{{ $item->execution_date?->format('Y-m-d') ?? '—' }}</td>
                        <td style="text-align:center;"><span class="done-badge">✅ منفذ</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top:24px; font-size:14px; color:#475569; line-height:1.7;">
            نأمل أن تجدوا في ذلك ما يُفيد، ونحن على أتم الاستعداد لتزويدكم بأي معلومات إضافية تحتاجونها.
            <br>مع خالص التقدير والاحترام.
        </p>
    </div>

    <div class="footer">
        هذا البريد أُرسل تلقائياً من نظام إدارة المستشفى — {{ now()->format('Y-m-d H:i') }}
    </div>
</div>
</body>
</html>
