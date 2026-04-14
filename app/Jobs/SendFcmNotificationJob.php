<?php

namespace App\Jobs;

use App\Models\NotificationDeliveryLog;
use App\Models\NotificationDeviceToken;
use App\Services\Notifications\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [5, 20, 60];
    }

    public function __construct(
        public int $userId,
        public string $eventKey,
        public array $payload,
        public ?string $databaseNotificationId = null,
    ) {}

    public function handle(FcmService $fcmService): void
    {
        $tokens = NotificationDeviceToken::query()
            ->where('user_id', $this->userId)
            ->where('is_active', true)
            ->pluck('token');

        foreach ($tokens as $token) {
            $result = $fcmService->send($token, $this->payload);
            $ok = (bool) ($result['ok'] ?? false);
            $error = $result['error'] ?? null;

            if (! $ok && is_string($error) && in_array($error, ['NotRegistered', 'InvalidRegistration'], true)) {
                NotificationDeviceToken::query()
                    ->where('token', $token)
                    ->update(['is_active' => false]);
            }

            NotificationDeliveryLog::create([
                'user_id' => $this->userId,
                'database_notification_id' => $this->databaseNotificationId,
                'event_key' => $this->eventKey,
                'channel' => 'fcm',
                'status' => $ok ? 'sent' : 'failed',
                'payload' => $this->payload,
                'sent_at' => $ok ? now() : null,
                'error_message' => $ok ? null : ($error ?: 'FCM send failed'),
            ]);
        }
    }
}
