<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;

class FcmService
{
    /**
     * @return array{ok: bool, error: string|null}
     */
    public function send(string $token, array $payload): array
    {
        if (! config('notification_hub.fcm.enabled')) {
            return ['ok' => false, 'error' => 'FCM disabled'];
        }

        $serverKey = config('notification_hub.fcm.server_key');
        if (empty($serverKey)) {
            return ['ok' => false, 'error' => 'Missing FCM server key'];
        }

        $response = Http::timeout((int) config('notification_hub.fcm.timeout', 20))
            ->withHeaders([
                'Authorization' => 'key='.$serverKey,
                'Content-Type' => 'application/json',
            ])
            ->post(config('notification_hub.fcm.endpoint'), [
                'to' => $token,
                'notification' => [
                    'title' => $payload['title'] ?? '',
                    'body' => $payload['body'] ?? '',
                ],
                'data' => $payload['data'] ?? [],
            ]);

        if (! $response->successful()) {
            return ['ok' => false, 'error' => 'HTTP '.$response->status()];
        }

        $json = $response->json();
        if (is_array($json) && isset($json['failure']) && (int) $json['failure'] > 0) {
            $firstError = $json['results'][0]['error'] ?? 'FCM failure';

            return ['ok' => false, 'error' => (string) $firstError];
        }

        return ['ok' => true, 'error' => null];
    }
}
