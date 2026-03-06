<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'عرض السعر / الفاتورة' : 'Price offer / Invoice' }}</title>
    <style>
        body { margin: 0; padding: 0; background: #fff; font-family: 'Traditional Arabic', 'Arial', Tahoma, sans-serif; font-size: 14px; line-height: 1.5; color: #000; direction: rtl; text-align: right; }
        .doc { max-width: 700px; margin: 0 auto; padding: 20px; }
        .header-wrap { width: 100%; overflow: hidden; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #333; }
        .header-right { float: right; width: 42%; text-align: center; }
        .header-right img { max-height: 70px; max-width: 160px; display: block; margin: 0 auto 6px; }
        .header-left { float: left; width: 54%; text-align: right; }
        .org-line { margin: 2px 0; font-size: 14px; font-weight: bold; color: #000; }
        .org-line-en { font-size: 12px; color: #333; margin: 2px 0; }
        .date-row { clear: both; margin: 14px 0; overflow: hidden; }
        .date-row .date { font-weight: bold; font-size: 14px; float: right; }
        .date-row .without { font-size: 12px; color: #555; float: left; }
        .to-block { margin: 16px 0 12px; font-weight: bold; font-size: 14px; }
        .body-text { margin: 14px 0; font-size: 13px; line-height: 1.8; text-align: right; }
        .table-caption { font-weight: bold; font-size: 13px; margin: 16px 0 8px; text-decoration: underline; text-align: right; }
        table.price-table { width: 100%; border-collapse: collapse; margin: 10px 0 16px; font-size: 12px; direction: rtl; }
        table.price-table th, table.price-table td { border: 1px solid #999; padding: 8px 10px; text-align: right; }
        table.price-table th { background: #e8e8e8; font-weight: bold; }
        table.price-table .total-row { font-weight: bold; background: #f0f0f0; }
        .closing { margin-top: 20px; font-size: 13px; }
        .sig-block { margin-top: 16px; text-align: center; font-size: 13px; }
        .sig-block .title { font-weight: bold; margin: 4px 0; }
        .sig-block .hospital { margin: 2px 0; }
        .sig-block .name { font-weight: bold; margin-top: 6px; }
        .action-bar { margin-top: 28px; padding-top: 20px; border-top: 2px solid #e2e8f0; text-align: center; }
        .btn { display: inline-block; padding: 12px 24px; margin: 8px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; }
        .btn-confirm { background: #16a34a; color: #fff; }
        .btn-reject { background: #dc2626; color: #fff; }
        .action-note { font-size: 12px; color: #64748b; margin-top: 12px; }
    </style>
</head>
<body>
<div class="doc">
    {{-- رأس رسمي كما في المستند: يسار = الجهة، يمين = الشعار --}}
    <div class="header-wrap">
        <div class="header-right">
            @php
                $logo = \App\Models\Setting::get('logo');
                $clusterEn = \App\Models\Setting::get('health_cluster_name_en', 'Aljouf Health Cluster');
                $clusterAr = \App\Models\Setting::get('health_cluster_name', 'تجمع الجوف الصحي');
            @endphp
            @if($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo))
                <img src="{{ asset('storage/' . $logo) }}" alt="شعار">
            @endif
            <div class="org-line">{{ $clusterAr }}</div>
            <div class="org-line-en">{{ $clusterEn }}</div>
        </div>
        <div class="header-left">
            <div class="org-line">المملكة العربية السعودية</div>
            <div class="org-line">وزارة الصحة</div>
            <div class="org-line">تجمع الجوف الصحي</div>
            <div class="org-line">مستشفى الملك عبد العزيز التخصصي</div>
            <div class="org-line-en">King Abdul-Aziz Specialist</div>
        </div>
    </div>

    <div class="date-row">
        <span class="without">بدون</span>
        <span class="date">10/08/2025</span>
    </div>

    <div class="to-block">
        سعادة / مدير جمعية حياة لسرطان الثدي بمنطقة الحدود الشمالية<br>
        المحترمين
    </div>

    <div class="body-text">
        السلام عليكم ورحمة الله وبركاته،<br>
        تجدون أدناه عرض سعر للخدمات العلاجية المطلوبة للمريضة / نور عمران أبو حسين رقم الجواز (3946845801) ونفيد سعادتكم بانه تم ارفاق التقرير الطبي وفي حال السداد نأمل تحويل المبلغ على الحساب في البنك الاهلي (20165627001703) رقم الأيبان SA2410000020165627001703
    </div>

    <div class="table-caption">عرض السعر حسب تسعيرة وزارة الصحة</div>

    <table class="price-table">
        <thead>
            <tr>
                <th>الكود</th>
                <th>الخدمة المقدمة</th>
                <th>العدد</th>
                <th>السعر الأفرادي</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>240149</td><td>CT - CHEST WITH CONTRAST</td><td>1</td><td>1500</td><td>1500</td></tr>
            <tr><td>240134</td><td>CT - ABDOMEN WITH CONTRAST</td><td>1</td><td>1400</td><td>1400</td></tr>
            <tr><td>240183</td><td>CT - PELVIS WITH CONTRAST</td><td>1</td><td>1400</td><td>1400</td></tr>
            <tr><td>240066</td><td>BONE SPECT</td><td>1</td><td>880</td><td>880</td></tr>
            <tr><td>18-475-14</td><td>Letrozole 2.5(Femara)</td><td>6</td><td>183.55</td><td>1101.3</td></tr>
            <tr><td>42-7-93</td><td>Zoladex(goserelin)3.6mg</td><td>6</td><td>307.65</td><td>1845.9</td></tr>
            <tr><td>1111211309</td><td>Palbociclib (ibrance)</td><td>6</td><td>8551</td><td>51306</td></tr>
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">الاجمالي</td>
                <td></td><td></td>
                <td style="text-align: right;">59433.2 ريال</td>
            </tr>
        </tbody>
    </table>

    <div class="closing">وتقبلوا تحياتي</div>
    <div class="sig-block">
        <div class="title">مدير إدارة تنميه الإيرادات</div>
        <div class="hospital">بمستشفى الملك عبد العزيز التخصصي بالجوف</div>
        <div class="name">جسار محمد الضويحي</div>
    </div>

    {{-- أزرار الرد (تأكيد/رفض) للعمل الفعلي للإيميل --}}
    <div class="action-bar">
        <p style="font-weight: bold; margin-bottom: 10px;">{{ app()->getLocale() === 'ar' ? 'للرد على هذه المطالبة:' : 'To respond to this claim:' }}</p>
        <a href="{{ $confirmUrl }}" class="btn btn-confirm">{{ app()->getLocale() === 'ar' ? 'أؤكد الالتزام بالدفع' : 'Confirm payment commitment' }}</a>
        <a href="{{ $rejectUrl }}" class="btn btn-reject">{{ app()->getLocale() === 'ar' ? 'رفض' : 'Reject' }}</a>
        <p class="action-note">{{ app()->getLocale() === 'ar' ? 'في كلا الحالتين سيُطلب منكم إدخال موافقة خطية أو سبب الرفض.' : 'In both cases you will be asked to provide written approval or rejection reason.' }}</p>
    </div>
</div>
</body>
</html>
