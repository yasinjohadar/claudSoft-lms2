<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Log;

class SendTelegramMessage
{
    public function __construct(
        private TelegramBotClient $client,
        private TelegramSettingsService $settingsService,
        private TelegramSendThrottle $throttle,
    ) {}

    public function sendText(string $chatId, string $text, bool $applyDelay = true): array
    {
        if (! $this->settingsService->isConfigured()) {
            throw new TelegramApiException('Telegram غير مفعّل أو Bot Token غير مُعرّف.');
        }

        if ($applyDelay) {
            $this->throttle->waitBeforeSend($chatId);
        }

        $result = $this->client->sendMessage($chatId, $text);

        Log::channel('single')->info('Telegram message sent', ['chat_id' => $chatId]);

        return $result;
    }

    public function sendToUser(\App\Models\User $user, string $text, bool $applyDelay = true): array
    {
        $chatId = trim((string) ($user->telegram_chat_id ?? ''));
        if ($chatId === '') {
            throw new TelegramApiException('الطالب لم يربط حساب Telegram بعد.');
        }

        return $this->sendText($chatId, $text, $applyDelay);
    }

    public function sendToChat(string $chatId, string $text, bool $applyDelay = true): array
    {
        return $this->sendText($chatId, $text, $applyDelay);
    }
}
