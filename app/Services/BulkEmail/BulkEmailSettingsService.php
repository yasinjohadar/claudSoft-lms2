<?php

namespace App\Services\BulkEmail;

use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailRecipient;
use App\Models\SystemSetting;

class BulkEmailSettingsService
{
    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $settings = SystemSetting::where('group', 'bulk_email')
            ->get()
            ->keyBy('key')
            ->map(fn ($setting) => $setting->value)
            ->toArray();

        return [
            'base_delay_seconds' => (int) ($settings['base_delay_seconds'] ?? 3),
            'random_delay_enabled' => filter_var($settings['random_delay_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'min_jitter_seconds' => (int) ($settings['min_jitter_seconds'] ?? 1),
            'max_jitter_seconds' => (int) ($settings['max_jitter_seconds'] ?? 4),
            'batch_size' => max(1, (int) ($settings['batch_size'] ?? 10)),
            'batch_pause_seconds' => (int) ($settings['batch_pause_seconds'] ?? 45),
            'max_recipients_per_campaign' => (int) ($settings['max_recipients_per_campaign'] ?? 500),
            'daily_send_limit' => (int) ($settings['daily_send_limit'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $newSettings
     */
    public function updateSettings(array $newSettings): void
    {
        $types = [
            'base_delay_seconds' => 'integer',
            'random_delay_enabled' => 'boolean',
            'min_jitter_seconds' => 'integer',
            'max_jitter_seconds' => 'integer',
            'batch_size' => 'integer',
            'batch_pause_seconds' => 'integer',
            'max_recipients_per_campaign' => 'integer',
            'daily_send_limit' => 'integer',
        ];

        foreach ($types as $key => $type) {
            if (! array_key_exists($key, $newSettings)) {
                continue;
            }

            SystemSetting::set($key, $newSettings[$key], $type, 'bulk_email');
        }
    }

    public function initializeDefaults(): void
    {
        $defaults = [
            'base_delay_seconds' => ['value' => 3, 'type' => 'integer'],
            'random_delay_enabled' => ['value' => true, 'type' => 'boolean'],
            'min_jitter_seconds' => ['value' => 1, 'type' => 'integer'],
            'max_jitter_seconds' => ['value' => 4, 'type' => 'integer'],
            'batch_size' => ['value' => 10, 'type' => 'integer'],
            'batch_pause_seconds' => ['value' => 45, 'type' => 'integer'],
            'max_recipients_per_campaign' => ['value' => 500, 'type' => 'integer'],
            'daily_send_limit' => ['value' => 0, 'type' => 'integer'],
        ];

        foreach ($defaults as $key => $config) {
            if (! SystemSetting::byKey($key)->ofGroup('bulk_email')->exists()) {
                SystemSetting::set($key, $config['value'], $config['type'], 'bulk_email');
            }
        }
    }

    /**
     * @param  array<string, mixed>|null  $settings
     */
    public function delayAfterMessage(int $messageNumber, ?array $settings = null, bool $useAverageJitter = false): int
    {
        $settings = $settings ?? $this->getSettings();

        $delay = $settings['base_delay_seconds'];

        if ($settings['random_delay_enabled']) {
            $min = min($settings['min_jitter_seconds'], $settings['max_jitter_seconds']);
            $max = max($settings['min_jitter_seconds'], $settings['max_jitter_seconds']);

            if ($useAverageJitter) {
                $delay += (int) floor(($min + $max) / 2);
            } else {
                $delay += random_int($min, $max);
            }
        }

        if ($messageNumber > 0 && $messageNumber % $settings['batch_size'] === 0) {
            $delay += $settings['batch_pause_seconds'];
        }

        return $delay;
    }

    /**
     * @param  array<string, mixed>|null  $settings
     */
    public function cumulativeDelayForIndex(int $queueIndex, ?array $settings = null, bool $useAverageJitter = false): int
    {
        if ($queueIndex <= 0) {
            return 0;
        }

        $total = 0;

        for ($i = 1; $i <= $queueIndex; $i++) {
            $total += $this->delayAfterMessage($i, $settings, $useAverageJitter);
        }

        return $total;
    }

    public function estimateDurationSeconds(int $recipientCount, ?array $settings = null): int
    {
        if ($recipientCount <= 0) {
            return 0;
        }

        return $this->cumulativeDelayForIndex($recipientCount, $settings, true);
    }

    public function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'فوري';
        }

        if ($seconds < 60) {
            return $seconds.' ثانية تقريباً';
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            if ($remainingSeconds > 0) {
                return $minutes.' دقيقة و '.$remainingSeconds.' ثانية تقريباً';
            }

            return $minutes.' دقيقة تقريباً';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes > 0) {
            return $hours.' ساعة و '.$remainingMinutes.' دقيقة تقريباً';
        }

        return $hours.' ساعة تقريباً';
    }

    public function assertCampaignLimits(int $recipientCount): void
    {
        $settings = $this->getSettings();

        if ($settings['max_recipients_per_campaign'] > 0 && $recipientCount > $settings['max_recipients_per_campaign']) {
            throw new \InvalidArgumentException(
                'عدد المستلمين ('.$recipientCount.') يتجاوز الحد الأقصى المسموح لكل حملة ('.$settings['max_recipients_per_campaign'].').'
            );
        }

        if ($settings['daily_send_limit'] > 0) {
            $todaySent = $this->getTodaySentCount();

            if ($todaySent + $recipientCount > $settings['daily_send_limit']) {
                $remaining = max(0, $settings['daily_send_limit'] - $todaySent);

                throw new \InvalidArgumentException(
                    'تم تجاوز الحد اليومي للإرسال. المتبقي اليوم: '.$remaining.' رسالة.'
                );
            }
        }
    }

    public function getTodaySentCount(): int
    {
        return BulkEmailRecipient::query()
            ->where('status', BulkEmailRecipient::STATUS_SENT)
            ->whereDate('sent_at', today())
            ->count();
    }

    public function getActiveCampaignsCount(): int
    {
        return BulkEmailCampaign::query()
            ->whereIn('status', [
                BulkEmailCampaign::STATUS_PENDING,
                BulkEmailCampaign::STATUS_PROCESSING,
            ])
            ->count();
    }
}
