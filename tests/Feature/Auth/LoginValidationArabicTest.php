<?php

use Tests\TestCase;

uses(TestCase::class);

test('login validation messages are in arabic for invalid email', function () {
    $response = $this->post('/login', [
        'email' => 'retester',
        'password' => '',
    ]);

    $response->assertSessionHasErrors('email');
    $response->assertSessionHasErrors('password');

    expect(session('errors')->get('email')[0])->toBe('يرجى إدخال بريد إلكتروني صحيح.');
    expect(session('errors')->get('password')[0])->toBe('يرجى إدخال كلمة المرور.');
});

test('auth failed translation is arabic', function () {
    app()->setLocale('ar');

    expect(__('auth.failed'))->toBe('بيانات الدخول غير صحيحة. تحقق من البريد الإلكتروني وكلمة المرور.');
});
