<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'عرض السعر / الفاتورة' : 'Price offer / Invoice' }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .header { background: #1e40af; color: #fff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { padding: 24px; }
        .intro { margin-bottom: 20px; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .info-box p { margin: 6px 0; }
        .actions { margin: 28px 0; text-align: center; }
        .btn { display: inline-block; padding: 14px 28px; margin: 8px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; }
        .btn-confirm { background: #16a34a; color: #fff; }
        .btn-reject { background: #dc2626; color: #fff; }
        .footer { background: #f1f5f9; padding: 16px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ app()->getLocale() === 'ar' ? 'عرض سعر / فاتورة طبية' : 'Medical Price Offer / Invoice' }}</h1>
        </div>
        <div class="content">
            <div class="intro">
                @if(app()->getLocale() === 'ar')
                    <p>السلام عليكم ورحمة الله وبركاته،</p>
                    <p>مرفق عرض السعر للخدمات العلاجية للمريض <strong>{{ $partySend->invoice->patient?->name }}</strong> (رقم الفاتورة: <strong>{{ $partySend->invoice->invoice_number }}</strong>).</p>
                    <p>يرجى الاطلاع على المرفق والرد عبر أحد الزرين أدناه لتأكيد الالتزام بالدفع أو الرفض، مع إرفاق موافقة خطية أو سبب الرفض.</p>
                @else
                    <p>Please find attached the price offer for the medical services for patient <strong>{{ $partySend->invoice->patient?->name }}</strong> (Invoice No: <strong>{{ $partySend->invoice->invoice_number }}</strong>).</p>
                    <p>Please review the attachment and respond using one of the buttons below to confirm payment commitment or reject, with a written approval or rejection reason.</p>
                @endif
            </div>
            <div class="info-box">
                <p><strong>{{ app()->getLocale() === 'ar' ? 'رقم الفاتورة:' : 'Invoice No:' }}</strong> {{ $partySend->invoice->invoice_number }}</p>
                <p><strong>{{ app()->getLocale() === 'ar' ? 'المريض:' : 'Patient:' }}</strong> {{ $partySend->invoice->patient?->name }}</p>
                <p><strong>{{ app()->getLocale() === 'ar' ? 'الإجمالي:' : 'Total:' }}</strong> {{ number_format((float) $partySend->invoice->total_amount, 2) }} {{ app()->getLocale() === 'ar' ? 'ريال' : 'SAR' }}</p>
            </div>
            <div class="actions">
                <a href="{{ $confirmUrl }}" class="btn btn-confirm">{{ app()->getLocale() === 'ar' ? 'أؤكد الالتزام بالدفع' : 'Confirm payment commitment' }}</a>
                <a href="{{ $rejectUrl }}" class="btn btn-reject">{{ app()->getLocale() === 'ar' ? 'رفض' : 'Reject' }}</a>
            </div>
            <p style="font-size: 13px; color: #64748b;">
                {{ app()->getLocale() === 'ar' ? 'في كلا الحالتين سيُطلب منكم إدخال موافقة خطية أو سبب الرفض، وسيتم تسجيل الرد في نظامنا.' : 'In both cases you will be asked to provide written approval or rejection reason, and the response will be recorded in our system.' }}
            </p>
        </div>
        <div class="footer">
            {{ app()->getLocale() === 'ar' ? 'هذه الرسالة آلية. يرجى عدم الرد عليها مباشرة؛ استخدم الزرين أعلاه.' : 'This is an automated message. Please do not reply directly; use the buttons above.' }}
        </div>
    </div>
</body>
</html>
