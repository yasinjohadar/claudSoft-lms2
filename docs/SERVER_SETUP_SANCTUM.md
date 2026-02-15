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

**إذا ظهر:** `Could not open input file: /home/.../composer.phar`  
معناه أن Composer غير مثبت بعد. نفّذ أوامر التثبيت أدناه أولاً (انسخها والصقها في الطرفية).

**الطريقة اليدوية — تثبيت Composer (بدون رفع أي ملف):** نفّذ على السيرفر (SSH):

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

في الحالتين (المسار 1 أو 2) تأكد أن الملفات التالية محدّثة على السيرفر:

- `config/auth.php` — فيه تعريف حارس `sanctum`.
- `app/Providers/AppServiceProvider.php` — فيه تسجيل الـ driver يدوياً.
- وجود مجلد `vendor/laravel/sanctum/` وبداخله ملف `src/Guard.php`.
- هذا الملف `docs/SERVER_SETUP_SANCTUM.md` للرجوع عند الحاجة.

---

## ملخص سريع

| الحالة | الإجراء |
|--------|---------|
| Composer غير موجود على السيرفر | المسار 1: تشغيل [install-composer-on-server.sh](install-composer-on-server.sh) ثم `php ~/composer.phar install --no-dev` |
| لا تريد تثبيت Composer على السيرفر | المسار 2: محلياً `composer install --no-dev` و `composer dump-autoload` ثم رفع مجلد `vendor` كاملاً |
| بعد أي مسار | `php artisan config:clear` و `cache:clear` ثم التحقق من Sanctum |

بعد تنفيذ هذه الخطوات، جرّب الطلب من التطبيق مرة أخرى.

---

## حل تعارض Git (merge) على السيرفر

---

### حل نهائي — لئلا يعود الخطأ عند كل سحب من cPanel

**إذا ظهر:** `fatal: empty ident name (for <user@host>) not allowed`  
معناه أن Git غير مضبوط (الاسم أو البريد). اضبط الهوية مرة واحدة ثم نفّذ بقية الأوامر:

```bash
cd /home/rootclaudsoftadi/public_html
git config user.email "you@example.com"
git config user.name "Server"
```

(غيّر `you@example.com` و `Server` إن أردت؛ يمكن استخدام أي بريد واسم.)

ثم نفّذ التسلسل التالي **مرة واحدة** من مجلد المشروع:

```bash
cd /home/rootclaudsoftadi/public_html
rm -f vendor.zip
git rm -r --cached vendor/
git commit -m "Stop tracking vendor"
git pull origin main
```

- إن كان الفرع عندك غير `main` استبدل `main` باسمه (مثلاً `master`).
- **إذا ظهر:** `fatal: Need to specify how to reconcile divergent branches` — نفّذ السحب مع الدمج صراحة:  
  `git pull origin main --no-rebase`  
  أو ضبط الدمج كافتراضي ثم السحب:  
  `git config pull.rebase false` ثم `git pull origin main`.
- بعد التنفيذ: مجلد `vendor` يبقى على القرص والموقع يعمل. في السحبات التالية (من cPanel أو SSH) لن يظهر الخطأ.

**إن كنت تعتمد على cPanel للسحب:** بعد تنفيذ الأوامر أعلاه من SSH، جرّب السحب مرة أخرى من cPanel للتأكد.

---

إذا ظهر خطأ مثل: **"Your local changes to the following files would be overwritten by merge"** — وغالباً يذكر ملفات مثل `vendor/composer/autoload_classmap.php`, `vendor/composer/autoload_files.php`, `vendor/composer/autoload_psr4.php`, `vendor/composer/autoload_static.php` أو غيرها داخل `vendor/` — أو **"The following untracked working tree files would be overwritten by merge: vendor.zip"** عند السحب أو الدمج من cPanel/SSH:

**نفس الحل ينطبق:** التخلّي عن التغييرات في كل مجلد `vendor` بالأمر أدناه ثم إعادة السحب/الدمج، ثم إعادة تثبيت الحزم فوراً.

**تحذير:** تنفيذ `git checkout -- vendor/` يستبدل أو يزيل مجلد `vendor` الحالي. **الموقع سيتوقف** حتى تعيد تثبيت الحزم (المسار 1 أو 2 أدناه). جهّز Composer على السيرفر أو مجلد `vendor` من جهازك قبل التنفيذ.

