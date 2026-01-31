# توصيات للذكاء الاصطناعي / المطوّر: تحديث برنامج ربط WhatsApp Web

هذا الملف موجه للذكاء الاصطناعي (Cursor أو غيره) أو للمطوّر عند تعديل أو تحديث **خدمة ربط واتساب ويب** (Node.js). الربط يعمل بشكل صحيح؛ الأخطاء التي تظهر أحياناً (مثل `markedUnread`) تأتي من واجهة واتساب ويب أو المكتبة المستخدمة.

---

## 1. مكان تخزين السجلات (Logs) لتشخيص السبب

- **Laravel:** يتم تخزين أخطاء واتساب في:
  - `storage/logs/whatsapp-YYYY-MM-DD.log` (قناة `whatsapp`، سجلات يومية).
- عند ظهور خطأ من نوع **WhatsApp Web client** (مثل `markedUnread` أو `static.whatsapp.net`)، يكتب Laravel سطراً إضافياً بعنوان:
  - **`WhatsApp Web client error - full details for diagnosis`**
  - ويضم: `exception_class`, `exception_message`, `exception_trace`, `exception_code`.
- **للمطوّر:** ابحث في الملفات أعلاه عن `WhatsApp Web client error` أو `markedUnread` أو `exception_trace` لمعرفة السبب الكامل.

---

## 2. حل خطأ `markedUnread` (مهم جداً - يناير 2026)

### 2.1 وصف المشكلة

- **الخطأ:** `TypeError: Cannot read properties of undefined (reading 'markedUnread')`
- **المصدر:** GitHub Issue #5736 في مكتبة `whatsapp-web.js`
- **السبب:** واتساب قام بتحديث واجهة واتساب ويب، والمكتبة لم تُحدَّث بعد في npm لتتوافق مع التغيير.
- **الحالة:** الإصلاح موجود في الفرع الرئيسي (main branch) لكن **لم يُنشر كإصدار npm جديد بعد**.

### 2.2 الحل الأول: التثبيت من الفرع الرئيسي (الأسهل والموصى به)

في مجلد خدمة واتساب ويب (Node.js)، نفّذ الخطوات التالية:

**الخطوة 1:** تعديل `package.json` - استبدال سطر whatsapp-web.js:

```json
"whatsapp-web.js": "github:pedroslopez/whatsapp-web.js#main"
```

**الخطوة 2:** إعادة التثبيت:

```bash
npm install
```

**الخطوة 3 (اختياري لكن موصى به):** إضافة متغيرات بيئية في `.env`:

```env
WEB_VERSION=2.3000.1031980585
WEB_VERSION_CACHE_TYPE=none
```

**الخطوة 4:** إعادة تشغيل خدمة Node.js:

```bash
# إذا كنت تستخدم PM2:
pm2 restart whatsapp-web-service

# أو إعادة التشغيل العادية:
npm start
```

**الخطوة 5:** إعادة ربط واتساب ويب (مسح QR من جديد) من لوحة تحكم Laravel.

### 2.3 الحل الثاني: تعديل يدوي لملف Utils.js (إذا لم يعمل الحل الأول)

**الملف المطلوب تعديله:**
```
node_modules/whatsapp-web.js/src/util/Injected/Utils.js
```

**البحث عن:**
```javascript
window.WWebJS.sendSeen = async (chatId) => {
```

**استبدالها بالكود التالي بالكامل:**

