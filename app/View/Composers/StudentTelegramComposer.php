<?php

namespace App\View\Composers;

use App\Services\Telegram\TelegramSettingsService;
use Illuminate\View\View;

class StudentTelegramComposer
{
    public function __construct(
        private TelegramSettingsService $settingsService,
    ) {}

    public function compose(View $view): void
    {
        if (! auth()->check()) {
            $view->with('studentTelegram', null);

            return;
        }

        $user = auth()->user();
        $settings = $this->settingsService->getSettings();
        $configured = $this->settingsService->isConfigured();
        $platformEnabled = filter_var($settings['telegram_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $view->with('studentTelegram', [
            'enabled' => $configured,
            'platform_enabled' => $platformEnabled,
            'linked' => filled($user->telegram_chat_id),
            'needs_link' => blank($user->telegram_chat_id),
            'username' => $user->telegram_username,
            'linked_at' => $user->telegram_linked_at,
            'bot_username' => $settings['bot_username'] ?? '',
        ]);
    }
}
