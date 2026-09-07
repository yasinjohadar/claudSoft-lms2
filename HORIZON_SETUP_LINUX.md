# تركيب Laravel Horizon على Linux (بديل queue:work)

Horizon يوفّر لوحة تحكم حيّة لمتابعة الطابور (المهام قيد التنفيذ الآن، الإنتاجية، الفاشلة مع إعادة المحاولة بضغطة زر...)، لكنه يعمل **حصراً مع طابور Redis** — ليس مع `database` المستخدم حالياً. اتبع الخطوات بالترتيب، فهناك خطوة تفريغ للطابور القديم **يجب** تنفيذها قبل التبديل حتى لا تُفقد أي مهمة عالقة (مثل دفعة توليد توثيق قيد التنفيذ).

---

## 1. تثبيت Redis على السيرفر

```bash
# Ubuntu / Debian
sudo apt update
sudo apt install redis-server

# تفعيل وتشغيل الخدمة
sudo systemctl enable redis-server
sudo systemctl start redis-server

# تحقق أنه يعمل
redis-cli ping   # يجب أن يطبع PONG
```

اختياري (أداء أفضل من `predis`): تثبيت إضافة PHP الأصلية بدل الاكتفاء بحزمة predis عبر Composer:

```bash
sudo apt install php-redis   # عدّل رقم إصدار PHP إن احتجت: php8.3-redis مثلاً
sudo systemctl restart php8.3-fpm   # أو اسم خدمة PHP-FPM لديك
```

إن ثبّتّ الإضافة، اضبط في `.env`: `REDIS_CLIENT=phpredis` (بدل `predis`).

---

## 2. تفريغ طابور `database` الحالي أولاً (مهم جداً)

**لا تُبدّل `QUEUE_CONNECTION` قبل التأكد من عدم وجود مهام عالقة في جدول `jobs`** — أي مهمة موجودة هناك (مثل دفعة مواضيع توثيق قيد المعالجة) ستبقى محفوظة في قاعدة البيانات لكن **لن يُعالجها أحد بعد التبديل** لأن الطابور الجديد (Redis) منفصل تماماً عنها.

- انتظر حتى تنتهي أي دفعة/مهمة تعرف أنها قيد التنفيذ حالياً، **أو**
- شغّل العامل الحالي مرة واحدة حتى يُفرِغ الجدول:
  ```bash
  php artisan queue:work --queue=default,whatsapp,webhooks --stop-when-empty
  ```
- تحقق أن الجدول فارغ فعلاً:
  ```bash
  php artisan tinker --execute="echo DB::table('jobs')->count();"   # يجب أن يطبع 0
  ```

---

## 3. تحديث الكود وتثبيت الحزم

```bash
cd /var/www/html   # عدّل حسب مسار مشروعك
git pull            # أو أي طريقة نشر تستخدمها
composer install --no-dev --optimize-autoloader
```

`composer.json`/`composer.lock` يحتويان الآن `laravel/horizon` و `predis/predis` — لا حاجة لأي علم `--ignore-platform-req` هنا لأن Linux يملك `ext-pcntl`/`ext-posix` أصلاً (بعكس Windows).

---

## 4. تحديث `.env`

```
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis        # أو phpredis إن ثبّتّ الإضافة في الخطوة 1
REDIS_HOST=127.0.0.1       # أو مضيف Redis الفعلي إن كان على خادم منفصل
REDIS_PORT=6379
REDIS_PASSWORD=null        # أو كلمة المرور إن ضبطتها في Redis
REDIS_QUEUE_RETRY_AFTER=3700
```

**`REDIS_QUEUE_RETRY_AFTER` إلزامي** — طابور `redis` افتراضياً يعتبر أي مهمة "ضائعة" بعد 90 ثانية فقط ويُعيد إتاحتها لعامل آخر. مهام توليد التوثيق بالذكاء الاصطناعي تستغرق حتى 3600 ثانية، فبدون رفع هذه القيمة ستُعالَج نفس المهمة مرتين في آنٍ واحد (تكرار استدعاء AI، احتمال حفظ الصفحة مرتين). القيمة يجب أن تبقى أعلى من أطول `timeout` لأي مهمة.

ثم:

```bash
php artisan config:clear
php artisan config:cache   # إن كان مستخدماً في النشر
```

---

## 5. استبدال Supervisor: queue:work → horizon

```bash
# أوقف عامل queue:work القديم
sudo supervisorctl stop laravel-queue-worker
sudo rm /etc/supervisor/conf.d/laravel-queue-worker.conf   # أو أبقه معطّلاً كنسخة احتياطية

# انسخ إعداد Horizon الجديد
sudo cp /var/www/html/deploy/supervisor-horizon.conf.example /etc/supervisor/conf.d/laravel-horizon.conf
sudo nano /etc/supervisor/conf.d/laravel-horizon.conf   # عدّل command/user/stdout_logfile حسب مشروعك

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-horizon
```

---

## 6. التحقق

```bash
sudo supervisorctl status laravel-horizon:*
```

ثم افتح `https://your-domain.com/horizon` (بعد تسجيل الدخول بحساب له دور `admin` — الوصول مقيّد بنفس نظام الصلاحيات المستخدم في لوحة التحكم). يجب أن تظهر لوحة Horizon مع العمّال النشطين (`Supervisors`).

اختبر ميزة حقيقية تعتمد على الطابور (مثلاً "توليد دفعة مواضيع" في التوثيق) وراقبها تظهر حيّة في `/horizon` من قيد الانتظار إلى الاكتمال، للتأكد أن التحويل تم دون كسر أي شيء.

---

## أوامر مفيدة

```bash
sudo supervisorctl status laravel-horizon:*
sudo supervisorctl stop laravel-horizon
sudo supervisorctl start laravel-horizon
sudo supervisorctl restart laravel-horizon   # مثلاً بعد تحديث الكود
```

## ملاحظة

زر "تشغيل/إيقاف عامل الطابور" في إعدادات واتساب بلوحة التحكم يبحث عن عملية باسم `queue:work` تحديداً، ولن يكتشف عملية `horizon` إطلاقاً. بعد إتمام هذا التحويل، تجاهل ذلك الزر — Supervisor هو من يدير Horizon الآن.
