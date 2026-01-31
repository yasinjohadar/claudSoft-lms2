# WhatsApp Web Service - Node.js

هذا المشروع منفصل عن Laravel ويجب تشغيله بشكل مستقل لإدارة WhatsApp Web.

## المتطلبات

- Node.js 16+ 
- npm أو yarn

## التثبيت

```bash
cd whatsapp-web-service
npm install
```

## Dependencies المطلوبة

```json
{
  "whatsapp-web.js": "^1.23.0",
  "express": "^4.18.2",
  "qrcode": "^1.5.3",
  "cors": "^2.8.5",
  "dotenv": "^16.3.1"
}
```

## البنية المقترحة

```
whatsapp-web-service/
├── package.json
├── server.js          # Express server
├── whatsapp-client.js # WhatsApp Web client management
├── routes/
│   └── api.js         # API routes
├── config/
│   └── config.js      # Configuration
└── .env               # Environment variables
```

## API Endpoints المطلوبة

### POST /api/whatsapp/connect
بدء عملية الربط وإنشاء جلسة جديدة

**Request:**
```json
{
  "session_id": "session_xxx"
}
```

**Response:**
```json
{
  "success": true,
  "session_id": "session_xxx",
  "qr_code": "data:image/png;base64,..."
}
```

### GET /api/whatsapp/qr/:sessionId
الحصول على QR Code للجلسة

**Response:**
```json
{
  "success": true,
  "qr_code": "data:image/png;base64,...",
  "status": "connecting"
}
```

### GET /api/whatsapp/status/:sessionId
الحصول على حالة الاتصال

**Response:**
```json
{
  "success": true,
  "connected": true,
  "phone_number": "+1234567890",
  "name": "User Name"
}
```

### POST /api/whatsapp/send
إرسال رسالة

**Request:**
```json
{
  "to": "+1234567890",
  "message": "Hello",
  "type": "text"
}
```

**Response:**
```json
{
  "success": true,
  "message_id": "xxx"
}
```

### POST /api/whatsapp/disconnect/:sessionId
قطع الاتصال

**Response:**
```json
{
  "success": true,
  "message": "Disconnected"
}
```

## Authentication

يجب حماية جميع endpoints بـ Bearer token:

```
Authorization: Bearer YOUR_API_TOKEN
```

## التشغيل

```bash
# Development
npm run dev

# Production (using PM2)
pm2 start server.js --name whatsapp-web-service
```

## Chrome / Puppeteer على السيرفر (Linux)

خدمة WhatsApp Web تعتمد على Puppeteer وتحتاج متصفح Chrome (أو Chromium). إذا ظهر خطأ مثل **"Could not find Chrome"**:

### 1. التأكد من مسار Chrome الصحيح

المسار `/bin/google-chrome` **غالباً خاطئ** على Linux. جرّب:

```bash
# ابحث عن مسار Chrome أو Chromium على السيرفر
which google-chrome
which google-chrome-stable
which chromium
which chromium-browser
```

المسارات الشائعة:
- `/usr/bin/google-chrome` أو `/usr/bin/google-chrome-stable`
- `/usr/bin/chromium` أو `/usr/bin/chromium-browser`

### 2. تعديل إعداد Puppeteer في الكود

في ملف إعداد العميل (مثل `whatsapp-client.js` أو الملف الذي فيه `puppeteer: { executablePath: ... }`):

**خيار أ:** استخدام المسار الصحيح بعد التأكد منه:
```javascript
puppeteer: {
  executablePath: '/usr/bin/google-chrome-stable',  // أو المسار من which
  headless: true,
  args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', ...]
}
```

**خيار ب:** إزالة `executablePath` واستخدام متصفح Puppeteer المثبت:
```bash
cd whatsapp-web-service
npx puppeteer browsers install chrome
```
ثم في الكود لا تضبط `executablePath` (اترك Puppeteer يستخدم المتصفح من `~/.cache/puppeteer`).

### 3. تثبيت Chrome/Chromium على السيرفر (إن لم يكن مثبتاً)

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install -y chromium-browser
# أو
sudo apt install -y google-chrome-stable

# بعد التثبيت تحقق من المسار
which chromium-browser
# أو
which google-chrome-stable
```

ثم ضبط `executablePath` في الكود على هذا المسار.

## ملاحظات

- يجب حفظ الجلسات بشكل آمن
- يجب إدارة إعادة الاتصال التلقائي
- يجب معالجة الأخطاء والانقطاعات بشكل صحيح
- يجب استخدام `whatsapp-web.js` أو `Baileys` لإدارة WhatsApp Web

