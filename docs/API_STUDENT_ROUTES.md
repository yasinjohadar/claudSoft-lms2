# مسارات API الطالب (Student API) — للتعامل مع Flutter أو أي عميل

**الاستخدام:** إعطاء هذا الملف للـ AI أو المطور لمعرفة المسارات والطلبات والاستجابات.

---

## مهم — استدعاء الكورسات في Flutter

**إذا كان تسجيل الدخول ناجحاً لكن الكورسات لا تظهر أبداً:** التطبيق غالباً لا يستدعي مسار الكورسات بعد تسجيل الدخول، أو لا يرسل التوكن مع الطلب.

- **قائمة الكورسات (كل الكورسات المنشورة):** بعد حفظ التوكن من استجابة `POST /api/student/login`، يجب استدعاء:
  - **`GET /api/student/catalog`**
  - مع الهيدر: **`Authorization: Bearer <token>`**
  - نفس التوكن المُرجَع من تسجيل الدخول (`data.token`).
- **لا تكفي مصادقة تسجيل الدخول وحدها** — كل طلب محمي (catalog, courses, profile) يحتاج إرسال الهيدر `Authorization: Bearer <token>` في نفس الطلب.

راجع القسم 4 (كتالوج الكورسات) أدناه لتفاصيل الطلب والاستجابة. إن احتجت أمثلة جاهزة لـ Flutter (Dio/Http) انظر [docs/FLUTTER_CATALOG_CALL.md](FLUTTER_CATALOG_CALL.md).

**WebView — صفحات التوثيق `/docs`:** إذا فتحت `https://YOUR_DOMAIN/docs/...` من WebView بدون كوكيز جلسة، يمكن تمرير نفس توكن Sanctum كمعامل **`?token=`** (middleware `auth.query_token` يحوّله إلى `Authorization: Bearer`). **رمّز التوكن في الرابط** (مثلاً `|` → `%7C`) عبر `Uri` في Flutter. التفاصيل: [docs/WEBVIEW_DOCS_TOKEN.md](WEBVIEW_DOCS_TOKEN.md).

---

## متطلبات التشغيل

- API الطالب يعتمد على **Laravel Sanctum** (مصادقة بالتوكن).
- موديل `User` يستخدم الـ trait `Laravel\Sanctum\HasApiTokens`.

**إذا ظهر خطأ:** `Trait "Laravel\Sanctum\HasApiTokens" not found`  
نفّذ من **جذر المشروع** (المجلد الذي فيه `composer.json`):

```bash
composer install
composer dump-autoload
```

ثم أعد تشغيل السيرفر أو الصفحة.

---

## Base URL

```
https://YOUR_DOMAIN.com/api/student
```

مثال: `https://claudsoft.com/api/student`

---

## المصادقة

- **تسجيل الدخول:** `POST /api/student/login` يعيد `token`.
- **باقي المسارات المحمية:** إرسال الهيدر:
  ```
  Authorization: Bearer <token>
  ```
- **Content-Type لطلبات الجسم:** `application/json`

---

## 1. تسجيل الدخول (للطالب فقط)

| البند | القيمة |
|--------|--------|
| **Method** | `POST` |
| **Path** | `/api/student/login` |
| **مصادقة** | لا |
| **Body (JSON)** | `email` (string), `password` (string) |

**طلب مثال:**
```http
POST /api/student/login
Content-Type: application/json

{
  "email": "student@example.com",
  "password": "password"
}
```

