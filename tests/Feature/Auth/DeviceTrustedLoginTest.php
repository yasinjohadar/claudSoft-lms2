<?php

use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
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
    expect($device->trusted_at)->not->toBeNull();
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

test('admin can trust a pending device and user can then login', function () {
    enableTrustedDevicesOnly();
    $user = createActiveUser();

    $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    $this->assertAuthenticated();
    auth()->logout();

    $response = $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'));
    $this->assertGuest();
    $response->assertSessionHasErrors('email');

    $pending = UserDevice::query()
        ->where('user_id', $user->id)
        ->where('is_trusted', false)
        ->latest('id')
        ->first();

    expect($pending)->not->toBeNull();

    expect($pending->trust())->toBeTrue();
    $pending->refresh();
    expect($pending->is_trusted)->toBeTrue();
    expect($pending->is_blocked)->toBeFalse();

    $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'));
    $this->assertAuthenticatedAs($user);
});

test('trust clears blocked flag', function () {
    $user = createActiveUser();

    $device = UserDevice::create([
        'user_id' => $user->id,
        'device_fingerprint' => 'fp-blocked',
        'device_type' => 'desktop',
        'browser' => 'Chrome',
        'platform' => 'Windows',
        'is_trusted' => false,
        'is_blocked' => true,
        'total_logins' => 0,
        'first_used_at' => now(),
        'last_used_at' => now(),
    ]);

    $device->trust();
    $device->refresh();

    expect($device->is_trusted)->toBeTrue();
    expect($device->is_blocked)->toBeFalse();
});

test('trust records its date and untrust clears it', function () {
    $user = createActiveUser();
    $device = UserDevice::create([
        'user_id' => $user->id,
        'device_fingerprint' => 'fp-trust-date',
        'device_type' => 'desktop',
        'browser' => 'Chrome',
        'platform' => 'Windows',
        'is_trusted' => false,
        'is_blocked' => false,
        'total_logins' => 0,
        'first_used_at' => now(),
        'last_used_at' => now(),
    ]);

    $device->trust();
    expect($device->fresh()->trusted_at)->not->toBeNull();

    $device->untrust();
    expect($device->fresh()->trusted_at)->toBeNull();
});

test('same client token matches existing device even if fingerprint components change', function () {
    $service = app(DeviceTrackingService::class);
    $user = createActiveUser();
    $token = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';

    $chromeRequest = Request::create('/', 'GET', ['device_token' => $token], [], [], [
        'HTTP_USER_AGENT' => TEST_USER_AGENT,
    ]);

    $device = $service->trackDeviceOnLogin($user, $chromeRequest, true);
    expect($device->is_trusted)->toBeTrue();

    $edgeUa = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0';
    $edgeRequest = Request::create('/', 'GET', ['device_token' => $token], [], [], [
        'HTTP_USER_AGENT' => $edgeUa,
    ]);

    $matched = $service->findDeviceForRequest($user, $edgeRequest);
    expect($matched)->not->toBeNull();
    expect($matched->id)->toBe($device->id);
});

test('android is detected before linux in user agent', function () {
    $service = app(DeviceTrackingService::class);
    $request = Request::create('/', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
    ]);

    $info = $service->detectDeviceInfo($request);
    expect($info['platform'])->toBe('Android');
});

