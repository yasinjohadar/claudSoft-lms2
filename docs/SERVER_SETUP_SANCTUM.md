# إعداد السيرفر لـ API الطالب (Sanctum)

إذا ظهر خطأ **"Auth driver [sanctum] for guard [sanctum] is not defined"** أو **`composer: command not found`** على السيرفر، استخدم أحد المسارين أدناه.

---

## عندما Composer غير مُثبت على السيرفر

على السيرفر الأمر `composer` غير موجود، لذلك لا يمكن تشغيل `composer install` أو `composer dump-autoload` هناك. لديك خياران:

---

### المسار 1: تثبيت Composer على السيرفر (بدون root)

يمكن تثبيت Composer باستخدام PHP فقط من مجلد المستخدم.

**الطريقة الآلية — سكربت جاهز:**

1. ارفع الملف [docs/install-composer-on-server.sh](install-composer-on-server.sh) إلى السيرفر (مثلاً في مجلد المشروع أو في `~/`).
2. على السيرفر نفّذ:
   ```bash
   bash install-composer-on-server.sh
   ```
   أو إذا رفعته داخل المشروع:
   ```bash
   cd /home/rootclaudsoftadi/public_html
   bash docs/install-composer-on-server.sh
   ```
3. بعد التثبيت، من مجلد التطبيق:
   ```bash
   cd /home/rootclaudsoftadi/public_html
   php ~/composer.phar install --no-dev --optimize-autoloader
   php artisan config:clear
   php artisan cache:clear
   ```

**الطريقة اليدوية:** نفّذ على السيرفر (SSH):

```bash
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

ثم من مجلد المشروع:

```bash
cd /home/rootclaudsoftadi/public_html
php ~/composer.phar install --no-dev --optimize-autoloader
php artisan config:clear
php artisan cache:clear
```

---

### المسار 2: الاعتماد على Composer محلياً ورفع vendor كامل

إذا لم ترد تثبيت Composer على السيرفر:

**على جهازك (مجلد المشروع):**

1. تحديث الـ autoload وتركيب الحزم للإنتاج:
   ```bash
   composer install --no-dev
   composer dump-autoload
   ```
2. رفع مجلد **`vendor` بالكامل** إلى السيرفر (استبدال المجلد الموجود).
   - المهم: أن يكون الهيكل على السيرفر: `public_html/vendor/` وبداخله `laravel/sanctum/` وملفات `composer/autoload_*.php` محدّثة.

**بعد الرفع على السيرفر:**

```bash
cd /home/rootclaudsoftadi/public_html
php artisan config:clear
php artisan cache:clear
```

ثم إعادة تشغيل PHP-FPM إن أمكن.

---

## مسح الكاش والتحقق (بعد أي مسار)

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

التحقق من وجود Sanctum:

```bash
php -r "var_dump(class_exists('Laravel\Sanctum\Guard'));"
```

المفترض أن يطبع `bool(true)`.

---

## إعادة تشغيل PHP (لمسح OPcache)

بعد رفع التعديلات، إن أمكن:

- إعادة تشغيل **PHP-FPM** أو **Apache** من لوحة التحكم أو:
  ```bash
  sudo systemctl restart php8.2-fpm
  ```

---

## التأكد من الملفات المرفوعة

تأكد أن هذه الملفات محدّثة على السيرفر:

- `config/auth.php` — فيه تعريف حارس `sanctum`.
- `app/Providers/AppServiceProvider.php` — فيه تسجيل الـ driver يدوياً.
- وجود مجلد `vendor/laravel/sanctum/` وبداخله ملف `src/Guard.php`.

---

## ملخص سريع

| الحالة | الإجراء |
|--------|---------|
| Composer غير موجود على السيرفر | المسار 1: تشغيل [install-composer-on-server.sh](install-composer-on-server.sh) ثم `php ~/composer.phar install --no-dev` |
| لا تريد تثبيت Composer على السيرفر | المسار 2: محلياً `composer install --no-dev` و `composer dump-autoload` ثم رفع مجلد `vendor` كاملاً |
| بعد أي مسار | `php artisan config:clear` و `cache:clear` ثم التحقق من Sanctum |

بعد تنفيذ هذه الخطوات، جرّب الطلب من التطبيق مرة أخرى.
