<?php

namespace App\Webhooks\N8n\Handlers;

class SendTelegramHandler extends BaseHandler
{
    public function handle(array $payload): array
    {
        try {
            $this->validate($payload, ['chat_id', 'message']);

            // This delegates to Telegram service when configured; otherwise logs for n8n fallback
            if (app(\App\Services\Telegram\TelegramSettingsService::class)->isConfigured()) {
                app(\App\Services\Telegram\SendTelegramMessage::class)->sendText(
                    (string) $payload['chat_id'],
                    (string) $payload['message'],
                    applyDelay: false
                );

                return $this->success('Telegram message sent.', [
                    'chat_id' => $payload['chat_id'],
                ]);
            }

            $this->logSuccess('Telegram message request received', [
                'chat_id' => $payload['chat_id'],
            ]);

            return $this->success('Telegram message request logged. Actual sending is handled by n8n workflow.', [
                'chat_id' => $payload['chat_id'],
                'message_preview' => substr($payload['message'], 0, 50),
            ]);
        } catch (\Exception $e) {
            $this->logError('Telegram handler failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to process Telegram request: ' . $e->getMessage());
        }
    }
}
