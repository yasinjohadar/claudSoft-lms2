<?php

namespace App\Services\Telegram;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;

class TelegramSettingsService
{
    public function getSettings(): array
    {
        $settings = SystemSetting::where('group', 'telegram')
            ->get()
            ->keyBy('key')
            ->map(fn ($s) => $s->value)
            ->toArray();

        return [
            'telegram_enabled' => filter_var($settings['telegram_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'bot_token' => $this->decryptIfEncrypted($settings['bot_token'] ?? ''),
            'bot_username' => $settings['bot_username'] ?? '',
            'webhook_secret' => $this->decryptIfEncrypted($settings['webhook_secret'] ?? ''),
            'auto_reply' => filter_var($settings['auto_reply'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'auto_reply_message' => $settings['auto_reply_message'] ?? 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.',
            'delay_between_messages' => (int) ($settings['delay_between_messages'] ?? 3),
            'random_delay_enabled' => filter_var($settings['random_delay_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'min_delay' => (int) ($settings['min_delay'] ?? 2),
            'max_delay' => (int) ($settings['max_delay'] ?? 5),
            'bridge_base_url' => rtrim(trim($settings['bridge_base_url'] ?? config('telegram.bridge.base_url', '')), '/'),
            'bridge_api_key' => $this->decryptIfEncrypted($settings['bridge_api_key'] ?? config('telegram.bridge.api_key', '')),
        ];
    }

    public function updateSettings(array $newSettings): void
    {
        foreach ($newSettings as $key => $value) {
            if (in_array($key, ['bot_token', 'webhook_secret', 'bridge_api_key'], true) && ! empty($value)) {
                $value = Crypt::encryptString($value);
            }

            SystemSetting::updateOrCreate(
                ['group' => 'telegram', 'key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
            );
        }
    }

    public function initializeDefaults(): void
    {
        $defaults = [
            'telegram_enabled' => '0',
            'auto_reply' => '0',
            'auto_reply_message' => 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.',
            'delay_between_messages' => '3',
            'random_delay_enabled' => '1',
            'min_delay' => '2',
            'max_delay' => '5',
        ];

        foreach ($defaults as $key => $value) {
            SystemSetting::firstOrCreate(
                ['group' => 'telegram', 'key' => $key],
                ['value' => $value]
            );
        }
    }

    public function calculateDelay(?int $customDelay = null): int
    {
        $settings = $this->getSettings();
        $baseDelay = $customDelay ?? $settings['delay_between_messages'];

        if ($settings['random_delay_enabled']) {
            return $baseDelay + rand($settings['min_delay'], $settings['max_delay']);
        }

        return $baseDelay;
    }

    public function isConfigured(): bool
    {
        $settings = $this->getSettings();

        return $settings['telegram_enabled'] && $settings['bot_token'] !== '';
    }

    private function decryptIfEncrypted(string $value): string
    {
        if ($value === '') {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }
}
