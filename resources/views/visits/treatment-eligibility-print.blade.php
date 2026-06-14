<!DOCTYPE html>
<html lang="ar-SA-u-nu-latn" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>أحقية العلاج - {{ $visit->patient->name }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }

        .print-container {
            width: 210mm;
            max-width: 100%;
            margin: 16px auto;
            background: white;
            padding: 0 16px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .title-banner {
            background: #eff6ff;
            color: #1e40af;
            padding: 8px 0;
            border-radius: 4px;
            font-size: 18px;
            font-weight: 800;
            text-align: center;
            margin: 8px 0 0;
            border: 1px solid #bfdbfe;
        }

        .content-body {
            padding: 12px 4px 0;
        }

        .info-row {
            display: flex;
            flex-direction: row;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 6px;
        }

        .info-label {
            flex-shrink: 0;
            width: 115px;
            font-weight: 700;
            color: #475569;
            font-size: 14px;
        }

        .info-value {
            flex: 1;
            min-width: 0;
            color: #1e293b;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
        }

        .status-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
            padding: 10px 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            width: fit-content;
            max-width: 100%;
        }

        .check-icon {
            width: 22px;
            height: 22px;
            background: #22c55e;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 13px;
            flex-shrink: 0;
        }

        .status-text {
            font-weight: 800;
            color: #166534;
            font-size: 15px;
            line-height: 1.3;
        }

        .signatures {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .sig-box {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            min-width: 140px;
        }

        .sig-label {
            font-size: 12px;
            color: #64748b;
            margin: 0;
            font-weight: 600;
        }

        .sig-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 13px;
        }

        .sig-img {
            max-width: 100px;
            max-height: 40px;
            margin-top: 2px;
            flex-basis: 100%;
            height: auto;
        }

        .timestamp {
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #f1f5f9;
        }

        .report-footer-wrap {
            margin-top: 10px !important;
            padding-top: 8px !important;
        }

        .no-print-actions {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            border: none;
        }

        .btn-primary { background: #3b82f6; color: white; }
        .btn-secondary { background: #64748b; color: white; }

        @media print {
            body {
                background: white !important;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-container {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 10mm 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0;
                page-break-inside: avoid;
            }

            .no-print-actions { display: none !important; }

            .report-header-wrap {
                margin-top: 0 !important;
                margin-bottom: 6px !important;
                padding: 10px 12px 8px !important;
            }

            .report-header-text .line-ar { font-size: 13px !important; }
            .report-header-text .line-hospital-ar { font-size: 17px !important; margin-top: 3px !important; }
            .report-header-text .line-hospital-en { font-size: 12px !important; margin-top: 3px !important; }
            .report-header-logo img { max-height: 42px !important; }

            .title-banner {
                font-size: 16px;
                padding: 6px 0;
                margin-top: 4px;
            }

            .content-body { padding: 8px 0 0; }

            .info-row { margin-bottom: 7px; padding-bottom: 4px; }
            .info-label, .info-value { font-size: 13px; }

            .status-section { margin-top: 8px; padding: 8px 10px; }
            .status-text { font-size: 14px; }

            .signatures { margin-top: 14px; gap: 8px; }
            .sig-img { max-height: 36px; max-width: 90px; }

            .timestamp { margin-top: 8px; padding-top: 6px; }

            .report-footer-wrap {
                margin-top: 8px !important;
                padding-top: 6px !important;
            }
            .report-footer-dept { font-size: 13px !important; }
            .report-footer-hospital { font-size: 10px !important; }

            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
        }
    </style>
</head>

<body>

    <div class="no-print-actions">
        <button onclick="window.print()" class="btn btn-primary">طباعة النموذج</button>
        <a href="{{ route('visits.create', ['patient_id' => $visit->patient_id, 'visit_id' => $visit->id, 'registered' => 1]) }}"
            class="btn btn-secondary">العودة</a>
    </div>

    <div class="print-container">
        @include('components.report-header')

        <div class="title-banner">أحقية العلاج</div>

        <div class="content-body">
            <div class="info-row">
                <div class="info-label">المريض :</div>
                <div class="info-value">{{ $visit->patient->fullArabicName() }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">رقم الملف :</div>
                <div class="info-value">{{ $visit->patient->file_number }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">الخدمة :</div>
                <div class="info-value">
                    @forelse($services as $s)
                        {{ $s['name_ar'] ?? $s['name'] ?? '—' }}{{ !$loop->last ? ' ، ' : '' }}
                    @empty
                        @if (!empty($eligibilityNotes))
                            {{ $eligibilityNotes }}
                        @else
                            {{ isset($targetDepartment) ? $targetDepartment->name_ar ?? $targetDepartment->name : ($visit->department->name_ar ?? $visit->department->name ?? '—') }}
                        @endif
                    @endforelse
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">نوع الخدمة :</div>
                <div class="info-value">
                    @if ($visit->case_type == 'emergency')
                        طوارئ
                    @elseif($visit->patient->payment_type == 'insurance')
                        تأمين
                        ({{ $visit->patient->insuranceCompany->name_ar ?? ($visit->patient->insuranceCompany->name ?? '—') }})
                    @elseif($visit->patient->payment_type == 'charity')
                        جمعية خيرية
                        ({{ $visit->patient->charityEntity->name_ar ?? ($visit->patient->charityEntity->name ?? '—') }})
                    @elseif($visit->patient->payment_type == 'treatment_eligibility')
                        {{ $visit->patient->payment_type_label }}
                    @else
                        شخصي (نقدي)
                    @endif
                </div>
            </div>

            <div class="status-section">
                <div class="check-icon">✓</div>
                <div class="status-text">أهلية لدخول {{ $targetDepartmentName ?? 'المستشفى' }}</div>
            </div>

            @if (!empty($eligibilityNotes) && !empty($services))
                <div class="info-row" style="margin-top: 10px;">
                    <div class="info-label">ملاحظة الأحقية :</div>
                    <div class="info-value">{{ $eligibilityNotes }}</div>
                </div>
            @endif

            <div class="signatures">
                <div class="sig-box">
                    <div class="sig-label">الموظف :</div>
                    <div class="sig-name">{{ auth()->user()->name ?? '—' }}</div>
                    @if (auth()->check() && auth()->user()->signature)
                        <img src="{{ asset('storage/' . ltrim(auth()->user()->signature ?? '', '/')) }}" class="sig-img" alt="Signature">
                    @endif
                </div>

                <div class="sig-box">
                    <div class="sig-label">مدير الايرادات :</div>
                    <div class="sig-name">{{ $manager->name ?? 'ناصر احمد الضويحي' }}</div>
                    @if ($manager && $manager->signature)
                        <img src="{{ asset('storage/' . ltrim($manager->signature ?? '', '/')) }}" class="sig-img" alt="Manager Signature">
                    @endif
                </div>
            </div>

            <div class="timestamp">
                {{ app()->getLocale() === 'ar' ? 'مساءً' : 'PM' }} {{ date('h:i') }} | {{ date('d-m-Y') }}
            </div>
        </div>

        @include('components.report-footer')
    </div>

</body>

</html>
