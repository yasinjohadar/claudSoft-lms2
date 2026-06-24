<?php

namespace App\Services;

use App\Models\GroupRegistration;
use App\Models\GroupRegistrationSetting;
use App\Models\WapiTemplate;
use App\Models\WhatsAppMessageTemplate;
use App\Services\Flaxxa\FlaxxaTemplateVariableResolver;
use App\Services\Flaxxa\WapiAutomationService;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Support\WapiPhoneNormalizer;
use App\Support\WapiTemplatePayloadBuilder;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class RegistrationWhatsAppService
{
    public function __construct(
        private SendWhatsAppMessage $whatsAppService,
        private WapiAutomationService $wapiAutomation,
        private WapiOutboundDispatcher $wapiDispatcher,
        private WhatsAppService $flaxxaService,
        private FlaxxaTemplateVariableResolver $variableResolver
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

            if ($settings && ! $settings->send_welcome_whatsapp) {
                return false;
            }

            if ($settings?->usesFlaxxaTemplate()) {
                $this->sendViaFlaxxaTemplate($registration, $settings, $group);
                $this->markSent($registration);

                return true;
            }

            // قالب Flaxxa عبر أتمتة الأحداث (عند عدم تحديد قالب per-group)
            if ($this->wapiAutomation->isTokenConfigured()
                && $this->wapiAutomation->dispatchForGroupRegistration($registration)) {
                $this->markSent($registration);

                return true;
            }

            $replacements = $this->registrationPlaceholders($registration, $group);

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

            $phone = $registration->full_phone;
            if (strpos($phone, '+') !== 0) {
                $phone = '+'.$phone;
            }
            $this->whatsAppService->sendTextSync($phone, $message);
            $this->markSent($registration);

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

    private function sendViaFlaxxaTemplate(
        GroupRegistration $registration,
        GroupRegistrationSetting $settings,
        $group
    ): void {
        $this->flaxxaService->assertConfigured();

        $template = WapiTemplate::query()->find($settings->wapi_template_id);
        if (! $template) {
            throw new InvalidArgumentException('قالب Flaxxa المحدّد غير موجود.');
        }

        $phone = preg_replace('/\s+/', '', (string) $registration->full_phone);
        $phone = WapiPhoneNormalizer::normalize($phone);
        if (! WapiPhoneNormalizer::isValidE164Digits($phone)) {
            throw new InvalidArgumentException('رقم واتساب غير صالح للتسجيل.');
        }

        $placeholders = $this->registrationPlaceholders($registration, $group);
        $bodyVars = $settings->wapi_body_variables;
        if (! is_array($bodyVars) || $bodyVars === []) {
            $bodyVars = ['{student_name}', '{group_name}'];
        }

        [, $resolvedBody] = $this->variableResolver->resolveArraysWithoutUser([], $bodyVars, $placeholders);

        $language = trim((string) ($settings->wapi_template_language ?? ''));
        if ($language === '') {
            $language = (string) ($template->language ?? 'ar');
        }

        $components = WapiTemplatePayloadBuilder::cloudApiComponentsFromVariables([], $resolvedBody);

        $this->wapiDispatcher->queueTemplate(
            phone: $phone,
            templateName: $template->name,
            language: $language,
            components: $components,
            attachmentStoragePath: null,
            wapiTemplateId: $template->id,
            variablesLog: [
                'group_registration_id' => $registration->id,
                'group_id' => $group->id,
                'delivery' => 'group_registration_flaxxa',
            ],
        );
    }

    /**
     * @return array<string, string>
     */
    private function registrationPlaceholders(GroupRegistration $registration, $group): array
    {
        return [
            'student_name' => (string) ($registration->name_ar ?? $registration->name),
            'group_name' => (string) $group->name,
            'email' => (string) ($registration->email ?? ''),
            'registration_id' => (string) $registration->id,
        ];
    }

    private function markSent(GroupRegistration $registration): void
    {
        $registration->update([
            'whatsapp_sent' => true,
            'whatsapp_sent_at' => now(),
            'whatsapp_error' => null,
        ]);
    }

    private function getDefaultWhatsAppTemplateForGroup(): string
    {
        return "مرحباً {{student_name}} 👋\n\nشكراً لك على التسجيل في مجموعة {{group_name}}\n\nتم استلام طلب تسجيلك بنجاح وسيتم مراجعته قريباً.\n\nمع تحياتنا،\nفريق الإدارة";
    }
}
