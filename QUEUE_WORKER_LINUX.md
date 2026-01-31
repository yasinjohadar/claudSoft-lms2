# تشغيل عامل الطابور (Queue Worker) أونلاين على Linux

عامل الطابور مطلوب للرد التلقائي والإرسال الجماعي. إذا شغّلته من زر "تشغيل" في الإعدادات أو من الطرفية يدوياً، قد **يتوقف بعد تحديث الصفحة أو إغلاق الطرفية**. على سيرفر Linux أونلاين، استخدم **Supervisor** ليشغّله بشكل دائم ويعيد تشغيله تلقائياً عند التوقف أو بعد إعادة تشغيل السيرفر.

---

## 1. تثبيت Supervisor على Linux

```bash
# Ubuntu / Debian
sudo apt update
sudo apt install supervisor

# تشغيل Supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

---

## 2. إعداد عامل الطابور

في المشروع يوجد ملف مثال للإعداد:

- **المسار:** `deploy/supervisor-queue-worker.conf.example`

انسخه إلى مجلد إعدادات Supervisor وعدّل المسارات والمستخدم:

```bash
# انسخ الملف (عدّل المسار حسب موقع مشروعك)
sudo cp /var/www/html/deploy/supervisor-queue-worker.conf.example /etc/supervisor/conf.d/laravel-queue-worker.conf

# عدّل الملف
sudo nano /etc/supervisor/conf.d/laravel-queue-worker.conf
```

**عدّل الأسطر التالية:**

| السطر | الوصف | مثال |
|--------|--------|------|
| `command` | مسار مشروعك + `artisan queue:work` | `php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600` |
| `user` | المستخدم الذي يشغّل ويب سيرفرك | `www-data` أو `nginx` أو اسم مستخدمك |
| `stdout_logfile` | مسار سجل الطابور داخل المشروع | `/var/www/html/storage/logs/queue-worker.log` |

احفظ الملف ثم نفّذ:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-queue-worker
```

---

## 3. أوامر مفيدة

```bash
# عرض الحالة
sudo supervisorctl status laravel-queue-worker

# إيقاف
sudo supervisorctl stop laravel-queue-worker

# تشغيل
sudo supervisorctl start laravel-queue-worker

# إعادة تشغيل (مثلاً بعد تحديث الكود)
sudo supervisorctl restart laravel-queue-worker
```

بعد إعداد Supervisor، عامل الطابور سيعمل أونلاين ولن يتوقف بعد التحديث أو إعادة تشغيل السيرفر.
