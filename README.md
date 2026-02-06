# نظام إدارة وتنمية الإيرادات بالمستشفى | Hospital Revenue Management System

نظام داخلي لإدارة المرضى، الفواتير، الموافقات، التحصيل، والتقارير.  
Internal system for patient registration, authorizations, invoicing, collection, and reports.

## المتطلبات | Requirements

- PHP 8.2+
- Composer
- MySQL / PostgreSQL / SQLite
- Node.js & NPM (for Vite assets)

## التثبيت | Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

تعديل `.env` لقاعدة البيانات (مثلاً SQLite للمطور):

```env
DB_CONNECTION=sqlite
# DB_DATABASE=absolute_path_to/database/database.sqlite
```

إنشاء الملف إن استخدمت SQLite:

```bash
# Windows (PowerShell)
New-Item -Path database\database.sqlite -ItemType File -Force
```

تشغيل الهجرات والبيانات الأولية:

```bash
php artisan migrate --force
php artisan db:seed --force
```

أول مستخدم دخول (يوزر أدمن):

- **Username:** `admin`
- **Password:** `password`

يُفضّل تغيير كلمة المرور بعد أول دخول.

## تشغيل المشروع | Run

```bash
npm install
npm run build
php artisan serve
```

ثم افتح: http://localhost:8000

للتطوير مع إعادة تحميل تلقائي للأصول:

```bash
npm run dev
```

في طرفية أخرى:

```bash
php artisan serve
```

## اللغات | Languages

- عربي (افتراضي) | Arabic (default)
- English

تبديل اللغة من الروابط في الهيدر أو إضافة `?lang=ar` أو `?lang=en` للرابط.

## الهيكل الحالي | Current Structure

- **Auth:** تسجيل دخول بـ Username + Password، ربط User بـ Employee، صلاحيات (Spatie).
- **الجداول:** Departments, Employees, Users, Patients, Insurance Companies, Charity Entities, Services, Visits, Authorizations, Admissions, Invoices, Payments, Insurance/Charity Claims, Attachments, Activity Log, Settings.
- **لوحة التحكم:** تبويبات (ملف المريض، الفواتير، الموافقات) وبداية واجهة الإجراءات الإدارية.

## الخطوات القادمة (مقترحة)

1. إكمال صفحات المرضى (إضافة/تعديل/عرض) وربط الخدمات والموافقات.
2. إعدادات المستشفى (لوجو، اسم، بيانات الطباعة في PDF).
3. إدارة الأقسام والخدمات من لوحة الأدمن.
4. الفوترة والتحصيل (كاش/تأمين/جمعية) وطباعة إيصال الدفع.
5. المطالبات وملاحظات رد التأمين/الجمعية.
6. Audit Log لتسجيل كل حركة.
7. التقارير وتصدير Excel/PDF ورفع التقارير الرسمية.

## الترخيص | License

MIT
