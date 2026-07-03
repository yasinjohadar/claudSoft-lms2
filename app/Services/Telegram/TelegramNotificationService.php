<?php

namespace App\Services\Telegram;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    public function __construct(
        private TelegramSettingsService $settingsService,
        private SendTelegramMessage $sendTelegramMessage,
    ) {}

    public function sendToUserIfEnabled(User $user, string $title, string $body): bool
    {
        if (! $this->settingsService->isConfigured()) {
            return false;
        }

        if (empty($user->telegram_chat_id)) {
            return false;
        }

        $text = '<b>'.e($title)."</b>\n\n".e($body);

        try {
            $this->sendTelegramMessage->sendText((string) $user->telegram_chat_id, $text, applyDelay: false);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Telegram notification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
