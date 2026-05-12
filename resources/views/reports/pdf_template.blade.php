<!DOCTYPE html>
<html lang="ar-SA-u-nu-latn" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ app()->getLocale() === 'ar' ? 'تقرير رسمي - الإيرادات والتحصيل' : 'Official Report - Revenue & Collection' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: xbriyaz, dejavusans, 'Traditional Arabic', sans-serif;
            direction: rtl;
            text-align: right;
            padding: 0 16px 20px;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.6;
        }
        .official-header {
            width: 100%;
            margin-bottom: 20px;
            padding: 14px 18px;
            border: 2px solid #0f172a;
            background: #f8fafc;
        }
        .official-header .official-lines {
            text-align: center;
            line-height: 1.5;
        }
        .official-header .line-country { font-size: 16px; font-weight: bold; color: #0f172a; margin: 0 0 4px 0; }
        .official-header .line-ministry { font-size: 14px; font-weight: bold; color: #1e293b; margin: 0 0 4px 0; }
        .official-header .line-cluster { font-size: 14px; color: #334155; margin: 0 0 6px 0; }
        .official-header .line-hospital { font-size: 20px; font-weight: bold; color: #0f172a; margin: 0 0 4px 0; }
        .official-header .line-hospital-en { font-size: 13px; color: #475569; margin: 0; }
        .report-header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 3px solid #0f172a;
        }
        .report-header .logo-cell {
            width: 72px;
            vertical-align: middle;
        }
        .report-header .logo-cell img {
            max-height: 56px;
            max-width: 72px;
            object-fit: contain;
        }
        .report-header .title-cell {
            vertical-align: middle;
            text-align: center;
            width: 100%;
        }
        .report-header .hospital-name { font-size: 20px; font-weight: bold; color: #0f172a; margin: 0 0 4px 0; }
        .report-header .cluster-name { font-size: 14px; color: #475569; margin: 0; }
        .badge-official {
            display: inline-block;
            background: #0f172a;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 4px 12px;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        .report-title-block {
            text-align: center;
            margin: 22px 0 20px;
        }
        .report-title-block h1 {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 8px 0;
        }
        .report-title-block .date-range {
            font-size: 14px;
            color: #334155;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            background: #0f172a;
            padding: 10px 14px;
            margin: 20px 0 10px 0;
            text-align: right;
        }
        .metrics-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0;
            margin-bottom: 8px;
        }
        .metrics-grid td {
            width: 33.33%;
            padding: 14px 12px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
            background: #f8fafc;
        }
        .metrics-grid td:first-child { border-left: 1px solid #cbd5e1; }
        .metrics-grid td:last-child { border-right: 1px solid #cbd5e1; }
        .metric-label { font-size: 11px; color: #475569; font-weight: bold; margin-bottom: 6px; }
        .metric-value { font-size: 15px; font-weight: bold; color: #0f172a; }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 12px;
        }
        table.data-table th {
            background: #0f172a;
            color: #fff;
            border: 1px solid #0f172a;
            padding: 10px 12px;
            text-align: right;
            font-weight: bold;
        }
        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            text-align: right;
        }
        table.data-table tbody tr:nth-child(even) { background: #f8fafc; }
        table.data-table .num { text-align: right; font-family: xbriyaz, dejavusans, monospace; }
        .manager-signature {
            margin-top: 32px;
            padding-top: 14px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
        }
        .manager-signature .title { font-size: 11px; color: #64748b; margin-bottom: 4px; }
        .manager-signature .name { font-size: 13px; font-weight: bold; color: #0f172a; }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #1e3a8a; /* Blue */
            border-top: 2px solid #1e3a8a;
            padding-top: 15px;
        }
        .footer .dept-line { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .footer .hospital-line { font-size: 10px; color: #64748b; }

    </style>
</head>
<body>
    <div class="official-header">
        <table style="width:100%; border:0;">
            <tr>
                <td style="width:72px; vertical-align:middle;">
                    @if(!empty($logoPath) && file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo" style="max-height:52px; max-width:72px; object-fit:contain;">
                    @endif
                </td>
                <td style="vertical-align:middle; text-align:center; width:100%;">
                    <div class="official-lines">
                        @if(!empty($reportCountryAr))<div class="line-country">{{ $reportCountryAr }}</div>@endif
                        @if(!empty($reportMinistryAr))<div class="line-ministry">{{ $reportMinistryAr }}</div>@endif
                        @if(!empty($healthClusterName))<div class="line-cluster">{{ $healthClusterName }}</div>@endif
                        @if(!empty($hospitalName))<div class="line-hospital">{{ $hospitalName }}</div>@endif
                        @if(!empty($hospitalNameEn))<div class="line-hospital-en">{{ $hospitalNameEn }}</div>@endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="badge-official">{{ app()->getLocale() === 'ar' ? 'تقرير رسمي' : 'Official Report' }}</div>

    <div class="report-title-block">
        <h1>{{ app()->getLocale() === 'ar' ? 'تقرير الإيرادات والتحصيل' : 'Revenue & Collection Report' }}</h1>
        <div class="date-range">
            {{ app()->getLocale() === 'ar' ? 'الفترة من' : 'From' }} {{ $start->format('d/m/Y') }}
            {{ app()->getLocale() === 'ar' ? 'إلى' : 'To' }} {{ $end->format('d/m/Y') }}
        </div>
    </div>

    <div class="section-title">{{ app()->getLocale() === 'ar' ? 'ملخص المؤشرات' : 'Key Metrics Summary' }}</div>
    <table class="metrics-grid">
        <tr>
            <td>
                <div class="metric-label">{{ app()->getLocale() === 'ar' ? 'إجمالي المحصّل' : 'Total Collected' }}</div>
                <div class="metric-value">{{ number_format($revenueTotal, 2) }} ر.س</div>
            </td>
            <td>
                <div class="metric-label">{{ app()->getLocale() === 'ar' ? 'إجمالي الفواتير' : 'Total Invoiced' }}</div>
                <div class="metric-value">{{ number_format($totalInvoiced, 2) }} ر.س</div>
            </td>
            <td>
                <div class="metric-label">{{ app()->getLocale() === 'ar' ? 'نسبة التحصيل' : 'Collection Rate' }}</div>
                <div class="metric-value">{{ $collectionRate }}%</div>
            </td>
        </tr>
    </table>

    <div class="section-title">{{ app()->getLocale() === 'ar' ? 'أداء الأقسام' : 'Department Performance' }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'عدد الفواتير' : 'Invoices' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'إجمالي الإيرادات (ر.س)' : 'Total Revenue (SAR)' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deptPerformance as $dept)
            <tr>
                <td>{{ $dept->name }}</td>
                <td class="num">{{ $dept->count }}</td>
                <td class="num">{{ number_format($dept->total, 2) }} ر.س</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">{{ app()->getLocale() === 'ar' ? 'توزيع الإيرادات حسب نوع الدفع' : 'Revenue by Payment Type' }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'المبلغ المحصّل (ر.س)' : 'Amount (SAR)' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'النسبة' : 'Percentage' }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = $paymentsByType->sum();
            @endphp
            @foreach($paymentsByType as $type => $amount)
            <tr>
                <td>
                    @if($type == 'cash') {{ app()->getLocale() === 'ar' ? 'نقدي (كاش)' : 'Cash' }}
                    @elseif($type == 'insurance') {{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}
                    @elseif($type == 'charity') {{ app()->getLocale() === 'ar' ? 'جمعية خيرية' : 'Charity' }}
                    @else {{ $type }}
                    @endif
                </td>
                <td class="num">{{ number_format($amount, 2) }} ر.س</td>
                <td class="num">{{ $total > 0 ? round(($amount / $total) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="manager-signature">
        <div class="title">{{ $managerTitle ?? (app()->getLocale() === 'ar' ? 'مدير إدارة تنمية الإيرادات' : 'Revenue Development Manager') }}</div>
        <div class="name">{{ $managerName ?? '—' }}</div>
    </div>

    <div class="footer">
        <div class="dept-line">{{ app()->getLocale() === 'ar' ? 'إدارة تنمية الإيرادات' : 'Revenue Development Department' }}</div>
        <div class="hospital-line">
            {{ $hospitalName ?? 'مستشفى الملك عبدالعزيز التخصصي للجوف' }}
            &nbsp;|&nbsp;
            {{ app()->getLocale() === 'ar' ? 'تاريخ الإصدار:' : 'Issue date:' }} {{ date('Y-m-d') }}
        </div>
    </div>

</body>
</html>
