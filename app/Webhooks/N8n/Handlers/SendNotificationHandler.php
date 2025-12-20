<?php

namespace App\Webhooks\N8n\Handlers;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GenericNotification;

class SendNotificationHandler extends BaseHandler
{
    public function handle(array $payload): array
    {
        try {
            $this->validate($payload, ['message', 'recipients']);

            $recipients = is_array($payload['recipients'])
                ? $payload['recipients']
                : [$payload['recipients']];

            $users = User::whereIn('id', $recipients)->get();

            if ($users->isEmpty()) {
                return $this->error('No valid recipients found');
            }

            $notificationData = [
                'title' => $payload['title'] ?? 'Notification',
                'message' => $payload['message'],
                'type' => $payload['type'] ?? 'info',
                'priority' => $payload['priority'] ?? 'normal',
            ];

            // Send notification
            Notification::send($users, new GenericNotification($notificationData));

            $this->logSuccess('Notifications sent successfully', [
                'recipient_count' => $users->count(),
            ]);

            return $this->success('Notifications sent successfully', [
                'recipients_count' => $users->count(),
                'recipients' => $users->pluck('name')->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->logError('Failed to send notifications', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to send notifications: ' . $e->getMessage());
        }
    }
}
