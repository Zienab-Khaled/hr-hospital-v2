<!DOCTYPE html>
<html lang="ar-SA-u-nu-latn" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'عرض السعر / الفاتورة' : 'Price offer / Invoice' }}</title>
    <style>
        body { margin: 0; padding: 0; background: #fff; font-family: 'Traditional Arabic', 'Arial', Tahoma, sans-serif; font-size: 14px; line-height: 1.5; color: #000; direction: rtl; text-align: right; }
        .doc { max-width: 700px; margin: 0 auto; padding: 20px; }
        .header-wrap { width: 100%; overflow: hidden; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #333; }
        .header-right { float: right; width: 42%; text-align: center; }
        .header-right img { max-height: 70px; max-width: 160px; display: block; margin: 0 auto 6px; }
        .header-left { float: left; width: 54%; text-align: right; }
        .org-line { margin: 2px 0; font-size: 14px; font-weight: bold; color: #000; }
        .org-line-en { font-size: 12px; color: #333; margin: 2px 0; }
        .date-row { clear: both; margin: 14px 0; overflow: hidden; }
        .date-row .date { font-weight: bold; font-size: 14px; float: right; }
        .to-block { margin: 16px 0 12px; font-weight: bold; font-size: 14px; }
        .body-text { margin: 14px 0; font-size: 13px; line-height: 1.8; text-align: right; }
        .table-caption { font-weight: bold; font-size: 13px; margin: 16px 0 8px; text-decoration: underline; text-align: right; }
        table.price-table { width: 100%; border-collapse: collapse; margin: 10px 0 16px; font-size: 12px; direction: rtl; }
        table.price-table th, table.price-table td { border: 1px solid #999; padding: 8px 10px; text-align: right; }
        table.price-table th { background: #e8e8e8; font-weight: bold; }
        table.price-table .total-row { font-weight: bold; background: #f0f0f0; }
        .closing { margin-top: 20px; font-size: 13px; }
        .sig-block { margin-top: 16px; text-align: center; font-size: 13px; }
        .sig-block .title { font-weight: bold; margin: 4px 0; }
        .sig-block .hospital { margin: 2px 0; }
        .sig-block .name { font-weight: bold; margin-top: 6px; }
        .action-bar { margin-top: 28px; padding-top: 20px; border-top: 2px solid #e2e8f0; text-align: center; }
        .btn { display: inline-block; padding: 12px 24px; margin: 8px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; }
        .btn-confirm { background: #16a34a; color: #fff; }
        .btn-reject { background: #dc2626; color: #fff; }
        .action-note { font-size: 12px; color: #64748b; margin-top: 12px; }
    </style>
</head>
<body>
@php
    $settings = $settings ?? [];
    $invoice = $partySend->invoice;
    $patient = $invoice->patient ?? null;
    $patientName = $patient ? ($patient->fullArabicName() ?: ($patient->name ?? '—')) : '—';
    $patientIdentity = $patient ? ($patient->identity_value ?: $patient->file_number ?? '—') : '—';
    $bankName = $settings['bank_name'] ?? 'البنك';
    $accountNumber = $settings['account_number'] ?? '';
    $ibanNumber = $settings['iban_number'] ?? '';
@endphp
<div class="doc">
    @if (!($showResponseButtons ?? false))
        <div style="background:#eff6ff;border:2px solid #3b82f6;padding:12px;margin-bottom:16px;border-radius:8px;text-align:center;font-size:13px;">
            <strong>{{ app()->getLocale() === 'ar' ? 'معاينة للموظفين' : 'Staff preview' }}</strong>
            — {{ app()->getLocale() === 'ar' ? 'أزرار «الموافقة / الرفض» تظهر للجمعية فقط داخل الإيميل المرسل.' : 'Confirm/Reject buttons appear for the charity only in the sent email.' }}
        </div>
    @endif
    {{-- رأس رسمي: يسار = الجهة، يمين = الشعار --}}
    <div class="header-wrap">
        <div class="header-right">
            @if(!empty($settings['logo']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo']))
                <img src="{{ asset('storage/' . $settings['logo']) }}" alt="شعار">
            @endif
            @if(!empty($settings['health_cluster_name']))
                <div class="org-line">{{ $settings['health_cluster_name'] }}</div>
            @endif
            @if(!empty($settings['health_cluster_name_en']))
                <div class="org-line-en">{{ $settings['health_cluster_name_en'] }}</div>
            @endif
        </div>
        <div class="header-left">
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
    </div>

    <div class="date-row">
        <span class="date">{{ $invoice->invoice_date?->format('d/m/Y') }}</span>
    </div>

    <div class="to-block">
        سعادة / مدير {{ $partySend->recipient_name }}<br>
        المحترمين
    </div>

    <div class="body-text">
        السلام عليكم ورحمة الله وبركاته،<br>
        @if(!empty($customIntro))
            {!! nl2br(e($customIntro)) !!}
        @else
            تجدون أدناه عرض سعر للخدمات العلاجية المطلوبة للمريضة / {{ $patientName }} رقم الجواز ({{ $patientIdentity }}) ونفيد سعادتكم بانه تم ارفاق التقرير الطبي وفي حال السداد نأمل تحويل المبلغ على الحساب في {{ $bankName }} ({{ $accountNumber }}) رقم الأيبان {{ $ibanNumber }}
        @endif
        @if(!empty($treatmentDuration))
            <br><strong>مدة العلاج:</strong> {{ $treatmentDuration }}
        @endif
    </div>

    <div class="table-caption">عرض السعر حسب تسعيرة وزارة الصحة</div>

    <table class="price-table">
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
            @foreach($invoice->items ?? [] as $item)
                <tr>
                    <td>{{ $item->service_id ? ($item->service?->code ?? '—') : '—' }}</td>
                    <td>{{ $item->service_id ? ($item->service?->name_ar ?: $item->service?->name ?? '—') : ($item->description ?? '—') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->total_price, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">الإجمالي</td>
                <td></td><td></td>
                <td style="text-align: right;">{{ number_format((float) $invoice->total_amount, 2) }} ريال</td>
            </tr>
            @if((float) $invoice->paid_amount > 0)
                <tr class="total-row" style="font-weight: normal;">
                    <td colspan="2">المسدد نقداً (مقدم/مساهمة)</td>
                    <td></td><td></td>
                    <td style="text-align: right;">- {{ number_format((float) $invoice->paid_amount, 2) }} ريال</td>
                </tr>
            @endif
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">المبلغ المطلوب من الجمعية (المتبقي / الصافي)</td>
                <td></td><td></td>
                <td style="text-align: right;">{{ number_format((float) $invoice->remaining_amount, 2) }} ريال</td>
            </tr>
        </tbody>
    </table>

    <div class="closing">وتقبلوا تحياتي</div>
    <div class="sig-block">
        <div class="title">مدير إدارة تنميه الإيرادات</div>
        @php
            $hName = $settings['hospital_name'] ?? '';
            $footerHospital = $hName && \Illuminate\Support\Str::startsWith($hName, 'مستشفى') ? 'بـ ' . $hName : ($hName ? 'بمستشفى ' . $hName : '');
        @endphp
        @if($footerHospital)
            <div class="hospital">{{ $footerHospital }}</div>
        @endif
        @php
            $displaySenderName = trim((string) ($senderName ?? ''));
            if ($displaySenderName === '') {
                $displaySenderName = $settings['department_manager_name'] ?? $settings['manager_name'] ?? '';
            }
        @endphp
        <div class="name">{{ $displaySenderName }}</div>
    </div>

    @if ($showResponseButtons ?? false)
    {{-- أزرار الرد (تأكيد/رفض) — للجمعية في الإيميل المرسل فقط --}}
    <div class="action-bar">
        <p style="font-weight: bold; margin-bottom: 10px;">{{ app()->getLocale() === 'ar' ? 'للرد على هذه المطالبة:' : 'To respond to this claim:' }}</p>
        <a href="{{ $confirmUrl }}" class="btn btn-confirm">{{ app()->getLocale() === 'ar' ? 'أؤكد الالتزام بالدفع' : 'Confirm payment commitment' }}</a>
        <a href="{{ $rejectUrl }}" class="btn btn-reject">{{ app()->getLocale() === 'ar' ? 'رفض' : 'Reject' }}</a>
        <p class="action-note">{{ app()->getLocale() === 'ar' ? 'في كلا الحالتين سيُطلب منكم إدخال موافقة خطية أو سبب الرفض.' : 'In both cases you will be asked to provide written approval or rejection reason.' }}</p>
    </div>
    @endif
</div>
</body>
</html>
