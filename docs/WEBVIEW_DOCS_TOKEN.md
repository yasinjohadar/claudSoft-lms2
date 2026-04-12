# فتح `/docs` من WebView (Flutter) بتوكن Sanctum

## الفكرة

مسارات التوثيق تحت **`/docs`** محمية. متصفح المستخدم العادي يعتمد على **جلسة الويب (cookies)**. تطبيق Flutter داخل **WebView** غالباً لا يرسل كوكيز الجلسة مع الطلب الأول.

الخادم يدعم الآن تمرير **Personal Access Token** الصادر من Sanctum كمعامل استعلام:

```
https://YOUR_DOMAIN/docs/html-introduction?token=PLAIN_TOKEN_FROM_LOGIN
```

قبل المصادقة، الـ middleware `auth.token` يحوّل ذلك إلى ترويسة:

`Authorization: Bearer PLAIN_TOKEN_FROM_LOGIN`

ثم **`auth:sanctum`** يتحقق من التوكن كالمعتاد.

## الحصول على التوكن

بعد `POST /api/student/login` (أو أي مسار يعيد توكن Sanctum)، استخدم القيمة النصية الكاملة للتوكن كما أعادها الـ API (مثلاً `data.token`).

## متطلبات

- **HTTPS** في الإنتاج (التوكن في الـ URL حساس).
- لا تشارك الروابط التي تحتوي على `?token=` (تسريب عبر سجلات الخادم، `Referer`، أو سجل التصفح).

## مخاطر أمنية (للمطوّر والخلفية)

| المخاطرة | ملاحظة |
|----------|--------|
| تسريب في سجلات الوصول | خوادم الويب تسجّل غالباً مسار الطلب الكامل بما فيه الاستعلام. |
| Referer | الانتقال من الصفحة إلى موقع خارجي قد يرسل الرابط الكامل. |
| تاريخ التصفح / WebView | المستخدم أو أي تطبيق قد يرى الرابط. |

يُفضّل ألا تكون صلاحية التوكن أطول من اللازم، واستخدام هذه الطريقة **لمسارات محدودة** (مثل `/docs` فقط) كما هو مُنفَّذ حالياً.

## المرجع في الكود

- Middleware: [`app/Http/Middleware/AcceptTokenFromUrl.php`](../app/Http/Middleware/AcceptTokenFromUrl.php)
- تسجيل الاسم: `auth.token` في [`bootstrap/app.php`](../bootstrap/app.php)
- المسارات: [`routes/frontend.php`](../routes/frontend.php) (مجموعة `docs`)

## مسارات API الطالب

راجع [API_STUDENT_ROUTES.md](API_STUDENT_ROUTES.md) لتسجيل الدخول والتوكن.
