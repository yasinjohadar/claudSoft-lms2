<?php

namespace Tests\Feature;

use App\Models\SessionActivity;
use App\Models\User;
use App\Models\UserSession;
use App\Services\SessionTrackingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class SessionActivityTrackingTest extends TestCase
{
    use DatabaseTransactions;

    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', 'cloudsoft_platform');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session_tracking.enabled', true);
        $app['config']->set('session_tracking.dedup_seconds', 30);
        $app['config']->set('session_tracking.cache_ttl_seconds', 35);
        $app['config']->set('activitylog.enabled', false);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function makeActiveSession(User $user): UserSession
    {
        return UserSession::create([
            'user_id' => $user->id,
            'session_uuid' => (string) Str::uuid(),
            'session_name' => 'Test session',
            'started_at' => now(),
            'status' => 'active',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);
    }

    public function test_skips_repeated_focus_lost_within_dedup_window(): void
    {
        $user = User::factory()->create();
        $session = $this->makeActiveSession($user);
        $service = app(SessionTrackingService::class);

        $first = $service->trackActivity($session->id, 'focus_lost', ['page_url' => '/a']);
        $second = $service->trackActivity($session->id, 'focus_lost', ['page_url' => '/a']);

        $this->assertNotNull($first);
        $this->assertSame(1, SessionActivity::where('user_session_id', $session->id)->where('activity_type', 'focus_lost')->count());
        $this->assertTrue($service->wasLastActivitySkipped());
    }

    public function test_updates_idle_start_within_dedup_window(): void
    {
        $user = User::factory()->create();
        $session = $this->makeActiveSession($user);
        $service = app(SessionTrackingService::class);

        $first = $service->trackActivity($session->id, 'idle_start', []);
        $this->assertNotNull($first);

        $first->forceFill(['occurred_at' => now()->subSeconds(5)])->save();
        Cache::flush();

        $second = $service->trackActivity($session->id, 'idle_start', ['note' => 'again']);

        $this->assertNotNull($second);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, SessionActivity::where('user_session_id', $session->id)->where('activity_type', 'idle_start')->count());
        $this->assertFalse($service->wasLastActivitySkipped());
    }

    public function test_always_inserts_action_and_learning_events(): void
    {
        $user = User::factory()->create();
        $session = $this->makeActiveSession($user);
        $service = app(SessionTrackingService::class);

        $service->trackActivity($session->id, 'action', ['action_name' => 'click']);
        $service->trackActivity($session->id, 'action', ['action_name' => 'click']);
        $service->trackActivity($session->id, 'quiz_submit', ['quiz_id' => 1]);
        $service->trackActivity($session->id, 'quiz_submit', ['quiz_id' => 1]);

        $this->assertSame(2, SessionActivity::where('user_session_id', $session->id)->where('activity_type', 'action')->count());
        $this->assertSame(2, SessionActivity::where('user_session_id', $session->id)->where('activity_type', 'quiz_submit')->count());
    }

    public function test_client_session_end_is_rejected_with_422(): void
    {
        $user = User::factory()->create();
        $session = $this->makeActiveSession($user);

        $response = $this->actingAs($user)
            ->withSession(['user_session_id' => $session->id])
            ->postJson(route('session.track'), [
                'activity_type' => 'session_end',
            ]);

        $response->assertStatus(422);
    }

    public function test_client_disconnect_is_accepted(): void
    {
        $user = User::factory()->create();
        $session = $this->makeActiveSession($user);

        $response = $this->actingAs($user)
            ->withSession(['user_session_id' => $session->id])
            ->postJson(route('session.track'), [
                'activity_type' => 'disconnect',
            ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('session_activities', [
            'user_session_id' => $session->id,
            'activity_type' => 'disconnect',
        ]);
    }

    public function test_heartbeat_does_not_create_activity_rows(): void
    {
        $user = User::factory()->create();
        $session = $this->makeActiveSession($user);
        $before = SessionActivity::where('user_session_id', $session->id)->count();

        $response = $this->actingAs($user)
            ->withSession(['user_session_id' => $session->id])
            ->postJson(route('session.heartbeat'));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertSame($before, SessionActivity::where('user_session_id', $session->id)->count());
    }
}
