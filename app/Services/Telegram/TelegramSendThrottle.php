<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Cache;

class TelegramSendThrottle
{
    public function __construct(
        private TelegramSettingsService $settingsService
    ) {}

    public function waitBeforeSend(string $chatId): void
    {
        $delaySeconds = $this->settingsService->calculateDelay();
        if ($delaySeconds <= 0) {
            return;
        }

        $cacheKey = 'telegram_last_send:'.md5($chatId);
        $lockKey = 'telegram_send_lock:'.md5($chatId);

        Cache::lock($lockKey, 30)->block(15, function () use ($cacheKey, $delaySeconds): void {
            $lastSentAt = Cache::get($cacheKey);
            if (is_numeric($lastSentAt)) {
                $waitSeconds = $delaySeconds - (microtime(true) - (float) $lastSentAt);
                if ($waitSeconds > 0) {
                    usleep((int) round($waitSeconds * 1_000_000));
                }
            }
            Cache::put($cacheKey, microtime(true), now()->addHours(2));
        });
    }
}