**بدون رفع أي ملف — انسخ الأوامر والصقها في الطرفية (أنت بالفعل في `public_html`):**
```bash
rm -f vendor.zip
git checkout -- vendor/
```
ثم أعد تنفيذ السحب/الدمج من cPanel أو نفّذ `git pull`. **وبعد نجاح الدمج مباشرةً ثبّت الحزم (الخطوة 4 أدناه) وإلا سيبقى الموقع متوقفاً.**

**إذا استمر نفس الخطأ بعد تنفيذ الأوامر أعلاه:** قد يكون Git لا يزال يتتبّع مجلد `vendor`. نفّذ التالي من مجلد المشروع (SSH) لإخراج `vendor` من التتبّع مع الإبقاء على الملفات على القرص؛ بعدها جرّب السحب/الدمج مرة أخرى:
```bash
cd /home/rootclaudsoftadi/public_html
rm -f vendor.zip
git rm -r --cached vendor/
git status
git pull
```
إن ظهرت رسالة تطلب رسالة commit (في بعض الإعدادات)، نفّذ: `git commit -m "Stop tracking vendor"` ثم `git pull`. بعد نجاح الـ pull الملفات داخل `vendor/` تبقى على القرص (غير متتبّعة) والموقع يعمل؛ لا حاجة لإعادة تثبيت الحزم إلا إذا حُذف مجلد `vendor` فعلياً.

**أو باستخدام السكربت:** ارفع [docs/fix-git-merge-on-server.sh](fix-git-merge-on-server.sh) إلى السيرفر داخل المشروع، ثم من مجلد المشروع: `bash docs/fix-git-merge-on-server.sh`.

**طريقة يدوية (تفصيل):**

1. **إزالة أو نقل `vendor.zip`** (حتى لا يُستبدل بالدمج):
   ```bash
   cd /home/rootclaudsoftadi/public_html   # أو مسار المشروع
   rm vendor.zip
   # أو: mv vendor.zip vendor.zip.bak
   ```

2. **التخلّي عن التغييرات المحلية في `vendor`** حتى يكتمل الدمج (مجلد `vendor` يجب ألا يُتتبّع بـ Git؛ بعد الدمج سيكون في `.gitignore`):
   ```bash
   git checkout -- vendor/
   ```

3. **إعادة تنفيذ السحب/الدمج** من واجهة cPanel أو:
   ```bash
   git pull
   ```

4. **بعد نجاح الدمج:** مجلد `vendor` لن يأتي من Git (لأنه مُدرج في `.gitignore`). ثبّت الحزم بأحد المسارين:
   - **المسار 1:** `php ~/composer.phar install --no-dev --optimize-autoloader`
   - **المسار 2:** رفع مجلد `vendor` كاملاً من جهازك بعد `composer install --no-dev` و `composer dump-autoload`

ثم نفّذ: `php artisan config:clear` و `php artisan cache:clear`.

---

## الموقع توقف بعد تنفيذ الأوامر (rm vendor.zip و git checkout -- vendor/)

هذا متوقّع: `git checkout -- vendor/` يزيل أو يستبدل مجلد `vendor` الذي كان يشغّل الموقع. لإعادة تشغيل الموقع:

1. **المسار 1 — تثبيت Composer على السيرفر ثم تثبيت الحزم:**  
   من الطرفية (بعد تثبيت Composer كما في بداية هذا الملف):
   ```bash
   cd /home/rootclaudsoftadi/public_html
   php ~/composer.phar install --no-dev --optimize-autoloader
   php artisan config:clear
   php artisan cache:clear
   ```

2. **المسار 2 — رفع مجلد vendor من جهازك:**  
   على جهازك من مجلد المشروع: `composer install --no-dev` ثم `composer dump-autoload`. ثم ارفع مجلد `vendor` بالكامل إلى السيرفر (استبدال المجلد الموجود). على السيرفر:
   ```bash
   cd /home/rootclaudsoftadi/public_html
   php artisan config:clear
   php artisan cache:clear
   ```

بعد تنفيذ أحد المسارين يعود الموقع للعمل.
