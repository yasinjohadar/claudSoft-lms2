<?php

namespace App\Webhooks\N8n\Handlers;

class SendTelegramHandler extends BaseHandler
{
    public function handle(array $payload): array
    {
        try {
            $this->validate($payload, ['chat_id', 'message']);

            // This is a placeholder - actual Telegram integration would be done through n8n
            // We just log that we received the request
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
