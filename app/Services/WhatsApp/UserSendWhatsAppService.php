<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppMessageTemplate;
use App\Services\BulkEmail\BulkEmailVariableBuilder;
use App\Support\WhatsAppRecipientNormalizer;
use InvalidArgumentException;

class UserSendWhatsAppService
{
    public function __construct(
        private BulkEmailVariableBuilder $variableBuilder,
        private WhatsAppSettingsService $settingsService,
        private SendWhatsAppMessage $sendWhatsAppMessage,
        private WhatsAppOutboundSendService $outboundSendService,
    ) {}

    public function renderTemplateForUser(WhatsAppMessageTemplate $template, User $user): string
    {
        return $template->render($this->variablesForUser($user));
    }

    public function sendTemplateToUser(
        User $user,
        WhatsAppMessageTemplate $template,
        ?string $evolutionInstanceName = null
    ): void {
        $body = $this->renderTemplateForUser($template, $user);
        $phone = $this->resolvePhone($user);

        if ($evolutionInstanceName) {
            $this->sendTextSyncWithInstance($phone, $body, $evolutionInstanceName);

            return;
        }

        $this->sendWhatsAppMessage->sendTextSync($phone, $body);
    }

    /**
     * @return array<string, string>
     */
    private function variablesForUser(User $user): array
    {
        $variables = $this->variableBuilder->build($user);
        $variables['student_email'] = $variables['email'] ?? '';

        return $variables;
    }

    private function resolvePhone(User $user): string
    {
        $phone = trim((string) ($user->full_phone ?? ''));
        if ($phone === '') {
            $phone = trim(($user->country_code ?? '').($user->phone ?? ''));
        }

        if ($phone === '') {
            throw new InvalidArgumentException('لا يوجد رقم واتساب لهذا المستخدم.');
        }

        if (! str_starts_with($phone, '+')) {
            $phone = '+'.$phone;
        }

        return $phone;
    }

    private function sendTextSyncWithInstance(string $to, string $text, string $instanceName): void
    {
        $settings = $this->settingsService->getSettings();
        $provider = $settings['whatsapp_provider'] ?? 'meta';
        $normalizedRecipient = WhatsAppRecipientNormalizer::normalize($provider, $to);
        $contact = WhatsAppContact::findOrCreateByWaId($normalizedRecipient);
        $message = WhatsAppMessage::create([
            'direction' => WhatsAppMessage::DIRECTION_OUTBOUND,
            'contact_id' => $contact->id,
            'type' => WhatsAppMessage::TYPE_TEXT,
            'body' => $text,
            'status' => WhatsAppMessage::STATUS_QUEUED,
            'payload' => ['evolution_instance_name' => $instanceName],
        ]);

        $this->outboundSendService->send($message, [
            'type' => 'text',
            'text' => $text,
            'preview_url' => false,
            'evolution_instance_name' => $instanceName,
        ]);
    }
}