```javascript
window.WWebJS.sendSeen = async (chatId) => {
    const chat = await window.WWebJS.getChat(chatId, { getAsModel: false });
    if (!chat) return false;
    const isChannel = window.Store.ChatGetters.getIsNewsletter(chat);
    const isStatus = window.Store.ChatGetters.getIsBroadcast(chat);
    const canUseSendSeen = typeof chat.markedUnread !== 'undefined';
    try {
        window.Store.WAWebStreamModel.Stream.markAvailable();
        if (canUseSendSeen && window.Store.SendSeen.sendSeen && !isChannel && !isStatus) {
            await window.Store.SendSeen.sendSeen(chat);
        } else if (window.Store.SendSeen.markSeen) {
            // fallback آمن
            await window.Store.SendSeen.markSeen(chat);
        } else {
            return false;
        }
        return true;
    } catch (err) {
        // fallback أخير (منع الانهيار)
        try {
            if (window.Store.SendSeen.markSeen) {
                await window.Store.SendSeen.markSeen(chat);
                return true;
            }
        } catch (_) {}
        return false;
    } finally {
        window.Store.WAWebStreamModel.Stream.markUnavailable();
    }
};
```

**بعد التعديل:** إعادة تشغيل خدمة Node.js.

**ملاحظة مهمة:** هذا التعديل اليدوي سيُفقد عند تشغيل `npm install` مرة أخرى. لذا الحل الأول (التثبيت من main branch) أفضل.

### 2.4 مرجع المشكلة

- **GitHub Issue:** https://github.com/pedroslopez/whatsapp-web.js/issues/5736
- **Pull Requests للإصلاح:** #5737, #5742, #5729, #5719
- **تاريخ الإبلاغ:** 20 يناير 2026

---

## 3. توصيات عامة لتحديث أو إصلاح خدمة ربط واتساب ويب (Node.js)

عند طلب المستخدم "تحديث برنامج ربط الواتساب" أو "إصلاح أخطاء واتساب ويب"، نفّذ التوصيات التالية حسب الحاجة.

### 3.1 تحديث المكتبات

- **ما الذي يجب تحديثه:**
  - مكتبة **whatsapp-web.js** (أو البديل المستخدم مثل Baileys) إلى أحدث إصدار مستقر.
  - باقي تبعيات المشروع: `express`, `qrcode`, `cors`, `dotenv`, `puppeteer`, إلخ.
- **كيف:**
  - في مجلد خدمة واتساب (مثلاً `whatsapp-web-service`):
    - `npm outdated` لمعرفة الإصدارات المتأخرة.
    - `npm update` أو تحديث `package.json` ثم `npm install`.
  - اقرأ **CHANGELOG** أو **Breaking Changes** لمكتبة واتساب قبل التحديث الكبير.
- **لماذا:** أخطاء مثل `Cannot read properties of undefined (reading 'markedUnread')` غالباً من سكربت واتساب ويب أو من تعامل المكتبة مع واجهة واتساب؛ التحديثات غالباً تحتوي إصلاحات أو توافقاً أفضل.

### 3.2 معالجة أخطاء واتساب ويب قبل إرجاعها إلى Laravel

- **المطلوب:** في نقطة إرسال الرسالة (مثلاً `POST /api/whatsapp/send`)، لا تُرجع نص الخطأ الخام من واتساب (مثل stack من `static.whatsapp.net`) كما هو للمستخدم.
- **ما الذي يجب فعله:**
  - استخدم `try/catch` حول استدعاء إرسال الرسالة.
  - إذا كان الخطأ يحتوي على `markedUnread` أو `undefined` أو `static.whatsapp.net`:
    - سجّل الخطأ الكامل في سجلات Node (مثلاً `console.error` أو Winston/Pino) مع السياق (رقم المستلم، نوع الرسالة، إلخ).
    - أرجِع للمستخدم (Laravel) رسالة خطأ **موحدة وقصيرة**، مثلاً:
      - `Failed to send message: WhatsApp Web client error. Please try again or re-scan QR.`
  - يمكن تعريف كود خطأ ثابت (مثلاً `WHATSAPP_CLIENT_ERROR`) لتمييز هذا النوع في Laravel إن رغبت.

### 3.3 التحقق من الجلسة والربط

- **عند فشل الإرسال المتكرر:**
  - التحقق من أن الجلسة (session) لا تزال متصلة قبل إرسال كل رسالة إن أمكن.
  - إذا كانت المكتبة تدعم "حدث انقطاع" أو "حدث إعادة اتصال"، استخدمها لتحديث حالة الجلسة في قاعدة البيانات أو في الذاكرة.
