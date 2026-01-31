# تعليمات للذكاء الاصطناعي: إعداد الواتساب ويب والرد التلقائي

هذا الملف موجّه للوكيل/الذكاء الاصطناعي لتنفيذ إعداد الواتساب ويب (بدون ميتا) وتوكن API والرد التلقائي. نفّذ الخطوات بالترتيب عند طلب المستخدم.

---

## 1. مصدر توكن الواتساب ويب (API Token)

- **لا يوجد توكن من ميتا.** عند استخدام مزود "WhatsApp Web (QR Code)" لا يُستخدم أي توكن من Meta.
- **التوكن يُنشئه المستخدم بنفسه:** سلسلة سرية (مثل كلمة مرور) يضعها في مكانين حتى يتطابقان:
  1. ملف `.env` داخل مشروع خدمة الواتساب ويب (Node.js).
  2. إعدادات الواتساب في Laravel (حقل "API Token" أو "توكن الواتساب ويب").

عند مساعدة المستخدم:
- أوضح أن التوكن **ليس من رابط ولا من ميتا**، بل يختاره المستخدم (أو تولده أنت) ويُنسخ إلى المكانين أعلاه.
- إذا طلب المستخدم توليد توكن: أنشئ سلسلة عشوائية طويلة (مثل 32 حرفاً) واكتبها في التعليمات للمكانين.

---

## 2. إعداد التوكن في خدمة الواتساب ويب (Node.js)

- المسار المتوقع لمشروع الخدمة: مجلد مثل `whatsapp-web-service` (قد يكون داخل المشروع أو في مسار آخر يحدده المستخدم).
- الملف المطلوب: `.env` داخل ذلك المجلد.
- إذا لم يكن الملف موجوداً: انسخ من `env.example` إلى `.env` (إن وُجد)، أو أنشئ `.env`.
- داخل `.env` تأكد من وجود سطر بالشكل:
  ```env
  API_TOKEN=قيمة_التوكن_هنا
  ```
- استبدل `قيمة_التوكن_هنا` بنفس القيمة التي ستُكتب لاحقاً في Laravel.
- إذا طلب المستخدم عدم التعرض للأمان يمكن ترك قيمة بسيطة للتطوير فقط، مع تنبيهه أن في الإنتاج يجب استخدام توكن قوي.

---

## 3. إعداد التوكن في Laravel

- الإعدادات مخزنة في قاعدة البيانات عبر `SystemSetting` (مجموعة `whatsapp`).
- المفتاح في الإعدادات: `whatsapp_web_api_token`.
- واجهة الإعدادات:
  - إما من **إعدادات WhatsApp** العامة: بعد اختيار المزود "WhatsApp Web (QR Code)" تظهر حقول إعدادات الواتساب ويب.
  - أو من صفحة **إعدادات WhatsApp Web** المخصصة إن وُجدت في القائمة.
- في النموذج:
  - **رابط خدمة Node.js:** الحقل `whatsapp_web_service_url` (مثال: `http://localhost:3000`).
  - **API Token / توكن الواتساب ويب:** الحقل `whatsapp_web_api_token` — يجب أن تكون قيمته **نفس** قيمة `API_TOKEN` في ملف `.env` لخدمة Node.
- الخدمة التي تقرأ/تكتب الإعدادات: `App\Services\WhatsApp\WhatsAppSettingsService` (مثلاً `getSettings()` و `updateSettings()`). التوكن يُشفّر عند الحفظ في الحقول الحساسة.

عند التعديل برمجياً (إن طُلب):
- استخدم `WhatsAppSettingsService::updateSettings(['whatsapp_web_api_token' => 'نفس_التوكن'])` مع التأكد أن القيمة مطابقة لـ `API_TOKEN` في Node.

---

## 4. تشغيل الرد التلقائي (Auto-Reply)

حتى يعمل الرد التلقائي عند استقبال رسالة على الواتساب ويب:

1. **تفعيل الرد التلقائي في الواجهة:**
   - في إعدادات WhatsApp: تفعيل "تفعيل الرد التلقائي" (`auto_reply`).
   - (اختياري) تفعيل "استخدام الذكاء الاصطناعي للرد التلقائي" (`auto_reply_use_ai`) واختيار موديل ورسالة نظام إن رغب المستخدم.

2. **تشغيل Queue Worker (مهم):**
   - المستمع `App\Listeners\AutoReplyWhatsAppListener` يعمل كـ Job في الطابور (`ShouldQueue`).
   - يجب تشغيل عامل الطابور **على نفس الجهاز/السيرفر الذي يشغّل عليه Laravel** (من جذر مشروع Laravel):
     ```bash
     php artisan queue:work
     ```
   - من داخل المشروع يمكن استخدام السكربتات الجاهزة:
     - **Windows:** من جذر المشروع: `scripts\queue-work.bat` (أو نقر مزدوج بعد فتح المسار في CMD).
     - **Linux/Mac:** من جذر المشروع: `./scripts/queue-work.sh` أو `bash scripts/queue-work.sh`.
   - أو استخدام Supervisor (أو ما يعادله) على السيرفر لتشغيل `queue:work` بشكل دائم.

