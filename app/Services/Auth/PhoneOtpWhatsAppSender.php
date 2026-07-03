<?php

namespace App\Services\Auth;

use App\Models\WapiTemplate;
use App\Services\WapiOutboundDispatcher;
use App\Services\WhatsAppService;
use App\Support\WapiTemplatePayloadBuilder;
use InvalidArgumentException;

class PhoneOtpWhatsAppSender
{
    public function __construct(
        private PhoneOtpSettingsService $settingsService,
        private WapiOutboundDispatcher $dispatcher,
        private WhatsAppService $whatsAppService,
        private PhoneOtpEvolutionSender $evolutionSender,
    ) {}

    public function isAvailable(): bool
    {
        if (! $this->settingsService->isEnabled()) {
            return false;
        }

        return $this->isChannelAvailable($this->deliveryChannel());
    }

    public function send(string $phone, string $code): void
    {
        if ($this->deliveryChannel() === 'evolution') {
            $this->evolutionSender->send($phone, $code);

            return;
        }

        $this->sendViaFlaxxa($phone, $code);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildHealthReport(): array
    {
        $settings = $this->settingsService->getSettings();
        $channel = $this->deliveryChannel();
        $template = $this->resolveTemplate();
        $tokenConfigured = false;

        try {
            $this->whatsAppService->assertConfigured();
            $tokenConfigured = true;
        } catch (\Throwable) {
            $tokenConfigured = false;
        }

        $structure = is_array($template?->structure) ? $template->structure : [];
        $templateIssues = $channel === 'flaxxa'
            ? ($template ? $this->validateTemplateForOtp($template) : ['لم يُختر قالب OTP.'])
            : $this->evolutionSender->availabilityIssues();

        $channelReady = $channel === 'evolution'
            ? $this->evolutionSender->isAvailable()
            : $tokenConfigured && $template !== null && $templateIssues === [];

        return [
            'delivery_channel' => $channel,
            'token_configured' => $tokenConfigured,
            'otp_enabled' => (bool) ($settings['enabled'] ?? false),
            'template_selected' => $channel === 'flaxxa' ? $template !== null : true,
            'template_name' => $channel === 'flaxxa' ? $template?->name : 'Evolution (نص)',
            'template_language' => $channel === 'flaxxa' ? $template?->language : null,
            'template_status' => $channel === 'flaxxa' ? ($structure['status'] ?? null) : null,
            'header_placeholders' => $channel === 'flaxxa' ? (int) ($structure['header_placeholders'] ?? 0) : 0,
            'body_placeholders' => $channel === 'flaxxa' ? (int) ($structure['body_placeholders'] ?? 0) : 0,
            'has_media_header' => $channel === 'flaxxa' ? (bool) ($structure['has_media_header'] ?? false) : false,
            'template_issues' => $templateIssues,
            'queue_async' => $channel === 'flaxxa' && config('queue.default') !== 'sync',
            'ready' => ($settings['enabled'] ?? false) && $channelReady,
        ];
    }

    /**
     * @return list<string>
     */
    public function validateTemplateForOtp(WapiTemplate $template): array
    {
        $structure = is_array($template->structure) ? $template->structure : [];
        $issues = [];

        if (($structure['has_media_header'] ?? false) === true) {
            $issues[] = 'القالب يحتوي على header وسائط (صورة/فيديو) ولا يدعمه مسار OTP.';
        }

        $status = strtoupper((string) ($structure['status'] ?? ''));
        if ($status !== '' && $status !== 'APPROVED') {
            $issues[] = 'حالة القالب ليست APPROVED.';
        }

        $headerCount = (int) ($structure['header_placeholders'] ?? 0);
        $bodyCount = (int) ($structure['body_placeholders'] ?? 0);

        if ($headerCount === 0 && $bodyCount === 0) {
            $issues[] = 'القالب لا يحتوي على placeholders لإدراج رمز OTP.';
        }

        return $issues;
    }

    private function deliveryChannel(): string
    {
        $settings = $this->settingsService->getSettings();
        $channel = strtolower(trim((string) ($settings['delivery_channel'] ?? 'flaxxa')));

        return in_array($channel, ['flaxxa', 'evolution'], true) ? $channel : 'flaxxa';
    }

    private function isChannelAvailable(string $channel): bool
    {
        try {
            return $channel === 'evolution'
                ? $this->evolutionSender->isAvailable()
                : $this->isFlaxxaAvailable();
        } catch (\Throwable) {
            return false;
        }
    }

    private function isFlaxxaAvailable(): bool
    {
        $this->whatsAppService->assertConfigured();
        $template = $this->resolveTemplate();

        return $template !== null && $this->validateTemplateForOtp($template) === [];
    }

    private function sendViaFlaxxa(string $phone, string $code): void
    {
        $settings = $this->settingsService->getSettings();
        $template = $this->resolveTemplate();

        if ($template === null) {
            throw new InvalidArgumentException('لم يُعرّف قالب Flaxxa لرسائل OTP. اختر قالباً من إعدادات OTP.');
        }

        $issues = $this->validateTemplateForOtp($template);
        if ($issues !== []) {
            throw new InvalidArgumentException(implode(' ', $issues));
        }

        $language = $this->resolveTemplateLanguage($template, $settings);
        [$headerVars, $bodyVars] = $this->buildOtpVariables($template, $code);
        $components = WapiTemplatePayloadBuilder::cloudApiComponentsFromVariables($headerVars, $bodyVars);

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

    /**
     * @param  array<string, mixed>  $settings
     */
    private function resolveTemplateLanguage(WapiTemplate $template, array $settings): string
    {
        $fromTemplate = trim((string) ($template->language ?? ''));
        if ($fromTemplate !== '') {
            return $fromTemplate;
        }

        return trim((string) ($settings['template_language'] ?? 'ar')) ?: 'ar';
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function buildOtpVariables(WapiTemplate $template, string $code): array
    {
        $structure = is_array($template->structure) ? $template->structure : [];
        $headerCount = (int) ($structure['header_placeholders'] ?? 0);
        $bodyCount = (int) ($structure['body_placeholders'] ?? 0);

        $headerVars = [];
        $bodyVars = [];

        if ($headerCount > 0) {
            $headerVars = $this->fillPlaceholders($headerCount, $code);
        } elseif ($bodyCount > 0) {
            $bodyVars = $this->fillPlaceholders($bodyCount, $code);
        }

        return [$headerVars, $bodyVars];
    }

    /**
     * @return array<int, string>
     */
    private function fillPlaceholders(int $count, string $code): array
    {
        $vars = [$code];
        for ($i = 1; $i < $count; $i++) {
            $vars[] = '';
        }

        return $vars;
    }

    private function resolveTemplate(): ?WapiTemplate
    {
        $settings = $this->settingsService->getSettings();
        $templateId = $settings['wapi_template_id'] ?? null;

        if ($templateId) {
            return WapiTemplate::query()->find($templateId);
        }

        return WapiTemplate::query()
            ->where(function ($q) {
                $q->where('name', 'like', '%otp%')
                    ->orWhere('name', 'like', '%verification%')
                    ->orWhere('name', 'like', '%auth%');
            })
            ->orderBy('id')
            ->first();
    }
}
