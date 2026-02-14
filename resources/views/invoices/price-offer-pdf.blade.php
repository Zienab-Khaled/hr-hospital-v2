<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>عرض السعر - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Tahoma, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.6; margin: 0; padding: 20px; direction: rtl; text-align: right; }
        .header { width: 100%; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #334155; overflow: hidden; }
        .header-right { float: right; width: 58%; text-align: right; }
        .header-left { float: left; width: 38%; text-align: center; }
        .header-left img { max-height: 70px; max-width: 180px; }
        .org-line { margin: 3px 0; font-size: 13px; font-weight: bold; }
        .org-line-en { font-size: 11px; color: #475569; margin: 2px 0; }
        .date-line { margin: 12px 0 16px 0; font-weight: bold; font-size: 12px; }
        .recipient { margin: 14px 0 8px 0; font-weight: bold; font-size: 12px; }
        .intro { margin: 12px 0 16px 0; font-size: 11px; line-height: 1.8; text-align: right; }
        .table-title { font-weight: bold; margin: 14px 0 8px 0; font-size: 12px; }
        table.services { width: 100%; border-collapse: collapse; margin: 10px 0 14px 0; direction: rtl; }
        table.services th, table.services td { border: 1px solid #334155; padding: 8px 10px; text-align: right; font-size: 11px; }
        table.services th { background: #e2e8f0; font-weight: bold; }
        .total-row { font-weight: bold; font-size: 12px; background: #f1f5f9; }
        .total-row td { padding: 10px; border-top: 2px solid #334155; }
        .footer { margin-top: 24px; padding-top: 14px; border-top: 1px solid #cbd5e1; text-align: right; }
        .footer p { margin: 4px 0; font-size: 11px; }
        .signatures { margin-top: 18px; overflow: hidden; }
        .sig-block { float: right; width: 48%; margin-right: 2%; text-align: right; }
        .sig-block img { max-height: 52px; max-width: 150px; display: block; margin: 6px 0; }
        .sig-name { font-weight: bold; margin-top: 4px; font-size: 11px; }
        .sig-title { font-size: 10px; color: #475569; }
    </style>
</head>
<body>
    {{-- الهيدر: يمين الصفحة = الجهة الرسمية، يسار الصفحة = الشعار --}}
    <div class="header">
        <div class="header-right">
            <div class="org-line">المملكة العربية السعودية</div>
            <div class="org-line">وزارة الصحة</div>
            @if(!empty($settings['health_cluster_name']))
                <div class="org-line">{{ $settings['health_cluster_name'] }}</div>
            @endif
            <div class="org-line">{{ $settings['hospital_name'] ?? 'مستشفى' }}</div>
            @if(!empty($settings['hospital_name_en']))
                <div class="org-line-en">{{ $settings['hospital_name_en'] }}</div>
            @endif
        </div>
        <div class="header-left">
            @if(!empty($settings['logo']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo']))
                <img src="{{ public_path('storage/' . $settings['logo']) }}" alt="شعار">
            @endif
            @if(!empty($settings['health_cluster_name']))
                <div class="org-line-en" style="margin-top: 6px;">{{ $settings['health_cluster_name'] }}</div>
            @endif
            @if(!empty($settings['health_cluster_name_en']))
                <div class="org-line-en">{{ $settings['health_cluster_name_en'] }}</div>
            @endif
        </div>
    </div>

    <div class="date-line">{{ $invoice->invoice_date?->format('d/m/Y') }}</div>

    <div class="recipient">
        المحترمين<br>
        سعادة / مدير {{ $recipientName }}
    </div>

    <div class="intro">
        السلام عليكم ورحمة الله وبركاته، تجدون أدناه عرض سعر للخدمات العلاجية المطلوبة للمريضة / {{ $invoice->patient?->name ?? '—' }} رقم الجواز ({{ $invoice->patient?->identity_value ?: $invoice->patient?->file_number ?: '—' }}) ونفيد سعادتكم بانه تم ارفاق التقرير الطبي وفي حال السداد نأمل تحويل المبلغ على الحساب في {{ $settings['bank_name'] ?? 'البنك' }} ({{ $settings['account_number'] ?? '' }}) رقم الآيبان {{ $settings['iban_number'] ?? '' }}
    </div>

    <div class="table-title">عرض السعر حسب تسعيرة وزارة الصحة</div>
    <table class="services">
        <thead>
            <tr>
                <th>الكود</th>
                <th>الخدمة المقدمة</th>
                <th>العدد</th>
                <th>السعر الأفرادي</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->service?->code ?? '—' }}</td>
                    <td>{{ $item->service?->name_ar ?: $item->service?->name ?? '—' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->total_price, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4">الاجمالي</td>
                <td style="text-align: left;">{{ number_format((float) $invoice->total_amount, 2) }} ريال</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>وتقبلوا تحياتي</p>
        <p>مدير إدارة تنميه الإيرادات</p>
        <p>بمستشفى {{ $settings['hospital_name'] ?? '' }}</p>
        <div class="signatures">
            <div class="sig-block">
                @if(!empty($settings['manager_signature']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['manager_signature']))
                    <img src="{{ public_path('storage/' . $settings['manager_signature']) }}" alt="توقيع">
                @endif
                <div class="sig-name">{{ $settings['manager_name'] ?? '' }}</div>
                <div class="sig-title">مدير إدارة تنمية الإيرادات</div>
            </div>
            <div class="sig-block">
                @if(!empty($settings['department_manager_signature']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['department_manager_signature']))
                    <img src="{{ public_path('storage/' . $settings['department_manager_signature']) }}" alt="توقيع">
                @endif
                <div class="sig-name">{{ $settings['department_manager_name'] ?? '' }}</div>
                <div class="sig-title">مدير الإدارة</div>
            </div>
        </div>
    </div>
</body>
</html>