**استجابة ناجحة (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد",
      "name_ar": "أحمد",
      "email": "student@example.com",
      "avatar": "https://domain.com/storage/avatars/1.jpg"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

**أخطاء (422):** بيانات خاطئة أو حساب غير طالب أو غير مفعّل — الحقل `message` أو `errors` يوضح السبب.

---

## 2. تسجيل الخروج (إبطال التوكن)

| البند | القيمة |
|--------|--------|
| **Method** | `POST` |
| **Path** | `/api/student/logout` |
| **مصادقة** | نعم — `Authorization: Bearer <token>` |

**طلب مثال:**
```http
POST /api/student/logout
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxx
```

**استجابة ناجحة (200):**
```json
{
  "success": true,
  "message": "تم تسجيل الخروج."
}
```

---

## 3. المستخدم الحالي (التحقق من التوكن)

| البند | القيمة |
|--------|--------|
| **Method** | `GET` |
| **Path** | `/api/student/me` |
| **مصادقة** | نعم — `Authorization: Bearer <token>` |

**طلب مثال:**
```http
GET /api/student/me
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxx
```

**استجابة ناجحة (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد",
      "name_ar": "أحمد",
      "email": "student@example.com",
      "avatar": "https://domain.com/storage/avatars/1.jpg"
    }
  }
}
```

---

## 4. كتالوج الكورسات (كل الكورسات المنشورة — للعرض في التطبيق)

يعيد **كل** الكورسات المنشورة والمرئية. استخدم هذا المسار لعرض قائمة الكورسات في Flutter حتى لو لم يكن الطالب مسجّلاً فيها. كل عنصر يتضمّن `is_enrolled` و `enrollment` إن كان الطالب مسجّلاً.

| البند | القيمة |
|--------|--------|
| **Method** | `GET` |
| **Path** | `/api/student/catalog` |
| **مصادقة** | نعم — `Authorization: Bearer <token>` (ودور المستخدم: طالب) |

**طلب مثال:**
```http
GET /api/student/catalog
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxx
```

**استجابة ناجحة (200):**
```json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": 1,
        "title": "اسم الكورس",
        "slug": "course-slug",
        "description": "وصف الكورس",
        "short_description": "وصف قصير",
        "image": "https://domain.com/storage/courses/1.jpg",
        "level": "مبتدئ",
        "language": "ar",
        "duration_in_hours": 10,
        "is_free": true,
        "is_enrolled": false,
        "enrollment": null
      },
      {
        "id": 2,
        "title": "كورس آخر",
        "slug": "other-course",
        "description": "...",
        "short_description": "...",
        "image": "https://...",
        "level": "متوسط",
        "language": "ar",
        "duration_in_hours": 5,
        "is_free": false,
        "is_enrolled": true,
        "enrollment": {
          "enrollment_id": 10,
          "enrollment_status": "active",
          "completion_percentage": 0,
          "last_accessed_at": null
        }
      }
    ]
  }
}
```

**ملاحظة:** لا يتضمّن الأقسام والدروس. لتفاصيل كورس واحد (أقسام + دروس) استخدم `/api/student/courses` للكورسات المسجّل فيها فقط.

---

## 5. قائمة كورساتي (المسجّل فيها) مع الأقسام والدروس

يعيد الكورسات التي **سجّل فيها الطالب فقط** مع الأقسام والوحدات (دروس/فيديو). إن كانت القائمة فارغة فاستخدم `/api/student/catalog` لعرض كل الكورسات.

**توثيق تفصيلي لبنية `sections` → `lessons` → `content` (HTML، فيديو، موارد):** [docs/API_STUDENT_COURSES.md](API_STUDENT_COURSES.md).

| البند | القيمة |
|--------|--------|
| **Method** | `GET` |
| **Path** | `/api/student/courses` |
| **مصادقة** | نعم — `Authorization: Bearer <token>` (ودور المستخدم: طالب) |

**طلب مثال:**
```http
GET /api/student/courses
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxx
```

**استجابة ناجحة (200):**
```json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": 1,
        "title": "اسم الكورس",
        "slug": "course-slug",
        "description": "وصف الكورس",
        "short_description": "وصف قصير",
        "image": "https://domain.com/storage/courses/1.jpg",
        "level": "مبتدئ",
        "language": "ar",
        "duration_in_hours": 10,
        "is_free": true,
        "enrollment": {
          "enrollment_id": 1,
          "enrollment_status": "active",
          "completion_percentage": 25.5,
          "last_accessed_at": "2025-02-14T12:00:00.000000Z"
        },
        "sections": [
          {
            "id": 1,
            "course_id": 1,
            "title": "القسم الأول",
            "description": "وصف القسم",
            "sort_order": 0,
            "order_index": 0,
            "lessons": [
              {
                "id": 1,
                "section_id": 1,
                "module_type": "lesson",
                "title": "عنوان الدرس",
                "description": "وصف الدرس",
                "sort_order": 0,
                "estimated_duration": 15,
                "completion_type": "auto",
                "is_completed": false,
                "content": {
                  "id": 1,
                  "title": "عنوان المحتوى",
                  "description": null,
                  "content": "نص HTML أو محتوى الدرس",
                  "reading_time": 5,
                  "video_url": null,
                  "video_path": null,
                  "duration": null,
                  "thumbnail": null
                }
              },
              {
                "id": 2,
                "section_id": 1,
                "module_type": "video",
                "title": "فيديو الدرس",
                "description": null,
                "sort_order": 1,
                "estimated_duration": 10,
                "completion_type": "auto",
                "is_completed": false,
                "content": {
                  "id": 1,
                  "title": "عنوان الفيديو",
                  "description": null,
                  "content": null,
                  "reading_time": null,
                  "video_url": "https://...",
                  "video_path": "/storage/videos/...",
                  "duration": 600,
                  "thumbnail": "https://..."
                }
              }
            ]
          }
        ]
      }
    ]
  }
}
```

- **module_type:** `lesson` أو `video` أو `quiz` أو `assignment` أو `resource` إلخ.
- **content:** للدرس: `content`, `reading_time` غالباً. للفيديو: `video_url` أو `video_path`, `duration`, `thumbnail`.
- **is_completed:** هل الطالب أتم هذه الوحدة.

---

## ملخص المسارات للـ AI / Flutter

| Method | Path | مصادقة | الوصف |
|--------|------|--------|--------|
| POST | `/api/student/login` | لا | تسجيل دخول طالب → يرجع `token` |
| POST | `/api/student/logout` | Bearer | إبطال التوكن |
| GET | `/api/student/me` | Bearer | بيانات المستخدم الحالي (مختصرة) |
| GET | `/api/student/profile` | Bearer | **بروفايل الطالب الكامل** (كل البيانات — انظر `docs/API_STUDENT_PROFILE.md`) |
| GET | `/api/student/courses` | Bearer | كورسات الطالب + أقسام + دروس/فيديوهات — تفاصيل JSON: [API_STUDENT_COURSES.md](API_STUDENT_COURSES.md) |

**الهيدر للمسارات المحمية:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**الاستجابة العامة:** كل استجابة ناجحة تحتوي على `"success": true` و `"data"` (أو `"message"` حيث ينطبق). الأخطاء تُرجع برموز HTTP المناسبة و `errors` أو `message`.
