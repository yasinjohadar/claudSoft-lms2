<?php

/**
 * Test n8n Webhook Integration
 *
 * شغل هذا الملف بعد إنشاء Endpoint في لوحة التحكم
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\N8nWebhookEvent;

echo "🚀 اختبار n8n Webhook Integration\n";
echo "=====================================\n\n";

// Test Data
$testData = [
    'student_id' => 123,
    'student_name' => 'أحمد محمد',
    'course_id' => 456,
    'course_name' => 'Laravel للمبتدئين',
    'enrolled_at' => now()->toDateTimeString(),
    'test_mode' => true,
];

echo "📤 إرسال webhook مع البيانات التالية:\n";
echo json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Trigger the event
event(new N8nWebhookEvent('student.enrolled', $testData));

echo "✅ تم إطلاق الحدث بنجاح!\n\n";
echo "الآن افتح:\n";
echo "1. لوحة التحكم: http://127.0.0.1:8000/admin/n8n/logs\n";
echo "2. webhook.site لترى البيانات المستلمة\n\n";

echo "💡 ملاحظة: تأكد من تشغيل Queue Worker:\n";
echo "   php artisan queue:work --queue=webhooks\n\n";

echo "انتظر 5-10 ثواني ثم تحقق من النتائج...\n";
