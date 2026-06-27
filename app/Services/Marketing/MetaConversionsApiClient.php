<?php

namespace App\Services\Marketing;

use App\Models\MetaPixelSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaConversionsApiClient
{
    public function send(MetaPixelSetting $settings, array $events): array
    {
        if (! $settings->hasValidCapi()) {
            return ['success' => false, 'message' => 'Conversions API غير مفعّل أو ناقص الإعدادات'];
        }

        if ($events === []) {
            return ['success' => false, 'message' => 'لا توجد أحداث للإرسال'];
        }

        $version = config('meta_pixel.graph_api_version', 'v21.0');
        $url = "https://graph.facebook.com/{$version}/{$settings->pixel_id}/events";

        $payload = [
            'data' => $events,
            'access_token' => $settings->capi_access_token,
        ];

        if (filled($settings->test_event_code)) {
            $payload['test_event_code'] = $settings->test_event_code;
        }

        try {
            /** @var Response $response */
            $response = Http::timeout(15)->post($url, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'تم إرسال الحدث بنجاح',
                    'response' => $response->json(),
                ];
            }

            Log::warning('Meta CAPI request failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => $response->json('error.message') ?? 'فشل إرسال الحدث إلى Meta',
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Meta CAPI exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
