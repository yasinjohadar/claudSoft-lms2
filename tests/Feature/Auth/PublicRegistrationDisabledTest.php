<?php

use App\Models\SiteSetting;
use Tests\Feature\Auth\AuthSiteSettingTestCase;

uses(AuthSiteSettingTestCase::class);

function disablePublicRegistration(): void
{
    SiteSetting::setValue('registration_public_enabled', false, 'تفعيل التسجيل العام للمستخدمين');
}

function enablePublicRegistration(): void
{
    SiteSetting::setValue('registration_public_enabled', true, 'تفعيل التسجيل العام للمستخدمين');
}

test('register page is blocked when public registration is disabled', function () {
    disablePublicRegistration();

    $response = $this->get('/register');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

test('register post is blocked when public registration is disabled', function () {
    disablePublicRegistration();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('login page hides create account link when public registration is disabled', function () {
    disablePublicRegistration();

    $response = $this->get('/login');

    $response->assertOk();
    $response->assertDontSee('إنشاء حساب جديد');
    $response->assertDontSee(route('register'));
});

test('login page shows create account link when public registration is enabled', function () {
    enablePublicRegistration();

    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('إنشاء حساب جديد');
});
