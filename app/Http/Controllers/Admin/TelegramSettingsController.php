<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramApiException;
use App\Services\Telegram\TelegramBotClient;
use App\Services\Telegram\TelegramBridgeClient;
use App\Services\Telegram\TelegramSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TelegramSettingsController extends Controller
{
    public function __construct(
        private TelegramSettingsService $settingsService,
        private TelegramBotClient $botClient,
        private TelegramBridgeClient $bridgeClient,
    ) {}

    public function index(): View
    {
        $this->settingsService->initializeDefaults();
        $settings = $this->settingsService->getSettings();
        $webhookUrl = url(config('telegram.webhook_path', '/api/webhooks/telegram'));

        return view('admin.pages.telegram.settings.index', compact('settings', 'webhookUrl'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'telegram_enabled' => 'nullable|boolean',
            'bot_token' => 'nullable|string|max:255',
            'bot_username' => 'nullable|string|max:100',
            'webhook_secret' => 'nullable|string|max:255',
            'auto_reply' => 'nullable|boolean',
            'auto_reply_message' => 'nullable|string|max:2000',
            'delay_between_messages' => 'nullable|integer|min:1|max:60',
            'random_delay_enabled' => 'nullable|boolean',
            'min_delay' => 'nullable|integer|min:1|max:30',
            'max_delay' => 'nullable|integer|min:1|max:30',
            'bridge_base_url' => 'nullable|string|max:500',
            'bridge_api_key' => 'nullable|string|max:255',
        ]);

        $payload = [
            'telegram_enabled' => $request->boolean('telegram_enabled') ? '1' : '0',
            'bot_username' => $validated['bot_username'] ?? '',
            'auto_reply' => $request->boolean('auto_reply') ? '1' : '0',
            'auto_reply_message' => $validated['auto_reply_message'] ?? '',
            'delay_between_messages' => (string) ($validated['delay_between_messages'] ?? 3),
            'random_delay_enabled' => $request->boolean('random_delay_enabled') ? '1' : '0',
            'min_delay' => (string) ($validated['min_delay'] ?? 2),
            'max_delay' => (string) ($validated['max_delay'] ?? 5),
            'bridge_base_url' => rtrim(trim($validated['bridge_base_url'] ?? ''), '/'),
        ];

        if (! empty($validated['bot_token'])) {
            $payload['bot_token'] = $validated['bot_token'];
        }
        if (! empty($validated['webhook_secret'])) {
            $payload['webhook_secret'] = $validated['webhook_secret'];
        }
        if (! empty($validated['bridge_api_key'])) {
            $payload['bridge_api_key'] = $validated['bridge_api_key'];
        }

        $this->settingsService->updateSettings($payload);

        return back()->with('success', 'تم حفظ إعدادات Telegram.');
    }

    public function testConnection(): JsonResponse
    {
        try {
            $me = $this->botClient->getMe();
            if (! empty($me['username'])) {
                $this->settingsService->updateSettings(['bot_username' => $me['username']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'الاتصال ناجح — البوت: @'.($me['username'] ?? 'unknown'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => TelegramApiException::resolveUserMessage($e),
            ], 422);
        }
    }

    public function activateWebhook(Request $request): RedirectResponse
    {
        try {
            $settings = $this->settingsService->getSettings();
            $url = url(config('telegram.webhook_path', '/api/webhooks/telegram'));
            $secret = $settings['webhook_secret'] ?: null;
            $this->botClient->setWebhook($url, $secret);

            return back()->with('success', 'تم تفعيل Webhook على: '.$url);
        } catch (\Throwable $e) {
            return back()->with('error', TelegramApiException::resolveUserMessage($e));
        }
    }

    public function testBridge(): JsonResponse
    {
        try {
            $result = $this->bridgeClient->testConnection();

            return response()->json([
                'success' => true,
                'message' => 'MTProto Bridge متصل.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => TelegramApiException::resolveUserMessage($e),
            ], 422);
        }
    }
}
