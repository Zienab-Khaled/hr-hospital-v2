<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعهد خطي</title>
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
            width: 80px; height: 80px;
            border: 1px dashed #ccc;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; color: #999;
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
            font-size: 34px;
            font-weight: bold;
            margin: 20px 0 30px;
            letter-spacing: 1px;
        }

        /* ====== PLEDGE TEXT BOX ====== */
        .pledge-box {
            border: 2px solid #000;
            padding: 18px 20px;
            margin-bottom: 30px;
            line-height: 2.2;
            font-size: 14.5px;
        }
        .pledge-line {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 6px;
        }
        .pledge-line .label {
            font-weight: bold;
            white-space: nowrap;
        }
        .pledge-line .fill-line {
            flex: 1;
            min-width: 120px;
            border-bottom: 1px solid #555;
            height: 22px;
            display: inline-block;
        }
        .pledge-text-paragraph {
            margin-top: 8px;
            line-height: 2;
        }
        .pledge-closing {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin-top: 14px;
        }

        /* ====== ACKNOWLEDGMENT TABLE ====== */
        .ack-section {
            margin-bottom: 40px;
        }
        .ack-table {
            width: 55%;
            margin-right: auto;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        .ack-table th {
            background-color: #e8e8e8 !important;
            text-align: center;
            padding: 8px 15px;
            font-size: 15px;
            font-weight: bold;
            border-bottom: 2px solid #000;
        }
        .ack-table td {
            padding: 9px 15px;
            border-bottom: 1px solid #aaa;
            vertical-align: middle;
        }
        .ack-table td:first-child {
            font-weight: bold;
            white-space: nowrap;
            background-color: #f5f5f5 !important;
            border-left: 1px solid #aaa;
            text-align: right;
            width: 130px;
        }
        .ack-table td:last-child {
            min-width: 160px;
        }
        .ack-table tr:last-child td {
            border-bottom: none;
        }
        .ack-table .fill-dots {
            display: inline-block;
            width: 100%;
            border-bottom: 1px dashed #555;
            min-height: 20px;
        }

        /* File reference note */
        .file-ref {
            text-align: left;
            font-size: 12px;
            color: #444;
            margin-top: 8px;
        }

        /* ====== EMPLOYEE SECTION ====== */
        .employee-section {
            margin-top: 50px;
            padding-top: 10px;
        }
        .employee-label {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 50px;
        }
        .employee-signature-area {
            display: flex;
            justify-content: flex-start;
            gap: 80px;
            margin-top: 20px;
        }
        .sig-block {
            text-align: center;
        }
        .sig-block .sig-title {
            font-weight: bold;
            margin-bottom: 50px;
            font-size: 14px;
        }
        .sig-block .sig-name {
            border-top: 1px solid #555;
            padding-top: 5px;
            font-size: 13px;
            min-width: 150px;
        }

        /* ====== DECORATIVE BACKGROUND WAVES ====== */
        .bg-waves {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 220px;
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

        /* ====== FOOTER WATERMARK ====== */
        .page-footer-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 25px;
            font-size: 12px;
            color: #888;
            z-index: 1;
        }

        /* ====== PRINT BUTTON ====== */
        .print-btn-wrap {
            text-align: center;
            margin-bottom: 25px;
        }
        .print-btn {
            padding: 10px 28px;
            background: #1a1a2e;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-family: inherit;
        }
        .print-btn:hover { background: #16213e; }
    </style>
</head>
<body>

    <!-- Print Button -->
    <div class="no-print print-btn-wrap">
        <button class="print-btn" onclick="window.print()">🖨️ طباعة التعهد</button>
        <a href="{{ route('written-commitments.index') }}"
           style="margin-right: 15px; color: #555; font-size: 14px; text-decoration: none;">
            ← رجوع للقائمة
        </a>
    </div>

    <!-- ============================================================
         HEADER
    ============================================================ -->
    <div class="page-header">
        <div class="header-logo">
            @if(isset($settings) && $settings && $settings->logo)
                <img src="{{ asset('storage/' . $settings->logo) }}" alt="شعار المستشفى">
            @elseif(App\Models\Setting::get('logo'))
                <img src="{{ asset('storage/' . App\Models\Setting::get('logo')) }}" alt="شعار المستشفى">
            @else
                <div class="header-logo-placeholder">شعار</div>
            @endif
        </div>

        <div class="header-info">
            <div class="hospital-name-ar">
                مستشفى {{ App\Models\Setting::get('hospital_name', 'الأمير متعب بن عبدالعزيز') }}
            </div>
            <div class="hospital-name-en">
                {{ App\Models\Setting::get('hospital_name_en', 'Prince Muteb bin Abdulaziz Hospital') }}
            </div>
            <div class="cluster-name">
                Empowered by {{ App\Models\Setting::get('health_cluster_name_en', 'Aljouf Health Cluster') }}
            </div>
            <div class="dept-name">إدارة تنمية الإيرادات</div>
        </div>

        <div class="header-spacer"></div>
    </div>

    <!-- ============================================================
         TITLE
    ============================================================ -->
    <div class="form-title">تعهد خطي</div>

    <!-- ============================================================
         PLEDGE TEXT
    ============================================================ -->
    <div class="pledge-box">
        <!-- Line 1: نعم أنا المدعو / حامل إقامة رقم -->
        <div class="pledge-line">
            <span class="label">نعم أنا المدعو /</span>
            <span class="fill-line" style="min-width: 180px;">
                {{ $commitment->patient->name_ar ?? $commitment->patient->name ?? '' }}
            </span>
            <span class="label" style="margin-right: 10px;">حامل إقامة رقم /</span>
            <span class="fill-line" style="min-width: 140px;">
                {{ $commitment->patient->identity_value ?? '' }}
            </span>
        </div>

        <!-- Line 2: مصدرها -->
        <div class="pledge-line">
            <span class="label">مصدرها /</span>
            <span class="fill-line" style="min-width: 300px;">{{ $commitment->patient->country_of_origin ?? '' }}</span>
        </div>

        <!-- Pledge paragraph -->
        <div class="pledge-text-paragraph">
            أتعهد بأنني سوف أقوم بدفع كافة المصاريف العلاجية الإضافية التي تكون خارج نطاق التغطية التأمينية وعلى هذا يتم التوقيع .
        </div>

        <!-- Closing dua -->
        <div class="pledge-closing">
            والله الموفق ،،،،
        </div>
    </div>

    <!-- ============================================================
         ACKNOWLEDGMENT TABLE: المقر بما فيه
    ============================================================ -->
    <div class="ack-section">
        <table class="ack-table">
            <thead>
                <tr>
                    <th colspan="2">المقر بما فيه</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>الأسم :</td>
                    <td>
                        <span class="fill-dots">{{ $commitment->patient->name_ar ?? $commitment->patient->name ?? '' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>الجنسية :</td>
                    <td>
                        <span class="fill-dots">{{ $commitment->patient->country_of_origin ?? '' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>رقم الإقامة :</td>
                    <td>
                        <span class="fill-dots">{{ $commitment->patient->identity_value ?? '' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>التوقيع :-</td>
                    <td>
                        <span class="fill-dots">&nbsp;</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- File reference -->
        <div class="file-ref">ص/ ملف المريض بالقسم .</div>
    </div>

    <!-- ============================================================
         EMPLOYEE SECTION: الموظف المختص
    ============================================================ -->
    <div class="employee-section">
        <div class="employee-label">الموظف المختص :-</div>

        <div class="employee-signature-area">
            @if(isset($commitment->createdByUser) && $commitment->createdByUser)
                <div class="sig-block">
                    <div class="sig-title">اسم الموظف</div>
                    <div class="sig-name">{{ $commitment->createdByUser->name }}</div>
                </div>
            @endif
            <div class="sig-block">
                <div class="sig-title">التوقيع</div>
                <div class="sig-name">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         DECORATIVE BACKGROUND WAVES (matching the image)
    ============================================================ -->
    <div class="bg-waves">
        <svg viewBox="0 0 1200 220" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <!-- Back wave - lighter -->
            <path d="M0,120 C200,200 400,20 600,100 C800,180 1000,30 1200,110 L1200,220 L0,220 Z"
                  fill="#e8e8e8" opacity="0.5"/>
            <!-- Front wave - slightly darker -->
            <path d="M0,160 C150,100 350,200 550,140 C750,80 950,180 1200,150 L1200,220 L0,220 Z"
                  fill="#d0d0d0" opacity="0.4"/>
        </svg>
    </div>

    <!-- ============================================================
         PAGE FOOTER
    ============================================================ -->
    <div class="page-footer-bar">
        <span>
            📷 @{{ App\Models\Setting::get('social_handle', 'AljoufCluster') }}
        </span>
        <span>
            التاريخ: {{ \Carbon\Carbon::parse($commitment->commitment_date)->format('Y/m/d') }}
        </span>
    </div>

</body>
</html>
