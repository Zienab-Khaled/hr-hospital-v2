    <!DOCTYPE html>
    <html lang="{{ app()->getLocale() === 'ar' ? 'ar-SA-u-nu-latn' : app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="UTF-8">
        <title>
            @if(isset($printTitle) && $printTitle === 'detailed_invoice')
                {{ app()->getLocale() === 'ar' ? 'فاتورة تفصيلية' : 'Detailed Invoice' }}
            @else
                {{ app()->getLocale() === 'ar' ? 'عرض سعر استعلامي' : 'Price Inquiry' }}
            @endif
        </title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; color: #000; }
            .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 120px; color: rgba(200, 200, 200, 0.15); font-weight: bold; z-index: -1; white-space: nowrap; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
            .header h1 { margin: 0; font-size: 24px; color: #1e40af; }
            .header p { margin: 5px 0 0; font-size: 14px; }
            .notice { background: #fef3c7; border: 2px solid #f59e0b; padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; }
            .notice strong { color: #92400e; font-size: 16px; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
            .info-item { border: 1px solid #ccc; padding: 10px; border-radius: 4px; }
            .info-label { font-weight: bold; display: block; margin-bottom: 5px; font-size: 12px; color: #555; }
            .info-value { font-size: 16px; font-weight: 600; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            th, td { border: 1px solid #000; padding: 8px; text-align: center; }
            th { background-color: #dbeafe; color: #1e40af; font-weight: bold; }
            .total-row td { border-top: 2px solid #000; font-weight: bold; }
            .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; font-style: italic; }
            @media print {
                body { padding: 0; }
                .no-print { display: none; }
                .report-header-wrap {
                    margin-top: 0 !important;
                    margin-bottom: 10px !important;
                    padding: 12px 16px 10px !important;
                    break-inside: avoid;
                }
                .report-header-logo img {
                    max-height: 58px !important;
                    max-width: 130px !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                img {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        </style>
    </head>
    <body>
        @if(!isset($printTitle) || $printTitle !== 'detailed_invoice')
        <div class="watermark">{{ app()->getLocale() === 'ar' ? 'استعلام فقط' : 'INQUIRY ONLY' }}</div>
        @endif

        <div class="no-print" style="margin-bottom: 20px; text-align: center; display: flex; justify-content: center; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #1e40af; color: #fff; border: none; border-radius: 5px; font-weight: bold;">
                {{ app()->getLocale() === 'ar' ? 'طباعة' : 'Print' }}
            </button>
        <a href="{{ isset($invoice) ? route('invoices.show', $invoice) : route('visits.create', ['patient_id' => $visit->patient_id, 'visit_id' => $visit->id ?? null, 'registered' => 1]) }}"
           style="padding: 10px 20px; background: #475569; color: #fff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: bold;">
           {{ app()->getLocale() === 'ar' ? (isset($invoice) ? 'العودة لتفاصيل الفاتورة' : 'العودة لتفاصيل الزيارة') : (isset($invoice) ? 'Back to invoice' : 'Back to Visit Details') }}
        </a>
            @if(isset($printTitle) && $printTitle === 'detailed_invoice' && isset($invoice))
            <a href="{{ route('invoices.show', $invoice) }}"
            style="padding: 10px 20px; background: #059669; color: #fff; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: bold;">
            {{ app()->getLocale() === 'ar' ? 'فتح الفاتورة في النظام' : 'Open invoice in system' }}
            </a>
            @endif
        </div>

        @include('components.report-header')

        <div class="header">
            <h1>
                @if(isset($printTitle) && $printTitle === 'detailed_invoice')
                    {{ app()->getLocale() === 'ar' ? '📋 فاتورة تفصيلية' : '📋 Detailed Invoice' }}
                @else
                    {{ app()->getLocale() === 'ar' ? '📋 عرض سعر استعلامي' : '📋 Price Inquiry' }}
                @endif
            </h1>
            <p>
                @if(isset($printTitle) && $printTitle === 'detailed_invoice' && isset($invoice))
                    {{ app()->getLocale() === 'ar' ? 'رقم الفاتورة: ' : 'Invoice No: ' }}{{ $invoice->invoice_number }}
                @else
                    {{ date('Y-m-d H:i') }}
                @endif
            </p>
        </div>

        @if(isset($printTitle) && $printTitle === 'detailed_invoice')
            <div class="notice" style="background: #ecfdf5; border-color: #10b981;">
                <strong style="color: #065f46;">{{ app()->getLocale() === 'ar' ? '✅ فاتورة تفصيلية رسمية — مسجّلة في النظام والإيرادات' : '✅ Official detailed invoice — recorded in system revenue' }}</strong>
                @if(isset($invoice))
                    <p style="margin: 5px 0 0; font-size: 13px; color: #047857;">
                        {{ app()->getLocale() === 'ar' ? 'رقم الفاتورة:' : 'Invoice number:' }} <strong>{{ $invoice->invoice_number }}</strong>
                        — {{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }} {{ $invoice->invoice_date?->format('Y-m-d') }}
                    </p>
                @endif
            </div>
        @else
        <div class="notice">
            <strong>{{ app()->getLocale() === 'ar' ? '⚠️ هذا عرض سعر استعلامي فقط - ليس فاتورة رسمية' : '⚠️ This is a price inquiry only - NOT an official invoice' }}</strong>
            <p style="margin: 5px 0 0; font-size: 13px;">{{ app()->getLocale() === 'ar' ? 'الأسعار المذكورة تقديرية وقابلة للتغيير' : 'Prices are estimates and subject to change' }}</p>
        </div>
        @endif

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</span>
                <div class="info-value">{{ app()->getLocale() === 'ar' && $visit->patient->fullArabicName() ? $visit->patient->fullArabicName() : $visit->patient->name }}</div>
                <div style="font-size: 12px; margin-top: 2px;">{{ $visit->patient->file_number }}</div>
            </div>
            <div class="info-item">
                <span class="info-label">{{ app()->getLocale() === 'ar' ? 'العيادة / القسم' : 'Clinic / Department' }}</span>
                <div class="info-value">{{ $visit->department ? (app()->getLocale() === 'ar' ? $visit->department->name_ar ?? $visit->department->name : $visit->department->name) : '—' }}</div>
            </div>
            <div class="info-item">
                <span class="info-label">{{ app()->getLocale() === 'ar' ? 'نوع الدفع' : 'Payment Type' }}</span>
                <div class="info-value">
                    {{ $visit->patient->payment_type_label }}
                </div>
                @if ($visit->patient->insuranceCompany)
                    <div style="font-size: 12px;">{{ app()->getLocale() === 'ar' ? ($visit->patient->insuranceCompany->name_ar ?? $visit->patient->insuranceCompany->name) : $visit->patient->insuranceCompany->name }}</div>
                @endif
            </div>
            <div class="info-item">
                <span class="info-label">{{ app()->getLocale() === 'ar' ? (isset($printTitle) && $printTitle === 'detailed_invoice' ? 'تاريخ الفاتورة' : 'تاريخ الاستعلام') : (isset($printTitle) && $printTitle === 'detailed_invoice' ? 'Invoice Date' : 'Inquiry Date') }}</span>
                <div class="info-value">{{ isset($invoice) && isset($printTitle) && $printTitle === 'detailed_invoice' ? $invoice->invoice_date?->format('Y-m-d') : $visit->visit_date->format('Y-m-d') }}</div>
            </div>
        </div>

        @if (!empty($services))
            <table>
                @php
                    $isInsurance = $visit->patient->payment_type === 'insurance';
                @endphp
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</th>
                        <th style="width: 40%;">{{ app()->getLocale() === 'ar' ? 'الخدمة' : 'Service' }}</th>
                        <th>{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}</th>
                        <th>{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                        <th>{{ app()->getLocale() === 'ar' ? 'المجموع' : 'Total' }}</th>
                        @if ($isInsurance)
                            <th>{{ app()->getLocale() === 'ar' ? 'التغطية' : 'Coverage' }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        $grandTotal = 0;
                        $insuranceTotal = 0;
                    @endphp
                    @foreach ($services as $index => $s)
                        @php
                            $qty = floatval($s['qty'] ?? 1);
                            $price = floatval($s['unit_price'] ?? 0);
                            $total = floatval($s['total'] ?? ($qty * $price));
                            $grandTotal += $total;

                            $covType = $s['insurance_coverage_type'] ?? '';
                            $covVal = floatval($s['insurance_coverage_value'] ?? 0);
                            $covered = 0;

                            if ($isInsurance && $covType && $total > 0) {
                                if ($covType === 'percentage') {
                                    $covered = $total * min(100, max(0, $covVal)) / 100;
                                } elseif ($covType === 'fixed') {
                                    $covered = min($covVal, $total);
                                }
                                $insuranceTotal += $covered;
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $s['code'] ?? '' }}</td>
                            <td style="text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};">{{ $s['name'] ?? '' }}</td>
                            <td>{{ $qty }}</td>
                            <td>{{ number_format($price, 2) }}</td>
                            <td>{{ number_format($total, 2) }}</td>
                            @if ($isInsurance)
                                <td>
                                    @if ($covType === 'percentage')
                                        {{ $covVal }}%
                                    @elseif ($covType === 'fixed')
                                        {{ number_format($covVal, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="{{ $isInsurance ? 5 : 5 }}" style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: 15px;">
                            {{ app()->getLocale() === 'ar' ? (isset($printTitle) && $printTitle === 'detailed_invoice' ? 'الإجمالي:' : 'الإجمالي التقديري:') : (isset($printTitle) && $printTitle === 'detailed_invoice' ? 'Total:' : 'Estimated Total:') }}
                        </td>
                        <td colspan="{{ $isInsurance ? 2 : 1 }}">{{ number_format(isset($invoice) && isset($printTitle) && $printTitle === 'detailed_invoice' ? (float) $invoice->total_amount : $grandTotal, 2) }}</td>
                    </tr>
                    @if ($isInsurance)
                    @php $patientShare = max(0, $grandTotal - $insuranceTotal); @endphp
                    <tr class="total-row" style="background-color: #f0fff4;">
                        <td colspan="5" style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: 15px; color: #065f46;">
                            {{ app()->getLocale() === 'ar' ? 'تحمّل التأمين (تقديري):' : 'Insurance Share (Estimated):' }}
                        </td>
                        <td colspan="2" style="color: #065f46;">{{ number_format($insuranceTotal, 2) }}</td>
                    </tr>
                    <tr class="total-row" style="background-color: #fffbeb;">
                        <td colspan="5" style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: 15px; color: #92400e;">
                            {{ app()->getLocale() === 'ar' ? 'تحمّل المريض (تقديري):' : 'Patient Share (Estimated):' }}
                        </td>
                        <td colspan="2" style="color: #92400e;">{{ number_format($patientShare, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        @else
            <div style="text-align: center; border: 1px dashed #ccc; padding: 20px; color: #777; margin-bottom: 30px;">
                {{ app()->getLocale() === 'ar' ? 'لا توجد خدمات محددة لهذا الاستعلام.' : 'No services specified for this inquiry.' }}
            </div>
        @endif


        <div class="footer">
            @if(isset($printTitle) && $printTitle === 'detailed_invoice')
                <p>{{ app()->getLocale() === 'ar' ? '* فاتورة تفصيلية رسمية مسجّلة في النظام' : '* Official detailed invoice recorded in the system' }}</p>
                @if(isset($invoice))
                    <p>{{ app()->getLocale() === 'ar' ? '* رقم الفاتورة: ' : '* Invoice No: ' }}{{ $invoice->invoice_number }}</p>
                @endif
            @else
                <p>{{ app()->getLocale() === 'ar' ? '* هذا المستند للاستعلام فقط ولا يعتبر فاتورة رسمية أو التزام بالدفع' : '* This document is for inquiry purposes only and is not an official invoice or payment commitment' }}</p>
                <p>{{ app()->getLocale() === 'ar' ? '* الأسعار النهائية قد تختلف بناءً على الفحص الفعلي والخدمات المقدمة' : '* Final prices may vary based on actual examination and services provided' }}</p>
            @endif
        </div>

        {{-- Electronic Signatures Section --}}
        <div style="margin-top: 40px; display: flex; justify-content: space-around; gap: 30px; page-break-inside: avoid;">
            {{-- Employee Signature --}}
            <div style="flex: 1; text-align: center;">
                <div style="font-weight: 600; margin-bottom: 10px; color: #1e293b;">
                    {{ app()->getLocale() === 'ar' ? 'توقيع الموظف' : 'Employee Signature' }}
                </div>
                @if (auth()->check() && auth()->user()->signature)
                    <img src="{{ asset('storage/' . ltrim(auth()->user()->signature ?? '', '/')) }}"
                        alt="Employee Signature"
                        style="max-width: 150px; max-height: 60px; margin: 10px auto; display: block;">
                @else
                    <div style="border-bottom: 1px solid #000; width: 150px; height: 50px; margin: 10px auto;"></div>
                @endif
                <div style="font-size: 0.85rem; color: #475569; margin-top: 5px;">
                    {{ auth()->check() ? auth()->user()->name : '___________' }}
                </div>
            </div>

            {{-- Manager Signature --}}
            <div style="flex: 1; text-align: center;">
                <div style="font-weight: 600; margin-bottom: 10px; color: #1e293b;">
                    {{ app()->getLocale() === 'ar' ? 'توقيع المدير' : 'Manager Signature' }}
                </div>
                @if (isset($manager) && $manager && $manager->signature)
                    <img src="{{ asset('storage/' . ltrim($manager->signature ?? '', '/')) }}"
                        alt="Manager Signature"
                        style="max-width: 150px; max-height: 60px; margin: 10px auto; display: block;">
                @else
                    <div style="border-bottom: 1px solid #000; width: 150px; height: 50px; margin: 10px auto;"></div>
                @endif
                <div style="font-size: 0.85rem; color: #475569; margin-top: 5px;">
                    {{ isset($manager) && $manager ? $manager->name : '___________' }}
                </div>
            </div>
        </div>

        @include('components.report-footer')
    </body>
    </html>
