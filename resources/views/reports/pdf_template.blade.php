<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ app()->getLocale() === 'ar' ? 'تقرير الإيرادات' : 'Revenue Report' }}</title>
    <style>
        body {
            font-family: 'cairo', sans-serif;
            direction: rtl;
            text-align: right;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            margin-bottom: 30px;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #1e3a8a;
            margin: 0;
            font-size: 24px;
        }
        .date-range {
            font-size: 14px;
            color: #64748b;
            margin-top: 5px;
        }
        .metrics-grid {
            width: 100%;
            margin-bottom: 40px;
            border-collapse: collapse;
        }
        .metrics-grid td {
            width: 33.33%;
            padding: 15px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .metric-label {
            font-size: 12px;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .metric-value {
            font-size: 18px;
            color: #1e293b;
            font-weight: bold;
        }
        .section-title {
            background-color: #f1f5f9;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 15px;
            border-right: 4px solid #3b82f6;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.data-table th {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: right;
            font-size: 14px;
            color: #475569;
        }
        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            font-size: 14px;
            color: #1e293b;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .report-header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 15px;
        }
        .report-header .logo-cell {
            width: 80px;
            vertical-align: middle;
        }
        .report-header .logo-cell img {
            max-height: 60px;
            max-width: 80px;
            object-fit: contain;
        }
        .report-header .title-cell {
            vertical-align: middle;
            text-align: center;
        }
        .report-header .hospital-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0 0 4px 0;
        }
        .report-header .cluster-name {
            font-size: 13px;
            color: #64748b;
            margin: 0;
        }
        .manager-signature {
            margin-top: 45px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        .manager-signature .title {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .manager-signature .name {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <table style="width:100%; border:0;">
            <tr>
                <td class="logo-cell" style="width:80px;">
                    @if(!empty($logoPath) && file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo" style="max-height:60px; max-width:80px; object-fit:contain;">
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
    <div class="header">
        <h1>{{ app()->getLocale() === 'ar' ? 'تقرير الإيرادات والتحصيل' : 'Revenue & Collection Report' }}</h1>
        <div class="date-range">
            {{ app()->getLocale() === 'ar' ? 'الفترة من' : 'From' }}: {{ $start->format('Y-m-d') }}
            {{ app()->getLocale() === 'ar' ? 'إلى' : 'To' }}: {{ $end->format('Y-m-d') }}
        </div>
    </div>

    <div class="section-title">{{ app()->getLocale() === 'ar' ? 'ملخص المؤشرات' : 'Key Metrics Summary' }}</div>
    <table class="metrics-grid">
        <tr>
            <td>
                <div class="metric-label">{{ app()->getLocale() === 'ar' ? 'إجمالي المحصل' : 'Total Collected' }}</div>
                <div class="metric-value">{{ number_format($revenueTotal, 2) }} ر.س</div>
            </td>
            <td>
                <div class="metric-label">{{ app()->getLocale() === 'ar' ? 'إجمالي المفوتر' : 'Total Invoiced' }}</div>
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
                <th>{{ app()->getLocale() === 'ar' ? 'إجمالي الإيرادات' : 'Total Revenue' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deptPerformance as $dept)
            <tr>
                <td>{{ $dept->name }}</td>
                <td>{{ $dept->count }}</td>
                <td>{{ number_format($dept->total, 2) }} ر.س</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">{{ app()->getLocale() === 'ar' ? 'توزيع الإيرادات حسب نوع الدفع' : 'Revenue Distribution' }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'المبلغ المحصل' : 'Amount' }}</th>
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
                <td>{{ number_format($amount, 2) }} ر.س</td>
                <td>{{ $total > 0 ? round(($amount / $total) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="manager-signature">
        <div class="title">{{ $managerTitle ?? (app()->getLocale() === 'ar' ? 'مدير إدارة تنمية الإيرادات' : 'Revenue Development Manager') }}</div>
        <div class="name">{{ $managerName ?? 'جسار محمد الضويحي' }}</div>
    </div>

    <div class="footer">
        {{ app()->getLocale() === 'ar' ? 'تم إنشاء هذا التقرير آلياً بتاريخ' : 'Generated automatically on' }}: {{ date('Y-m-d H:i') }}
    </div>
</body>
</html>
