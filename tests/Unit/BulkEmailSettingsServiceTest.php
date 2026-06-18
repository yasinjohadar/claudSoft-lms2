<?php

use App\Services\BulkEmail\BulkEmailSettingsService;
use Tests\TestCase;

uses(TestCase::class);

function bulkEmailTestSettings(array $overrides = []): array
{
    return array_merge([
        'base_delay_seconds' => 3,
        'random_delay_enabled' => true,
        'min_jitter_seconds' => 1,
        'max_jitter_seconds' => 4,
        'batch_size' => 10,
        'batch_pause_seconds' => 45,
        'max_recipients_per_campaign' => 500,
        'daily_send_limit' => 0,
    ], $overrides);
}

test('cumulative delay for index zero is zero', function () {
    $service = new BulkEmailSettingsService();

    expect($service->cumulativeDelayForIndex(0, bulkEmailTestSettings()))->toBe(0);
});

test('cumulative delay sums per message delays with average jitter', function () {
    $service = new BulkEmailSettingsService();
    $settings = bulkEmailTestSettings(['random_delay_enabled' => true]);

    $expected = 0;
    for ($i = 1; $i <= 3; $i++) {
        $expected += $service->delayAfterMessage($i, $settings, true);
    }

    expect($service->cumulativeDelayForIndex(3, $settings, true))->toBe($expected);
});

test('delay after message includes batch pause on batch boundary', function () {
    $service = new BulkEmailSettingsService();
    $settings = bulkEmailTestSettings([
        'random_delay_enabled' => false,
        'base_delay_seconds' => 3,
        'batch_size' => 10,
        'batch_pause_seconds' => 45,
    ]);

    expect($service->delayAfterMessage(9, $settings, true))->toBe(3);
    expect($service->delayAfterMessage(10, $settings, true))->toBe(48);
    expect($service->delayAfterMessage(20, $settings, true))->toBe(48);
});

test('delay after message without jitter uses base delay only', function () {
    $service = new BulkEmailSettingsService();
    $settings = bulkEmailTestSettings([
        'random_delay_enabled' => false,
        'base_delay_seconds' => 5,
        'batch_size' => 100,
    ]);

    expect($service->delayAfterMessage(1, $settings, true))->toBe(5);
});

test('estimate duration uses average jitter for eta', function () {
    $service = new BulkEmailSettingsService();
    $settings = bulkEmailTestSettings(['random_delay_enabled' => true]);

    $recipientCount = 5;
    $expected = $service->cumulativeDelayForIndex($recipientCount, $settings, true);

    expect($service->estimateDurationSeconds($recipientCount, $settings))->toBe($expected);
});

test('estimate duration returns zero for empty audience', function () {
    $service = new BulkEmailSettingsService();

    expect($service->estimateDurationSeconds(0))->toBe(0);
});

test('format duration returns arabic labels', function () {
    $service = new BulkEmailSettingsService();

    expect($service->formatDuration(0))->toBe('فوري');
    expect($service->formatDuration(30))->toBe('30 ثانية تقريباً');
    expect($service->formatDuration(90))->toBe('1 دقيقة و 30 ثانية تقريباً');
    expect($service->formatDuration(3600))->toBe('1 ساعة تقريباً');
});
