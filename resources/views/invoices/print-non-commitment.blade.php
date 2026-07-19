<!DOCTYPE html>
<html lang="ar-SA-u-nu-latn" dir="rtl">

<head>
    <title>محضر عدم التوقيع - {{ $invoice->invoice_number }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            color: #000;
            font-size: 14px;
            padding: 30px 40px;
            position: relative;
            min-height: 100vh;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .sig-canvas-wrap {
                display: none !important;
            }

            .sig-preview {
                display: block;
            }

            .no-print {
                display: none !important;
            }

            .print-page-num {
                display: block !important;
                position: fixed;
                bottom: 0.4cm;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 11px;
                color: #333;
                z-index: 9999;
            }

            .print-page-num::after {
                content: counter(page);
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        @page {
            margin: 1cm;
            margin-bottom: 1.8cm;
            size: A4;
        }

        @page {
            @bottom-center {
                content: counter(page);
                font-family: 'Segoe UI', Tahoma, sans-serif;
                font-size: 11px;
                color: #333;
            }
        }

        /* ====== PRINT BUTTON ====== */
        .no-print {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 9px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-family: inherit;
        }

        .btn-dark {
            background: #1a1a2e;
            color: #fff;
        }

        .btn-dark:hover {
            background: #16213e;
        }

        /* ====== HEADER ====== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ccc;
        }

        .header-logo img {
            max-width: 90px;
            max-height: 90px;
        }

        .header-logo-placeholder {
            width: 80px;
            height: 80px;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #999;
        }

        .header-info {
            text-align: center;
            flex: 1;
            padding: 0 20px;
        }

        .header-info .hospital-name-ar {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .header-info .hospital-name-en {
            font-size: 13px;
            color: #333;
            margin-bottom: 3px;
        }

        .header-info .cluster-name {
            font-size: 12px;
            color: #555;
            margin-bottom: 3px;
        }

        .header-info .dept-name {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
        }

        .header-spacer {
            width: 80px;
        }

        /* ====== TITLE ====== */
        .form-title {
            text-align: center;
            font-size: 22px;
            font-weight: 900;
            margin: 18px 0 22px;
            text-decoration: underline;
            text-underline-offset: 6px;
            letter-spacing: 0.5px;
        }

        .report-meta {
            margin: 0 0 22px;
            max-width: 280px;
            margin-right: auto;
            font-size: 14px;
            line-height: 2;
        }

        .report-meta .meta-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 4px;
        }

        .report-meta .meta-label {
            font-weight: 700;
            white-space: nowrap;
        }

        .report-meta .meta-line {
            flex: 1;
            border-bottom: 1px dotted #333;
            min-height: 20px;
        }

        /* فراغات قابلة للكتابة بالكيبورد */
        .kb-input {
            border: none;
            border-bottom: 1px dotted #333;
            background: #fefce8;
            font: inherit;
            color: #000;
            padding: 2px 4px;
            outline: none;
            min-width: 120px;
        }
        .kb-input.flex-1 { flex: 1; width: 100%; max-width: 100%; }
        .kb-input.long { min-width: 280px; width: 60%; }
        .kb-input.inline { display: inline-block; vertical-align: baseline; margin: 0 4px; min-width: 200px; }
        .kb-input.sig { min-width: 140px; }
        @media print {
            .kb-input {
                background: transparent !important;
                border-bottom: 1px solid #000;
            }
        }

        .report-body {
            font-size: 15px;
            line-height: 2.15;
            text-align: justify;
            margin-bottom: 14px;
        }

        .report-body .blank-inline {
            display: inline-block;
            border-bottom: 1px dotted #333;
            min-width: 240px;
            height: 1.1em;
            vertical-align: baseline;
            margin: 0 4px;
        }

        .report-body .blank-inline.long {
            min-width: 340px;
        }

        .report-field {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin: 10px 0;
            font-size: 15px;
        }

        .report-field .fld-label {
            font-weight: 700;
            white-space: nowrap;
        }

        .report-field .fld-line {
            flex: 1;
            max-width: 420px;
            border-bottom: 1px dotted #333;
            min-height: 22px;
        }

        .report-sigs-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .report-sigs-staff {
            flex: 0 0 auto;
            min-width: 240px;
        }

        .report-sig-line {
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.95;
        }

        .report-sig-line .role {
            font-weight: 700;
        }

        .report-sig-line .sig-dots {
            display: inline-block;
            min-width: 150px;
            border-bottom: 1px dotted #333;
            margin-right: 6px;
        }

        .report-sigs-manager {
            flex: 0 0 auto;
            text-align: center;
            min-width: 240px;
            margin-top: 8px;
        }

        .report-sigs-manager .mgr-title {
            font-weight: 800;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .report-sigs-manager .mgr-name {
            font-weight: 700;
            font-size: 14px;
            margin-top: 28px;
        }

        .report-sigs-manager .mgr-sig-img img {
            max-width: 140px;
            max-height: 60px;
            margin: 8px auto;
            display: block;
        }

        /* ====== MAIN BOX (legacy unused) ====== */
        .pledge-box {
            border: 2px solid #000;
            padding: 18px 20px;
            margin-bottom: 30px;
            font-size: 14.5px;
        }

        .pledge-row {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 10px;
            line-height: 2.2;
        }

        .pledge-row .lbl {
            font-weight: bold;
            white-space: nowrap;
        }

        .pledge-row .fill {
            flex: 1;
            min-width: 140px;
            border-bottom: 1px solid #555;
            height: 22px;
            display: inline-block;
            padding: 0 4px;
        }

        .pledge-blank {
            display: inline-block;
            border-bottom: 1px dotted #333;
            min-width: 200px;
            height: 1.2em;
            vertical-align: baseline;
            margin: 0 6px;
        }

        .pledge-blank.wide {
            min-width: 280px;
        }

        .pledge-blank.amount {
            min-width: 120px;
        }

        .print-page-num {
            display: none;
        }

        .pledge-text {
            margin-top: 10px;
            line-height: 2.1;
            font-size: 14.5px;
        }

        .pledge-closing {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin-top: 16px;
        }

        /* ====== ACKNOWLEDGMENT TABLE ====== */
        .ack-table {
            width: 58%;
            margin-right: auto;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 10px;
        }

        .ack-table thead th {
            background-color: #e8e8e8 !important;
            text-align: center;
            padding: 9px 15px;
            font-size: 15px;
            font-weight: bold;
            border-bottom: 2px solid #000;
        }

        .ack-table td {
            padding: 9px 15px;
            border-bottom: 1px solid #aaa;
            vertical-align: middle;
        }

        .ack-table td.row-label {
            font-weight: bold;
            white-space: nowrap;
            background-color: #f5f5f5 !important;
            border-left: 1px solid #aaa;
            text-align: right;
            width: 130px;
        }

        .ack-table tr:last-child td {
            border-bottom: none;
        }

        .fill-dots {
            display: inline-block;
            width: 100%;
            min-height: 22px;
            padding: 0 4px;
            border-bottom: 1px solid #555;
        }

        /* Signature pad in table */
        .sig-td {
            padding: 4px 10px !important;
        }

        .sig-canvas-wrap {
            border: 1px dashed #999;
            border-radius: 4px;
            background: #fafafa;
            margin: 4px 0;
            width: 100%;
            max-width: 320px;
        }

        .sig-canvas-wrap canvas {
            display: block;
            width: 100%;
            height: 90px;
            cursor: crosshair;
        }

        .sig-actions {
            display: flex;
            gap: 8px;
            margin-top: 4px;
        }

        .sig-preview {
            display: none;
            max-width: 250px;
            max-height: 80px;
        }

        /* البصمة الإلكترونية */
        .fingerprint-wrap {
            margin: 4px 0;
            max-width: 320px;
        }

        .fingerprint-hint {
            font-size: 12px;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .fingerprint-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .fingerprint-wrap input[type="file"] {
            font-size: 12px;
        }

        .fingerprint-paste {
            border: 1px dashed #1e40af;
            border-radius: 4px;
            background: #eff6ff;
            padding: 10px;
            margin-top: 6px;
            font-size: 11px;
            color: #1e40af;
            text-align: center;
            min-height: 40px;
            cursor: pointer;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
        }

        .fingerprint-preview {
            display: none;
            max-width: 120px;
            max-height: 100px;
        }

        @media print {
            .fingerprint-preview.has-image {
                display: block !important;
            }

            .fingerprint-wrap .no-print {
                display: none !important;
            }
        }

        .btn-gray {
            background: #e5e7eb;
            color: #333;
            border: none;
            border-radius: 6px;
            padding: 6px 14px;
            cursor: pointer;
            font-family: inherit;
        }

        .sig-hint {
            font-size: 11px;
            color: #888;
            margin-bottom: 3px;
        }

        .file-ref {
            font-size: 12px;
            color: #444;
            margin-top: 8px;
            text-align: left;
        }

        /* ====== EMPLOYEE SECTION ====== */
        .employee-section {
            margin-top: 55px;
        }

        .employee-label {
            font-size: 17px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 55px;
        }

        .sig-row {
            display: flex;
            justify-content: flex-start;
            gap: 70px;
        }

        .sig-block {
            text-align: center;
        }

        .sig-block .sig-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 55px;
        }

        .sig-block .sig-img img {
            max-width: 150px;
            max-height: 60px;
            display: block;
            margin: 0 auto 6px;
        }

        .sig-block .sig-name {
            border-top: 1px solid #555;
            padding-top: 6px;
            font-size: 13px;
            min-width: 150px;
            color: #333;
        }

        /* ====== SEAL ====== */
        .seal-block {
            text-align: center;
            margin-top: 24px;
            display: inline-block;
        }

        .seal-block .seal-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .seal-box {
            width: 90px;
            height: 90px;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .seal-box img {
            max-width: 86px;
            max-height: 86px;
        }

        /* ====== WITNESS SECTION ====== */
        .witness-section {
            margin-top: 40px;
        }

        .witness-label {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 50px;
        }

        /* ====== DECORATIVE WAVES ====== */
        .bg-waves {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 230px;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }

        .bg-waves svg {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
        }

        /* ====== PAGE FOOTER ====== */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 25px;
            font-size: 11px;
            color: #888;
            z-index: 1;
        }
    </style>
</head>

<body>

    <!-- Print Button -->
    <div class="no-print">
        <button type="button" class="btn btn-dark" id="btn-print-report" onclick="window.print()">🖨️ طباعة المحضر</button>
        <a href="{{ route('invoices.show', $invoice) }}" style="color: #555; font-size: 14px; text-decoration: none;">←
            العودة للفاتورة</a>
        <p style="width:100%;text-align:center;font-size:12px;color:#555;">عبّي الفراغات بالكيبورد ثم اضغط طباعة.</p>
    </div>

    <!-- ====== HEADER ====== -->
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
            <div class="hospital-name-ar" style="font-size: 22px; font-weight: 900;">مستشفى الملك عبدالعزيز التخصصي
                بالجوف</div>
            <div class="hospital-name-en" style="font-size: 15px; font-weight: 700;">King Abdulaziz Specialist Hospital
                - Aljouf</div>
            <div class="dept-name" style="font-size: 16px; font-weight: 800; color: #1e40af; margin-top: 5px;">إدارة
                تنمية الإيرادات</div>
        </div>
        <div class="header-spacer"></div>
    </div>

    <!-- ====== TITLE ====== -->
    <div class="form-title">محضر عدم التوقيع</div>

    {{-- رقم المحضر / التاريخ / الوقت — قابلة للكتابة بالكيبورد --}}
    <div class="report-meta">
        <div class="meta-row">
            <span class="meta-label">رقم المحضر :</span>
            <input type="text" class="kb-input flex-1" name="report_no" autocomplete="off">
        </div>
        <div class="meta-row">
            <span class="meta-label">التاريخ :</span>
            <input type="text" class="kb-input flex-1" name="report_date" autocomplete="off">
        </div>
        <div class="meta-row">
            <span class="meta-label">الوقت :</span>
            <input type="text" class="kb-input flex-1" name="report_time" autocomplete="off">
        </div>
    </div>

    <div class="report-body">
        نقر نحن الموقعين أدناه بأنه تم شرح جميع التعهدات والسندات النظامية المتعلقة بتلقي الخدمات الصحية المدفوعة للمستفيد، وهي:
        (<input type="text" class="kb-input inline long" name="services_list" autocomplete="off">)
    </div>

    <div class="report-field">
        <span class="fld-label">اسم المستفيد :</span>
        <input type="text" class="kb-input flex-1" name="beneficiary_name" autocomplete="off" style="max-width:420px;">
    </div>
    <div class="report-field">
        <span class="fld-label">رقم الإقامة :</span>
        <input type="text" class="kb-input flex-1" name="iqama_no" autocomplete="off" style="max-width:420px;">
    </div>

    <div class="report-body">
        وقد تم توضيح ما يترتب على هذه التعهدات والالتزامات المالية للمستفيد بصورة واضحة، إلا أنه رفض التوقيع عليها مع إبدائه رغبته بتلقي الخدمة.
        وعليه تم تحرير هذا المحضر لإثبات واقعة رفض التوقيع وإحاطته بأن عدم التوقيع قد يترتب عليه إجراءات قانونية.
        وتم إخطاري بمبلغ الخدمة وأنها خارج نطاق التغطية التأمينية.
    </div>

    <div class="report-sigs-wrap">
        <div class="report-sigs-staff">
            <div class="report-sig-line">
                <span class="role">المحصل :</span>
                <input type="text" class="kb-input sig" name="collector_name" autocomplete="off">
                <br>
                <span class="role">التوقيع :</span>
                <input type="text" class="kb-input sig" name="collector_sig" autocomplete="off">
            </div>
            <div class="report-sig-line">
                <span class="role">فني المتابعة :</span>
                <input type="text" class="kb-input sig" name="followup_name" autocomplete="off">
                <br>
                <span class="role">التوقيع :</span>
                <input type="text" class="kb-input sig" name="followup_sig" autocomplete="off">
            </div>
            <div class="report-sig-line">
                <span class="role">المحاسب :</span>
                <input type="text" class="kb-input sig" name="accountant_name" autocomplete="off">
                <br>
                <span class="role">التوقيع :</span>
                <input type="text" class="kb-input sig" name="accountant_sig" autocomplete="off">
            </div>
        </div>

        <div class="report-sigs-manager">
            <div class="mgr-title">مدير إدارة تنمية الإيرادات</div>
            <div class="mgr-sig-img">
                @if (isset($manager) && $manager && $manager->signature)
                    <img src="{{ asset('storage/' . ltrim($manager->signature ?? '', '/')) }}" alt="توقيع المدير">
                @endif
            </div>
            <div class="mgr-name">
                {{ isset($manager) && $manager ? ($manager->name_ar ?? $manager->name) : (\App\Models\Setting::get('manager_name', 'جسار بن محمد الضويحي')) }}
            </div>
        </div>
    </div>

    <!-- ====== DECORATIVE WAVES ====== -->
    <div class="bg-waves">
        <svg viewBox="0 0 1200 230" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0,120 C200,200 400,20 600,100 C800,180 1000,30 1200,110 L1200,230 L0,230 Z" fill="#e8e8e8"
                opacity="0.55" />
            <path d="M0,160 C150,100 350,200 550,140 C750,80 950,180 1200,150 L1200,230 L0,230 Z" fill="#d0d0d0"
                opacity="0.4" />
        </svg>
    </div>

    <!-- ====== PAGE FOOTER ====== -->
    <div class="page-footer">
        <span>📷 {{ App\Models\Setting::get('social_handle', 'AljoufCluster') }}</span>
        <span>{{ $invoice->invoice_number }} | {{ $invoice->invoice_date?->format('Y/m/d') }}</span>
    </div>

    {{-- ترقيم الصفحات عند الطباعة (1، 2، …) --}}
    <div class="print-page-num" aria-hidden="true"></div>

    <!-- ====== PRINT JS ====== -->
    <script>
        function handlePrint() {
            window.print();
        }

        // فتح نافذة الطباعة تلقائياً لو جت من زرار الفاتورة (?print=1)
        (function () {
            var params = new URLSearchParams(window.location.search);
            if (params.get('print') === '1') {
                window.addEventListener('load', function () {
                    setTimeout(function () {
                        window.print();
                    }, 300);
                });
            }

            var btn = document.getElementById('btn-print-report');
            if (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.print();
                });
            }
        })();
    </script>

    @include('components.report-footer')
</body>

</html>
