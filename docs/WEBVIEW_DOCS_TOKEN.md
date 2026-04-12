# فتح `/docs` من WebView (Flutter) بتوكن Sanctum

## الفكرة

مسارات التوثيق تحت **`/docs`** محمية. متصفح المستخدم العادي يعتمد على **جلسة الويب (cookies)**. تطبيق Flutter داخل **WebView** غالباً لا يرسل كوكيز الجلسة مع الطلب الأول.

الخادم يدعم تمرير **Personal Access Token** الصادر من Sanctum كمعامل استعلام:

```
https://YOUR_DOMAIN/docs/html-introduction?token=PLAIN_TOKEN_FROM_LOGIN
```

قبل **`auth:sanctum`**، الـ middleware **`auth.query_token`** (`AcceptTokenFromQueryParam`) يحوّل ذلك إلى ترويسة:

`Authorization: Bearer PLAIN_TOKEN_FROM_LOGIN`

ثم يتم التحقق من التوكن كالمعتاد **بدون الاعتماد على cookie session** (مع بقاء دعم الجلسة للمتصفح العادي عبر نفس `auth:sanctum`).

## الحصول على التوكن

بعد `POST /api/student/login` (أو أي مسار يعيد توكن Sanctum)، استخدم القيمة النصية **كاملة** كما أعادها الـ API (مثلاً `data.token`). غالباً يتضمن التوكن حرف **`|`** (مثل `12|abcdef...`).

## Flutter / بناء الرابط

- **رمّز التوكن في الـ URL** (مهم جداً): استخدم `Uri` مع `queryParameters` أو `Uri.encodeQueryComponent(token)` حتى تُرمَّز الأحرف الخاصة (`|`، `+`، `&`، …).
- **سطر واحد:** لا تضع سطراً جديداً داخل التوكن داخل الرابط؛ يلغي التحقق.
- مثال مفهومي:

```dart
final token = loginResponse.data.token; // نص كامل من الـ API
final uri = Uri.https('claudsoft.com', '/docs/html-introduction', {'token': token});
webViewController.loadRequest(uri);
```

## إن بقي الطلب يوجّه لتسجيل الدخول

1. تأكد أن النشر على السيرفر يتضمن آخر كود (`auth.query_token` على مجموعة `/docs`).
2. تأكد أن التوكن **لم ينتهِ** وأنه نسخ كاملاً من الاستجابة.
3. جرّب نفس الرابط بعد **ترميز** التوكن يدوياً في المتصفح (استبدال `|` بـ `%7C`).

## متطلبات

- **HTTPS** في الإنتاج (التوكن في الـ URL حساس).
- لا تشارك الروابط التي تحتوي على `?token=`.

## مخاطر أمنية (للمطوّر والخلفية)

| المخاطرة | ملاحظة |
|----------|--------|
| تسجيل الوصول | مسار الطلب الكامل قد يُسجَّل مع الاستعلام. |
| Referer | قد يُرسل الرابط لمواقع أخرى. |
| سجل المتصفح / WebView | الرابط مرئي للمستخدم. |

## المرجع في الكود

- Middleware: [`app/Http/Middleware/AcceptTokenFromQueryParam.php`](../app/Http/Middleware/AcceptTokenFromQueryParam.php)
- أسماء مسجّلة: `auth.query_token` و `auth.token` (نفس الصنف) في [`bootstrap/app.php`](../bootstrap/app.php)
- المسارات: [`routes/frontend.php`](../routes/frontend.php) (مجموعة `docs`: `auth.query_token`, `auth:sanctum`)

**ملاحظة:** Laravel 11 يستخدم `bootstrap/app.php` لتسجيل الـ middleware aliases؛ لا يوجد `Kernel.php` للأسماء في هذا المشروع.

## مسارات API الطالب

راجع [API_STUDENT_ROUTES.md](API_STUDENT_ROUTES.md) لتسجيل الدخول والتوكن.