3. **استقبال الأحداث من خدمة الواتساب ويب:**
   - خدمة Node.js يجب أن ترسل أحداث الرسائل الواردة إلى Laravel على مسار الـ webhook الخاص بالواتساب ويب:
     ```
     POST /api/webhooks/whatsapp-web/incoming
     ```
   - **الرابط الكامل مثلاً:**
     ```
     http://127.0.0.1:8000/api/webhooks/whatsapp-web/incoming
     ```
   - يجب إرسال التوكن في الـ header:
     ```
     Authorization: Bearer YOUR_API_TOKEN
     ```
   - صيغة البيانات المتوقعة من Node.js:
     ```json
     {
       "event": "message",
       "from": "966501234567@c.us",
       "body": "نص الرسالة",
       "type": "chat",
       "timestamp": 1706600000,
       "messageId": "true_966501234567@c.us_ABC123",
       "notifyName": "اسم المرسل",
       "isGroup": false
     }
     ```
   - في خدمة Node.js (whatsapp-web.js)، أضف في ملف `.env`:
     ```env
     LARAVEL_WEBHOOK_URL=http://127.0.0.1:8000/api/webhooks/whatsapp-web/incoming
     ```
   - وفي كود Node.js عند استقبال رسالة، أرسلها إلى Laravel:
     ```javascript
     client.on('message', async (msg) => {
       await axios.post(process.env.LARAVEL_WEBHOOK_URL, {
         event: 'message',
         from: msg.from,
         body: msg.body,
         type: msg.type,
         timestamp: msg.timestamp,
         messageId: msg.id._serialized,
         notifyName: msg._data.notifyName,
         isGroup: msg.from.endsWith('@g.us')
       }, {
         headers: { 'Authorization': 'Bearer ' + process.env.API_TOKEN }
       });
     });
     ```

عند مساعدة المستخدم في "لم يعمل الرد":
- تحقق من: تشغيل `queue:work`، تطابق التوكن بين Node و Laravel، تفعيل الرد التلقائي، وأن خدمة Node ترسل الأحداث إلى webhook Laravel.

---

## 5. Laravel أونلاين وخدمة الواتساب محلياً (على جهازك)

عندما يكون **مشروع Laravel مستضافاً أونلاين** (على سيرفر له عنوان عام مثل `https://myapp.com`) وتريد تشغيل **مشروع الواتساب (Node.js) محلياً** على جهازك:

- **المشكلة:** السيرفر الأونلاين لا يستطيع الوصول إلى `localhost` أو `127.0.0.1` على جهازك؛ أي طلب من Laravel إلى `http://localhost:3000` سيذهب إلى السيرفر نفسه وليس إلى جهازك.
- **الحل:** جعل خدمة الواتساب المحلية **قابلة للوصول من الإنترنت** عبر نفق (tunnel)، ثم إعداد Laravel و Node لاستخدام العناوين العامة.

### الخطوات

1. **تشغيل خدمة الواتساب محلياً**  
   تشغيل مشروع Node.js كالعادة (مثلاً على المنفذ 3000).

2. **فتح نفق من جهازك إلى الإنترنت**  
   استخدام أحد الأدوات لتعريض المنفذ المحلي (مثلاً 3000) بعنوان عام:
   - **ngrok:** `ngrok http 3000` — يعطيك عنواناً مثل `https://abc123.ngrok-free.app`.
   - **Cloudflare Tunnel (cloudflared):** إن كان لديك حساب Cloudflare.
   - **localtunnel:** `npx localtunnel --port 3000`.

   المهم: الحصول على رابط **HTTPS** عام يوجه إلى `http://localhost:3000` على جهازك.

3. **إعداد Laravel (الأونلاين)**  
   في إعدادات WhatsApp في لوحة التحكم:
   - **رابط خدمة Node.js** (`whatsapp_web_service_url`): ضع **رابط النفق** (مثل `https://abc123.ngrok-free.app`) وليس `http://localhost:3000`.
   - **API Token:** كما هو، نفس القيمة الموضوعة في Node.

   بهذا يستطيع Laravel الأونلاين إرسال طلبات الإرسال إلى خدمة الواتساب على جهازك عبر النفق.

4. **إعداد مشروع الواتساب (Node.js) محلياً**  
   في ملف `.env` لمشروع الواتساب:
   - **LARAVEL_WEBHOOK_URL:** ضع **الرابط العام لـ Laravel** + مسار الـ webhook، مثلاً:
     ```env
     LARAVEL_WEBHOOK_URL=https://myapp.com/api/webhooks/whatsapp-web/incoming
     ```
   - **API_TOKEN:** نفس القيمة في Laravel.

   بهذا ترسل خدمة الواتساب المحلية الرسائل الواردة إلى موقعك الأونلاين.

