<?php

use App\Models\EvolutionInstance;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Services\Auth\PasswordCredentialDeliveryService;
use App\Services\Auth\PasswordResetMessageRenderer;
use App\Services\WhatsApp\Evolution\EvolutionInstanceRotator;
use App\Services\WhatsApp\Evolution\EvolutionRotatingSendService;
use App\Services\WhatsApp\Evolution\EvolutionWhatsAppNumberResolver;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

uses(TestCase::class);

test('password credential whatsapp uses sticky instance for credentials and password-only then marks used', function () {
    Mail::fake();
    Cache::flush();

    $instanceA = new EvolutionInstance(['instance_name' => 'evo-pwd-a']);
    $instanceA->id = 1;
    $instanceB = new EvolutionInstance(['instance_name' => 'evo-pwd-b']);
    $instanceB->id = 2;

    $user = new User([
        'name' => 'Test Student',
        'email' => 'pwd-sticky@example.com',
        'full_phone' => '+905551112233',
        'country_code' => '+90',
        'phone' => '5551112233',
    ]);
    $user->id = 42;

    $settings = Mockery::mock(WhatsAppSettingsService::class);
    $settings->shouldReceive('getSettings')->andReturn([
        'whatsapp_enabled' => true,
        'whatsapp_provider' => 'evolution',
        'evolution_base_url' => 'https://evo.example',
        'evolution_api_key' => 'key',
        'evolution_instance_name' => 'evo-pwd-a',
        'evolution_rotation_enabled' => true,
    ]);

    $rotator = Mockery::mock(EvolutionInstanceRotator::class);
    $rotator->shouldReceive('poolCount')->zeroOrMoreTimes()->andReturn(2);
    $rotator->shouldReceive('orderedPoolForFailover')->with(true)->andReturn(collect([$instanceA, $instanceB]));
    $rotator->shouldReceive('markUsed')->zeroOrMoreTimes();

    $rotatingSend = Mockery::mock(EvolutionRotatingSendService::class);
    $rotatingSend->shouldReceive('isRotationActive')->andReturn(true);

    $resolver = Mockery::mock(EvolutionWhatsAppNumberResolver::class);
    $resolver->shouldReceive('resolve')->andReturn([
        'digits' => '905551112233',
        'jid' => '905551112233@s.whatsapp.net',
        'exists' => true,
        'checked' => true,
    ]);

    $accepted = new WhatsAppMessage([
        'status' => WhatsAppMessage::STATUS_SENT,
        'meta_message_id' => 'pwd_sticky_msg_1234567890',
    ]);

    $instancesUsed = [];
    $sender = Mockery::mock(SendWhatsAppMessage::class);
    $sender->shouldReceive('sendTextSync')
        ->twice()
        ->withArgs(function ($to, $text, $preview, $delay, $instance) use (&$instancesUsed) {
            $instancesUsed[] = $instance;

            return $instance === 'evo-pwd-a';
        })
        ->andReturn($accepted);

    $renderer = Mockery::mock(PasswordResetMessageRenderer::class);
    $renderer->shouldReceive('renderCredentialWhatsApp')
        ->once()
        ->andReturn("بيانات الدخول\n📧 البريد: pwd-sticky@example.com\n🔑 كلمة المرور: SecretPass123!");

    $service = new PasswordCredentialDeliveryService(
        $renderer,
        $settings,
        $sender,
        $rotator,
        $rotatingSend,
        $resolver,
    );

    $result = $service->deliver(
        $user,
        'SecretPass123!',
        PasswordCredentialDeliveryService::CONTEXT_FORGOT_AUTO,
        requireWhatsApp: true,
    );

    expect($result['whatsapp_sent'])->toBeTrue()
        ->and($instancesUsed)->toHaveCount(2)
        ->and(array_unique($instancesUsed))->toBe(['evo-pwd-a'])
        ->and(Cache::get('evolution_pwd_cred_last_instance'))->toBe('evo-pwd-a');
});

test('second password credential delivery prefers a different sticky instance', function () {
    Mail::fake();
    Cache::flush();
    Cache::put('evolution_pwd_cred_last_instance', 'evo-pwd-a', now()->addDay());

    $instanceA = new EvolutionInstance(['instance_name' => 'evo-pwd-a']);
    $instanceB = new EvolutionInstance(['instance_name' => 'evo-pwd-b']);

    $user = new User([
        'name' => 'Test Student',
        'email' => 'pwd-sticky-2@example.com',
        'full_phone' => '+905551112244',
        'country_code' => '+90',
        'phone' => '5551112244',
    ]);
    $user->id = 43;

    $settings = Mockery::mock(WhatsAppSettingsService::class);
    $settings->shouldReceive('getSettings')->andReturn([
        'whatsapp_enabled' => true,
        'whatsapp_provider' => 'evolution',
        'evolution_base_url' => 'https://evo.example',
        'evolution_api_key' => 'key',
        'evolution_instance_name' => 'evo-pwd-a',
        'evolution_rotation_enabled' => true,
    ]);

    $rotator = Mockery::mock(EvolutionInstanceRotator::class);
    $rotator->shouldReceive('poolCount')->zeroOrMoreTimes()->andReturn(2);
    $rotator->shouldReceive('orderedPoolForFailover')->with(true)->andReturn(collect([$instanceA, $instanceB]));
    $rotator->shouldReceive('markUsed')->zeroOrMoreTimes();

    $rotatingSend = Mockery::mock(EvolutionRotatingSendService::class);
    $rotatingSend->shouldReceive('isRotationActive')->andReturn(true);

    $resolver = Mockery::mock(EvolutionWhatsAppNumberResolver::class);
    $resolver->shouldReceive('resolve')->andReturn([
        'digits' => '905551112244',
        'jid' => '905551112244@s.whatsapp.net',
        'exists' => true,
        'checked' => true,
    ]);

    $accepted = new WhatsAppMessage([
        'status' => WhatsAppMessage::STATUS_SENT,
        'meta_message_id' => 'pwd_sticky_msg_abcdef123456',
    ]);

    $instancesUsed = [];
    $sender = Mockery::mock(SendWhatsAppMessage::class);
    $sender->shouldReceive('sendTextSync')
        ->twice()
        ->withArgs(function ($to, $text, $preview, $delay, $instance) use (&$instancesUsed) {
            $instancesUsed[] = $instance;

            return $instance === 'evo-pwd-b';
        })
        ->andReturn($accepted);

    $renderer = Mockery::mock(PasswordResetMessageRenderer::class);
    $renderer->shouldReceive('renderCredentialWhatsApp')->once()->andReturn('credentials body 📧');

    $service = new PasswordCredentialDeliveryService(
        $renderer,
        $settings,
        $sender,
        $rotator,
        $rotatingSend,
        $resolver,
    );

    $result = $service->deliver(
        $user,
        'OtherPass123!',
        PasswordCredentialDeliveryService::CONTEXT_ADMIN_RESET,
        requireWhatsApp: true,
    );

    expect($result['whatsapp_sent'])->toBeTrue()
        ->and(array_unique($instancesUsed))->toBe(['evo-pwd-b'])
        ->and(Cache::get('evolution_pwd_cred_last_instance'))->toBe('evo-pwd-b');
});
