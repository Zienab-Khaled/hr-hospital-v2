<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ app()->getLocale() === 'ar' ? 'إحقاق علاج' : 'Treatment Eligibility' }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; color: #000; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; font-size: 14px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
        .info-item { border: 1px solid #ccc; padding: 10px; border-radius: 4px; }
        .info-label { font-weight: bold; display: block; margin-bottom: 5px; font-size: 12px; color: #555; }
        .info-value { font-size: 16px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; }
        .total-row td { border-top: 2px solid #000; font-weight: bold; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; text-align: center; }
        .sig-box { width: 200px; border-top: 1px solid #000; padding-top: 10px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 5px;">
            {{ app()->getLocale() === 'ar' ? 'طباعة' : 'Print' }}
        </button>
    </div>

    <div class="header">
        <h1>{{ app()->getLocale() === 'ar' ? 'إحقاق علاج' : 'Treatment Eligibility' }}</h1>
        <p>{{ date('Y-m-d H:i') }}</p>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</span>
            <div class="info-value">{{ app()->getLocale() === 'ar' && $visit->patient->name_ar ? $visit->patient->name_ar : $visit->patient->name }}</div>
            <div style="font-size: 12px; margin-top: 2px;">{{ $visit->patient->file_number }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">{{ app()->getLocale() === 'ar' ? 'العيادة / القسم' : 'Clinic / Department' }}</span>
            <div class="info-value">{{ app()->getLocale() === 'ar' && $visit->department->name_ar ? $visit->department->name_ar : $visit->department->name }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}</span>
            <div class="info-value">
                @if ($visit->patient->payment_type === 'cash') {{ app()->getLocale() === 'ar' ? 'نقد' : 'Cash' }}
                @elseif ($visit->patient->payment_type === 'insurance') {{ app()->getLocale() === 'ar' ? 'تأمين' : 'Insurance' }}
                @elseif ($visit->patient->payment_type === 'charity') {{ app()->getLocale() === 'ar' ? 'جمعية خيرية' : 'Charity' }}
                @endif
            </div>
            @if ($visit->patient->insuranceCompany)
                <div style="font-size: 12px;">{{ $visit->patient->insuranceCompany->name }}</div>
            @endif
        </div>
        <div class="info-item">
            <span class="info-label">{{ app()->getLocale() === 'ar' ? 'الطبيب / المسؤول' : 'Doctor / Officer' }}</span>
            <div class="info-value">{{ auth()->user()->name }}</div>
        </div>
    </div>

    @if (!empty($services))
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</th>
                    <th style="width: 50%;">{{ app()->getLocale() === 'ar' ? 'الخدمة' : 'Service' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                    {{-- Prices might be hidden for some roles, but typically shown on eligibility/invoice --}}
                    <th>{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'المجموع' : 'Total' }}</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach ($services as $index => $s)
                    @php
                        $qty = floatval($s['qty'] ?? 1);
                        $price = floatval($s['unit_price'] ?? 0);
                        $total = floatval($s['total'] ?? ($qty * $price));
                        $grandTotal += $total;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $s['code'] ?? '' }}</td>
                        <td style="text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};">{{ $s['name'] ?? '' }}</td>
                        <td>{{ $qty }}</td>
                        <td>{{ number_format($price, 2) }}</td>
                        <td>{{ number_format($total, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: 15px;">
                        {{ app()->getLocale() === 'ar' ? 'الإجمالي:' : 'Total:' }}
                    </td>
                    <td>{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <div style="text-align: center; border: 1px dashed #ccc; padding: 20px; color: #777; margin-bottom: 30px;">
            {{ app()->getLocale() === 'ar' ? 'لا توجد خدمات محددة لهذا الإحقاق.' : 'No services specified for this eligibility.' }}
        </div>
    @endif

    <div class="footer">
        <div class="sig-box">
            {{ app()->getLocale() === 'ar' ? 'توقيع الطبيب' : 'Doctor Signature' }}
        </div>
        <div class="sig-box">
            {{ app()->getLocale() === 'ar' ? 'توقيع المريض' : 'Patient Signature' }}
        </div>
        <div class="sig-box">
            {{ app()->getLocale() === 'ar' ? 'الختم' : 'Stamp' }}
        </div>
    </div>
</body>
</html>
