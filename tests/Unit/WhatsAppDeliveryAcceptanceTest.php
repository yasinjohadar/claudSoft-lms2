<?php

use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\WhatsAppDeliveryAcceptance;

uses(Tests\TestCase::class);

test('whatsapp delivery acceptance requires sent status and meta id', function () {
    expect(WhatsAppDeliveryAcceptance::isAccepted(null))->toBeFalse();

    $queued = new WhatsAppMessage([
        'status' => WhatsAppMessage::STATUS_QUEUED,
        'meta_message_id' => 'ABC123',
    ]);
    expect(WhatsAppDeliveryAcceptance::isAccepted($queued))->toBeFalse();

    $failed = new WhatsAppMessage([
        'status' => WhatsAppMessage::STATUS_FAILED,
        'meta_message_id' => 'ABC123',
    ]);
    expect(WhatsAppDeliveryAcceptance::isAccepted($failed))->toBeFalse();

    $placeholder = new WhatsAppMessage([
        'status' => WhatsAppMessage::STATUS_SENT,
        'meta_message_id' => 'evo_abc',
        'payload' => null,
    ]);
    expect(WhatsAppDeliveryAcceptance::isAccepted($placeholder))->toBeFalse();

    $accepted = new WhatsAppMessage([
        'status' => WhatsAppMessage::STATUS_SENT,
        'meta_message_id' => '3EB0ABCDEF1234567890',
    ]);
    expect(WhatsAppDeliveryAcceptance::isAccepted($accepted))->toBeTrue();
});
