<?php

namespace App\Services;

use App\Models\GroupRegistration;
use App\Models\GroupRegistrationSetting;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Illuminate\Support\Facades\Log;

class RegistrationWhatsAppService
{
    public function __construct(
        private SendWhatsAppMessage $whatsAppService
    ) {}

    /**
     * إرسال رسالة واتساب ترحيبية للتسجيل (للمجموعة)
     */
    public function sendWelcomeWhatsAppForGroup(GroupRegistration $registration): bool
    {
        try {
            if (!$registration->phone || !$registration->full_phone) {
                Log::warning('Cannot send WhatsApp: phone number missing', [
                    'registration_id' => $registration->id,
                ]);
                return false;
            }

            $group = $registration->group;
            $settings = GroupRegistrationSetting::where('group_id', $group->id)->first();

            // احترام خيار "إرسال رسالة واتساب ترحيبية" من إعدادات المجموعة
            if ($settings && !$settings->send_welcome_whatsapp) {
                return false;
            }

            // الحصول على القالب: أولاً قالب إعدادات المجموعة، ثم قالب محفوظ (slug welcome_group)، ثم الافتراضي
            $templateText = $settings && trim((string) ($settings->whatsapp_template ?? '')) !== ''
                ? $settings->whatsapp_template
                : null;

            if ($templateText !== null) {
                $message = str_replace('{{student_name}}', $registration->name_ar ?? $registration->name, $templateText);
                $message = str_replace('{{group_name}}', $group->name, $message);
                $message = str_replace('{{email}}', $registration->email ?? '', $message);
            } else {
                $messageTemplate = WhatsAppMessageTemplate::findBySlug('welcome_group');
                if ($messageTemplate) {
                    $message = $messageTemplate->render([
                        'student_name' => $registration->name_ar ?? $registration->name,
                        'group_name' => $group->name,
                        'email' => $registration->email ?? '',
                    ]);
                } else {
                    $default = $this->getDefaultWhatsAppTemplateForGroup();
                    $message = str_replace('{{student_name}}', $registration->name_ar ?? $registration->name, $default);
                    $message = str_replace('{{group_name}}', $group->name, $message);
                    $message = str_replace('{{email}}', $registration->email ?? '', $message);
                }
            }

            // إرسال الرسالة فوراً (بدون الاعتماد على قائمة الانتظار) حتى تصل للمستخدم مباشرة
            $phone = $registration->full_phone;
            if (strpos($phone, '+') !== 0) {
                $phone = '+' . $phone;
            }
            $this->whatsAppService->sendTextSync($phone, $message);

            // تحديث حالة الإرسال
            $registration->update([
                'whatsapp_sent' => true,
                'whatsapp_sent_at' => now(),
                'whatsapp_error' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send group registration WhatsApp', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            $registration->update([
                'whatsapp_sent' => false,
                'whatsapp_error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * الحصول على قالب واتساب افتراضي للمجموعة
     */
    private function getDefaultWhatsAppTemplateForGroup(): string
    {
        return "مرحباً {{student_name}} 👋\n\nشكراً لك على التسجيل في مجموعة {{group_name}}\n\nتم استلام طلب تسجيلك بنجاح وسيتم مراجعته قريباً.\n\nمع تحياتنا،\nفريق الإدارة";
    }
}
