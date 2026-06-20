<?php

namespace App\Services\Notifications;

use App\Events\UserNotificationBroadcasted;
use App\Jobs\SendFcmNotificationJob;
use App\Models\GamificationNotification;
use App\Models\NotificationDeliveryLog;
use App\Models\NotificationTemplate;
use App\Models\NotificationUserPreference;
use App\Models\User;
use App\Notifications\HubDatabaseNotification;
use App\Services\Flaxxa\WapiAutomationService;
use Illuminate\Support\Facades\Log;

class NotificationHubService
{
    public function __construct(
        protected TemplateRenderer $renderer,
        protected NotificationHubSettings $settings
    ) {}

    public function sendToUser(User $user, string $eventKey, array $data = [], ?array $requestedChannels = null): array
    {
        if (! $this->settings->eventEnabled($eventKey)) {
            return [
                'notification_id' => null,
                'channels' => [],
            ];
        }

        $locale = $data['locale'] ?? $user->locale ?? config('notification_hub.defaults.locale', 'ar');
        $resolvedChannels = $this->resolveChannels($user, $eventKey, $requestedChannels);

        $databasePayload = $this->buildPayload($eventKey, 'database', $locale, $data);
        $databasePayload['event_key'] = $eventKey;
        $databasePayload['channels'] = $resolvedChannels;

        $notificationId = null;

        if (in_array('database', $resolvedChannels, true)) {
            $user->notify(new HubDatabaseNotification($databasePayload));
            $notificationId = (string) optional($user->notifications()->latest()->first())->id;
            $this->storeLegacyStudentInboxNotification($user, $eventKey, $databasePayload, $notificationId);

            NotificationDeliveryLog::create([
                'user_id' => $user->id,
                'database_notification_id' => $notificationId,
                'event_key' => $eventKey,
                'channel' => 'database',
                'status' => 'sent',
                'payload' => $databasePayload,
                'sent_at' => now(),
            ]);
        }

        if (in_array('realtime', $resolvedChannels, true)) {
            try {
                event(new UserNotificationBroadcasted($user->id, [
                    'notification_id' => $notificationId,
                    'event_key' => $eventKey,
                    'title' => $databasePayload['title'] ?? null,
                    'body' => $databasePayload['body'] ?? null,
                    'data' => $databasePayload['data'] ?? [],
                ]));

                NotificationDeliveryLog::create([
                    'user_id' => $user->id,
                    'database_notification_id' => $notificationId,
                    'event_key' => $eventKey,
                    'channel' => 'realtime',
                    'status' => 'sent',
                    'payload' => $databasePayload,
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Notification hub realtime broadcast failed', [
                    'user_id' => $user->id,
                    'event_key' => $eventKey,
                    'error' => $e->getMessage(),
                ]);

                NotificationDeliveryLog::create([
                    'user_id' => $user->id,
                    'database_notification_id' => $notificationId,
                    'event_key' => $eventKey,
                    'channel' => 'realtime',
                    'status' => 'failed',
                    'payload' => $databasePayload,
                    'error_message' => $e->getMessage(),
                    'sent_at' => null,
                ]);
            }
        }

        if (in_array('fcm', $resolvedChannels, true)) {
            $fcmPayload = $this->buildPayload($eventKey, 'fcm', $locale, $data);
            $fcmPayload['data']['event_key'] = $eventKey;
            SendFcmNotificationJob::dispatch($user->id, $eventKey, $fcmPayload, $notificationId);
        }

        if (in_array('whatsapp_wapi', $resolvedChannels, true)) {
            app(WapiAutomationService::class)->dispatchFromNotificationHub($eventKey, $user, $data);

            NotificationDeliveryLog::create([
                'user_id' => $user->id,
                'database_notification_id' => $notificationId,
                'event_key' => $eventKey,
                'channel' => 'whatsapp_wapi',
                'status' => 'queued',
                'payload' => array_merge($data, [
                    'event_key' => $eventKey,
                    'hub_note_ar' => 'تمت جدولة إرسال واتساب Flaxxa؛ التسليم الفعلي يراجع في سجل Flaxxa وليس مضموناً من هذا السطر.',
                ]),
                'sent_at' => null,
            ]);
        }

        return [
            'notification_id' => $notificationId,
            'channels' => $resolvedChannels,
        ];
    }

    /**
     * @param  array<int, User>  $users
     */
    public function sendToUsers(iterable $users, string $eventKey, array $data = [], ?array $requestedChannels = null): int
    {
        $count = 0;
        foreach ($users as $user) {
            $result = $this->sendToUser($user, $eventKey, $data, $requestedChannels);
            if (! empty($result['channels'])) {
                $count++;
            }
        }

        return $count;
    }

    protected function resolveChannels(User $user, string $eventKey, ?array $requestedChannels): array
    {
        $defaultChannels = ['database', 'realtime', 'fcm'];
        if ($this->settings->channelEnabled('whatsapp_wapi')) {
            $defaultChannels[] = 'whatsapp_wapi';
        }
        $channels = $requestedChannels ?: $defaultChannels;

        $channels = array_values(array_filter($channels, fn ($channel) => $this->settings->channelEnabled($channel)));

        $pref = NotificationUserPreference::query()
            ->where('user_id', $user->id)
            ->where('event_key', $eventKey)
            ->first();

        if (! $pref) {
            return $channels;
        }

        $allowed = [];
        foreach ($channels as $channel) {
            $column = "{$channel}_enabled";
            if (isset($pref->{$column}) && $pref->{$column}) {
                $allowed[] = $channel;
            }
        }

        return $allowed;
    }

    protected function buildPayload(string $eventKey, string $channel, string $locale, array $data): array
    {
        $template = NotificationTemplate::query()
            ->where('event_key', $eventKey)
            ->where('channel', $channel)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            $template = NotificationTemplate::query()
                ->where('event_key', $eventKey)
                ->where('channel', $channel)
                ->where('locale', config('notification_hub.defaults.locale', 'ar'))
                ->where('is_active', true)
                ->first();
        }

        $titleTemplate = $template?->title_template;
        if ($titleTemplate === null || $titleTemplate === '') {
            $titleTemplate = $data['title'] ?? NotificationHubFallbackCopy::titleTemplate($eventKey, $data);
        }

        $bodyTemplate = $template?->body_template;
        if ($bodyTemplate === null || $bodyTemplate === '') {
            $bodyTemplate = $data['body'] ?? NotificationHubFallbackCopy::bodyTemplate($eventKey, $data);
        }

        return [
            'title' => $this->renderer->render((string) $titleTemplate, $data),
            'body' => $this->renderer->render((string) $bodyTemplate, $data),
            'data' => $data,
            'locale' => $locale,
        ];
    }

    /**
     * Keep the existing student inbox UI in sync until all screens fully migrate
     * to the new notification-hub endpoints.
     */
    protected function storeLegacyStudentInboxNotification(
        User $user,
        string $eventKey,
        array $databasePayload,
        ?string $notificationId
    ): void {
        $data = $databasePayload['data'] ?? [];

        GamificationNotification::create([
            'user_id' => $user->id,
            'type' => (string) ($data['type'] ?? $eventKey),
            'title' => (string) ($databasePayload['title'] ?? 'إشعار جديد'),
            'message' => (string) ($databasePayload['body'] ?? ''),
            'icon' => $data['icon'] ?? '🔔',
            'action_url' => $data['action_url'] ?? null,
            'metadata' => [
                'event_key' => $eventKey,
                'notification_id' => $notificationId,
                'source' => 'notification_hub',
            ],
            'is_read' => false,
        ]);
    }
}
