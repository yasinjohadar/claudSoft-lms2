<?php

namespace App\Services;

use App\Models\GroupRegistration;
use App\Models\GroupRegistrationSetting;
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

            // الحصول على القالب
            $template = $settings && $settings->whatsapp_template 
                ? $settings->whatsapp_template 
                : $this->getDefaultWhatsAppTemplateForGroup();

            // استبدال المتغيرات
            $message = str_replace('{{student_name}}', $registration->name_ar ?? $registration->name, $template);
            $message = str_replace('{{group_name}}', $group->name, $message);
            $message = str_replace('{{email}}', $registration->email, $message);

            // إرسال الرسالة
            $result = $this->whatsAppService->sendText($registration->full_phone, $message);

            if ($result) {
                // تحديث حالة الإرسال
                $registration->update([
                    'whatsapp_sent' => true,
                    'whatsapp_sent_at' => now(),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send group registration WhatsApp', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
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
