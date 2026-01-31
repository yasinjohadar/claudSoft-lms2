<?php

namespace App\Listeners;

use App\Events\WhatsAppMessageReceived;
use App\Models\AIModel;
use App\Services\Ai\AIModelService;
use App\Services\Ai\AIProviderFactory;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppSettingsService;
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
        // Only process text messages
        if ($event->message->type !== 'text' || $event->message->direction !== 'inbound') {
            Log::info('AutoReply: skipped non-text message', [
                'type' => $event->message->type,
                'direction' => $event->message->direction,
            ]);
            return;
        }

        // Get settings from database
        $settings = $this->settingsService->getSettings();

        Log::info('AutoReply: checking settings', [
            'auto_reply' => $settings['auto_reply'] ?? false,
            'auto_reply_use_ai' => $settings['auto_reply_use_ai'] ?? false,
            'auto_reply_ai_model_id' => $settings['auto_reply_ai_model_id'] ?? null,
        ]);

        // Check if auto-reply is enabled
        $autoReplyEnabled = $settings['auto_reply'] ?? false;
        if (!$autoReplyEnabled) {
            Log::info('AutoReply: disabled, skipping');
            return;
        }

        $useAi = $settings['auto_reply_use_ai'] ?? false;
        
        Log::info('AutoReply: generating reply', [
            'use_ai' => $useAi,
            'incoming_message' => substr($event->message->body ?? '', 0, 100),
        ]);
        
        $replyText = $useAi
            ? $this->generateAiReply($event->message->body ?? '', $settings)
            : ($settings['auto_reply_message'] ?? 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.');

        Log::info('AutoReply: generated reply', [
            'use_ai' => $useAi,
            'reply_length' => strlen($replyText ?? ''),
            'is_fallback' => $replyText === null || $replyText === '',
        ]);

        if ($replyText === null || $replyText === '') {
            $replyText = $settings['auto_reply_message'] ?? 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.';
            Log::warning('AutoReply: AI returned null, using fallback message');
        }

        try {
            $contact = $event->message->contact;
            $sendService = app(SendWhatsAppMessage::class);
            $sendService->sendText($contact->wa_id, $replyText);

            Log::info('Auto-reply sent', [
                'original_message_id' => $event->message->id,
                'to' => $contact->wa_id,
                'used_ai' => $useAi,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send auto-reply', [
                'original_message_id' => $event->message->id,
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
        if (!empty($modelId)) {
            $model = AIModel::find($modelId);
        }
        if (!$model || !$model->is_active) {
            $model = $this->aiModelService->getBestModelFor('chat');
        }
        if (!$model) {
            Log::warning('WhatsApp AI auto-reply: no AI model available, using fallback');
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

            if (!empty($response['success']) && !empty($response['content'])) {
                return trim($response['content']);
            }

            Log::warning('WhatsApp AI auto-reply: empty or failed response', [
                'error' => $response['error'] ?? 'unknown',
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('WhatsApp AI auto-reply failed', [
                'error' => $e->getMessage(),
                'model_id' => $model->id,
            ]);
            return null;
        }
    }
}
