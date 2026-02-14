<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() === 'ar' ? 'محضر عدم تعهد خطي' : 'Written Non-Commitment Form' }} - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif; padding: 24px; max-width: 800px; margin: 0 auto; color: #1e293b; line-height: 1.6; }
        h1 { text-align: center; font-size: 1.5rem; margin-bottom: 24px; border-bottom: 2px solid #334155; padding-bottom: 8px; }
        .meta { margin-bottom: 20px; font-size: 0.9rem; color: #475569; }
        .meta p { margin: 4px 0; }
        .section { margin: 16px 0; }
        .section-title { font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 0.9rem; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: right; }
        th { background: #f1f5f9; font-weight: 600; }
        .signature-block { margin-top: 32px; display: flex; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
        .signature-block > div { flex: 1; min-width: 180px; }
        .signature-line { border-bottom: 1px solid #1e293b; height: 28px; margin-top: 4px; }
        .no-print { margin-bottom: 16px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print();" style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            {{ app()->getLocale() === 'ar' ? '🖨️ طباعة' : 'Print' }}
        </button>
        <a href="{{ route('invoices.show', $invoice) }}" style="margin-right: 12px; color: #2563eb;">{{ app()->getLocale() === 'ar' ? '← العودة للفاتورة' : 'Back to invoice' }}</a>
    </div>

    <h1>{{ app()->getLocale() === 'ar' ? 'محضر عدم تعهد خطي' : 'Written Non-Commitment Form' }}</h1>

    <div class="meta">
        <p><strong>{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة:' : 'Invoice No:' }}</strong> {{ $invoice->invoice_number }}</p>
        <p><strong>{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }}</strong> {{ $invoice->invoice_date?->format('Y-m-d') }}</p>
    </div>

    @if($invoice->patient)
        <div class="section">
            <div class="section-title">{{ app()->getLocale() === 'ar' ? 'بيانات المريض' : 'Patient Data' }}</div>
            <table>
                <tr><th>{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th><td>{{ $invoice->patient->name }}</td></tr>
                @if($invoice->patient->name_ar)<tr><th>{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (AR)' }}</th><td>{{ $invoice->patient->name_ar }}</td></tr>@endif
                <tr><th>{{ app()->getLocale() === 'ar' ? 'رقم الملف' : 'File No' }}</th><td>{{ $invoice->patient->file_number }}</td></tr>
                @if($invoice->patient->identity_value)<tr><th>{{ app()->getLocale() === 'ar' ? 'رقم الهوية/الفيزا' : 'ID/Visa' }}</th><td>{{ $invoice->patient->identity_value }}</td></tr>@endif
                @if($invoice->patient->phone)<tr><th>{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</th><td>{{ $invoice->patient->phone }}</td></tr>@endif
            </table>
        </div>
    @endif

    <div class="section">
        <div class="section-title">{{ app()->getLocale() === 'ar' ? 'إجمالي الفاتورة' : 'Invoice Total' }}</div>
        <p><strong>@currency($invoice->total_amount)</strong></p>
    </div>

    <div class="section">
        <p>{{ app()->getLocale() === 'ar' ? 'تم إثبات عدم تعهد المريض/الطرف المعني خطياً بسداد المبلغ المذكور أعلاه في التاريخ المبين، وذلك لعدم القدرة أو عدم الرغبة في التوقيع على محضر التعهد.' : 'This form records that the patient/concerned party has declined in writing to commit to paying the above amount on the stated date, due to inability or unwillingness to sign a commitment form.' }}</p>
    </div>

    <div class="signature-block">
        <div>
            <span>{{ app()->getLocale() === 'ar' ? 'توقيع الموظف المسؤول' : 'Authorized staff signature' }}</span>
            <div class="signature-line"></div>
        </div>
        <div>
            <span>{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</span>
            <div class="signature-line"></div>
        </div>
    </div>
</body>
</html>