5. **تشغيل مستمر للنفق**  
   طالما أن خدمة الواتساب تعمل على جهازك، يجب أن يبقى النفق (ngrok أو غيره) يعمل على نفس الجهاز؛ عند إيقاف النفق أو إعادة تشغيله يتغير الرابط (مع ngrok المجاني) وعليك تحديث **رابط خدمة Node.js** في إعدادات Laravel من جديد.

6. **Queue و الإرسال الجماعي**  
   عامل الطابور `php artisan queue:work` يعمل على **سيرفر Laravel الأونلاين**؛ لا حاجة لتشغيله على جهازك. الفواصل الزمنية والإرسال الجماعي يعملان كما في الحالة العادية.

### ملخص الربط

| الاتجاه | من | إلى | الإعداد |
|--------|-----|-----|--------|
| إرسال رسالة | Laravel (أونلاين) | Node (محلي) | في Laravel: رابط خدمة Node = **رابط النفق** (مثل `https://xxx.ngrok-free.app`) |
| رسائل واردة (webhook) | Node (محلي) | Laravel (أونلاين) | في Node `.env`: `LARAVEL_WEBHOOK_URL=https://myapp.com/api/webhooks/whatsapp-web/incoming` |

---

## 6. ملخص سريع للمرجع

| المطلوب | الموقع |
|--------|--------|
| إنشاء توكن (سلسلة سرية) | يختاره المستخدم أو يولده الوكيل |
| وضع التوكن في Node | ملف `.env` → `API_TOKEN=...` |
| وضع التوكن في Laravel | إعدادات WhatsApp → حقل API Token / `whatsapp_web_api_token` |
| تشغيل الرد التلقائي | تفعيل من الواجهة + تشغيل `php artisan queue:work` |
| الإرسال الجماعي | الفواصل من إعدادات واتساب؛ يجب تشغيل `queue:work` لتنفيذ الرسائل |
| استقبال الرسائل | خدمة Node ترسل أحداث الوارد إلى `POST /api/webhooks/whatsapp-web/incoming` |
| رابط الـ webhook | `http://your-laravel-url/api/webhooks/whatsapp-web/incoming` |
| Laravel أونلاين + Node محلي | نفق (ngrok أو غيره) لتعريض Node؛ في Laravel: رابط الخدمة = رابط النفق؛ في Node: LARAVEL_WEBHOOK_URL = رابط موقعك الأونلاين + مسار الـ webhook |
| تشغيل الطابور من المشروع | من جذر المشروع: `php artisan queue:work` أو `scripts\queue-work.bat` (Windows) أو `scripts/queue-work.sh` (Linux/Mac) |

---

## 7. أين يتم تشغيل كل مكون

| المكون | أين يُشغَّل | ملاحظة |
|--------|-------------|--------|
| **Queue Worker** (`queue:work`) | على **نفس الجهاز/السيرفر الذي يشغّل عليه Laravel** (مشروعك Laravel) | من جذر المشروع: `php artisan queue:work` أو السكربتات في `scripts/` (مثلاً `scripts\queue-work.bat` على Windows). مطلوب للرد التلقائي والإرسال الجماعي. |
| **خدمة الواتساب (Node.js)** | على الجهاز الذي تريده (محلي أو سيرفر) | إن كان محلياً وLaravel أونلاين، تحتاج نفق (ngrok) لتعريض Node. |
| **النفق (ngrok / localtunnel)** | على **نفس الجهاز الذي يشغّل عليه Node.js** | لربط Laravel أونلاين مع Node محلي؛ لا يُشغَّل داخل مشروع Laravel. |

---

## 8. تشغيل الطابور أونلاين على Linux (لا يتوقف بعد التحديث)

عند تشغيل الطابور من زر "تشغيل" في الإعدادات أو من الطرفية يدوياً، قد **يتوقف بعد تحديث الصفحة أو إغلاق الطرفية**. على سيرفر Linux أونلاين، استخدم **Supervisor** ليشغّل عامل الطابور بشكل دائم:

1. **تثبيت Supervisor:** `sudo apt install supervisor` (Ubuntu/Debian).
2. **نسخ إعداد الطابور:** يوجد في المشروع ملف مثال `deploy/supervisor-queue-worker.conf.example` — انسخه إلى `/etc/supervisor/conf.d/` (مثلاً `laravel-queue-worker.conf`).
3. **تعديل المسارات:** عدّل داخل الملف مسار المشروع (`command`)، المستخدم (`user`)، ومسار السجل (`stdout_logfile`) ليتوافقا مع سيرفرك.
4. **تفعيل:** `sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start laravel-queue-worker`.

بعد ذلك يعمل الطابور أونلاين ولا يتوقف بعد التحديث أو إعادة تشغيل السيرفر. للتفاصيل انظر ملف المشروع `QUEUE_WORKER_LINUX.md`.

---

*هذا الملف للاستخدام من قبل الوكيل/الذكاء الاصطناعي عند تنفيذ أو شرح إعداد الواتساب ويب وتوكن API والرد التلقائي.*
