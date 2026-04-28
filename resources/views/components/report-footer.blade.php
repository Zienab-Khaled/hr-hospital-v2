<style>
    .report-footer-wrap {
        margin-top: 30px;
        width: 100%;
        border-top: 2px solid #1e3a8a; /* Blue border */
        padding-top: 15px;
        font-family: 'Cairo', 'Tajawal', sans-serif;
    }
    .report-footer-content {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        gap: 5px;
    }
    .report-footer-dept {
        color: #1e40af; /* Formal Blue */
        font-size: 16px;
        font-weight: 900;
        letter-spacing: 0.5px;
    }
    .report-footer-hospital {
        color: #64748b;
        font-size: 11px;
        font-weight: bold;
    }
    @media print {
        .report-footer-wrap {
            position: relative;
            bottom: 0;
            break-inside: avoid;
        }
    }
</style>

<div class="report-footer-wrap">
    <div class="report-footer-content">
        <div class="report-footer-dept">
            {{ app()->getLocale() === 'ar' ? 'إدارة تنمية الإيرادات' : 'Revenue Development Department' }}
        </div>
        <div class="report-footer-hospital">
            {{ \App\Models\Setting::get('hospital_name', 'مستشفى الملك عبدالعزيز التخصصي بالجوف') }}
        </div>
    </div>
</div>
