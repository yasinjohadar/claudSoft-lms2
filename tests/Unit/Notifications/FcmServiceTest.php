<?php

use App\Services\Notifications\FcmService;
use Tests\TestCase;

uses(TestCase::class);

it('returns disabled status when fcm disabled', function () {
    config()->set('notification_hub.fcm.enabled', false);

    $service = new FcmService();
    $result = $service->send('dummy-token', [
        'title' => 'Hello',
        'body' => 'World',
    ]);

    expect($result['ok'])->toBeFalse()
        ->and($result['error'])->toBe('FCM disabled');
});
