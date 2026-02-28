# تشغيل المشروع بـ Docker

## المتطلبات
- Docker و Docker Compose مثبتين على الجهاز.

## التشغيل السريع

```bash
# نسخ ملف البيئة
cp .env.example .env

# تعديل .env لقاعدة البيانات (أو اترك القيم الافتراضية):
# DB_DATABASE=hr_app_hospital
# DB_USERNAME=hr_user
# DB_PASSWORD=secret

# بناء وتشغيل الحاويات
docker compose up -d --build

# توليد مفتاح التطبيق وتشغيل الهجرات والبيانات الأولية
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force

# (اختياري) إنشاء روابط التخزين و Passport
docker compose exec app php artisan storage:link
docker compose exec app php artisan passport:install
```

## الوصول للتطبيق
- **التطبيق:** http://localhost:8080  
- **MySQL:** localhost:3306 (مستخدم: `hr_user` / كلمة المرور من `DB_PASSWORD` في `.env`)

## أوامر مفيدة

```bash
# إيقاف الحاويات
docker compose down

# عرض السجلات
docker compose logs -f app

# الدخول إلى حاوية التطبيق
docker compose exec app sh

# إعادة تشغيل الهجرات من الصفر (يحذف كل الجداول)
docker compose exec app php artisan migrate:fresh --seed
```

## الخدمات (docker-compose)
| الخدمة | الوصف |
|--------|--------|
| **app** | Laravel (PHP 8.2-FPM) |
| **web** | Nginx (منفذ 8080) |
| **mysql** | MySQL 8.0 (منفذ 3306) |
