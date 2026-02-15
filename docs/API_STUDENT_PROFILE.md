# توثيق API بروفايل الطالب — لمبرمج Flutter

هذا الملف يشرح **بروفايل الطالب الكامل** من أجل عرضه في تطبيق Flutter. يمكن تسليمه لمبرمج Flutter كما هو.

---

## 1. المسار والطريقة والمصادقة

| البند | القيمة |
|--------|--------|
| **Method** | `GET` |
| **Path** | `/api/student/profile` |
| **المصادقة** | مطلوبة — هيدر: `Authorization: Bearer <token>` |
| **دور المستخدم** | طالب فقط (`role:student`) |

**مثال طلب:**
```http
GET /api/student/profile
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxx
Accept: application/json
```

---

## 2. استجابة ناجحة (200)

الجسم بصيغة JSON بالشكل التالي:

```json
{
  "success": true,
  "data": {
    "user": { ... },
    "stats": { ... },
    "enrollments_summary": { ... },
    "badges": [ ... ],
    "achievements_completed": [ ... ],
    "achievements_in_progress": [ ... ]
  }
}
```

---

## 3. تفصيل الحقول (للاستخدام في Flutter)

### 3.1 `data.user` — بيانات الحساب الشخصية

| الحقل | النوع | الوصف |
|--------|--------|--------|
| `id` | int | معرّف المستخدم |
| `name` | string | الاسم (إنكليزي أو افتراضي) |
| `name_ar` | string | الاسم بالعربية |
| `email` | string | البريد الإلكتروني |
| `phone` | string \| null | رقم الجوال (بدون رمز الدولة) |
| `country_code` | string \| null | رمز الدولة (مثل 966) |
| `full_phone` | string \| null | الرقم الكامل دولياً |
| `avatar` | string \| null | رابط صورة Avatar (URL كامل) أو null |
| `date_of_birth` | string \| null | تاريخ الميلاد بصيغة `Y-m-d` أو null |
| `gender` | string \| null | الجنس (قيمة من النظام أو null) |
| `address` | string \| null | العنوان |
| `city` | string \| null | المدينة |
| `nationality_id` | int \| null | معرّف الجنسية |
| `nationality_name` | string \| null | اسم الجنسية |
| `student_id` | string \| null | الرقم الجامعي/الطالب إن وُجد |
| `is_profile_public` | bool | هل الملف الشخصي عام |
| `last_login_at` | string \| null | آخر تسجيل دخول بصيغة ISO 8601 أو null |

---

### 3.2 `data.stats` — إحصائيات الجيميفيكيشن والنشاط

| الحقل | النوع | الوصف |
|--------|--------|--------|
| `total_points` | int | إجمالي النقاط المكتسبة |
| `available_points` | int | النقاط المتاحة (غير المصروفة) |
| `total_xp` | int | إجمالي نقاط الخبرة (XP) |
| `current_level` | int | المستوى الحالي (رقم) |
| `level_name` | string \| null | اسم المستوى (مثل "مبتدئ"، "متعلم") |
| `xp_to_next_level` | int | XP المطلوب للمستوى التالي |
| `level_progress` | double | نسبة التقدم للمستوى التالي (0–100 أو أكثر) |
| `total_badges` | int | عدد الشارات المكتسبة |
| `total_achievements` | int | عدد الإنجازات المكتملة |
| `current_streak` | int | سلسلة الأيام الحالية |
| `longest_streak` | int | أطول سلسلة أيام |
| `courses_completed` | int | عدد الكورسات المكتملة |
| `courses_enrolled` | int | عدد الكورسات المسجّل فيها |
| `lessons_completed` | int | عدد الدروس/الوحدات المكتملة |
| `quizzes_completed` | int | عدد الاختبارات المكتملة |
| `perfect_scores` | int | عدد الدرجات الكاملة |
| `assignments_submitted` | int | عدد الواجبات المسلّمة |
| `average_quiz_score` | double | متوسط درجة الاختبارات |
| `average_assignment_score` | double | متوسط درجة الواجبات |
| `global_rank` | int \| null | الترتيب العالمي أو null |
| `division` | string \| null | الفئة (مثل bronze, silver) أو null |
| `total_study_time` | int | إجمالي وقت الدراسة (بالدقائق) |
| `last_activity_at` | string \| null | آخر نشاط ISO 8601 أو null |

