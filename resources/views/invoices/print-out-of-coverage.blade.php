<!DOCTYPE html>
<html lang="ar-SA-u-nu-latn" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إقرار بتلقي خدمة خارج التغطية التأمينية - {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #000;
            font-size: 14px;
            padding: 30px 40px;
            min-height: 100vh;
        }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .kb-input { background: transparent !important; border-bottom: 1px solid #000 !important; }
            @page { margin: 1cm; margin-bottom: 1.6cm; size: A4; }
            @page {
                @bottom-center {
                    content: counter(page);
                    font-size: 11px;
                    color: #333;
                }
            }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
        .no-print {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .no-print .hint { width: 100%; text-align: center; font-size: 12px; color: #555; }
        .btn {
            padding: 9px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            background: #1a1a2e;
            color: #fff;
            font-weight: bold;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 2px solid #ccc;
        }
        .header-logo img { max-width: 90px; max-height: 90px; }
        .header-logo-placeholder {
            width: 80px; height: 80px; border: 1px dashed #ccc;
            display: flex; align-items: center; justify-content: center; font-size: 12px; color: #999;
        }
        .header-info { text-align: center; flex: 1; padding: 0 20px; }
        .header-spacer { width: 80px; }
        .form-title {
            text-align: center;
            font-size: 20px;
            font-weight: 900;
            margin: 10px 0 24px;
            text-decoration: underline;
            text-underline-offset: 6px;
        }
        .pledge-box {
            border: 2px solid #000;
            padding: 20px 22px;
            margin-bottom: 24px;
            font-size: 15px;
            line-height: 2.2;
        }
        .pledge-row {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        .pledge-row .lbl { font-weight: 700; white-space: nowrap; }
        .kb-input {
            border: none;
            border-bottom: 1px dotted #333;
            background: #fefce8;
            font: inherit;
            color: #000;
            padding: 2px 6px;
            outline: none;
            min-width: 140px;
        }
        .kb-input.grow { flex: 1; min-width: 160px; }
        .kb-input.wide { min-width: 260px; width: 55%; }
        .kb-input.amount { min-width: 140px; width: 160px; }
        .pledge-text { margin-top: 8px; }
        .pledge-closing {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin-top: 18px;
        }
        .ack-table {
            width: 58%;
            margin-right: auto;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 14px;
        }
        .ack-table th {
            background: #e8e8e8;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 14px;
        }
        .ack-table td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: middle;
        }
        .ack-table .row-label {
            width: 38%;
            font-weight: 700;
            background: #f8fafc;
        }
        .ack-table .kb-input { width: 100%; }
        .file-ref { margin: 10px 0 20px; font-size: 13px; }
        .sigs {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
            margin-top: 28px;
        }
        .sig-block { flex: 1; min-width: 160px; text-align: center; }
        .sig-title { font-weight: 700; margin-bottom: 8px; font-size: 13px; }
        .sig-box {
            min-height: 56px;
            border: 1px solid #333;
            padding: 6px;
            margin-bottom: 6px;
        }
        .sig-box img { max-width: 120px; max-height: 50px; display: block; margin: 0 auto; }
        .sig-name { font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn" onclick="window.print()">🖨️ طباعة الإقرار</button>
        <a href="{{ route('invoices.show', $invoice) }}" style="color:#555;text-decoration:none;">← العودة للفاتورة</a>
        <p class="hint">عبّي الفراغات بالكيبورد ثم اضغط طباعة.</p>
    </div>

    <div class="page-header">
        <div class="header-logo">
            @php $logo = \App\Models\Setting::get('logo'); @endphp
            @if ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo))
                <img src="{{ asset('storage/' . $logo) }}" alt="شعار المستشفى">
            @else
                <div class="header-logo-placeholder">شعار</div>
            @endif
        </div>
        <div class="header-info">
            <div style="font-size:22px;font-weight:900;">مستشفى الملك عبدالعزيز التخصصي بالجوف</div>
            <div style="font-size:15px;font-weight:700;">King Abdulaziz Specialist Hospital - Aljouf</div>
            <div style="font-size:16px;font-weight:800;color:#1e40af;margin-top:5px;">إدارة تنمية الإيرادات</div>
        </div>
        <div class="header-spacer"></div>
    </div>

    <div class="form-title">إقرار بتلقي خدمة خارج التغطية التأمينية</div>

    <div class="pledge-box">
        <div class="pledge-row">
            <span class="lbl">نعم أنا المدعو /</span>
            <input type="text" class="kb-input grow" name="full_name" autocomplete="off" placeholder="الاسم">
            <span class="lbl">حامل إقامة رقم /</span>
            <input type="text" class="kb-input grow" name="iqama" autocomplete="off" placeholder="رقم الإقامة">
        </div>
        <div class="pledge-row">
            <span class="lbl">مصدرها /</span>
            <input type="text" class="kb-input grow" name="nationality" autocomplete="off" placeholder="الجنسية / مصدر الإقامة">
        </div>

        <div class="pledge-text">
            أقر بأني تلقيت خدمة
            <input type="text" class="kb-input wide" name="service_name" autocomplete="off" placeholder="اسم الخدمة">
            وتم إخطاري بمبلغ الخدمة وأنها خارج نطاق التغطية التأمينية.
        </div>

        <div class="pledge-closing">والله الموفق ،،،،</div>
    </div>

    <div class="file-ref">ص/ ملف المريض بالقسم .</div>

    <div class="sigs">
        <div class="sig-block">
            <div class="sig-title">توقيع الموظف</div>
            <div class="sig-box">
                @if (auth()->check() && auth()->user()->signature)
                    <img src="{{ asset('storage/' . ltrim(auth()->user()->signature ?? '', '/')) }}" alt="توقيع الموظف">
                @endif
            </div>
            <div class="sig-name">{{ auth()->user()->name ?? '________________' }}</div>
        </div>
        <div class="sig-block">
            <div class="sig-title">مدير إدارة تنمية الإيرادات</div>
            <div class="sig-box">
                @if (isset($manager) && $manager && $manager->signature)
                    <img src="{{ asset('storage/' . ltrim($manager->signature ?? '', '/')) }}" alt="توقيع المدير">
                @endif
            </div>
            <div class="sig-name">
                {{ isset($manager) && $manager ? ($manager->name_ar ?? $manager->name) : (\App\Models\Setting::get('manager_name', 'جسار بن محمد الضويحي')) }}
            </div>
        </div>
        <div class="sig-block">
            <div class="sig-title">الختم</div>
            <div class="sig-box">
                @php $seal = \App\Models\Setting::get('seal') ?: \App\Models\Setting::get('stamp'); @endphp
                @if ($seal && \Illuminate\Support\Facades\Storage::disk('public')->exists($seal))
                    <img src="{{ asset('storage/' . $seal) }}" alt="الختم">
                @endif
            </div>
        </div>
    </div>

    @include('components.report-footer')
</body>
</html>
