# يعمل على اللوكال ولا يعمل على السيرفر (ربط Flutter)

عندما التطبيق يشتغل نظامي مع اللوكال لكن عند الربط مع السيرفر الحقيقي لا يشتغل، راجع التالي.

---

## 1. من جهة Flutter (التطبيق)

- **Base URL للـ API:** يجب أن يتغيّر حسب البيئة:
  - على اللوكال: مثلاً `http://localhost:8000` أو `http://10.0.2.2:8000` (أندرويد محاكي).
  - على السيرفر الحقيقي: `https://الدومين-الحقيقي.com` (بدون `/api/student` في النهاية).
- المسار الكامل للكتالوج: `{baseUrl}/api/student/catalog`.
- **نفس التوكن:** التوكن يُستلم من **نفس السيرفر** الذي ستُرسل له الطلبات. لا تستخدم توكن من اللوكال عند الطلب إلى السيرفر الحقيقي — سجّل الدخول ضد السيرفر الحقيقي واحفظ التوكن، ثم استخدمه في طلبات catalog/courses.

---

## 2. من جهة السيرفر (Laravel)

### أ) إعدادات `.env` على السيرفر

```env
APP_URL=https://الدومين-الحقيقي.com
```

(يجب أن يكون نفس الدومين الذي يتصل منه Flutter.)

إن كنت تستخدم Flutter **Web** وتربط من دومين ويب معيّن، أضف في `.env`:

```env
CORS_ALLOWED_ORIGINS=https://دومين-التطبيق-الويب.com,https://الدومين-الحقيقي.com
```

ثم على السيرفر:

```bash
php artisan config:clear
php artisan cache:clear
```

### ب) CORS

- تأكد أن ملف **`config/cors.php`** موجود على السيرفر (تم إنشاؤه في المشروع).
- إن لم تضبط `CORS_ALLOWED_ORIGINS` يُسمح بأي مصدر (`*`). إن ضبطته فالدومين الذي يعمل منه Flutter Web يجب أن يكون ضمن القائمة.

### ج) Sanctum (المصادقة بالتوكن)

- الطلبات المحمية تحتاج هيدر: `Authorization: Bearer <token>`.
- التوكن يُصدر من `POST /api/student/login` على **نفس السيرفر** الذي تطلب منه `/api/student/catalog`.
- إن ظهر 401 على السيرفر: تحقق من أن الطلب يصل فعلاً (لوغ `[Student API] request` في `storage/logs/laravel.log`) وأن الهيدر مُرسَل. راجع [docs/FLUTTER_CATALOG_CALL.md](FLUTTER_CATALOG_CALL.md) و [docs/SERVER_SETUP_SANCTUM.md](SERVER_SETUP_SANCTUM.md).

### د) مسار الـ API

- التأكد أن المسارات تعمل على السيرفر:
  - `POST https://الدومين/api/student/login`
  - `GET https://الدومين/api/student/catalog` (مع هيدر Authorization)
- يمكن التجربة من المتصفح أو Postman ضد السيرفر الحقيقي؛ إن نجحت من Postman ولم تنجح من Flutter فالمشكلة من التطبيق (URL أو هيدر أو CORS إن كان ويب).

---

## 3. ملخص سريع

| الجهة   | التحقق |
|--------|--------|
| **Flutter** | Base URL = السيرفر الحقيقي؛ تسجيل الدخول ضد السيرفر واستخدام التوكن في طلبات catalog؛ إرسال `Authorization: Bearer <token>`. |
| **السيرفر** | `APP_URL` صحيح؛ `config/cors.php` موجود؛ إن Flutter Web: `CORS_ALLOWED_ORIGINS` أو `*`؛ مسح الكاش بعد تغيير `.env`. |

بعد ضبط ذلك، إن استمرت المشكلة أرسل رسالة الخطأ من التطبيق أو من اللوغ (`[Student API]` أو 401/403/CORS) لتحديد السبب بدقة.
