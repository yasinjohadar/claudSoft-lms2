# دليل تشغيل النسخ الاحتياطي المجدولة

> بدون الخطوات في القسم الأول **لن تعمل أي نسخة احتياطية مجدولة إطلاقاً**.
> زر «تشغيل الآن» في لوحة الأدمن هو الشيء الوحيد الذي يعمل بدونها.

## كيف يعمل النظام

ثلاث طبقات، وتعطُّل أي واحدة يوقف النسخ بصمت:

| الطبقة | ما تفعله | كيف تتحقق |
|---|---|---|
| **المجدول** (`schedule:work` أو cron) | يستدعي `backup:run-scheduled` كل دقيقة | `supervisorctl status laravel-scheduler` |
| **الأمر** `backup:run-scheduled` | يقرأ `backup_schedules` وينشئ صف `Backup` ويدفعه للطابور | `php artisan backup:run-scheduled -v` |
| **عامل الطابور** (`queue:work`) | ينفّذ `CreateBackupJob` فعلياً | `supervisorctl status laravel-queue-worker` |

المجدول ينشئ الصفوف فقط. لو توقف عامل الطابور بقيت النسخ في حالة `pending` إلى الأبد.

## 1. تفعيل المجدول

### الخيار أ — supervisor (مفضّل)

```bash
sudo cp deploy/supervisor-scheduler.conf.example /etc/supervisor/conf.d/laravel-scheduler.conf
sudo cp deploy/supervisor-queue-worker.conf.example /etc/supervisor/conf.d/laravel-queue-worker.conf
# عدّل المسار والمستخدم داخل الملفين
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start laravel-scheduler laravel-queue-worker
sudo supervisorctl status
```

### الخيار ب — cron

```bash
crontab -e
```

أضف (استبدل المسار بمسار مشروعك):

```cron
* * * * * cd /home/rootclaudsoftadi/public_html && php artisan schedule:run >> /dev/null 2>&1
```

عامل الطابور يبقى ضرورياً حتى مع cron — استخدم ملف supervisor الخاص به.

> لا تفعّل cron و`schedule:work` معاً — سيتضاعف التنفيذ.

## 2. التحقق بعد التفعيل

```bash
php artisan schedule:list                 # يجب أن تظهر backup:run-scheduled كل دقيقة
php artisan backup:run-scheduled -v       # تشغيل يدوي للتأكد
php artisan tinker --execute="\$s = App\Models\BackupSchedule::first(); echo \$s->last_run_at.' | '.\$s->next_run_at;"
```

في لوحة الأدمن `/admin/backup-schedules`: عمود «آخر تشغيل» يجب أن يتحدّث، ولا يجب أن تظهر شارة «متأخرة» الحمراء.

## 3. الأعطال الشائعة

### النسخ تبقى في حالة `pending`
عامل الطابور متوقف. `supervisorctl start laravel-queue-worker`.

### النسخ تفشل بـ `MaxAttemptsExceededException`
أمر عامل الطابور ينقصه `--timeout`. الافتراضي 60 ثانية بينما مهمة النسخ تحتاج حتى 3600.
تأكد أن الأمر يحوي `--timeout=3600` (يجب أن يبقى أقل من `DB_QUEUE_RETRY_AFTER=3700`).

### نسخ عالقة في حالة `running`
`backups:mark-stuck-failed` يعمل كل ساعة ويعلّمها فاشلة بعد `BACKUP_STUCK_RUNNING_MINUTES` (120 افتراضياً)،
لكنه لا يعمل إن كان المجدول متوقفاً. لتنظيف فوري:

```bash
php artisan backups:mark-stuck-failed --minutes=120
```

### `mysqldump غير متاح`
عيّن المسار صراحة في `.env`:

```env
BACKUP_MYSQLDUMP_PATH=/usr/bin/mysqldump
```

### `mysqldump failed: exit code 5`

رمز الخروج 5 في mysqldump يعني `EX_EOF` — **فشل الكتابة إلى المخرجات**، وسببه شبه الدائم امتلاء القرص.
انتبه إلى أن القرص الممتلئ قد لا يكون قرص المشروع:

- **على Windows (التطوير):** تُفرِّغ Symfony مخرجات العملية في ملف مؤقت داخل `sys_get_temp_dir()`
  (عادةً `C:\Users\...\AppData\Local\Temp`) وليس داخل مجلد المشروع — لأن `Process` يبني
  `WindowsPipes` متى مُرِّر callback حتى مع `disableOutput()`. أي أن الـ dump الخام كاملاً
  (قد يتجاوز 1 GB) يمرّ عبر قرص `C:` حتى لو كان المشروع على `D:`.
- **على Linux (الإنتاج):** تُستخدم أنابيب حقيقية (`UnixPipes`) بلا أي ملف مؤقت، فهذه الحالة لا تحدث.

الفحص المسبق صار يتحقق من القرصين معاً ويرفض البدء برسالة واضحة تسمّي القرص ومساحته الحرة،
بدل تشغيل الـ dump لدقيقة ثم الفشل برسالة غامضة.

للفحص السريع:

```powershell
Get-PSDrive -PSProvider FileSystem | Select-Object Name, @{n='FreeGB';e={[math]::Round($_.Free/1GB,2)}}
```

### النسخة تفشل بسبب المساحة
`DatabaseBackupSource` يُجهض إذا نزلت المساحة الحرة عن 512 ميجابايت.
السبب الأكثر شيوعاً هو `storage/logs/laravel.log` — فهو **لا يُدوَّر** مع `LOG_CHANNEL=stack`
(بلغ 246 ميجابايت في بيئة التطوير). على الإنتاج استخدم:

```env
LOG_CHANNEL=daily
LOG_DAILY_DAYS=14
LOG_LEVEL=warning
```

## 4. ملاحظات

- **التوقيت:** لكل جدولة حقل توقيت مستقل. الجدولات المنشأة قبل إضافة الحقل تستخدم
  `config('app.timezone')` وهو `UTC` — أي أن «02:00» فيها تعني 05:00 بتوقيت دمشق.
  حرّرها من اللوحة واختر التوقيت الصحيح.
- **الفشل لا يوقف الجدولة:** إذا فشل التنفيذ (مثلاً لا يوجد مكان تخزين نشط) يُقدَّم
  `next_run_at` إلى الموعد التالي ويُسجَّل الخطأ، بدل إعادة المحاولة كل دقيقة.
- **بعد كل نشر:** `deploy.sh` ينفّذ `queue:restart` تلقائياً ويتحقق من وجود المجدول ويحذّر إن لم يجده.
