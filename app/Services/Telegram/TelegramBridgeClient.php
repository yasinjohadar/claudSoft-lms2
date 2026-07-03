<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for external MTProto bridge service (Telethon/GramJS).
 */
class TelegramBridgeClient
{
    public function __construct(
        private TelegramSettingsService $settingsService
    ) {}

    public function isConfigured(): bool
    {
        $settings = $this->settingsService->getSettings();

        return ($settings['bridge_base_url'] ?? '') !== '';
    }

    public function testConnection(): array
    {
        return $this->request('GET', '/health');
    }

    public function listGroups(?int $accountId = null): array
    {
        $query = $accountId ? ['account_id' => $accountId] : [];

        return $this->request('GET', '/groups', $query);
    }

    public function getGroupMembers(string $chatId, ?int $accountId = null): array
    {
        return $this->request('GET', '/groups/'.urlencode($chatId).'/members', array_filter([
            'account_id' => $accountId,
        ]));
    }

    public function createGroup(string $title, ?int $accountId = null): array
    {
        return $this->request('POST', '/groups/create', array_filter([
            'title' => $title,
            'account_id' => $accountId,
        ]));
    }

    public function addBotToGroup(string $chatId, string $botUsername): array
    {
        return $this->request('POST', '/groups/'.urlencode($chatId).'/add-bot', [
            'bot_username' => ltrim($botUsername, '@'),
        ]);
    }

    protected function request(string $method, string $path, array $data = []): array
    {
        $settings = $this->settingsService->getSettings();
        $baseUrl = rtrim($settings['bridge_base_url'] ?? '', '/');

        if ($baseUrl === '') {
            throw new TelegramApiException('لم يُعرّف عنوان MTProto Bridge. أدخله من إعدادات Telegram.');
        }

        $url = $baseUrl.$path;
        $apiKey = $settings['bridge_api_key'] ?? '';

        try {
            $pending = Http::timeout((int) config('telegram.bridge.timeout', 30))
                ->withHeaders(array_filter([
                    'X-Api-Key' => $apiKey !== '' ? $apiKey : null,
                ]));

            $response = match (strtoupper($method)) {
                'GET' => $pending->get($url, $data),
                'POST' => $pending->post($url, $data),
                'DELETE' => $pending->delete($url, $data),
                default => throw new TelegramApiException('Unsupported HTTP method'),
            };
        } catch (TelegramApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Telegram bridge request failed', ['path' => $path, 'error' => $e->getMessage()]);
            throw new TelegramApiException('تعذر الاتصال بـ MTProto Bridge: '.$e->getMessage());
        }

        if (! $response->successful()) {
            throw new TelegramApiException('MTProto Bridge: '.($response->json('message') ?? $response->body()));
        }

        return $response->json() ?? [];
    }
}
