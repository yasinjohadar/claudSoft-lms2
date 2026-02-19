# إعداد CORS لـ Flutter Web (Laravel)

## الملخص

عند تشغيل التطبيق كـ **Flutter Web**، المتصفح يرسل الطلبات من مصدر (Origin) مختلف عن مصدر الـ API (مثلاً `http://localhost:xxxx` مقابل `https://api.example.com`). السيرفر يجب أن يرد بهيدرات CORS للسماح بهذه الطلبات.

تم إعداد CORS في المشروع عبر **`config/cors.php`**.

---

## ما تم إعداده

- **الملف:** `config/cors.php`
- **المسارات:** `api/*` و `sanctum/csrf-cookie`
- **الطرق:** كل الطرق (بما فيها OPTIONS للـ preflight)
- **الهيدرات:** الكل مسموح (`*`) — بما فيها **`Authorization`** للتوكن
- **الأصول (Origins):**
  - **بدون إعداد في `.env`:** يُسمح بأي مصدر (`*`) — مناسب للتطوير و Flutter Web على localhost بأي بورت
  - **مع إعداد في `.env`:** استخدم `CORS_ALLOWED_ORIGINS` لقائمة محددة في الإنتاج

---

## الإنتاج (السيرفر الحقيقي)

في ملف **`.env`** على السيرفر يمكنك تقييد الأصول المسموحة:

```env
# قائمة مفصولة بفاصلة، بدون مسافرات زائدة
CORS_ALLOWED_ORIGINS=https://your-flutter-web.example.com,https://www.your-app.com
```

إن لم تضبط `CORS_ALLOWED_ORIGINS` يبقى الإعداد **`*`** (أي مصدر) — استخدم القائمة أعلاه في الإنتاج إن أردت تقييداً.

بعد تعديل `.env` نفّذ:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## التحقق

1. تشغيل التطبيق كـ Flutter Web: `flutter run -d chrome`
2. تنفيذ تسجيل الدخول ثم طلب الكورسات (مثلاً `GET /api/student/catalog`) مع هيدر `Authorization: Bearer <token>`
3. في أدوات المطور (F12) → Network: التأكد من عدم ظهور خطأ **CORS** أو **blocked by CORS policy**
4. استجابة الطلب يجب أن تكون 200 مع بيانات الكورسات

---

## مراجع

- [Laravel CORS](https://laravel.com/docs/routing#cors)
- `config/cors.php` — قيم `paths`, `allowed_origins`, `allowed_methods`, `allowed_headers`, `supports_credentials`
