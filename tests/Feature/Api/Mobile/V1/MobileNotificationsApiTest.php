<?php

namespace Tests\Feature\Api\Mobile\V1;

use App\Events\UserNotificationBroadcasted;
use App\Models\GamificationNotification;
use App\Models\User;
use App\Services\Gamification\NotificationService;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

class MobileNotificationsApiTest extends MobileApiMysqlTestCase
{
    private function inbox(User $user, array $attrs = []): GamificationNotification
    {
        return GamificationNotification::create(array_merge([
            'user_id' => $user->id,
            'type' => 'course',
            'title' => 'إشعار',
            'message' => 'نص الإشعار',
            'is_read' => false,
        ], $attrs));
    }

    public function test_list_filter_and_unread_count(): void
    {
        $student = $this->completeStudent();
        $this->inbox($student);
        $this->inbox($student, ['is_read' => true, 'read_at' => now()]);
        $this->inbox($this->completeStudent()); // another student's
        Sanctum::actingAs($student);

        $this->getJson('/api/mobile/v1/notifications')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.unread_count', 1)
            ->assertJsonStructure(['data' => [['id', 'type', 'title', 'body', 'icon', 'action_url', 'is_read', 'created_at']]]);

        $this->getJson('/api/mobile/v1/notifications?filter=unread')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/mobile/v1/notifications/unread-count')->assertOk()->assertJsonPath('data.count', 1);
    }

    public function test_mark_read_mark_all_and_delete(): void
    {
        $student = $this->completeStudent();
        $a = $this->inbox($student);
        $b = $this->inbox($student);
        $c = $this->inbox($student);
        Sanctum::actingAs($student);

        $this->postJson("/api/mobile/v1/notifications/{$a->id}/read")->assertOk()->assertJsonPath('data.unread_count', 2);
        $this->assertTrue($a->fresh()->is_read);

        $this->deleteJson("/api/mobile/v1/notifications/{$b->id}")->assertOk()->assertJsonPath('data.unread_count', 1);
        $this->assertNull(GamificationNotification::find($b->id));

        $this->postJson('/api/mobile/v1/notifications/read-all')->assertOk()->assertJsonPath('data.unread_count', 0);
        $this->assertTrue($c->fresh()->is_read);
    }

    public function test_cannot_touch_another_students_notification(): void
    {
        $other = $this->inbox($this->completeStudent());
        Sanctum::actingAs($this->completeStudent());

        $this->postJson("/api/mobile/v1/notifications/{$other->id}/read")->assertNotFound();
        $this->deleteJson("/api/mobile/v1/notifications/{$other->id}")->assertNotFound();
        $this->assertNotNull(GamificationNotification::find($other->id));
    }

    public function test_realtime_config_and_private_channel_auth_with_token(): void
    {
        $student = $this->completeStudent();
        Sanctum::actingAs($student);

        $this->getJson('/api/mobile/v1/realtime/config')
            ->assertOk()
            ->assertJsonPath('data.channel', 'private-user.notifications.'.$student->id)
            ->assertJsonPath('data.event', 'notification.created');

        if (config('broadcasting.default') !== 'reverb') {
            $this->markTestSkipped('Reverb is not the default broadcaster in this environment.');
        }

        $this->postJson('/api/mobile/v1/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-user.notifications.'.$student->id,
        ])->assertOk()->assertJsonStructure(['auth']);

        $this->postJson('/api/mobile/v1/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-user.notifications.'.($student->id + 1),
        ])->assertForbidden();
    }

    public function test_gamification_notifications_are_broadcast_in_real_time(): void
    {
        Event::fake([UserNotificationBroadcasted::class]);
        $student = $this->completeStudent();

        $notification = app(NotificationService::class)->send($student, 'badge_earned', 'وسام جديد', 'حصلت على وسام');

        if ($notification === null) {
            $this->markTestSkipped('The student preferences disable this notification type.');
        }

        Event::assertDispatched(
            UserNotificationBroadcasted::class,
            fn (UserNotificationBroadcasted $e) => $e->userId === $student->id
                && $e->payload['inbox_id'] === $notification->id
                && $e->payload['title'] === 'وسام جديد',
        );
    }

    public function test_device_token_register_and_unregister(): void
    {
        $student = $this->completeStudent();
        Sanctum::actingAs($student);
        $token = 'fcm-test-'.uniqid();

        $this->postJson('/api/mobile/v1/devices', ['token' => $token, 'platform' => 'android'])->assertOk();
        $this->assertDatabaseHas('notification_device_tokens', ['token' => $token, 'user_id' => $student->id, 'is_active' => true]);

        $this->deleteJson('/api/mobile/v1/devices', ['token' => $token])->assertOk();
        $this->assertDatabaseHas('notification_device_tokens', ['token' => $token, 'is_active' => false]);
    }
}
