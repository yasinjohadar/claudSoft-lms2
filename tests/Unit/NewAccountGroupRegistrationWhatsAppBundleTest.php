<?php

use App\Models\EvolutionInstance;
use App\Models\GroupRegistration;
use App\Models\User;
use App\Models\WhatsAppMessage;
use App\Services\Auth\AccountCreatedCredentialDeliveryService;
use App\Services\Auth\NewAccountGroupRegistrationWhatsAppBundleService;
use App\Services\RegistrationWhatsAppService;
use App\Services\WhatsApp\Evolution\EvolutionInstanceRotator;
use App\Services\WhatsApp\Evolution\EvolutionRotatingSendService;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Tests\TestCase;

uses(TestCase::class);

test('credential deliver accepts optional evolution instance without breaking default signature', function () {
    $ref = new ReflectionMethod(AccountCreatedCredentialDeliveryService::class, 'deliver');
    $names = array_map(fn (ReflectionParameter $p) => $p->getName(), $ref->getParameters());

    expect($names)->toContain('evolutionInstanceName');

    $instanceParam = collect($ref->getParameters())->first(
        fn (ReflectionParameter $p) => $p->getName() === 'evolutionInstanceName'
    );

    expect($instanceParam->isDefaultValueAvailable())->toBeTrue()
        ->and($instanceParam->getDefaultValue())->toBeNull();
});

test('registration whatsapp service exposes evolution welcome text builder', function () {
    expect(method_exists(RegistrationWhatsAppService::class, 'buildWelcomeTextForGroup'))->toBeTrue()
        ->and(method_exists(RegistrationWhatsAppService::class, 'markWelcomeSent'))->toBeTrue();
});

test('new account bundle sends welcome and credentials on the same sticky instance then marks used', function () {
    $instanceName = 'evo-sticky-a';
    $evoInstance = new EvolutionInstance(['instance_name' => $instanceName]);

    $registration = Mockery::mock(GroupRegistration::class)->makePartial();
    $registration->id = 99;
    $registration->full_phone = '+905551112233';
    $registration->shouldReceive('update')->zeroOrMoreTimes()->andReturnTrue();

    $user = new User([
        'name' => 'Student',
        'email' => 'student@example.com',
        'full_phone' => '+905551112233',
    ]);
    $user->id = 7;

    $welcomeService = Mockery::mock(RegistrationWhatsAppService::class);
    $welcomeService->shouldReceive('buildWelcomeTextForGroup')
        ->once()
        ->with($registration)
        ->andReturn('مرحباً بك في المجموعة');
    $welcomeService->shouldReceive('markWelcomeSent')
        ->once()
        ->with($registration);

    $accepted = new WhatsAppMessage([
        'status' => WhatsAppMessage::STATUS_SENT,
        'meta_message_id' => 'evo_accepted_message_id_1234567890',
    ]);

    $sender = Mockery::mock(SendWhatsAppMessage::class);
    $sender->shouldReceive('sendTextSync')
        ->once()
        ->withArgs(function ($to, $text, $preview, $delay, $instance) use ($instanceName) {
            return $text === 'مرحباً بك في المجموعة' && $instance === $instanceName;
        })
        ->andReturn($accepted);

    $credentialDelivery = Mockery::mock(AccountCreatedCredentialDeliveryService::class);
    $credentialDelivery->shouldReceive('deliver')
        ->once()
        ->withArgs(function ($u, $password, $context, $sendEmail, $sendWhatsApp, $override, $instance) use ($user, $instanceName) {
            return $u === $user
                && $password === 'SecretPass123!'
                && $sendEmail === true
                && $sendWhatsApp === true
                && $override === null
                && $instance === $instanceName;
        })
        ->andReturn([
            'email_sent' => true,
            'whatsapp_sent' => true,
            'whatsapp_recipient' => '+905551112233',
            'email_error' => null,
            'whatsapp_error' => null,
        ]);

    $rotator = Mockery::mock(EvolutionInstanceRotator::class);
    $rotator->shouldReceive('poolCount')->andReturn(1);
    $rotator->shouldReceive('nextInstance')->once()->andReturn($evoInstance);
    $rotator->shouldReceive('markUsed')->once()->with($evoInstance);

    $rotatingSend = Mockery::mock(EvolutionRotatingSendService::class);
    $rotatingSend->shouldReceive('isRotationActive')->andReturn(true);

    $settings = Mockery::mock(WhatsAppSettingsService::class);
    $settings->shouldReceive('getSettings')->andReturn([
        'whatsapp_provider' => 'evolution',
    ]);

    $bundle = new NewAccountGroupRegistrationWhatsAppBundleService(
        $welcomeService,
        $credentialDelivery,
        $sender,
        $rotator,
        $rotatingSend,
        $settings,
    );

    $result = $bundle->deliver($user, $registration, 'SecretPass123!', true, true);

    expect($result['welcome_sent'])->toBeTrue()
        ->and($result['whatsapp_sent'])->toBeTrue()
        ->and($result['email_sent'])->toBeTrue()
        ->and($result['evolution_instance'])->toBe($instanceName);
});

test('new account bundle without whatsapp falls back to email-only credential deliver', function () {
    $user = new User(['email' => 'a@b.com']);
    $user->id = 3;
    $registration = new GroupRegistration;
    $registration->id = 1;

    $credentialDelivery = Mockery::mock(AccountCreatedCredentialDeliveryService::class);
    $credentialDelivery->shouldReceive('deliver')
        ->once()
        ->withArgs(function ($u, $password, $context, $sendEmail, $sendWhatsApp) {
            return $sendEmail === true && $sendWhatsApp === false;
        })
        ->andReturn([
            'email_sent' => true,
            'whatsapp_sent' => false,
            'whatsapp_recipient' => null,
            'email_error' => null,
            'whatsapp_error' => null,
        ]);

    $bundle = new NewAccountGroupRegistrationWhatsAppBundleService(
        Mockery::mock(RegistrationWhatsAppService::class),
        $credentialDelivery,
        Mockery::mock(SendWhatsAppMessage::class),
        Mockery::mock(EvolutionInstanceRotator::class),
        Mockery::mock(EvolutionRotatingSendService::class),
        Mockery::mock(WhatsAppSettingsService::class),
    );

    $result = $bundle->deliver($user, $registration, 'Pass#1', true, false);

    expect($result['email_sent'])->toBeTrue()
        ->and($result['whatsapp_sent'])->toBeFalse()
        ->and($result['welcome_sent'])->toBeFalse();
});
