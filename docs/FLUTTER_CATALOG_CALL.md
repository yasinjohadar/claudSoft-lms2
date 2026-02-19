# استدعاء الكورسات من Flutter بعد تسجيل الدخول

إذا كان تسجيل الدخول يعمل لكن **قائمة الكورسات لا تُحمّل أبداً**، تأكد من التالي.

---

## 1. حفظ التوكن بعد تسجيل الدخول

من استجابة `POST /api/student/login` احفظ التوكن (مثلاً في SharedPreferences أو secure storage):

```dart
// بعد نجاح تسجيل الدخول
final token = response.data['data']['token']; // القيمة مثل: "1|xxxxxxxx..."
// احفظها للطلبات التالية
await storage.write(key: 'auth_token', value: token);
```

---

## 2. استدعاء الكتالوج مع الهيدر

عند فتح شاشة الكورسات (أو بعد تسجيل الدخول)، استدعِ **نفس الـ base URL + `/api/student/catalog`** مع الهيدر:

```
Authorization: Bearer <token>
```

**مثال باستخدام Dio:**

```dart
final token = await storage.read(key: 'auth_token');
if (token == null || token.isEmpty) {
  // أعد توجيه المستخدم لتسجيل الدخول
  return;
}

final response = await dio.get(
  'https://YOUR_DOMAIN.com/api/student/catalog',
  options: Options(
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    },
  ),
);

if (response.statusCode == 200 && response.data['success'] == true) {
  final courses = response.data['data']['courses'] as List;
  // اعرض courses في الواجهة
}
```

**مثال باستخدام http:**

```dart
final token = await storage.read(key: 'auth_token');
final response = await http.get(
  Uri.parse('https://YOUR_DOMAIN.com/api/student/catalog'),
  headers: {
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  },
);
```

---

## 3. المسار والـ Base URL

| الاستخدام        | المسار الكامل (استبدل YOUR_DOMAIN)     |
|------------------|----------------------------------------|
| قائمة الكورسات   | `https://YOUR_DOMAIN.com/api/student/catalog` |
| كورساتي (مع دروس) | `https://YOUR_DOMAIN.com/api/student/courses`  |

يجب أن يكون الـ Base URL مطابقاً لاستدعاء تسجيل الدخول (نفس الدومين ونفس البورت إن وُجد).

---

## 4. التحقق من الأخطاء

- **401 Unauthorized:** التوكن غير مُرسَل أو منتهي أو خاطئ. تأكد من الهيدر `Authorization: Bearer <token>`.
- **403 Forbidden:** المستخدم ليس بدور طالب. تحقق من أن الحساب المستخدم للتسجيل له دور طالب في النظام.
- **لا يظهر شيء:** تأكد أن الطلب يُنفَّذ فعلياً (أضف print أو breakpoint على الـ URL والهيدر)، وأن واجهة الكورسات تعرض `response.data['data']['courses']`.

---

## 5. استجابة ناجحة (200)

```json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": 1,
        "title": "اسم الكورس",
        "slug": "course-slug",
        "image": "https://...",
        "is_enrolled": false,
        "enrollment": null
      }
    ]
  }
}
```

اعرض `data.courses` في الـ ListView أو الـ widget الذي يعرض الكورسات.

---

## 6. يعمل على اللوكال ولا يعمل على السيرفر الحقيقي

إذا كانت الكورسات تُجلب بشكل صحيح مع اللوكال لكن تظهر رسائل خطأ عند الربط مع السيرفر الحقيقي، راجع التالي:

### تغيير الـ Base URL

- تأكد أن التطبيق يستخدم عنوان السيرفر الحقيقي وليس `localhost` أو `127.0.0.1` أو عنوان اللوكال.
- مثال للسيرفر: `https://claudsoft.com` (بدون `/api/student` في نهاية الـ base).
- المسار الكامل للكتالوج: `https://claudsoft.com/api/student/catalog`.

### رسائل شائعة وحلولها

