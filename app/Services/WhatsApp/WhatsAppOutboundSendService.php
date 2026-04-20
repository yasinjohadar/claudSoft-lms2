<?php

namespace App\Services\WhatsApp;

use App\Exceptions\WhatsAppApiException;
use App\Models\WhatsAppMessage;
use App\Support\WhatsAppRecipientNormalizer;

/**
 * Single place for outbound send logic (used by queue job and sync paths).
 * Avoids dispatching SendWhatsAppMessageJob twice when broadcast uses sync send.
 */
class WhatsAppOutboundSendService
{
    public function __construct(
        private WhatsAppSettingsService $settingsService
    ) {}

    /**
     * Perform API send and update the WhatsAppMessage row.
     *
     * @throws WhatsAppApiException|\Throwable
     */
    public function send(WhatsAppMessage $message, array $messageData = []): void
    {
        $contact = $message->contact;
        if (! $contact) {
            $message->load('contact');
            $contact = $message->contact;
        }

        $settings = $this->settingsService->getSettings();
        $provider = $settings['whatsapp_provider'] ?? 'meta';
        $config = $this->settingsService->getProviderConfig();
        $to = WhatsAppRecipientNormalizer::normalize($provider, $contact->wa_id);

        $providerInstance = WhatsAppProviderFactory::create($provider, $config);

        $messageType = $messageData['type'] ?? $message->type;

        if ($messageType === 'template') {
            $response = $providerInstance->sendTemplate(
                $to,
                $messageData['template_name'] ?? $message->body,
                $messageData['language'] ?? 'ar',
                $messageData['components'] ?? []
            );
        } elseif ($messageType === 'document') {
            $response = $providerInstance->sendDocument(
                $to,
                $messageData['document_url'] ?? '',
                $messageData['filename'] ?? 'document.pdf',
                $messageData['caption'] ?? null
            );
        } else {
            $response = $providerInstance->sendText(
                $to,
                $messageData['text'] ?? $message->body ?? '',
                $messageData['preview_url'] ?? false
            );
        }

        $message->update([
            'meta_message_id' => $response->metaMessageId,
            'status' => WhatsAppMessage::STATUS_SENT,
            'payload' => array_merge($message->payload ?? [], [
                'response' => $response->rawResponse,
            ]),
        ]);
    }
}
