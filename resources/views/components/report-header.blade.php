@php
    use App\Models\Setting;

    $countryAr = Setting::get('report_header_country_ar', 'المملكة العربية السعودية');
    $ministryAr = Setting::get('report_header_ministry_ar', 'وزارة الصحة');
    $clusterAr = Setting::get('health_cluster_name', 'تجمع الجوف الصحي');
    $hospitalNameAr = Setting::get('hospital_name', 'مستشفى الملك عبدالعزيز التخصصي');
    $hospitalNameEn = Setting::get('hospital_name_en', 'King Abdul-Aziz Specialist Hospital');
    $clusterNameEn = Setting::get('health_cluster_name_en', 'Aljouf Health Cluster');
    $logo = Setting::get('logo');
@endphp

<style>
    .report-header-wrap {
        margin-top: 24px;
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #1a1a1a;
        padding: 22px 36px 18px 36px;
        margin-bottom: 12px;
        font-family: 'Cairo', 'Tajawal', 'Segoe UI', sans-serif;
    }
    .report-header {
        display: flex;
        flex-direction: row-reverse;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
        padding: 0;
        border: none;
        margin: 0;
        font-family: inherit;
    }
    .report-header-text {
        flex: 1;
        text-align: center;
        line-height: 1.5;
        color: #0f172a;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    }
    .report-header-text .line-ar,
    .report-header-text .line-hospital-ar,
    .report-header-text .line-hospital-en {
        width: 100%;
        text-align: center;
    }
    .report-header-text .line-ar {
        font-size: 12px;
        font-weight: 700;
    }
    .report-header-text .line-hospital-ar {
        font-size: 14px;
        font-weight: 800;
    }
    .report-header-text .line-hospital-en {
        font-size: 11px;
        font-weight: 600;
        margin-top: 2px;
    }
    .report-header-logo {
        text-align: right;
        min-width: 120px;
    }
    .report-header-logo img {
        max-height: 52px;
        max-width: 120px;
        object-fit: contain;
        display: block;
        margin-bottom: 4px;
        margin-inline-end: 0;
        margin-inline-start: auto;
    }
    .report-header-logo .logo-placeholder {
        width: 80px;
        height: 40px;
        border-radius: 8px;
        border: 1px dashed #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #64748b;
        margin-bottom: 4px;
        margin-inline-start: auto;
        margin-inline-end: 0;
    }
    .report-header-logo .cluster-en {
        font-size: 11px;
        font-weight: 600;
        color: #0f766e;
    }
    @media print {
        .report-header-wrap {
            margin-top: 12px;
            margin-bottom: 12px;
            padding: 18px 28px 16px 28px;
            margin-left: 0;
            margin-right: 0;
            width: 100%;
            box-sizing: border-box;
            break-inside: avoid;
        }
    }
</style>

<div class="report-header-wrap">
<div class="report-header">
    <div class="report-header-text">
        <div class="line-ar">{{ $countryAr }}</div>
        <div class="line-ar">{{ $ministryAr }}</div>
        <div class="line-ar">{{ $clusterAr }}</div>
        <div class="line-hospital-ar">{{ $hospitalNameAr }}</div>
        <div class="line-hospital-en">{{ $hospitalNameEn }}</div>
    </div>
    <div class="report-header-logo">
        @if($logo)
            <img src="{{ asset('storage/' . $logo) }}" alt="Logo">
        @else
            <div class="logo-placeholder">Logo</div>
        @endif
        <div class="cluster-en">{{ $clusterNameEn }}</div>
    </div>
</div>
</div>

