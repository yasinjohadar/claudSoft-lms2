# توثيق JSON لـ `GET /api/student/courses`

**الجمهور:** مطورو Flutter / أي عميل يستهلك API الطالب.

**المصدر في الكود:** [`app/Http/Controllers/Api/Student/CourseController.php`](../app/Http/Controllers/Api/Student/CourseController.php) — الدالة `index` (تقريباً الأسطر 190–267).

**المصادقة:** `Authorization: Bearer <token>` + دور المستخدم **طالب** (`role:student`).

**ملاحظة:** يُرجع فقط التسجيلات بحالة **`enrollment_status === "active"`** (وليس `completed`).

---

## شكل الاستجابة العام

```json
{
  "success": true,
  "data": {
    "courses": []
  }
}
```

---

## مسار الشجرة: `courses` → `sections` → `lessons` → `content`

| المستوى | الوصف |
|---------|--------|
| **`data.courses[]`** | كل عنصر: حقول الكورس + **`enrollment`** + **`sections`**. |
| **`sections[]`** | كل قسم: **`lessons`** — في قاعدة البيانات هذا `CourseModule`، لكن مفتاح JSON هو **`lessons`** وليس `modules`. |
| **`lessons[]`** | كل درس: **`module_type`** + **`content`**. |
| **`content`** | إما **`null`**، أو **كائن (object)** — ليس نصاً في جذر الدرس. |

---

## درس نصي / HTML (ما يعادل "article" في الويب)

في الويب، درس HTML يكون عادةً **`module_type === "lesson"`** والنموذج المورف **`App\Models\Lesson`**. يُعرض المحتوى كـ `{!! $module->modulable->content !!}` في [`resources/views/student/courses/learning/module-main-inner.blade.php`](../resources/views/student/courses/learning/module-main-inner.blade.php).

| السؤال | الجواب |
|--------|--------|
| أين HTML؟ | **`lessons[].content.content`** — المفتاح الداخلي **`content`** من نوع **string** (قد يحتوي HTML). |
| هل `content` كائن أم نص؟ | **`lessons[].content`** = **object**؛ حقل HTML = **`content.content`** = **string** أو `null`. |

### مثال (قيم وهمية)

```json
{
  "id": 101,
  "section_id": 10,
  "module_type": "lesson",
  "title": "مقدمة",
  "description": null,
  "sort_order": 1,
  "estimated_duration": 15,
  "completion_type": "view",
  "is_completed": false,
  "content": {
    "id": 55,
    "title": "مقدمة",
    "description": "وصف اختياري",
    "content": "<p>HTML هنا</p>",
    "reading_time": 10,
    "video_url": null,
    "video_path": null,
    "duration": null,
    "thumbnail": null
  }
}
```

**Flutter:** استخدم `flutter_html` أو `WebView` مع `loadHtmlString` على **`content.content`**، مع معالجة أمنية مناسبة للـ HTML.

---

## "Web" / صفحة خارجية / iframe

لا يوجد `module_type` باسم **`web`** في الكود الحالي. الأقرب:

- **`module_type === "resource"`** مع **`App\Models\Resource`**: يُضاف **`resource_url`** و **`display_mode`** (مثل `external` أو ما يُخزَّن في DB). الويب يعرض الرابط في iframe أو فتح خارجي.

### مثال `content` لمورد

```json
{
  "id": 20,
  "title": "رابط مرجعي",
  "description": null,
  "content": null,
  "reading_time": null,
  "video_url": null,
  "video_path": null,
  "duration": null,
  "thumbnail": null,
  "resource_url": "https://example.com/page",
  "display_mode": "external"
}
```

**WebView + URL مباشر:** عند `module_type === "resource"` استخدم **`content.resource_url`**.

---

## فيديو (`module_type === "video"`)

نفس كائن `content` يُملأ من نموذج **`Video`**. الحقول المعرَّضة حالياً في الـ API:

| الحقل | المعنى |
|--------|--------|
| **`content.video_url`** | رابط فيديو (YouTube، Bunny، رابط مباشر، …) — string أو `null`. |
| **`content.video_path`** | مسار تخزين **نسبي** للرفع المحلي — string أو `null`. الـ API **لا** يحوّله إلى URL مطلق (عكس `course.image` الذي يُمرَّر عبر `url()`). |
| **`content.duration`** | المدة (حسب النموذج). |
| **`content.thumbnail`** | صورة مصغرة إن وُجدت. |

### فجوة مقارنة بالويب

صفحة التعلم تستخدم `getEmbedCode()` و`video_type` ومنطق YouTube/Bunny في Blade. الـ API **لا يُرجع** حالياً **`embed_code`** ولا **`video_type`**. قد يحتاج التطبيق استنتاج نوع المصدر من `video_url` أو طلب توسيع الـ API لاحقاً.

---

## أنواع `module_type` شائعة

| `module_type` | ماذا تعرض في Flutter |
|---------------|----------------------|
| **`lesson`** | HTML في **`content.content`**. |
| **`video`** | **`content.video_url`** و/أو **`content.video_path`** (+ بناء URL كامل للمسار إن لزم). |
| **`resource`** | **`content.resource_url`** + **`content.display_mode`**. |
| **`quiz`**, **`assignment`**, **`question_module`** | نفس الهيكل الأساسي لـ `content`؛ تفاصيل الواجب/الاختبار قد لا تكون كاملة في هذا الـ endpoint حسب ما يُعرَّض في المتحكم. |

---

## ملخص سريع لمطوّر Flutter

1. المسار: **`data.courses[i].sections[j].lessons[k]`**.
2. نوع الدرس: **`module_type`**.
3. HTML للدرس النصي: **`lessons[k].content.content`** (string).
4. رابط WebView: **`lessons[k].content.resource_url`** عند `resource`، أو **`video_url`** للفيديو حسب المصدر.
5. **`lessons[k].content`**: إما `null` أو object بحقول ثابتة؛ غير المستخدم = `null`.

---

## مراجع ذات صلة

- [API_STUDENT_ROUTES.md](API_STUDENT_ROUTES.md) — جدول المسارات وقسم مختصر لـ `/api/student/courses`.
