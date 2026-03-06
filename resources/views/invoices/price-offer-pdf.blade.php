<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>عرض السعر - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Tahoma, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            direction: rtl;
            text-align: right;
        }

        .header {
            width: 100%;
            margin-bottom: 8px;
            padding-bottom: 12px;
            border-bottom: 2px solid #334155;
            overflow: hidden;
        }

        .header-institutional {
            float: left;
            width: 58%;
            text-align: right;
            padding-left: 0;
        }

        .header-logo {
            float: right;
            width: 38%;
            text-align: left;
            border-right: 2px solid #334155;
            padding-right: 12px;
        }

        .header-logo img {
            max-height: 70px;
            max-width: 180px;
        }

        .org-line {
            margin: 3px 0;
            font-size: 13px;
            font-weight: bold;
        }

        .org-line-en {
            font-size: 11px;
            color: #475569;
            margin: 2px 0;
        }

        .date-ref-row { clear: both; overflow: hidden; margin: 12px 0 14px 0; }
        .date-ref-row .date-line { margin: 0; font-weight: bold; font-size: 12px; float: left; }
        .date-ref-row .ref-no { font-size: 11px; color: #64748b; float: right; margin: 0; }

        .recipient {
            margin: 14px 0 8px 0;
            font-weight: bold;
            font-size: 12px;
        }

        .intro {
            margin: 12px 0 16px 0;
            font-size: 11px;
            line-height: 1.8;
            text-align: right;
        }

        .table-title {
            font-weight: bold;
            margin: 14px 0 8px 0;
            font-size: 12px;
            text-align: center;
        }

        table.services {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px 0;
            direction: rtl;
        }

        table.services th,
        table.services td {
            border: 1px solid #334155;
            padding: 8px 10px;
            text-align: right;
            font-size: 11px;
        }

        table.services th {
            background: #e2e8f0;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            font-size: 12px;
            background: #f1f5f9;
        }

        .total-row td {
            padding: 10px;
            border-top: 2px solid #334155;
        }

        .footer {
            margin-top: 24px;
            padding-top: 14px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
        }

        .footer p {
            margin: 4px 0;
            font-size: 11px;
        }

        .signatures {
            margin-top: 18px;
        }

        .sig-block {
            text-align: center;
        }

        .sig-block img {
            max-height: 52px;
            max-width: 150px;
            display: block;
            margin: 6px 0;
        }

        .sig-name {
            font-weight: bold;
            margin-top: 4px;
            font-size: 11px;
        }

        .sig-title {
            font-size: 10px;
            color: #475569;
        }
        .section-title { font-weight: bold; font-size: 12px; margin: 18px 0 10px; padding-bottom: 4px; border-bottom: 1px solid #cbd5e1; }
        .attach-img { max-width: 100%; max-height: 280px; display: block; margin: 8px 0; border: 1px solid #e2e8f0; }
        .patient-photo-box { margin-top: 14px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; }
        .amount-words-block { margin: 14px 0 18px 0; font-size: 11px; text-align: right; }
        .table-title-wrap { border-top: 1px solid #cbd5e1; margin-top: 4px; padding-top: 10px; }
    </style>
</head>

<body>
    @php
        $attachmentPaths = $attachmentPaths ?? ['documents' => [], 'patient_photo' => null];
    @endphp
    {{-- كالصورة الثانية: يسار الصفحة = الجهة الرسمية، يمين الصفحة = الشعار --}}
    <div class="header">
        <div class="header-institutional">
            <div class="org-line">المملكة العربية السعودية</div>
            <div class="org-line">وزارة الصحة</div>
            @if (!empty($settings['health_cluster_name']))
                <div class="org-line">{{ $settings['health_cluster_name'] }}</div>
            @endif
            <div class="org-line">{{ $settings['hospital_name'] ?? 'مستشفى' }}</div>
            @if (!empty($settings['hospital_name_en']))
                <div class="org-line-en">{{ $settings['hospital_name_en'] }}</div>
            @endif
        </div>
        <div class="header-logo">
            @if (!empty($settings['logo']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo']))
                <img src="{{ public_path('storage/' . $settings['logo']) }}" alt="شعار">
            @endif
            @if (!empty($settings['health_cluster_name']))
                <div class="org-line-en" style="margin-top: 6px;">{{ $settings['health_cluster_name'] }}</div>
            @endif
            @if (!empty($settings['health_cluster_name_en']))
                <div class="org-line-en">{{ $settings['health_cluster_name_en'] }}</div>
            @endif
        </div>
    </div>

    <div class="date-ref-row">
        <div class="date-line">{{ $invoice->invoice_date?->format('d/m/Y') }}</div>
    </div>

    <div class="recipient">
        سعادة / مدير {{ $recipientName }}<br>
        المحترمين
    </div>

    <div class="intro">
        السلام عليكم ورحمة الله وبركاته، تجدون أدناه عرض سعر للخدمات العلاجية المطلوبة للمريضة /
        {{ $invoice->patient?->name_ar ?? $invoice->patient?->name ?? '—' }} رقم الجواز
        ({{ $invoice->patient?->identity_value ?: $invoice->patient?->file_number ?: '—' }}) ونفيد سعادتكم بانه تم ارفاق التقرير الطبي وفي حال السداد نأمل تحويل المبلغ على الحساب في {{ $settings['bank_name'] ?? 'البنك' }} ({{ $settings['account_number'] ?? '' }}) رقم الآيبان {{ $settings['iban_number'] ?? '' }}
    </div>

    <div class="table-title-wrap">
        <div class="table-title">عرض السعر حسب تسعيرة وزارة الصحة</div>
    </div>
    <table class="services">
        <thead>
            <tr>
                <th>الكود</th>
                <th>الخدمة المقدمة</th>
                <th>العدد</th>
                <th>السعر الإفرادي</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->service?->code ?? '—' }}</td>
                    <td>{{ $item->service?->name_ar ?: $item->service?->name ?? '—' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->total_price, 2) }}</td>
                </tr>
            @endforeach
            @if ($invoice->paid_amount > 0)
                <tr class="total-row">
                    <td style="text-align: right;">الإجمالي</td>
                    <td colspan="3"></td>
                    <td style="text-align: left;">{{ number_format((float) $invoice->total_amount, 2) }} ريال</td>
                </tr>
                <tr class="total-row" style="color: #64748b; font-weight: normal;">
                    <td>المسدد نقداً (مقدم/مساهمة)</td>
                    <td colspan="3"></td>
                    <td style="text-align: left;">- {{ number_format((float) $invoice->paid_amount, 2) }} ريال</td>
                </tr>
                <tr class="total-row" style="background: #e2e8f0; border-top: 2px solid #1e293b;">
                    <td>المبلغ المطلوب من الجمعية (الصافي)</td>
                    <td colspan="3"></td>
                    <td style="text-align: left; font-size: 13px;">{{ number_format((float) $invoice->remaining_amount, 2) }} ريال</td>
                </tr>
            @else
                <tr class="total-row" style="background: #e2e8f0; border-top: 2px solid #1e293b;">
                    <td style="text-align: right;">الإجمالي</td>
                    <td colspan="3"></td>
                    <td style="text-align: left; font-size: 13px;">{{ number_format((float) $invoice->total_amount, 2) }} ريال</td>
                </tr>
            @endif
        </tbody>
    </table>

    @php
        $amountForCharity = $invoice->paid_amount > 0 ? (float) $invoice->remaining_amount : (float) $invoice->total_amount;
        $amountWords = \App\Helpers\CurrencyHelper::amountInArabicWords($amountForCharity);
    @endphp
    <div class="amount-words-block">
        <strong>مبلغ وقدره:</strong> {{ $amountWords }}
    </div>

    @if(count($attachmentPaths['documents']) > 0)
        <div class="section-title">📎 التقرير الطبي / المستندات المرفقة</div>
        @foreach($attachmentPaths['documents'] as $doc)
            @if(!empty($doc['path']) && file_exists($doc['path']))
                <p style="font-size: 10px; color: #64748b;">{{ $doc['name'] }}</p>
                <img src="{{ $doc['path'] }}" class="attach-img" alt="{{ $doc['name'] }}">
            @endif
        @endforeach
    @endif

    @if(!empty($attachmentPaths['patient_photo']) && !empty($attachmentPaths['patient_photo']['path']) && file_exists($attachmentPaths['patient_photo']['path']))
        <div class="section-title">🖼️ صورة المريض / الهوية</div>
        <div class="patient-photo-box">
            <img src="{{ $attachmentPaths['patient_photo']['path'] }}" class="attach-img" alt="صورة المريض" style="max-height: 200px;">
        </div>
    @endif

    <div class="footer">
        <p>وتقبلوا تحياتي</p>
        <p>مدير إدارة تنميه الإيرادات</p>
        @php
            $hName = $settings['hospital_name'] ?? '';
            $footerHospital = $hName && \Illuminate\Support\Str::startsWith($hName, 'مستشفى') ? 'بـ ' . $hName : ($hName ? 'بمستشفى ' . $hName : '');
        @endphp
        @if($footerHospital)
            <p>{{ $footerHospital }}</p>
        @endif
        <div class="signatures">
            <div class="sig-block">
                @if (!empty($settings['manager_signature']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['manager_signature']))
                    <img src="{{ public_path('storage/' . $settings['manager_signature']) }}" alt="توقيع" style="max-height: 52px;">
                @endif
                <div class="sig-name">{{ $settings['manager_name'] ?? '' }}</div>
            </div>
        </div>
    </div>
</body>

</html>
