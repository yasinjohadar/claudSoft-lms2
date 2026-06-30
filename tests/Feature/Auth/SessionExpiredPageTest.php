<?php

use App\Support\SessionExpiredRedirect;
use Illuminate\Http\Request;
use Tests\Feature\Auth\AuthSiteSettingTestCase;

uses(AuthSiteSettingTestCase::class);

test('session expired redirect resolves login by default', function () {
    $request = Request::create('/login', 'POST');

    $redirect = SessionExpiredRedirect::resolve($request);

    expect($redirect['url'])->toContain('/login');
    expect($redirect['url'])->toContain('session_expired=1');
    expect($redirect['label'])->toBe('تحديث الصفحة وتسجيل الدخول');
});

test('session expired redirect follows referer for register', function () {
    $request = Request::create('/register', 'POST');
    $request->headers->set('referer', 'https://claudsoft.com/register');

    $redirect = SessionExpiredRedirect::resolve($request);

    expect($redirect['url'])->toContain('/register');
    expect($redirect['label'])->toBe('العودة لصفحة التسجيل');
});

test('session expired page view renders arabic content', function () {
    $html = view('errors.419', [
        'redirect' => SessionExpiredRedirect::resolve(Request::create('/login', 'POST')),
    ])->render();

    expect($html)->toContain('انتهت صلاحية الجلسة');
    expect($html)->toContain('تحديث الصفحة وتسجيل الدخول');
});
