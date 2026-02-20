<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تعهد خطي - {{ $invoice->invoice_number }}</title>
    <!-- Signature Pad Library -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

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
            body { padding: 0; margin: 0; }
            .no-print { display: none !important; }
            @page { margin: 1cm; size: A4; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .sig-canvas-wrap { display: none !important; }
            .sig-preview { display: block !important; }
        }

        /* ====== PRINT BUTTON ====== */
        .no-print {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 9px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-family: inherit;
        }
        .btn-dark  { background: #1a1a2e; color: #fff; }
        .btn-dark:hover  { background: #16213e; }
        .btn-red   { background: #dc2626; color: #fff; }
        .btn-red:hover   { background: #b91c1c; }
        .btn-gray  { background: #e5e7eb; color: #333; }
        .btn-gray:hover  { background: #d1d5db; }

        /* ====== HEADER ====== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ccc;
        }
        .header-logo img { max-width: 90px; max-height: 90px; }
        .header-logo-placeholder {
            width: 80px; height: 80px; border: 1px dashed #ccc;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; color: #999;
        }
        .header-info { text-align: center; flex: 1; padding: 0 20px; }
        .header-info .hospital-name-ar { font-size: 17px; font-weight: bold; margin-bottom: 3px; }
        .header-info .hospital-name-en { font-size: 13px; color: #333; margin-bottom: 3px; }
        .header-info .cluster-name     { font-size: 12px; color: #555; margin-bottom: 3px; }
        .header-info .dept-name        { font-size: 13px; font-weight: bold; margin-top: 4px; }
        .header-spacer { width: 80px; }

        /* ====== TITLE ====== */
        .form-title {
            text-align: center;
            font-size: 34px;
            font-weight: bold;
            margin: 20px 0 30px;
            letter-spacing: 1px;
        }

        /* ====== PLEDGE BOX ====== */
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
        .pledge-row .lbl { font-weight: bold; white-space: nowrap; }
        .pledge-row .fill {
            flex: 1;
            min-width: 120px;
            border-bottom: 1px solid #555;
            height: 22px;
            display: inline-block;
            padding: 0 4px;
        }
        .pledge-text   { margin-top: 10px; line-height: 2.1; font-size: 14.5px; }
        .pledge-closing { text-align: center; font-size: 15px; font-weight: bold; margin-top: 16px; }

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
        .ack-table tr:last-child td { border-bottom: none; }
        .fill-dots { display: inline-block; width: 100%; min-height: 20px; padding: 0 4px; }

        /* Signature row in table */
        .sig-td { padding: 4px 10px !important; }
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
        /* Preview image (shown in print) */
        .sig-preview {
            display: none;
            max-width: 250px;
            max-height: 80px;
        }

        .file-ref { font-size: 12px; color: #444; margin-top: 8px; text-align: left; }

        /* ====== EMPLOYEE SECTION ====== */
        .employee-section { margin-top: 55px; }
        .employee-label {
            font-size: 17px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 55px;
        }
        .sig-row { display: flex; justify-content: flex-start; gap: 70px; }
        .sig-block { text-align: center; }
        .sig-block .sig-title { font-weight: bold; font-size: 14px; margin-bottom: 55px; }
        .sig-block .sig-img img { max-width: 150px; max-height: 60px; display: block; margin: 0 auto 6px; }
        .sig-block .sig-name { border-top: 1px solid #555; padding-top: 6px; font-size: 13px; min-width: 150px; color: #333; }

        /* ====== DECORATIVE WAVES ====== */
        .bg-waves {
            position: fixed;
            bottom: 0; left: 0;
            width: 100%; height: 230px;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }
        .bg-waves svg { position: absolute; bottom: 0; left: 0; width: 100%; }

        /* ====== PAGE FOOTER ====== */
        .page-footer {
            position: fixed;
            bottom: 0; left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 25px;
            font-size: 11px;
            color: #888;
            z-index: 1;
        }

        /* Sig instructions */
        .sig-hint { font-size: 11px; color: #888; margin-bottom: 3px; }
    </style>
</head>
<body>

    <!-- Print Button -->
    <div class="no-print">
        <button class="btn btn-dark" onclick="handlePrint()">🖨️ طباعة التعهد</button>
        <a href="{{ route('invoices.show', $invoice) }}"
           style="color: #555; font-size: 14px; text-decoration: none;">← العودة للفاتورة</a>
    </div>

    <!-- ====== HEADER ====== -->
    <div class="page-header">
        <div class="header-logo">
            @if(App\Models\Setting::get('logo'))
                <img src="{{ asset('storage/' . App\Models\Setting::get('logo')) }}" alt="شعار المستشفى">
            @else
                <div class="header-logo-placeholder">شعار</div>
            @endif
        </div>
        <div class="header-info">
            <div class="hospital-name-ar">مستشفى {{ App\Models\Setting::get('hospital_name', 'الأمير متعب بن عبدالعزيز') }}</div>
            <div class="hospital-name-en">{{ App\Models\Setting::get('hospital_name_en', 'Prince Muteb bin Abdulaziz Hospital') }}</div>
            <div class="cluster-name">Empowered by {{ App\Models\Setting::get('health_cluster_name_en', 'Aljouf Health Cluster') }}</div>
            <div class="dept-name">إدارة تنمية الإيرادات</div>
        </div>
        <div class="header-spacer"></div>
    </div>

    <!-- ====== TITLE ====== -->
    <div class="form-title">تعهد خطي</div>

    <!-- ====== PLEDGE BOX ====== -->
    <div class="pledge-box">
        <div class="pledge-row">
            <span class="lbl">نعم أنا المدعو /</span>
            <span class="fill">@if($invoice->patient){{ $invoice->patient->name_ar ?? $invoice->patient->name }}@endif</span>
            <span class="lbl" style="margin-right: 10px;">حامل إقامة رقم /</span>
            <span class="fill">@if($invoice->patient){{ $invoice->patient->identity_value ?? '' }}@endif</span>
        </div>
        <div class="pledge-row">
            <span class="lbl">مصدرها /</span>
            <span class="fill">@if($invoice->patient){{ $invoice->patient->country_of_origin ?? '' }}@endif</span>
        </div>
        <div class="pledge-text">
            أتعهد بأنني سوف أقوم بدفع كافة المصاريف العلاجية الإضافية التي تكون خارج نطاق التغطية التأمينية وعلى هذا يتم التوقيع .
        </div>
        <div class="pledge-closing">والله الموفق ،،،،</div>
    </div>

    <!-- ====== ACKNOWLEDGMENT TABLE: المقر بما فيه ====== -->
    <table class="ack-table">
        <thead>
            <tr><th colspan="2">المقر بما فيه</th></tr>
        </thead>
        <tbody>
            <tr>
                <td class="row-label">الأسم :</td>
                <td><span class="fill-dots">@if($invoice->patient){{ $invoice->patient->name_ar ?? $invoice->patient->name }}@endif</span></td>
            </tr>
            <tr>
                <td class="row-label">الجنسية :</td>
                <td><span class="fill-dots">@if($invoice->patient){{ $invoice->patient->country_of_origin ?? '' }}@endif</span></td>
            </tr>
            <tr>
                <td class="row-label">رقم الإقامة :</td>
                <td><span class="fill-dots">@if($invoice->patient){{ $invoice->patient->identity_value ?? '' }}@endif</span></td>
            </tr>
            <tr>
                <td class="row-label">التوقيع :-</td>
                <td class="sig-td">
                    <!-- Signature pad — hidden on print -->
                    <div class="sig-canvas-wrap no-print" id="sigWrap">
                        <p class="sig-hint no-print">وقّع هنا بصوابعك أو الماوس ↓</p>
                        <canvas id="sigCanvas"></canvas>
                    </div>
                    <div class="sig-actions no-print">
                        <button class="btn btn-gray" onclick="clearSig()" type="button">مسح</button>
                    </div>
                    <!-- Preview shown on print -->
                    <img id="sigPreview" class="sig-preview" src="" alt="توقيع المريض">
                </td>
            </tr>
        </tbody>
    </table>

    <div class="file-ref">ص/ ملف المريض بالقسم .</div>

    <!-- ====== EMPLOYEE SECTION ====== -->
    <div class="employee-section">
        <div class="employee-label">الموظف المختص :-</div>
        <div class="sig-row">
            <div class="sig-block">
                <div class="sig-title">توقيع الموظف</div>
                <div class="sig-img">
                    @if(auth()->check() && auth()->user()->signature)
                        <img src="{{ asset('storage/' . auth()->user()->signature) }}" alt="توقيع الموظف">
                    @endif
                </div>
                <div class="sig-name">{{ auth()->check() ? auth()->user()->name : '________________________________' }}</div>
            </div>
            @if(isset($manager) && $manager)
            <div class="sig-block">
                <div class="sig-title">توقيع المدير</div>
                <div class="sig-img">
                    @if($manager->signature)
                        <img src="{{ asset('storage/' . $manager->signature) }}" alt="توقيع المدير">
                    @endif
                </div>
                <div class="sig-name">{{ $manager->name }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- ====== DECORATIVE WAVES ====== -->
    <div class="bg-waves">
        <svg viewBox="0 0 1200 230" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0,120 C200,200 400,20 600,100 C800,180 1000,30 1200,110 L1200,230 L0,230 Z" fill="#e8e8e8" opacity="0.55"/>
            <path d="M0,160 C150,100 350,200 550,140 C750,80 950,180 1200,150 L1200,230 L0,230 Z" fill="#d0d0d0" opacity="0.4"/>
        </svg>
    </div>

    <!-- ====== PAGE FOOTER ====== -->
    <div class="page-footer">
        <span>📷 {{ App\Models\Setting::get('social_handle', 'AljoufCluster') }}</span>
        <span>{{ $invoice->invoice_number }} | {{ $invoice->invoice_date?->format('Y/m/d') }}</span>
    </div>

    <!-- ====== SIGNATURE PAD JS ====== -->
    <script>
        const canvas  = document.getElementById('sigCanvas');
        const preview = document.getElementById('sigPreview');
        const sigWrap = document.getElementById('sigWrap');

        // Resize canvas to match its CSS width
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width  = canvas.offsetWidth  * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }

        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255,255,255,0)',
            penColor: '#000'
        });

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        function clearSig() {
            signaturePad.clear();
            preview.src = '';
            preview.style.display = 'none';
        }

        function handlePrint() {
            if (!signaturePad.isEmpty()) {
                // Embed signature into the preview <img>
                preview.src = signaturePad.toDataURL('image/png');
                preview.style.display = 'block';
            }
            setTimeout(() => window.print(), 150);
        }
    </script>

</body>
</html>