| الرسالة / السلوك | السبب المحتمل | الحل |
|------------------|----------------|------|
| **401 Unauthorized** | التوكن غير معترف به على السيرفر (مثلاً مصدر التوكن مختلف) | تسجيل الدخول من التطبيق ضد السيرفر الحقيقي (نفس الـ base URL) ثم استخدام التوكن المُرجَع في طلبات catalog/courses. |
| **500 Internal Server Error** | خطأ في السيرفر (Sanctum، إعدادات، أو استثناء في الكود) | مراجعة `storage/logs/laravel.log` على السيرفر عند تنفيذ الطلب؛ راجع [docs/SERVER_SETUP_SANCTUM.md](SERVER_SETUP_SANCTUM.md) و [docs/MESSAGE_TO_BACKEND_PROFILE_500.md](MESSAGE_TO_BACKEND_PROFILE_500.md). |
| **Connection refused / Timeout** | عنوان خاطئ أو جدار ناري أو السيرفر لا يستجيب | التحقق من الـ URL والإنترنت؛ اختبار الطلب من المتصفح أو Postman إلى `https://الدومين/api/student/catalog` مع هيدر Authorization. |
| **SSL / Certificate error** (أندرويد) | شهادة HTTPS أو سياسة cleartext | استخدام `https://` للسيرفر؛ إن كان السيرفر يستخدم شهادة ذاتية، قد تحتاج إعدادات خاصة في التطبيق (لا يُنصح بتجاهل التحقق في الإنتاج). |
| **CORS** (إن كان Flutter Web) | السيرفر يرفض الطلب من مصدر مختلف | تم إعداد CORS في `config/cors.php`؛ راجع [docs/CORS_FLUTTER_WEB.md](CORS_FLUTTER_WEB.md). في الإنتاج يمكن ضبط `CORS_ALLOWED_ORIGINS` في `.env`. |

### التحقق من السيرفر مباشرة

من المتصفح أو Postman على جهازك:

1. **تسجيل الدخول:**  
   `POST https://الدومين/api/student/login`  
   Body: `{"email":"...","password":"..."}`  
   واحفظ `data.token` من الاستجابة.

2. **جلب الكتالوج:**  
   `GET https://الدومين/api/student/catalog`  
   هيدر: `Authorization: Bearer <التوكن>`  

إن نجح هذا ولم ينجح من Flutter، فالسبب غالباً في التطبيق (URL، أو عدم إرسال الهيدر، أو بيئة الإنتاج). إن فشل من المتصفح/Postman أيضاً، فالسبب من السيرفر أو الـ API (راجع الـ log).

**مهم:** أرسل **نص رسالة الخطأ** أو لقطة شاشة من التطبيق/الـ console لمطورة Flutter أو الباكند لتحديد السبب بدقة.

---

## 7. اللوغ بعد الطلب (لمعرفة سبب عدم تحميل الكورسات)

تم إضافة تسجيل (logging) في الـ API عند استدعاء الكتالوج. بعد تنفيذ الطلب من Flutter إلى السيرفر:

1. **مكان اللوغ:** على السيرفر ملف `storage/logs/laravel.log`.
2. **ماذا تبحث عنه:**
   - **`[Student API] request`** — طلب وصل إلى مسارات الطالب (قبل التحقق من التوكن). يُسجّل `path`, `method`, `has_authorization_header`, `has_bearer_prefix`, `origin`. إن ظهر هذا لكن بدون السطر التالي فالمصادقة رفضت الطلب (401/403).
   - **`[Student API] response`** — انتهاء معالجة الطلب؛ يُسجّل `path`, `status` (مثل 200 أو 401).
   - **`[Student API] catalog: request started`** — الطلب وصل إلى الـ controller (المصادقة ناجحة). يُسجّل أيضاً `has_user`, `user_id`, `origin`.
   - **`[Student API] catalog: success`** — تم تنفيذ الطلب بنجاح؛ يُسجّل `courses_count`.
   - **`[Student API] catalog: exception`** — حدث استثناء؛ يُسجّل `message`, `file`, `line`.
3. **إن لم يظهر أي سطر `[Student API]`** — الطلب **لم يصل إلى السيرفر** أو لم يصل إلى مسارات api/student: تحقق من الـ URL في Flutter (الدومين ومسار `/api/student/catalog`)، أو من CORS إن كان Flutter Web.

**لماذا تشغيل الأمر؟** الأمر `tail -n 100 ...` هو **لقراءة** آخر 100 سطر من اللوغ فقط (أسرع من فتح الملف كاملاً). اللوغ لا يُحذف ولا يُدوّر تلقائياً بهذا الأمر — الملف يبقى ويستمر الـ append عليه.

لرؤية آخر السطور بعد الطلب مباشرة (على السيرفر):
```bash
tail -n 100 storage/logs/laravel.log
```

**تدوير اللوغ (اختياري):** Laravel افتراضياً يكتب في ملف واحد `laravel.log` دون تدوير تلقائي. إن كبر الملف يمكن تفعيل التدوير اليومي في `config/logging.php` (قناة `daily`) أو استخدام نظام الـ logrotate على السيرفر.
