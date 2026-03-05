<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ app()->getLocale() === 'ar' ? 'تقرير الإيرادات' : 'Revenue Report' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Traditional Arabic', 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            padding: 0 24px 24px;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.5;
        }
        .report-header {
            display: table;
            width: 100%;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 3px solid #1e40af;
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
        .report-header .hospital-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            margin: 0 0 4px 0;
        }
        .report-header .cluster-name {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }
        .report-title-block {
            text-align: center;
            margin: 28px 0 24px;
        }
        .report-title-block h1 {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 8px 0;
        }
        .report-title-block .date-range {
            font-size: 13px;
            color: #475569;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            background: #1e40af;
            padding: 10px 14px;
            margin: 24px 0 12px 0;
            border-radius: 4px;
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
            padding: 16px 14px;
            border: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: middle;
            background: #f8fafc;
        }
        .metrics-grid td:first-child { border-left: 1px solid #e2e8f0; }
        .metrics-grid td:last-child { border-right: 1px solid #e2e8f0; }
        .metric-label {
            font-size: 11px;
            color: #475569;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .metric-value {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        table.data-table th {
            background: #1e40af;
            color: #fff;
            border: 1px solid #1e40af;
            padding: 10px 12px;
            text-align: right;
            font-weight: bold;
        }
        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            text-align: right;
        }
        table.data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        table.data-table tbody tr:hover {
            background: #f1f5f9;
        }
        table.data-table .num {
            text-align: right;
            font-family: 'DejaVu Sans', monospace;
        }
        .manager-signature {
            margin-top: 36px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        .manager-signature .title {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .manager-signature .name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .footer {
            margin-top: 32px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <table style="width:100%; border:0;">
            <tr>
                <td class="logo-cell" style="width:72px;">
                    @if(!empty($logoPath) && file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo" style="max-height:56px; max-width:72px; object-fit:contain;">
                    @endif
                </td>
                <td class="title-cell">
                    @if(!empty($hospitalName))
                        <p class="hospital-name">{{ $hospitalName }}</p>
                    @endif
                    @if(!empty($healthClusterName))
                        <p class="cluster-name">{{ $healthClusterName }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

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
        {{ app()->getLocale() === 'ar' ? 'تم إنشاء التقرير آلياً في' : 'Report generated on' }} {{ date('Y-m-d H:i') }}
    </div>
</body>
</html>
