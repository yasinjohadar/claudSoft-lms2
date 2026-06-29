<?php

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\DeviceSecuritySettingsService;
use App\Services\DeviceTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Auth\DeviceSecurityTestCase;

uses(DeviceSecurityTestCase::class);

const TEST_DEVICE_TOKEN = '11111111-2222-4333-8444-555555555555';
const TEST_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36';

function createActiveUser(array $overrides = []): User
{
    return User::query()->create(array_merge([
        'name' => 'Test User',
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'device_lock_mode' => 'inherit',
    ], $overrides));
}

function deviceLoginPayload(string $email, string $password = 'password', ?string $token = TEST_DEVICE_TOKEN): array
{
    return [
        'email' => $email,
        'password' => $password,
        'device_token' => $token,
    ];
}

function deviceLoginHeaders(?string $token = TEST_DEVICE_TOKEN): array
{
    return [
        'User-Agent' => TEST_USER_AGENT,
        'X-Device-Token' => $token,
    ];
}

function enableTrustedDevicesOnly(bool $autoTrustFirst = true): void
{
    SystemSetting::set('trusted_devices_only_enabled', true, 'boolean', DeviceSecuritySettingsService::GROUP);
    SystemSetting::set('auto_trust_first_device', $autoTrustFirst, 'boolean', DeviceSecuritySettingsService::GROUP);
}

function disableTrustedDevicesOnly(): void
{
    SystemSetting::set('trusted_devices_only_enabled', false, 'boolean', DeviceSecuritySettingsService::GROUP);
}

test('login succeeds when trusted devices policy is disabled globally', function () {
    disableTrustedDevicesOnly();
    $user = createActiveUser();

    $response = $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders());

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect();
});

test('first device is auto-trusted and login succeeds when policy is enabled', function () {
    enableTrustedDevicesOnly();
    $user = createActiveUser();

    $response = $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders());

    $this->assertAuthenticatedAs($user);

    $device = UserDevice::where('user_id', $user->id)->first();
    expect($device)->not->toBeNull();
    expect($device->is_trusted)->toBeTrue();
});

test('untrusted new device is denied when user already has a trusted device', function () {
    enableTrustedDevicesOnly();
    $user = createActiveUser();

    $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    $this->assertAuthenticated();
    auth()->logout();

    $response = $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'));

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
    expect(UserDevice::where('user_id', $user->id)->count())->toBe(2);
});

test('user with device lock disabled bypasses global policy', function () {
    enableTrustedDevicesOnly();

    $user = createActiveUser(['device_lock_mode' => 'disabled']);

    UserDevice::create([
        'user_id' => $user->id,
        'device_fingerprint' => app(DeviceTrackingService::class)->generateDeviceFingerprint(
            Request::create('/', 'GET', [], [], [], [
                'HTTP_USER_AGENT' => TEST_USER_AGENT,
                'HTTP_X_DEVICE_TOKEN' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            ])
        ),
        'device_type' => 'desktop',
        'browser' => 'Chrome',
        'platform' => 'Windows',
        'is_trusted' => true,
        'is_blocked' => false,
        'total_logins' => 1,
        'first_used_at' => now(),
        'last_used_at' => now(),
    ]);

    $response = $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('dddddddd-dddd-4ddd-8ddd-dddddddddddd'));

    $this->assertAuthenticatedAs($user);
});

test('user with device lock enabled is enforced even when global policy is off', function () {
    disableTrustedDevicesOnly();

    $user = createActiveUser(['device_lock_mode' => 'enabled']);

    UserDevice::create([
        'user_id' => $user->id,
        'device_fingerprint' => app(DeviceTrackingService::class)->generateDeviceFingerprint(
            Request::create('/', 'GET', [], [], [], [
                'HTTP_USER_AGENT' => TEST_USER_AGENT,
                'HTTP_X_DEVICE_TOKEN' => 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
            ])
        ),
        'device_type' => 'desktop',
        'browser' => 'Chrome',
        'platform' => 'Windows',
        'is_trusted' => true,
        'is_blocked' => false,
        'total_logins' => 1,
        'first_used_at' => now(),
        'last_used_at' => now(),
    ]);

    $response = $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('ffffffff-ffff-4fff-8fff-ffffffffffff'));

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('blocked device cannot login when policy is enabled', function () {
    enableTrustedDevicesOnly();
    $user = createActiveUser();

    $fingerprint = app(DeviceTrackingService::class)->generateDeviceFingerprint(
        Request::create('/', 'GET', ['device_token' => TEST_DEVICE_TOKEN], [], [], [
            'HTTP_USER_AGENT' => TEST_USER_AGENT,
        ])
    );

    UserDevice::create([
        'user_id' => $user->id,
        'device_fingerprint' => $fingerprint,
        'device_type' => 'desktop',
        'browser' => 'Chrome',
        'platform' => 'Windows',
        'is_trusted' => false,
        'is_blocked' => true,
        'total_logins' => 0,
        'first_used_at' => now(),
        'last_used_at' => now(),
    ]);

    $response = $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders());

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('device fingerprint ignores ip address changes', function () {
    $service = app(DeviceTrackingService::class);

    $requestA = Request::create('/', 'GET', ['device_token' => TEST_DEVICE_TOKEN], [], [], [
        'HTTP_USER_AGENT' => TEST_USER_AGENT,
        'REMOTE_ADDR' => '127.0.0.1',
    ]);

    $requestB = Request::create('/', 'GET', ['device_token' => TEST_DEVICE_TOKEN], [], [], [
        'HTTP_USER_AGENT' => TEST_USER_AGENT,
        'REMOTE_ADDR' => '10.0.0.50',
    ]);

    expect($service->generateDeviceFingerprint($requestA))
        ->toBe($service->generateDeviceFingerprint($requestB));
});
