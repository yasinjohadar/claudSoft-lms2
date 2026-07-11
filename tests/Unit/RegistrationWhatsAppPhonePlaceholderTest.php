<?php

use App\Models\CourseGroup;
use App\Models\GroupRegistration;
use App\Models\WhatsAppMessageTemplate;
use App\Services\RegistrationWhatsAppService;
use Tests\TestCase;

uses(TestCase::class);

test('registration whatsapp placeholders include phone aliases', function () {
    $registration = new GroupRegistration([
        'name' => 'Yasin',
        'name_ar' => 'ياسين',
        'email' => 'yasin@example.com',
        'phone' => '5519665883',
        'country_code' => '+90',
        'full_phone' => '+905519665883',
    ]);
    $registration->id = 77;

    $group = new CourseGroup(['name' => 'دبلوم البرمجة']);

    $method = new \ReflectionMethod(RegistrationWhatsAppService::class, 'registrationPlaceholders');
    $method->setAccessible(true);

    $placeholders = $method->invoke(app(RegistrationWhatsAppService::class), $registration, $group);

    expect($placeholders['phone'])->toBe('+905519665883')
        ->and($placeholders['full_phone'])->toBe('+905519665883')
        ->and($placeholders['student_phone'])->toBe('+905519665883')
        ->and($placeholders['email'])->toBe('yasin@example.com')
        ->and($placeholders['group_name'])->toBe('دبلوم البرمجة');
});

test('whatsapp template render replaces phone placeholder', function () {
    $template = new WhatsAppMessageTemplate([
        'body' => 'مرحباً {student_name} رقمك {phone} وبريدك {email}',
        'type' => 'text',
        'language' => 'ar',
        'is_active' => true,
    ]);

    $rendered = $template->render([
        'student_name' => 'ياسين',
        'phone' => '+905519665883',
        'email' => 'yasin@example.com',
    ]);

    expect($rendered)->toContain('+905519665883')
        ->and($rendered)->not->toContain('{phone}')
        ->and($rendered)->toContain('ياسين');
});
