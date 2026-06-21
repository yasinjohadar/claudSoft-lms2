<?php

namespace App\Services;

use App\Models\GroupRegistration;
use App\Models\GroupRegistrationSetting;
use App\Models\WhatsAppMessageTemplate;
use App\Services\Flaxxa\WapiAutomationService;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Illuminate\Support\Facades\Log;

class RegistrationWhatsAppService
{
    public function __construct(
        private SendWhatsAppMessage $whatsAppService,
        private WapiAutomationService $wapiAutomation
    ) {}

    /**
     * إرسال رسالة واتساب ترحيبية للتسجيل (للمجموعة)
     */
    public function sendWelcomeWhatsAppForGroup(GroupRegistration $registration): bool
    {
        try {
            if (! $registration->phone || ! $registration->full_phone) {
                Log::warning('Cannot send WhatsApp: phone number missing', [
                    'registration_id' => $registration->id,
                ]);

                return false;
            }

            $group = $registration->group;
            $settings = GroupRegistrationSetting::where('group_id', $group->id)->first();

            // احترام خيار "إرسال رسالة واتساب ترحيبية" من إعدادات المجموعة
            if ($settings && ! $settings->send_welcome_whatsapp) {
                return false;
            }

            // قالب Flaxxa المعتمد (Meta) عند وجود توكن وقاعدة أتمتة نشطة لـ group.registration.submitted
            if ($this->wapiAutomation->isTokenConfigured()
                && $this->wapiAutomation->dispatchForGroupRegistration($registration)) {
                $registration->update([
                    'whatsapp_sent' => true,
                    'whatsapp_sent_at' => now(),
                    'whatsapp_error' => null,
                ]);

                return true;
            }

            $replacements = [
                'student_name' => $registration->name_ar ?? $registration->name,
                'group_name' => $group->name,
                'email' => $registration->email ?? '',
            ];

            // الحصول على القالب: أولاً القالب المحدد في الإعدادات، ثم النص القديم، ثم welcome_group، ثم الافتراضي
            $messageTemplate = null;
            if ($settings?->whatsapp_template_id) {
                $messageTemplate = WhatsAppMessageTemplate::active()
                    ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
                    ->find($settings->whatsapp_template_id);
            }

            if ($messageTemplate) {
                $message = $messageTemplate->render($replacements);
            } elseif ($settings && trim((string) ($settings->whatsapp_template ?? '')) !== '') {
                $message = str_replace(
                    ['{{student_name}}', '{student_name}', '{{group_name}}', '{group_name}', '{{email}}', '{email}'],
                    [$replacements['student_name'], $replacements['student_name'], $replacements['group_name'], $replacements['group_name'], $replacements['email'], $replacements['email']],
                    $settings->whatsapp_template
                );
            } else {
                $messageTemplate = WhatsAppMessageTemplate::findBySlug('welcome_group');
                if ($messageTemplate) {
                    $message = $messageTemplate->render($replacements);
                } else {
                    $message = str_replace(
                        ['{{student_name}}', '{{group_name}}', '{{email}}'],
                        [$replacements['student_name'], $replacements['group_name'], $replacements['email']],
                        $this->getDefaultWhatsAppTemplateForGroup()
                    );
                }
            }

            // إرسال الرسالة فوراً (بدون الاعتماد على قائمة الانتظار) حتى تصل للمستخدم مباشرة
            $phone = $registration->full_phone;
            if (strpos($phone, '+') !== 0) {
                $phone = '+'.$phone;
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
