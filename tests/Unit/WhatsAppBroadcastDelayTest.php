<?php

use App\Jobs\BroadcastWhatsAppMessageJob;
use App\Models\User;
use App\Models\WhatsAppBroadcast;
use Tests\TestCase;

uses(TestCase::class);

test('broadcast job stores cumulative delay seconds', function () {
    $broadcast = new WhatsAppBroadcast(['id' => 1, 'message_template' => 'test', 'total_recipients' => 3]);
    $student = new User(['id' => 2, 'name' => 'Test']);

    $job = new BroadcastWhatsAppMessageJob($broadcast, $student, 'test message', 'text', 12, 2);

    expect($job->delay)->toBe(12);
});

test('cumulative delay simulation spaces messages apart', function () {
    $baseDelay = 4;
    $cumulative = 0;
    $delays = [];

    for ($i = 0; $i < 5; $i++) {
        $cumulative += $baseDelay;
        $delays[] = $cumulative;
    }

    expect($delays)->toBe([4, 8, 12, 16, 20]);
});
