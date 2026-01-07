# إصلاح مشكلة حفظ embed_code على السيرفر

## المشكلة
كود embed لا يتم حفظه على السيرفر رغم أنه يعمل في البيئة المحلية.

## الحل

### 1. تشغيل Migration على السيرفر
```bash
php artisan migrate --force
```

### 2. التحقق من وجود الحقل في قاعدة البيانات
```bash
php artisan tinker
>>> Schema::hasColumn('videos', 'embed_code')
```

### 3. مسح Cache
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

### 4. إعادة بناء Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. التحقق من Logs
```bash
tail -f storage/logs/laravel.log
```

## ملاحظات
- تأكد من أن migration تم تشغيله على السيرفر
- تأكد من أن embed_code موجود في $fillable في Video model
- تأكد من أن embed_code موجود في validation في VideoController

