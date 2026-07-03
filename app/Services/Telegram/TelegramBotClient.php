<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotClient
{
    public function __construct(
        private TelegramSettingsService $settingsService
    ) {}

    public function sendMessage(string $chatId, string $text, array $options = []): array
    {
        return $this->request('sendMessage', array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $options['parse_mode'] ?? 'HTML',
            'disable_web_page_preview' => $options['disable_preview'] ?? false,
        ], $options));
    }

    public function sendPhoto(string $chatId, string $photo, ?string $caption = null): array
    {
        $payload = ['chat_id' => $chatId, 'photo' => $photo];
        if ($caption !== null) {
            $payload['caption'] = $caption;
        }

        return $this->request('sendPhoto', $payload);
    }

    public function getMe(): array
    {
        return $this->request('getMe');
    }

    public function setWebhook(string $url, ?string $secretToken = null): array
    {
        $payload = ['url' => $url, 'allowed_updates' => ['message', 'callback_query']];
        if ($secretToken) {
            $payload['secret_token'] = $secretToken;
        }

        return $this->request('setWebhook', $payload);
    }

    public function deleteWebhook(): array
    {
        return $this->request('deleteWebhook');
    }

    public function getWebhookInfo(): array
    {
        return $this->request('getWebhookInfo');
    }

    public function createChatInviteLink(string $chatId, array $options = []): array
    {
        return $this->request('createChatInviteLink', array_merge(['chat_id' => $chatId], $options));
    }

    protected function request(string $method, array $params = []): array
    {
        $token = $this->settingsService->getSettings()['bot_token'] ?? '';
        if ($token === '') {
            throw new TelegramApiException('لم يتم إعداد Bot Token لـ Telegram. أدخله من إعدادات Telegram.');
        }

        $base = rtrim(config('telegram.api_base', 'https://api.telegram.org'), '/');
        $url = $base.'/bot'.$token.'/'.$method;

        try {
            $response = Http::timeout(30)->asJson()->post($url, $params);
        } catch (\Throwable $e) {
            Log::channel('single')->error('Telegram API connection failed', ['method' => $method, 'error' => $e->getMessage()]);
            throw new TelegramApiException('تعذر الاتصال بـ Telegram Bot API.');
        }

        $body = $response->json();
        if (! ($body['ok'] ?? false)) {
            $desc = $body['description'] ?? $response->body();
            throw new TelegramApiException('Telegram API: '.$desc);
        }

        return $body['result'] ?? $body;
    }
}
