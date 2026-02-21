<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نموذج أحقية علاج</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none; }
            @page { margin: 1cm; size: A4; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            text-align: right;
        }
        .header-logo img {
            max-width: 120px;
        }
        .header-text {
            text-align: center;
            flex-grow: 1;
        }
        .header-text h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header-text p {
            margin: 5px 0 0;
            font-size: 14px;
        }

        h1.title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        /* Table Styling */
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #555;
            margin-bottom: 15px;
        }
        table.info-table td {
            border: 1px solid #777;
            padding: 8px 10px;
            vertical-align: middle;
            font-size: 14px;
        }
        table.info-table td.label-cell {
            background-color: #d1d5db !important; /* Darker gray and forced */
            font-weight: bold;
            width: 120px;
            white-space: nowrap;
        }

        /* Checkboxes */
        .checkbox-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1px solid #000;
            margin-left: 5px;
            vertical-align: middle;
            position: relative;
        }
        .checkbox-box.checked::after {
            content: "✔";
            position: absolute;
            top: -4px;
            left: 2px;
            font-size: 14px;
        }

        /* Eligibility Section */
        .eligibility-section {
            margin: 10px 40px;
            text-align: right;
            font-size: 15px;
            line-height: 1.6;
        }
        .eligibility-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        .eligibility-row label {
            font-weight: bold;
            cursor: pointer;
        }
        .eligibility-subtext {
            display: block;
            direction: ltr;
            text-align: right;
            font-size: 11px;
            color: #444;
            margin-top: -3px;
            margin-bottom: 5px;
        }
        /* Interactive Inputs */
        .big-checkbox-input {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #000; /* For modern browsers */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .days-input {
            font-family: inherit;
            font-size: 16px;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            .days-input::placeholder {
                color: transparent;
            }
            /* Ensure inputs are visible */
            input[type="text"], textarea {
                border: none;
                background: transparent;
            }
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 0 40px;
        }
        .footer-col {
            text-align: center;
        }
        .footer-col h4 {
            margin-bottom: 60px;
            font-size: 16px;
        }
        .footer-col p {
            font-weight: bold;
            font-size: 15px;
        }

        /* Watermark/Background Design similar to image */
        .page-background {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40%;
            z-index: -1;
            overflow: hidden;
            opacity: 0.1;
        }
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: #000;
            border-radius: 100% 100% 0 0;
        }

        .note-input {
            width: 100%;
            border: none;
            border-bottom: 1px dashed #999;
            background: transparent;
            font-family: inherit;
            font-size: 14px;
            color: #000;
            resize: none;
            overflow: hidden;
            min-height: 30px;
            margin-top: 5px;
            outline: none;
        }
        @media print {
            .note-input {
                border-bottom: 1px dashed #ccc;
                display: block; /* Ensure it is always displayed */
            }
            .note-input::placeholder {
                color: transparent;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px; display: flex; justify-content: center; gap: 10px; align-items: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #000; color: #fff; cursor: pointer; border: none; border-radius: 4px; font-weight: bold;">طباعة النموذج</button>
        <a href="{{ route('visits.create', ['patient_id' => $visit->patient_id, 'visit_id' => $visit->id, 'registered' => 1]) }}"
           style="padding: 10px 20px; background: #475569; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: bold;">
           {{ app()->getLocale() === 'ar' ? 'العودة لتفاصيل الزيارة' : 'Back to Visit Details' }}
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="header-logo">
            <!-- Placeholder for logo if needed -->
            @if(App\Models\Setting::get('logo'))
                <img src="{{ asset('storage/'.App\Models\Setting::get('logo')) }}" alt="Logo">
            @else
                <div style="width:80px; height:80px; border:1px dashed #ccc; display:flex; align-items:center; justify-content:center;">شعار</div>
            @endif
        </div>
        <div class="header-text">
            <h2>مستشفى {{ App\Models\Setting::get('hospital_name', 'الأمير متعب بن عبد العزيز') }}</h2>
            <p>{{ App\Models\Setting::get('hospital_name_en', 'Prince Muteb bin Abdulaziz Hospital') }}</p>
            <p style="font-size: 11px; margin-top:2px;">{{ App\Models\Setting::get('health_cluster_name', 'تجمع الجوف الصحي') }} - {{ App\Models\Setting::get('health_cluster_name_en', 'Aljouf Health Cluster') }}</p>
            <p style="font-weight: bold; margin-top: 5px;">إدارة تنمية الإيرادات</p>
        </div>
        <div style="width: 100px;"></div> <!-- Spacer for balance -->
    </div>

    <h1 class="title">نموذج أحقية علاج</h1>

    <!-- Patient Info Table -->
    <table class="info-table">
        <tr>
            <td colspan="2" class="label-cell">اسم المريض :</td>
            <td colspan="4">{{ $visit->patient->name_ar ?? $visit->patient->name }}</td>
            <td class="label-cell" style="width: 80px;">أنثى <div class="checkbox-box {{ $visit->patient->gender == 'female' ? 'checked' : '' }}"></div></td>
            <td class="label-cell" style="width: 80px;">ذكر <div class="checkbox-box {{ $visit->patient->gender == 'male' ? 'checked' : '' }} {{ $visit->patient->gender == 'M' ? 'checked' : '' }}"></div></td>
        </tr>
        <tr>
            <td colspan="2" class="label-cell">رقم الإقامة / جواز / تأشيرة :</td>
            <td colspan="2">{{ $visit->patient->identity_value }}</td>
            <td class="label-cell">الجنسية :</td>
            <td colspan="3">{{ $visit->patient->country_of_origin ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label-cell">مصدرها :</td>
            <td colspan="6">—</td> <!-- Source not in db yet -->
        </tr>
        <tr>
            <td colspan="2" class="label-cell">المهنة / صلة القرابة :</td>
            <td colspan="2">—</td> <!-- Profession not in db yet -->
            <td class="label-cell">اسم الكفيل</td>
            <td colspan="3">{{ $visit->patient->sponsor_name ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label-cell">رقم الملف :</td>
            <td colspan="2">{{ $visit->patient->file_number }}</td>
            <td class="label-cell">رقم الهاتف :</td>
            <td colspan="3">{{ $visit->patient->phone }}</td>
        </tr>
        <tr>
            <td colspan="4" style="border-bottom: 0; border-left: 0;"></td>
            <td class="label-cell">رقم السند :</td>
            <td colspan="3">
                {{-- If there is a paid invoice, show its receipt/reference number if available --}}
                @php
                    $invoice = $visit->invoices()->latest()->first();
                    $receipt = $invoice ? $invoice->paymentReceipts()->latest()->first() : null;
                @endphp
                {{ $receipt ? $receipt->receipt_number : '—' }}
            </td>
        </tr>
    </table>

    <!-- Eligibility Checkboxes -->
    <div class="eligibility-section">
        <div style="text-align: center; margin-bottom: 20px; font-weight: bold; font-size: 18px;">
            @if(isset($targetDepartment) && $targetDepartment)
                المكرم رئيس قسم {{ $targetDepartment->name_ar ?? $targetDepartment->name }} / {{ $targetDepartment->manager->name ?? '' }}
            @else
                المكرم رئيس قسم {{ $visit->department->name_ar ?? $visit->department->name }} / {{ $visit->department->manager->name ?? '' }}
            @endif
        </div>

        <div class="eligibility-row">
            <label>المريض له أحقية علاج.</label>
            {{-- Fallback: if not insurance and not cash, assume eligible/charity --}}
            <div class="big-checkbox {{ !in_array($visit->patient->payment_type, ['insurance', 'cash']) || $visit->case_type == 'emergency' ? 'checked' : '' }}"></div>
        </div>
        <textarea class="note-input" placeholder="ملاحظات..." oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
        <span class="eligibility-subtext">Patient is eligible for treatment in the hospital</span>

        <div class="eligibility-row">
            <label>المريض يحمل بطاقة تأمين ، عدد أيام التنويم ( &nbsp;&nbsp;&nbsp; ) .</label>
            <div class="big-checkbox {{ $visit->patient->payment_type == 'insurance' ? 'checked' : '' }}"></div>
        </div>
        <textarea class="note-input" placeholder="ملاحظات..." oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
        <span class="eligibility-subtext">The patient has a health insurance card</span>

        <div class="eligibility-row">
            <label>مريض نقدي.</label>
            <div class="big-checkbox {{ $visit->patient->payment_type == 'cash' ? 'checked' : '' }}"></div>
        </div>
        <textarea class="note-input" placeholder="ملاحظات..." oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
        <span class="eligibility-subtext">Patients treatment cash</span>
    </div>

    <!-- Footer Signatures -->
    <div class="footer">
        <div class="footer-col" style="text-align: right;">
            <h4>مدير إدارة تنمية الإيرادات</h4>
            @if (isset($manager) && $manager && $manager->signature)
                <img src="{{ asset('storage/' . $manager->signature) }}" alt="Manager Signature" style="max-width: 150px; max-height: 60px; margin: 10px auto; display: block;">
                <p style="margin-top: 5px;">{{ $manager->name }}</p>
            @else
                <p style="margin-top: 40px;">{{ $manager->name ?? 'ناصر احمد الضويحي' }}</p>
            @endif
        </div>

        <div class="footer-col" style="text-align: left;">
            <h4>الموظف المختص /</h4>
            @if (auth()->check() && auth()->user()->signature)
                <img src="{{ asset('storage/' . auth()->user()->signature) }}" alt="Employee Signature" style="max-width: 150px; max-height: 60px; margin: 10px auto; display: block;">
            @else
                <p style="margin-top: 40px;">التوقيع / _________________</p>
            @endif
            <p style="font-weight: normal; font-size: 12px; margin-top: 5px;">{{ auth()->user()->name }}</p>
        </div>
    </div>

    <div style="position: fixed; bottom: 10px; left: 20px; font-size: 12px; color: #999;">
        User: {{ auth()->user()->name }} | Date: {{ date('Y-m-d H:i') }}
    </div>

    {{-- Second Page: Services List (if available) --}}
    @if(count($services) > 0)
        <!-- Page Break -->
        <div style="page-break-before: always; height: 0; margin: 0; padding: 0;"></div>

        <!-- Header for 2nd page -->
        <div class="header">
            <div class="header-logo">
                @if(App\Models\Setting::get('logo'))
                    <img src="{{ asset('storage/'.App\Models\Setting::get('logo')) }}" alt="Logo">
                @else
                    <div style="width:80px; height:80px; border:1px dashed #ccc; display:flex; align-items:center; justify-content:center;">شعار</div>
                @endif
            </div>
            <div class="header-text">
                <h2>مستشفى {{ App\Models\Setting::get('hospital_name', 'الأمير متعب بن عبد العزيز') }}</h2>
                <h3 style="font-weight: bold; margin-top: 15px; font-size: 16px;">قائمة الخدمات المطلوبة</h3>
            </div>
            <div style="width: 100px;"></div>
        </div>

        <table class="info-table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th class="label-cell" style="text-align: center; width: 50px;">#</th>
                    <th class="label-cell" style="text-align: center;">كود الخدمة</th>
                    <th class="label-cell" style="text-align: right;">اسم الخدمة</th>
                    <th class="label-cell" style="text-align: center; width: 80px;">الكمية</th>
                    <th class="label-cell" style="text-align: center;">السعر</th>
                    <th class="label-cell" style="text-align: center;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($services as $index => $s)
                    @php
                        $qty = $s['quantity'] ?? 1;
                        $price = $s['price'] ?? 0;
                        $subtotal = $qty * $price;
                        $total += $subtotal;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td style="text-align: center;">{{ $s['code'] ?? '-' }}</td>
                        <td>{{ $s['name'] ?? '-' }}</td>
                        <td style="text-align: center;">{{ $qty }}</td>
                        <td style="text-align: center;">{{ number_format($price, 2) }}</td>
                        <td style="text-align: center;">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: bold; background-color: #f0f0f0;">
                    <td colspan="5" style="text-align: left; padding-left: 15px;">الإجمالي الكلي</td>
                    <td style="text-align: center;">{{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

</body>
</html>
