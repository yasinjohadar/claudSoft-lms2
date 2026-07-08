<?php

namespace Tests\Support;

use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\Evolution\EvolutionWhatsAppNumberResolver;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Mockery;

final class PasswordCredentialTestDoubles
{
    public static function acceptedWhatsAppMessage(): WhatsAppMessage
    {
        return new WhatsAppMessage([
            'status' => WhatsAppMessage::STATUS_SENT,
            'meta_message_id' => '3EB0TESTMESSAGEID0001',
        ]);
    }

    public static function mockAcceptedWhatsAppSender(?callable $withArgs = null): SendWhatsAppMessage
    {
        $mock = Mockery::mock(SendWhatsAppMessage::class);
        $expectation = $mock->shouldReceive('sendTextSync');

        if ($withArgs !== null) {
            $expectation->withArgs($withArgs)->once();
        } else {
            $expectation->zeroOrMoreTimes();
        }

        $expectation->andReturn(self::acceptedWhatsAppMessage());

        app()->instance(SendWhatsAppMessage::class, $mock);

        return $mock;
    }

    public static function mockNumberResolverPassThrough(): void
    {
        $mock = Mockery::mock(EvolutionWhatsAppNumberResolver::class);
        $mock->shouldReceive('resolve')
            ->andReturnUsing(function (string $phone) {
                $digits = preg_replace('/\D+/', '', $phone) ?? '';

                return [
                    'digits' => $digits,
                    'jid' => $digits !== '' ? $digits.'@s.whatsapp.net' : null,
                    'exists' => true,
                    'checked' => true,
                ];
            });

        app()->instance(EvolutionWhatsAppNumberResolver::class, $mock);
    }
}
