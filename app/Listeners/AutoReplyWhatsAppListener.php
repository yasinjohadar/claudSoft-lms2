<?php

namespace App\Listeners;

use App\Events\WhatsAppMessageReceived;
use App\Models\AIModel;
use App\Services\Ai\AIModelService;
use App\Services\Ai\AIProviderFactory;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Support\WhatsAppRecipientNormalizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AutoReplyWhatsAppListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private WhatsAppSettingsService $settingsService,
        private AIModelService $aiModelService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(WhatsAppMessageReceived $event): void
    {
        if ($event->message->direction !== 'inbound') {
            return;
        }

        if ($event->message->type !== 'text') {
            Log::channel('whatsapp')->info('AutoReply: skipped non-text message', [
                'type' => $event->message->type,
            ]);

            return;
        }

        $incomingBody = trim((string) ($event->message->body ?? ''));
        if ($incomingBody === '') {
            Log::channel('whatsapp')->info('AutoReply: skipped empty text body');

            return;
        }

        $settings = $this->settingsService->getSettings();

        if (! ($settings['whatsapp_enabled'] ?? false)) {
            Log::channel('whatsapp')->info('AutoReply: WhatsApp disabled globally');

            return;
        }

        if (! ($settings['auto_reply'] ?? false)) {
            Log::channel('whatsapp')->info('AutoReply: disabled in settings');

            return;
        }

        $contact = $event->message->contact;
        if (! $contact || ! WhatsAppRecipientNormalizer::isReplyableRecipient($contact->wa_id)) {
            Log::channel('whatsapp')->info('AutoReply: skipped non-replyable recipient', [
                'wa_id' => $contact?->wa_id,
            ]);

            return;
        }

        $useAi = $settings['auto_reply_use_ai'] ?? false;

        Log::channel('whatsapp')->info('AutoReply: generating reply', [
            'provider' => $settings['whatsapp_provider'] ?? null,
            'use_ai' => $useAi,
            'incoming_preview' => mb_substr($incomingBody, 0, 100),
        ]);

        $replyText = $useAi
            ? $this->generateAiReply($incomingBody, $settings)
            : ($settings['auto_reply_message'] ?? 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.');

        if ($replyText === null || trim($replyText) === '') {
            $replyText = $settings['auto_reply_message'] ?? 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.';
            Log::channel('whatsapp')->warning('AutoReply: AI returned empty, using fallback message');
        }

        try {
            $sendService = app(SendWhatsAppMessage::class);
            // Sync send inside queued listener — avoids a third queue hop for the reply.
            $sendService->sendTextSync($contact->wa_id, $replyText);

            Log::channel('whatsapp')->info('Auto-reply sent', [
                'original_message_id' => $event->message->id,
                'to' => $contact->wa_id,
                'used_ai' => $useAi,
                'provider' => $settings['whatsapp_provider'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Failed to send auto-reply', [
                'original_message_id' => $event->message->id,
                'to' => $contact->wa_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate reply text using AI model. Returns null on failure (caller will use fallback message).
     */
    private function generateAiReply(string $incomingMessage, array $settings): ?string
    {
        $modelId = $settings['auto_reply_ai_model_id'] ?? null;
        $systemPrompt = trim($settings['auto_reply_ai_system_prompt'] ?? '');

        $model = null;
        if (! empty($modelId)) {
            $model = AIModel::find($modelId);
        }
        if (! $model || ! $model->is_active) {
            $model = $this->aiModelService->getBestModelFor('chat');
        }
        if (! $model) {
            Log::channel('whatsapp')->warning('WhatsApp AI auto-reply: no AI model available, using fallback');

            return null;
        }

        $defaultSystemPrompt = 'أنت مساعد ودود يرد على رسائل الواتساب نيابة عن منصة تعليمية. أجب بشكل مختصر ومهذب بالعربية. لا تخرج عن دور المساعد ولا تقدم معلومات حساسة.';
        $systemContent = $systemPrompt !== '' ? $systemPrompt : $defaultSystemPrompt;

        $messages = [
            ['role' => 'system', 'content' => $systemContent],
            ['role' => 'user', 'content' => $incomingMessage],
        ];

        try {
            $provider = AIProviderFactory::create($model);
            $response = $provider->chat($messages, ['max_tokens' => $model->max_tokens ?? 512]);

            if (! empty($response['success']) && ! empty($response['content'])) {
                return trim($response['content']);
            }

            Log::channel('whatsapp')->warning('WhatsApp AI auto-reply: empty or failed response', [
                'error' => $response['error'] ?? 'unknown',
            ]);

            return null;
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('WhatsApp AI auto-reply failed', [
                'error' => $e->getMessage(),
                'model_id' => $model->id,
            ]);

            return null;
        }
    }
}
