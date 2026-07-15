<?php

use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\DeviceSecuritySettingsService;
use App\Services\SingleSessionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Auth\DeviceSecurityTestCase;

uses(DeviceSecurityTestCase::class);

beforeEach(function () {
    Schema::table('users', function (Blueprint $table) {
        if (! Schema::hasColumn('users', 'active_session_id')) {
            $table->string('active_session_id', 128)->nullable();
        }
    });

    Schema::dropIfExists('sessions');
    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });

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

function createSingleSessionUser(array $overrides = []): User
{
    return User::query()->create(array_merge([
        'name' => 'Session User',
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'device_lock_mode' => 'inherit',
    ], $overrides));
}

function enableSingleSession(): void
{
    SystemSetting::set('single_session_enabled', true, 'boolean', DeviceSecuritySettingsService::GROUP);
}

function disableSingleSession(): void
{
    SystemSetting::set('single_session_enabled', false, 'boolean', DeviceSecuritySettingsService::GROUP);
}

test('second login becomes the only active session and marks previous tracking as disconnected', function () {
    enableSingleSession();
    $user = createSingleSessionUser();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_token' => '11111111-2222-4333-8444-555555555555',
    ], ['User-Agent' => 'Mozilla/5.0 Chrome/149.0']);

    $this->assertAuthenticatedAs($user);
    $firstSessionId = session()->getId();
    expect($user->fresh()->active_session_id)->toBe($firstSessionId);

    DB::table('sessions')->insert([
        'id' => $firstSessionId,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Chrome',
        'payload' => 'a',
        'last_activity' => time(),
    ]);
    DB::table('user_sessions')->insert([
        'user_id' => $user->id,
        'session_uuid' => (string) fake()->uuid(),
        'session_name' => 'جلسة سابقة',
        'started_at' => now()->subHour(),
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Auth::logout();
    $this->flushSession();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_token' => '11111111-2222-4333-8444-555555555555',
    ], ['User-Agent' => 'Mozilla/5.0 Chrome/149.0']);

    $this->assertAuthenticatedAs($user);
    $secondSessionId = session()->getId();
    expect($secondSessionId)->not->toBe($firstSessionId);
    expect($user->fresh()->active_session_id)->toBe($secondSessionId);

    expect(DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $secondSessionId)->count())->toBe(0);
    expect(DB::table('user_sessions')->where('user_id', $user->id)->where('status', 'disconnected')->count())->toBe(1);
});

test('middleware logs out when active_session_id no longer matches', function () {
    enableSingleSession();
    $user = createSingleSessionUser();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $this->assertAuthenticatedAs($user);

    $user->forceFill(['active_session_id' => 'other-device-session'])->save();

    $response = $this->get('/login');
    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
});

test('single session is not enforced when setting is disabled', function () {
    disableSingleSession();
    $user = createSingleSessionUser();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->active_session_id)->toBeNull();

    $user->forceFill(['active_session_id' => 'stale'])->save();
    $this->get('/login');
    $this->assertAuthenticatedAs($user);
});

test('restricted group activates single session even when global setting is off', function () {
    disableSingleSession();
    $user = createSingleSessionUser();
    $group = CourseGroup::query()->create([
        'name' => 'Restricted Group',
        'is_active' => true,
        'device_lock_enabled' => true,
    ]);
    CourseGroupMember::query()->create([
        'group_id' => $group->id,
        'student_id' => $user->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);

    expect(app(DeviceSecuritySettingsService::class)->isSingleSessionActiveForUser($user))->toBeTrue();

    $request = Request::create('/', 'GET');
    $request->setLaravelSession(app('session.store'));
    $request->session()->start();

    app(SingleSessionService::class)->enforce($user, $request);

    expect($user->fresh()->active_session_id)->toBe($request->session()->getId());
});

test('logout clears active_session_id', function () {
    enableSingleSession();
    $user = createSingleSessionUser();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->active_session_id)->not->toBeNull();

    $this->post('/logout');
    $this->assertGuest();
    expect($user->fresh()->active_session_id)->toBeNull();
});