---

### 3.3 `data.enrollments_summary` — ملخص التسجيلات

| الحقل | النوع | الوصف |
|--------|--------|--------|
| `total` | int | إجمالي التسجيلات في الكورسات |
| `active` | int | التسجيلات النشطة فقط |
| `completed` | int | كورسات مكتملة (نسبة الإكمال 100%) |

---

### 3.4 `data.badges` — الشارات المكتسبة

مصفوفة من كائنات، كل عنصر:

| الحقل | النوع | الوصف |
|--------|--------|--------|
| `id` | int | معرّف الشارة |
| `name` | string | اسم الشارة |
| `slug` | string | معرف الشارة (للاستخدام في الروابط أو التخزين) |
| `description` | string \| null | وصف الشارة |
| `icon` | string \| null | أيقونة (رمز أو اسم) |
| `rarity` | string \| null | الندرة (مثل common, rare, epic) |
| `points_value` | int | نقاط الشارة |
| `awarded_at` | string \| null | تاريخ الحصول عليها ISO 8601 |

---

### 3.5 `data.achievements_completed` — الإنجازات المكتملة

مصفوفة من كائنات، كل عنصر:

| الحقل | النوع | الوصف |
|--------|--------|--------|
| `id` | int | معرّف الإنجاز |
| `name` | string | الاسم |
| `slug` | string | المعرف |
| `description` | string \| null | الوصف |
| `icon` | string \| null | الأيقونة |
| `tier` | string \| null | المستوى/الفئة |
| `status` | string | الحالة (مثلاً `completed`) |
| `current_value` | int | القيمة الحالية المحققة |
| `target_value` | int | القيمة المستهدفة |
| `progress_percentage` | double | نسبة التقدم |
| `started_at` | string \| null | تاريخ البدء ISO 8601 |
| `completed_at` | string \| null | تاريخ الإكمال ISO 8601 |
| `points_claimed` | int | النقاط المطالب بها |
| `xp_claimed` | int | XP المطالب به |

---

### 3.6 `data.achievements_in_progress` — الإنجازات قيد التقدم

نفس بنية `achievements_completed`؛ الفرق أن `status` ليس `completed` ونسبة التقدم أقل من 100.

---

## 4. أمثلة استخدام في Flutter (مختصرة)

- بعد تسجيل الدخول احفظ الـ `token` ثم أضفه لهيدر كل طلب:
  `Authorization: Bearer <token>`.
- استدعِ `GET /api/student/profile` عند فتح شاشة البروفايل أو عند سحب للتحديث.
- تحقق من `response.data.success == true` ثم استخدم `response.data.data`.
- لربط الصورة: استخدم `data.user.avatar` إن لم يكن null.
- لعرض الاسم في الواجهة: استخدم `data.user.name_ar` أو `data.user.name`.
- لعرض المستوى: `data.stats.level_name` أو `data.stats.current_level`.
- لعرض النقاط والمستوى والتقدم: من `data.stats`.
- لعرض عدد الكورسات والشارات والإنجازات: من `data.enrollments_summary` و `data.stats` و `data.badges` و `data.achievements_completed`.

---

## 5. الأخطاء الشائعة

| رمز HTTP | المعنى |
|----------|--------|
| 401 | توكن غير صالح أو منتهي — إعادة تسجيل الدخول |
| 403 | المستخدم ليس طالباً — هذا المسار للطلاب فقط |
| 500 | خطأ من الخادم — إعادة المحاولة أو مراجعة السجلات |

---

## 6. ملخص المسارات للطالب (لتذكير مبرمج Flutter)

| Method | Path | الوصف |
|--------|------|--------|
| POST | `/api/student/login` | تسجيل الدخول → يرجع `token` |
| POST | `/api/student/logout` | تسجيل الخروج |
| GET | `/api/student/me` | بيانات المستخدم المختصرة |
| GET | `/api/student/profile` | **بروفايل الطالب الكامل (كل البيانات)** |
| GET | `/api/student/courses` | الكورسات مع الأقسام والدروس |

كل المسارات المحمية تتطلب: `Authorization: Bearer <token>`.