- **إعادة الربط (إعادة مسح QR):**
  - إذا استمر الخطأ بعد التحديثات، أوصِ المستخدم بإعادة ربط واتساب ويب (مسح QR من جديد) لأن واتساب يغيّر واجهته أحياناً وتصبح الجلسة القديمة غير متوافقة.

### 3.4 إصدارات مقترحة (مرجعية)

- **ما هو موثّق في المشروع حالياً:**
  - `whatsapp-web.js`: `^1.23.0` (راجع `whatsapp-web-service-README.md`).
- **توصية حالية (يناير 2026):** استخدام الفرع الرئيسي مباشرة:
  ```json
  "whatsapp-web.js": "github:pedroslopez/whatsapp-web.js#main"
  ```
- **بديل:** إن استمرت المشاكل، يمكن تقييم استخدام **Baileys** كبديل (مذكور في README).

### 3.5 هيكل الخدمة والـ API

- الـ API المتوقع من Laravel موثّق في:
  - `whatsapp-web-service-README.md` (مثلاً: `POST /api/whatsapp/send` مع Bearer token).
- عند التعديل على الـ endpoints أو شكل الاستجابة، يجب تحديث:
  - خدمة Node.js.
  - كود Laravel الذي يستدعيها (مثلاً `WhatsAppWebProvider` في `app/Services/WhatsApp/Providers/WhatsAppWebProvider.php`) حتى يبقى التوافق مع الـ request/response الحالي.

---

## 4. ملخص سريع للذكاء الاصطناعي

عند طلب المستخدم تحديث أو إصلاح "برنامج ربط الواتساب" أو "خدمة واتساب ويب":

### إذا كان الخطأ `markedUnread`:

1. **الحل السريع:** تعديل `package.json` لتثبيت من الفرع الرئيسي:
   ```json
   "whatsapp-web.js": "github:pedroslopez/whatsapp-web.js#main"
   ```
2. تشغيل `npm install`
3. إضافة متغيرات بيئية (اختياري):
   ```env
   WEB_VERSION=2.3000.1031980585
   WEB_VERSION_CACHE_TYPE=none
   ```
4. إعادة تشغيل خدمة Node.js
5. إعادة ربط واتساب ويب (مسح QR جديد)

### إذا كان خطأ آخر:

1. **قراءة السجلات:** استخدم `storage/logs/whatsapp-*.log` وابحث عن `WhatsApp Web client error` أو `exception_trace` لمعرفة السبب.
2. **تحديث المكتبات:** تحديث `whatsapp-web.js` (والباقي) في مشروع الخدمة (مثلاً `whatsapp-web-service`)، مع مراجعة CHANGELOG.
3. **معالجة الأخطاء في Node:** التقاط أخطاء واتساب ويب، تسجيل التفاصيل محلياً، وإرجاع رسالة خطأ موحدة للمستخدم بدل النص الخام.
4. **التحقق من الجلسة والربط:** التأكد من أن الجلسة متصلة، واقتراح إعادة مسح QR عند استمرار المشكلة.
5. **عدم كسر الـ API:** الحفاظ على نفس شكل استدعاء Laravel للـ Node (نفس الـ endpoints والـ request/response) أو تحديث كلا الطرفين معاً.

---

## 5. روابط مرجعية

- **GitHub Repository:** https://github.com/pedroslopez/whatsapp-web.js
- **Issue #5736 (markedUnread):** https://github.com/pedroslopez/whatsapp-web.js/issues/5736
- **Documentation:** https://docs.wwebjs.dev/
- **بديل Baileys:** https://github.com/WhiskeySockets/Baileys

---

*آخر تحديث للملف: 31 يناير 2026 - إضافة حل خطأ markedUnread من GitHub Issue #5736 مع خطوات تفصيلية.*
