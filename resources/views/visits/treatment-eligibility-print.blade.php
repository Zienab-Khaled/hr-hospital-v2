<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>أحقية علاج - {{ $visit->patient->name }}</title>
    <style>
        @font-face {
            font-family: 'Cairo';
            src: url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        }
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .print-container {
            width: 500px;
            background: white;
            padding: 0;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 40px;
            margin-bottom: 40px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        /* Wave Backdrop */
        .wave-backdrop {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 400px;
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(219,234,254,0.4) 100%);
            z-index: 0;
            pointer-events: none;
        }
        .wave-svg {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            opacity: 0.3;
        }

        .header-card {
            background-color: #ffffff;
            padding: 20px;
            text-align: center;
            border-bottom: 2px dashed #cbd5e1;
            position: relative;
            z-index: 1;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .hospital-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 700;
            color: #1e293b;
            font-size: 14px;
        }
        .hospital-logo {
            width: 30px;
            height: 30px;
        }
        .title-banner {
            background: #eff6ff;
            color: #1e40af;
            padding: 10px 0;
            border-radius: 4px;
            font-size: 20px;
            font-weight: 800;
            margin: 10px 0;
            border: 1px solid #bfdbfe;
        }

        .content-body {
            padding: 30px;
            position: relative;
            z-index: 1;
        }
        .info-row {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 10px;
        }
        .info-label {
            width: 120px;
            font-weight: 700;
            color: #475569;
            font-size: 16px;
        }
        .info-value {
            flex-grow: 1;
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
        }

        .status-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 30px;
            padding: 15px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            width: fit-content;
        }
        .check-icon {
            width: 24px;
            height: 24px;
            background: #22c55e;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .status-text {
            font-weight: 800;
            color: #166534;
            font-size: 18px;
        }

        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            padding-bottom: 20px;
        }
        .sig-box {
            text-align: right;
        }
        .sig-label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .sig-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 15px;
        }
        .sig-img {
            max-width: 120px;
            max-height: 50px;
            margin-bottom: 5px;
        }

        .timestamp {
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            margin-top: 40px;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
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
            transition: all 0.2s;
            border: none;
        }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-secondary { background: #64748b; color: white; }
        .btn-secondary:hover { background: #475569; }

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .print-container {
                width: 100%;
                max-width: none;
                margin: 0;
                box-shadow: none;
                border: none;
                border-radius: 0;
            }
            .no-print-actions { display: none; }
            .wave-backdrop { opacity: 1; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print-actions">
        <button onclick="window.print()" class="btn btn-primary">طباعة النموذج</button>
        <a href="{{ route('visits.create', ['patient_id' => $visit->patient_id, 'visit_id' => $visit->id, 'registered' => 1]) }}" class="btn btn-secondary">العودة</a>
    </div>

    <div class="print-container">
        <!-- Wave Background -->
        <div class="wave-backdrop">
            <svg class="wave-svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
                <path fill="#3b82f6" fill-opacity="0.1" d="M0,192L48,197.3C96,203,192,213,288,192C384,171,480,117,576,112C672,107,768,149,864,165.3C960,181,1056,171,1152,149.3C1248,128,1344,96,1392,80L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>

        <div class="header-card">
            <div class="header-top">
                <div class="hospital-badge">
                    <svg class="hospital-logo" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="45" stroke="#3b82f6" stroke-width="5"/>
                        <path d="M50 25V75M25 50H75" stroke="#3b82f6" stroke-width="10" stroke-linecap="round"/>
                    </svg>
                    المستشفى
                </div>
                <div style="font-size: 12px; color: #94a3b8; font-weight: bold;">وحدة ERP / HIS</div>
            </div>

            <div class="title-banner">
                أحقية علاج
            </div>
        </div>

        <div class="content-body">
            <div class="info-row">
                <div class="info-label">المريض :</div>
                <div class="info-value">{{ $visit->patient->name_ar ?? $visit->patient->name }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">رقم الملف :</div>
                <div class="info-value">{{ $visit->patient->file_number }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">الخدمة :</div>
                <div class="info-value">
                    @forelse($services as $s)
                        {{ $s['name'] ?? '-' }}{{ !$loop->last ? ' ، ' : '' }}
                    @empty
                        {{ isset($targetDepartment) ? ($targetDepartment->name_ar ?? $targetDepartment->name) : ($visit->department->name_ar ?? $visit->department->name) }}
                    @endforelse
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">نوع الخدمة :</div>
                <div class="info-value">
                    @if($visit->case_type == 'emergency')
                        طوارئ
                    @elseif($visit->patient->payment_type == 'insurance')
                        تأمين ({{ $visit->patient->insuranceCompany->name_ar ?? $visit->patient->insuranceCompany->name ?? '—' }})
                    @elseif($visit->patient->payment_type == 'charity')
                        جمعية خيرية ({{ $visit->patient->charityEntity->name_ar ?? $visit->patient->charityEntity->name ?? '—' }})
                    @else
                        شخصي (نقدي)
                    @endif
                </div>
            </div>

            <div class="status-section">
                <div class="check-icon">✓</div>
                <div class="status-text">أهلية لدخول {{ $targetDepartmentName ?? 'المستشفى' }}</div>
            </div>

            <div class="signatures">
                <div class="sig-box">
                    <div class="sig-label">الموظف :</div>
                    @if (auth()->check() && auth()->user()->signature)
                        <img src="{{ asset('storage/' . auth()->user()->signature) }}" class="sig-img" alt="Signature">
                    @endif
                    <div class="sig-name">{{ auth()->user()->name }}</div>
                </div>

                <div class="sig-box" style="text-align: left;">
                    <div class="sig-label">مدير الايرادات :</div>
                    @if ($manager && $manager->signature)
                        <img src="{{ asset('storage/' . $manager->signature) }}" class="sig-img" alt="Manager Signature">
                    @endif
                    <div class="sig-name">{{ $manager->name ?? 'ناصر احمد الضويحي' }}</div>
                </div>
            </div>

            <div class="timestamp">
                {{ app()->getLocale() === 'ar' ? 'مساءً' : 'PM' }} {{ date('h:i') }} | {{ date('d-m-Y') }}
            </div>
        </div>
    </div>

</body>
</html>
