<?php

namespace App\Services\BulkEmail;

class BulkEmailThrottleService
{
    public function __construct(
        private BulkEmailSettingsService $settingsService
    ) {}

    /**
     * @param  array<string, mixed>|null  $settings
     */
    public function delayAfterMessage(int $messageNumber, ?array $settings = null, bool $useAverageJitter = false): int
    {
        return $this->settingsService->delayAfterMessage($messageNumber, $settings, $useAverageJitter);
    }

    public function cumulativeDelayForIndex(int $queueIndex): int
    {
        return $this->settingsService->cumulativeDelayForIndex($queueIndex);
    }

    public function estimateDurationSeconds(int $recipientCount): int
    {
        return $this->settingsService->estimateDurationSeconds($recipientCount);
    }

    public function formatDuration(int $seconds): string
    {
        return $this->settingsService->formatDuration($seconds);
    }
}
