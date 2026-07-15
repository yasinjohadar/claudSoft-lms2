<?php

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\DeviceSecuritySettingsService;
use App\Services\SessionDeviceBindingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Auth\DeviceSecurityTestCase;

uses(DeviceSecurityTestCase::class);

const BIND_TOKEN_A = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
const BIND_TOKEN_B = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
const BIND_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36';

beforeEach(function () {
    Schema::dropIfExists('user_sessions');
    Schema::create('user_sessions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->uuid('session_uuid')->nullable();
        $table->string('session_name')->nullable();
        $table->timestamp('started_at')->nullable();
        $table->timestamp('ended_at')->nullable();
        $table->integer('duration_seconds')->nullable();
        $table->string('status')->default('active');
        $table->json('meta')->nullable();
        $table->timestamps();
    });
});

function createBindUser(array $overrides = []): User
{
    return User::query()->create(array_merge([
        'name' => 'Bind User',
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'device_lock_mode' => 'inherit',
    ], $overrides));
}

function enableSessionDeviceBinding(): void
{
    SystemSetting::set('bind_session_to_device_enabled', true, 'boolean', DeviceSecuritySettingsService::GROUP);
}

function disableSessionDeviceBinding(): void
{
    SystemSetting::set('bind_session_to_device_enabled', false, 'boolean', DeviceSecuritySettingsService::GROUP);
}

test('login binds the trusted device id into the session when binding is enabled', function () {
    enableSessionDeviceBinding();
    $user = createBindUser();

    $this->withHeaders([
        'User-Agent' => BIND_UA,
        'X-Device-Token' => BIND_TOKEN_A,
    ])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_token' => BIND_TOKEN_A,
    ]);

    $this->assertAuthenticatedAs($user);

    $device = UserDevice::where('user_id', $user->id)->first();
    expect($device)->not->toBeNull();
    expect(session(SessionDeviceBindingService::SESSION_KEY))->toBe($device->id);
});

test('subsequent request from the same device is allowed', function () {
    enableSessionDeviceBinding();
    $user = createBindUser();

    $this->withHeaders([
        'User-Agent' => BIND_UA,
        'X-Device-Token' => BIND_TOKEN_A,
    ])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_token' => BIND_TOKEN_A,
    ]);
    $this->assertAuthenticatedAs($user);

    $this->withHeaders([
        'User-Agent' => BIND_UA,
        'X-Device-Token' => BIND_TOKEN_A,
    ])->get('/login');

    $this->assertAuthenticatedAs($user);
});

test('copied session without matching device token is logged out', function () {
    enableSessionDeviceBinding();
    $user = createBindUser();

    $this->withHeaders([
        'User-Agent' => BIND_UA,
        'X-Device-Token' => BIND_TOKEN_A,
    ])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_token' => BIND_TOKEN_A,
    ]);
    $this->assertAuthenticatedAs($user);
    expect(session(SessionDeviceBindingService::SESSION_KEY))->not->toBeNull();

    // Unit check: different client token must invalidate the bound session.
    $boundId = session(SessionDeviceBindingService::SESSION_KEY);
    $requestB = Request::create('/login', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => BIND_UA,
        'HTTP_X_DEVICE_TOKEN' => BIND_TOKEN_B,
    ]);
    $requestB->setLaravelSession(app('session.store'));
    $requestB->session()->put(SessionDeviceBindingService::SESSION_KEY, $boundId);
    expect(app(SessionDeviceBindingService::class)->validate($user->fresh(), $requestB))->toBeFalse();

    // Simulate cookie theft via HTTP: keep session, present a different browser identity.
    $response = $this->withHeaders([
        'User-Agent' => BIND_UA,
        'X-Device-Token' => BIND_TOKEN_B,
    ])->get('/login');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

test('blocked bound device forces logout even with a valid session', function () {
    enableSessionDeviceBinding();
    $user = createBindUser();

    $this->withHeaders([
        'User-Agent' => BIND_UA,
        'X-Device-Token' => BIND_TOKEN_A,
    ])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_token' => BIND_TOKEN_A,
    ]);
    $this->assertAuthenticatedAs($user);

    $device = UserDevice::where('user_id', $user->id)->first();
    expect($device)->not->toBeNull();
    $device->block();

    $response = $this->withHeaders([
        'User-Agent' => BIND_UA,
        'X-Device-Token' => BIND_TOKEN_A,
    ])->get('/login');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('session device binding is skipped when setting is disabled', function () {
    disableSessionDeviceBinding();
    $user = createBindUser();

    $this->withHeaders([
        'User-Agent' => BIND_UA,
        'X-Device-Token' => BIND_TOKEN_A,
    ])->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_token' => BIND_TOKEN_A,
    ]);
    $this->assertAuthenticatedAs($user);
    expect(session(SessionDeviceBindingService::SESSION_KEY))->toBeNull();

    // Different token still allowed when binding is off (and trusted-device policy is off).
    $this->withHeaders([
        'User-Agent' => BIND_UA,
        'X-Device-Token' => BIND_TOKEN_B,
    ])->get('/login');
    $this->assertAuthenticatedAs($user);
});

test('settings service exposes bind_session_to_device_enabled independently of single session', function () {
    SystemSetting::set('single_session_enabled', true, 'boolean', DeviceSecuritySettingsService::GROUP);
    SystemSetting::set('bind_session_to_device_enabled', false, 'boolean', DeviceSecuritySettingsService::GROUP);

    $settings = app(DeviceSecuritySettingsService::class);
    $user = createBindUser();

    expect($settings->isSingleSessionActiveForUser($user))->toBeTrue();
    expect($settings->isSessionDeviceBindingActiveForUser($user))->toBeFalse();

    SystemSetting::set('bind_session_to_device_enabled', true, 'boolean', DeviceSecuritySettingsService::GROUP);
    expect($settings->isSessionDeviceBindingActiveForUser($user))->toBeTrue();
});