test('enforcement activates when user belongs to a restricted group even if global policy is off', function () {
    disableTrustedDevicesOnly();

    $user = createActiveUser();

    $group = \App\Models\CourseGroup::create([
        'name' => 'مجموعة مقيدة',
        'is_active' => true,
        'device_lock_enabled' => true,
    ]);

    \App\Models\CourseGroupMember::create([
        'group_id' => $group->id,
        'student_id' => $user->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect(app(DeviceSecuritySettingsService::class)->isEnforcementActiveForUser($user->fresh()))->toBeTrue();
});

test('enforcement stays off when user groups are not restricted', function () {
    disableTrustedDevicesOnly();

    $user = createActiveUser();

    $group = \App\Models\CourseGroup::create([
        'name' => 'مجموعة عادية',
        'is_active' => true,
        'device_lock_enabled' => false,
    ]);

    \App\Models\CourseGroupMember::create([
        'group_id' => $group->id,
        'student_id' => $user->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect(app(DeviceSecuritySettingsService::class)->isEnforcementActiveForUser($user->fresh()))->toBeFalse();
});

test('security settings can synchronize restricted groups', function () {
    $restrictedGroup = CourseGroup::create([
        'name' => 'مجموعة مقيدة',
        'is_active' => true,
        'device_lock_enabled' => false,
    ]);
    $normalGroup = CourseGroup::create([
        'name' => 'مجموعة عادية',
        'is_active' => true,
        'device_lock_enabled' => true,
    ]);

    app(DeviceSecuritySettingsService::class)->syncRestrictedGroups([$restrictedGroup->id]);

    expect($restrictedGroup->fresh()->device_lock_enabled)->toBeTrue();
    expect($normalGroup->fresh()->device_lock_enabled)->toBeFalse();
});

test('explicit per-user disabled overrides restricted group', function () {
    disableTrustedDevicesOnly();

    $user = createActiveUser(['device_lock_mode' => 'disabled']);

    $group = \App\Models\CourseGroup::create([
        'name' => 'مجموعة مقيدة',
        'is_active' => true,
        'device_lock_enabled' => true,
    ]);

    \App\Models\CourseGroupMember::create([
        'group_id' => $group->id,
        'student_id' => $user->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect(app(DeviceSecuritySettingsService::class)->isEnforcementActiveForUser($user->fresh()))->toBeFalse();
});

test('login is denied for new device when user is in a restricted group', function () {
    disableTrustedDevicesOnly();

    $user = createActiveUser();

    $group = \App\Models\CourseGroup::create([
        'name' => 'مجموعة مقيدة',
        'is_active' => true,
        'device_lock_enabled' => true,
    ]);

    \App\Models\CourseGroupMember::create([
        'group_id' => $group->id,
        'student_id' => $user->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);

    // First device auto-trusted (default auto_trust_first_device seed is true, but disabled globally seeds default)
    SystemSetting::set('auto_trust_first_device', true, 'boolean', DeviceSecuritySettingsService::GROUP);

    $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    $this->assertAuthenticatedAs($user);
    auth()->logout();

    $response = $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'));
    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('new untrusted device notifies admin users', function () {
    \Illuminate\Support\Facades\Notification::fake();

    enableTrustedDevicesOnly();

    $admin = createActiveUser(['email' => 'admin-notify@example.com']);
    $admin->assignRole(\Spatie\Permission\Models\Role::findOrCreate('admin', 'web'));

    $user = createActiveUser();

    $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    auth()->logout();

    $this->post('/login', deviceLoginPayload($user->email), deviceLoginHeaders('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'));
    $this->assertGuest();

    \Illuminate\Support\Facades\Notification::assertSentTo(
        $admin,
        \App\Notifications\NewUntrustedDeviceNotification::class
    );
});

test('device can be matched by client fingerprint when token is absent', function () {
    $user = createActiveUser();
    $clientFp = str_repeat('a', 64);

    UserDevice::create([
        'user_id' => $user->id,
        'device_fingerprint' => 'old-fingerprint-value',
        'device_type' => 'desktop',
        'browser' => 'Chrome',
        'platform' => 'Windows',
        'is_trusted' => true,
        'is_blocked' => false,
        'total_logins' => 1,
        'first_used_at' => now(),
        'last_used_at' => now(),
        'meta' => ['client_fp' => $clientFp],
    ]);

    $request = Request::create('/', 'GET', ['device_fingerprint_client' => $clientFp], [], [], [
        'HTTP_USER_AGENT' => TEST_USER_AGENT,
    ]);

    $matched = app(DeviceTrackingService::class)->findDeviceForRequest($user, $request);

    expect($matched)->not->toBeNull();
    expect($matched->meta['client_fp'] ?? null)->toBe($clientFp);
});

test('legacy token-based devices still match after stronger fingerprint is available', function () {
    $user = createActiveUser();
    $token = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';

    UserDevice::create([
        'user_id' => $user->id,
        'device_fingerprint' => 'legacy-fp',
        'device_type' => 'desktop',
        'browser' => 'Chrome',
        'platform' => 'Windows',
        'is_trusted' => true,
        'is_blocked' => false,
        'total_logins' => 3,
        'first_used_at' => now(),
        'last_used_at' => now(),
        'meta' => ['client_device_token' => $token],
    ]);

    $request = Request::create('/', 'GET', [
        'device_token' => $token,
        'device_fingerprint_client' => str_repeat('b', 64),
    ], [], [], [
        'HTTP_USER_AGENT' => TEST_USER_AGENT,
    ]);

    $matched = app(DeviceTrackingService::class)->findDeviceForRequest($user, $request);

    expect($matched)->not->toBeNull();
    expect($matched->meta['client_device_token'] ?? null)->toBe($token);
});
