<?php

namespace App\Services\Auth;

use App\Models\WapiTemplate;
use App\Services\WapiOutboundDispatcher;
use App\Services\WhatsAppService;
use InvalidArgumentException;

class PhoneOtpWhatsAppSender
{
    public function __construct(
        private PhoneOtpSettingsService $settingsService,
        private WapiOutboundDispatcher $dispatcher,
        private WhatsAppService $whatsAppService
    ) {}

    public function isAvailable(): bool
    {
        if (! $this->settingsService->isEnabled()) {
            return false;
        }

        try {
            $this->whatsAppService->assertConfigured();

            return $this->resolveTemplate() !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    public function send(string $phone, string $code): void
    {
        $settings = $this->settingsService->getSettings();
        $template = $this->resolveTemplate();

        if ($template === null) {
            throw new InvalidArgumentException('لم يُعرّف قالب Flaxxa لرسائل OTP. اختر قالباً من إعدادات OTP.');
        }

        $language = (string) ($settings['template_language'] ?? 'ar');
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $code],
                ],
            ],
        ];

        $this->dispatcher->queueTemplate(
            phone: $phone,
            templateName: $template->name,
            language: $language,
            components: $components,
            attachmentStoragePath: null,
            wapiTemplateId: $template->id,
            variablesLog: ['otp_code' => '***', 'purpose' => 'phone_otp'],
        );
    }

    private function resolveTemplate(): ?WapiTemplate
    {
        $settings = $this->settingsService->getSettings();
        $templateId = $settings['wapi_template_id'] ?? null;

        if ($templateId) {
            return WapiTemplate::query()->find($templateId);
        }

        return WapiTemplate::query()
            ->where('name', 'like', '%otp%')
            ->orWhere('name', 'like', '%verification%')
            ->orWhere('name', 'like', '%auth%')
            ->orderBy('id')
            ->first();
    }
}
