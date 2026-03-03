# رفع الموقع باستخدام Docker على السيرفر

## 1) تجهيز السيرفر (مرة واحدة)

### تثبيت Docker و Docker Compose

اتصلي بالسيرفر عبر SSH ثم نفذي (أمثلة لـ Ubuntu/Debian):

```bash
# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت Docker
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER
# ثم اخرجي وأعيدي الدخول بالـ SSH حتى تصبح صلاحية docker فعالة

# تثبيت Docker Compose (إن لم يكن مرفقاً)
sudo apt install -y docker-compose-plugin
```

---

## 2) رفع المشروع على السيرفر

### الطريقة أ: باستخدام Git (مفضّلة)

على السيرفر:

```bash
cd /var/www   # أو أي مجلد تفضلينه
sudo git clone https://github.com/YOUR_USER/hr-hospital-v2.git
cd hr-hospital-v2
```

(استبدلي رابط الريبو برابط مشروعك، وتأكدي أن الريبو خاص أو أن السيرفر له صلاحية الوصول.)

### الطريقة ب: رفع الملفات (FTP/SFTP)

- ارفعي كل المشروع (نفس المجلدات والملفات) إلى مجلد على السيرفر، مثلاً `/var/www/hr-hospital-v2`.
- لا ترفعي: `node_modules`, `vendor`, `.env` (ستُنشئين `.env` على السيرفر يدوياً).

---

## 3) إعداد ملف البيئة `.env` على السيرفر

```bash
cd /var/www/hr-hospital-v2
cp .env.example .env
nano .env   # أو استخدمي vi
```

عدّلي كحد أدنى:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=hr_app_hospital
DB_USERNAME=hr_user
DB_PASSWORD=كلمة_مرور_قوية_للقاعدة
```

احفظي الملف واخرجي من المحرر.

---

## 4) تشغيل الحاويات (للمرة الأولى)

### تشغيل عادي (منفذ 8080)

```bash
cd /var/www/hr-hospital-v2
docker compose up -d --build
```

### تشغيل كإنتاج (منفذ 80 + عامل الطوابير)

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

انتظري حتى تكتمل عملية البناء وتصبح حاوية MySQL جاهزة (حوالي دقيقتين أول مرة).

---

## 5) أوامر Laravel داخل الحاوية (مرة واحدة بعد التشغيل)

```bash
# مفتاح التطبيق
docker compose exec app php artisan key:generate

# الهجرات والبيانات الأولية
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force

# روابط التخزين و Passport
docker compose exec app php artisan storage:link
docker compose exec app php artisan passport:install

# كاش الإنتاج
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

إذا استخدمتِ `docker-compose.prod.yml` فاستخدمي نفس الأمر مع الملفين:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec app php artisan key:generate
# ... وباقي الأوامر نفسها مع exec app
```

---

## 6) الوصول للموقع

- إذا شغّلتِ **بدون** `docker-compose.prod.yml`:  
  `http://IP-SERVER:8080`
- إذا شغّلتِ **مع** `docker-compose.prod.yml`:  
  `http://IP-SERVER` (منفذ 80)

ربطي الدومين بعنوان الـ IP في لوحة تحكم الاستضافة/الدومين.

---

## 7) تفعيل HTTPS (SSL)

على السيرفر نفسه يمكنك استخدام **Caddy** أو **Nginx** كبروكسي عكسي فوق Docker:

- يستمع على 443 ويستقبل الدومين.
- يوجّه الطلبات إلى `http://127.0.0.1:80` (أو `:8080` إن لم تستخدمي prod).
- Caddy يوفّر شهادة Let's Encrypt تلقائياً.

مثال بسيط لـ Caddy (على السيرفر، خارج Docker):

```bash
sudo apt install -y caddy
sudo nano /etc/caddy/Caddyfile
```

محتوى مثال:

```
your-domain.com {
    reverse_proxy localhost:80
}
```

ثم:

```bash
sudo systemctl reload caddy
```

غيّري `APP_URL` في `.env` إلى `https://your-domain.com`.

---

## 8) تحديث الموقع بعد تعديل الكود

إذا استخدمتِ Git على السيرفر:

```bash
cd /var/www/hr-hospital-v2
git pull
docker compose build --no-cache app
docker compose up -d
# أو مع prod:
# docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## ملخص الأوامر (إنتاج)

| المطلوب | الأمر |
|---------|--------|
| تشغيل (إنتاج) | `docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build` |
| إيقاف | `docker compose -f docker-compose.yml -f docker-compose.prod.yml down` |
| تنفيذ أمر Artisan | `docker compose -f docker-compose.yml -f docker-compose.prod.yml exec app php artisan ...` |
| السجلات | `docker compose logs -f app` |

بعد اتباع هذه الخطوات يكون الرفع بالكامل باستخدام الـ Docker اللي عندك.
