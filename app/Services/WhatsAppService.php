<?php

namespace App\Services;

use App\Enums\WapiMessageStatus;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Support\WapiPhoneNormalizer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * ينتج عنواناً صالحاً لـ wapi (يُلحق /api/v1 إن وُجد فقط اسم النطاق).
     * يمنع الطلبات إلى https://wapi.flaxxa.com/sendmessage بدون api/v1 (404).
     *
     * @see https://documenter.getpostman.com/view/38526086/2sB3HgPiJx
     */
    public function baseUrl(): string
    {
        try {
            $svc = app(WhatsAppSettingsService::class);
            $svc->initializeDefaults();
            $u = trim((string) ($svc->getSettings()['wapi_base_url'] ?? ''));
            if ($u !== '') {
                return $this->normalizeWapiBaseUrl($u);
            }
        } catch (\Throwable) {
        }

        return $this->normalizeWapiBaseUrl((string) config('services.whatsapp.base_url', 'https://wapi.flaxxa.com/api/v1'));
    }

    /**
     * يطبّق نفس منطق baseUrl على قيمة قادمة من نموذج الاختبار أو .env.
     */
    public function normalizeWapiBaseUrl(string $base): string
    {
        $base = rtrim(trim($base), '/');
        if ($base === '') {
            $fromConfig = trim((string) config('services.whatsapp.base_url', 'https://wapi.flaxxa.com/api/v1'));

            return $fromConfig !== '' ? rtrim($fromConfig, '/') : 'https://wapi.flaxxa.com/api/v1';
        }

        $path = parse_url($base, PHP_URL_PATH);

        if ($path === null || $path === '' || $path === '/') {
            return $base.'/api/v1';
        }

        return $base;
    }

    public function token(): string
    {
        try {
            $svc = app(WhatsAppSettingsService::class);
            $svc->initializeDefaults();
            $t = (string) ($svc->getSettings()['wapi_token'] ?? '');
            if ($t !== '') {
                return $t;
            }
        } catch (\Throwable) {
        }

        return (string) config('services.whatsapp.token', '');
    }

    /**
     * اختبار الاتصال عبر GET getTemplates (كما في توثيق Postman).
     *
     * @return array{success: bool, message: string, http_status?: int}
     */
    public function testConnection(?string $tokenOverride = null, ?string $baseUrlOverride = null): array
    {
        $token = trim((string) ($tokenOverride ?? ''));
        if ($token === '') {
            $token = $this->token();
        }

        if ($token === '') {
            return [
                'success' => false,
                'message' => 'لم يُعرّف توكن. أدخل التوكن في الحقل أو احفظه مسبقاً أو عيّن WHATSAPP_TOKEN في .env.',
            ];
        }

        $baseRaw = trim((string) ($baseUrlOverride ?? ''));
        $base = $baseRaw !== '' ? $this->normalizeWapiBaseUrl($baseRaw) : $this->baseUrl();

        $url = $base.'/getTemplates';

        Log::channel('whatsapp')->info('[Flaxxa WAPI] test connection', [
            'url' => $url,
            'token_preview' => $this->maskToken($token),
        ]);

        try {
            $response = Http::timeout(25)
                ->connectTimeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($url, ['token' => $token]);

            $status = $response->status();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'تم الاتصال بالمزود بنجاح (HTTP '.$status.') عبر getTemplates.',
                    'http_status' => $status,
                ];
            }

            if (in_array($status, [401, 403], true)) {
                return [
                    'success' => false,
                    'message' => 'رفض الوصول: تحقق من صحة التوكن.',
                    'http_status' => $status,
                ];
            }

            if ($status === 404) {
                return [
                    'success' => false,
                    'message' => 'المسار غير موجود (404). تأكد أن عنوان الأساس صحيحاً (مثلاً يتضمن /api/v1) أو اترك الحقل فارغاً للافتراضي.',
                    'http_status' => $status,
                ];
            }

            if ($status >= 500) {
                return [
                    'success' => false,
                    'message' => 'خطأ من خادم المزود (HTTP '.$status.').',
                    'http_status' => $status,
                ];
            }

            return [
                'success' => false,
                'message' => 'فشل الاتصال (HTTP '.$status.').',
                'http_status' => $status,
            ];
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->warning('[Flaxxa WAPI] test connection exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'تعذّر الاتصال: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{response: Response, status: WapiMessageStatus, log_payload: array<string, mixed>}
     */
    public function sendMessage(
        string $phone,
        string $message,
        ?string $attachmentAbsolutePath = null,
        string $header = '',
        string $footer = '',
        string $buttons = ''
    ): array {
        $phone = WapiPhoneNormalizer::normalize($phone);
        $base = $this->baseUrl();
        $token = $this->token();

        $this->assertConfigured();

        $hasFile = $attachmentAbsolutePath !== null && $attachmentAbsolutePath !== '' && is_readable($attachmentAbsolutePath);

        if (! $hasFile) {
            $url = $base.'/sendmessage';

            // حسب توثيق Postman: header/footer لا تُطبَّق إلا مع وجود أزرار،
            // وإرسال buttons: [] أو سلاسل فارغة يتسبب في 500 Server Error من الخادم.
            $payload = [
                'token' => $token,
                'phone' => $phone,
                'message' => $message,
            ];

            $buttonsPayload = $this->parseButtonsJsonOrEmpty($buttons);
            if ($buttonsPayload !== []) {
                $payload['buttons'] = $buttonsPayload;
                if ($header !== '') {
                    $payload['header'] = $header;
                }
                if ($footer !== '') {
                    $payload['footer'] = $footer;
                }
            }

            $this->logRequest('sendmessage', [
                'phone' => $phone,
                'mode' => 'json',
                'url' => $url,
                'has_buttons' => $buttonsPayload !== [],
            ]);

            $response = Http::timeout((int) config('services.whatsapp.timeout', 60))
                ->withHeaders(['Accept' => 'application/json'])
                ->asJson()
                ->post($url, $payload);

            return $this->finalize($response, 'sendmessage');
        }

        $url = $base.'/sendmessagewithattachment';

        $multipart = [
            ['name' => 'token', 'contents' => $token],
            ['name' => 'phone', 'contents' => $phone],
            ['name' => 'message', 'contents' => $message],
            ['name' => 'header', 'contents' => $header],
            ['name' => 'footer', 'contents' => $footer],
            ['name' => 'buttons', 'contents' => is_string($buttons) ? $buttons : json_encode($buttons)],
        ];

        $multipart[] = [
            'name' => 'header_attachment',
            'contents' => (string) file_get_contents($attachmentAbsolutePath),
            'filename' => basename($attachmentAbsolutePath),
        ];

        $this->logRequest('sendmessagewithattachment', ['phone' => $phone, 'has_attachment' => true, 'url' => $url]);

        $response = Http::timeout((int) config('services.whatsapp.timeout', 60))
            ->withHeaders(['Accept' => 'application/json'])
            ->asMultipart()
            ->post($url, $multipart);

        return $this->finalize($response, 'sendmessagewithattachment');
    }

    /**
     * @param  array<int, array<string, mixed>>  $cloudApiComponents
     *                                                                مصفوفة بنمط Meta Cloud API، مثال:
     *                                                                [
     *                                                                ['type'=>'header','parameters'=>[['type'=>'text','text'=>'...']]],
     *                                                                ['type'=>'body','parameters'=>[['type'=>'text','text'=>'...']]]
     *                                                                ]
     * @return array{response: Response, status: WapiMessageStatus, log_payload: array<string, mixed>}
     */
    public function sendTemplate(
        string $phone,
        string $templateName,
        string $language,
        array $cloudApiComponents = [],
        ?string $attachmentAbsolutePath = null
    ): array {
        $phone = WapiPhoneNormalizer::normalize($phone);
        $base = $this->baseUrl();
        $token = $this->token();

        $this->assertConfigured();

        $hasFile = $attachmentAbsolutePath !== null && $attachmentAbsolutePath !== '' && is_readable($attachmentAbsolutePath);

        if (! $hasFile) {
            $url = $base.'/sendtemplatemessage';

            $payload = [
                'token' => $token,
                'phone' => $phone,
                'template_name' => $templateName,
                'template_language' => $language,
                'components' => array_values($cloudApiComponents),
            ];

            $this->logRequest('sendtemplatemessage', [
                'phone' => $phone,
                'template_name' => $templateName,
                'template_language' => $language,
                'mode' => 'json',
                'url' => $url,
                'components_count' => count($cloudApiComponents),
            ]);

            $response = Http::timeout((int) config('services.whatsapp.timeout', 60))
                ->withHeaders(['Accept' => 'application/json'])
                ->asJson()
                ->post($url, $payload);

            return $this->finalize($response, 'sendtemplatemessage');
        }

        $url = $base.'/sendtemplatemessage_withattachment';

        $parts = [
            ['name' => 'token', 'contents' => $token],
            ['name' => 'phone', 'contents' => $phone],
            ['name' => 'template_name', 'contents' => $templateName],
            ['name' => 'template_language', 'contents' => $language],
        ];

        if ($cloudApiComponents === []) {
            $parts[] = ['name' => 'components[]', 'contents' => ''];
        } else {
            foreach (array_values($cloudApiComponents) as $component) {
                $parts[] = [
                    'name' => 'components[]',
                    'contents' => is_string($component)
                        ? $component
                        : (string) json_encode($component, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ];
            }
        }

        $parts[] = [
            'name' => 'header_attachment',
            'contents' => (string) file_get_contents($attachmentAbsolutePath),
            'filename' => basename($attachmentAbsolutePath),
        ];

        $this->logRequest('sendtemplatemessage_withattachment', [
            'phone' => $phone,
            'template_name' => $templateName,
            'template_language' => $language,
            'has_attachment' => true,
            'url' => $url,
            'components_count' => count($cloudApiComponents),
        ]);

        $response = Http::timeout((int) config('services.whatsapp.timeout', 60))
            ->withHeaders(['Accept' => 'application/json'])
            ->asMultipart()
            ->post($url, $parts);

        return $this->finalize($response, 'sendtemplatemessage_withattachment');
    }

    /**
     * @param  array{header?: array<string, string>, body?: array<string, string>}  $bodyJson
     * @return array{response: Response, status: WapiMessageStatus, log_payload: array<string, mixed>}
     */
    public function sendCampaign(string $name, string $templateId, string $groupId, array $bodyJson = []): array
    {
        $this->assertConfigured();
        $token = $this->token();

        $url = $this->baseUrl().'/Create_Campaign';

        $query = [
            'token' => $token,
            'name' => $name,
            'template_id' => $templateId,
            'type' => 'api',
            'group_id' => $groupId,
        ];

        $bodyString = $bodyJson === [] ? '{}' : json_encode($bodyJson, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $this->logRequest('Create_Campaign', [
            'name' => $name,
            'template_id' => $templateId,
            'group_id' => $groupId,
            'body_empty' => $bodyJson === [],
        ]);

        $response = Http::timeout((int) config('services.whatsapp.timeout', 120))
            ->withHeaders(['Accept' => 'application/json'])
            ->withQueryParameters($query)
            ->withBody($bodyString, 'application/json')
            ->post($url);

        return $this->finalize($response, 'Create_Campaign');
    }

    public function assertConfigured(): void
    {
        if ($this->token() === '') {
            throw new \RuntimeException('WHATSAPP_TOKEN / services.whatsapp.token is not configured.');
        }
    }

    /**
     * جلب القوالب المعتمدة من Meta عبر Flaxxa.
     *
     * @return array{success: bool, http_status: int, templates: array<int, array<string, mixed>>, message: string, raw?: string}
     */
    public function fetchTemplates(): array
    {
        $this->assertConfigured();

        $url = $this->baseUrl().'/getTemplates';

        Log::channel('whatsapp')->info('[Flaxxa WAPI] fetch templates', ['url' => $url]);

        try {
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($url, ['token' => $this->token()]);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'http_status' => 0,
                'templates' => [],
                'message' => 'تعذّر الاتصال بالمزود: '.$e->getMessage(),
            ];
        }

        $status = $response->status();
        $json = $response->json();
        $body = $response->body();

        if (! $response->successful() || ! is_array($json)) {
            return [
                'success' => false,
                'http_status' => $status,
                'templates' => [],
                'message' => 'فشل جلب القوالب (HTTP '.$status.')',
                'raw' => $body,
            ];
        }

        $list = [];
        if (isset($json['templates']) && is_array($json['templates'])) {
            $list = $json['templates'];
        } elseif (array_is_list($json)) {
            $list = $json;
        } elseif (isset($json['data']) && is_array($json['data'])) {
            $list = $json['data'];
        }

        return [
            'success' => true,
            'http_status' => $status,
            'templates' => array_values(array_filter($list, 'is_array')),
            'message' => 'تم جلب القوالب بنجاح.',
            'raw' => $body,
        ];
    }

    /**
     * استعلام حالة التوصيل من Flaxxa (بعد ~5 ثوانٍ من الإرسال).
     *
     * @return array{success: bool, http_status?: int, data?: array<string, mixed>, message?: string, raw?: string}
     */
    public function getMessageResponse(int|string $messageId): array
    {
        $this->assertConfigured();

        $url = $this->baseUrl().'/get_message_response';

        $response = Http::timeout(25)
            ->withHeaders(['Accept' => 'application/json'])
            ->asJson()
            ->post($url, [
                'token' => $this->token(),
                'message_id' => is_numeric($messageId) ? (int) $messageId : $messageId,
            ]);

        $json = $response->json();
        $body = $response->body();

        Log::channel('whatsapp')->info('[Flaxxa WAPI] get_message_response', [
            'url' => $url,
            'http_status' => $response->status(),
            'body_length' => strlen($body),
        ]);

        if ($response->successful() && is_array($json)) {
            return [
                'success' => true,
                'http_status' => $response->status(),
                'data' => $json,
                'raw' => $body,
            ];
        }

        return [
            'success' => false,
            'http_status' => $response->status(),
            'message' => 'تعذّر استعلام حالة الرسالة (HTTP '.$response->status().')',
            'data' => is_array($json) ? $json : null,
            'raw' => $body,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function parseButtonsJsonOrEmpty(string $buttons): array
    {
        $trim = trim($buttons);
        if ($trim === '') {
            return [];
        }
        $decoded = json_decode($trim, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function logRequest(string $endpoint, array $context): void
    {
        Log::channel('whatsapp')->info('[Flaxxa WAPI] request', array_merge([
            'endpoint' => $endpoint,
            'token_preview' => $this->maskToken($this->token()),
        ], $context));
    }

    protected function maskToken(string $token): string
    {
        if ($token === '') {
            return '';
        }
        if (strlen($token) <= 8) {
            return '***';
        }

        return substr($token, 0, 4).'…'.substr($token, -2);
    }

    /**
     * @return array{response: Response, status: WapiMessageStatus, log_payload: array<string, mixed>}
     */
    protected function finalize(Response $response, string $endpoint): array
    {
        $body = $response->body();
        $json = $response->json();

        $effectiveUrl = (string) $response->effectiveUri();

        $logPayload = [
            'endpoint' => $endpoint,
            'effective_url' => $effectiveUrl,
            'http_status' => $response->status(),
            'body_raw' => $body,
            'json' => is_array($json) ? $json : null,
        ];

        if ($response->status() === 404 && str_contains($body, 'could not be found')) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            $requestHost = parse_url($this->baseUrl(), PHP_URL_HOST);
            $logPayload['hint'] = [
                'ar' => 'هذا النمط من الخطأ يظهر عادةً عندما يصل الطلب إلى خادم Laravel خاطئ (مثل تطبيقك) وليس إلى wapi. تأكد من أن «عنوان الأساس» في إعدادات Flaxxa يطابق خادم المزود (مثال: https://wapi.flaxxa.com فقط، ثم يُضاف /api/v1 تلقائياً).',
                'request_host' => is_string($requestHost) ? $requestHost : null,
                'app_url_host' => is_string($appHost) ? $appHost : null,
                'same_host_as_app' => is_string($requestHost) && is_string($appHost) && strcasecmp($requestHost, $appHost) === 0,
            ];
        }

        Log::channel('whatsapp')->info('[Flaxxa WAPI] response', [
            'endpoint' => $endpoint,
            'http_status' => $response->status(),
            'body_length' => strlen($body),
        ]);

        $status = $this->deriveStatus($response);

        return [
            'response' => $response,
            'status' => $status,
            'log_payload' => $logPayload,
        ];
    }

    protected function deriveStatus(Response $response): WapiMessageStatus
    {
        if (! $response->successful()) {
            return WapiMessageStatus::Failed;
        }

        $body = trim($response->body());
        if ($body === '') {
            return WapiMessageStatus::SentPendingConfirmation;
        }

        $json = $response->json();
        if (is_array($json)) {
            if (isset($json['error']) || isset($json['errors'])) {
                return WapiMessageStatus::Failed;
            }

            $success = $json['success'] ?? null;
            if ($success === false) {
                return WapiMessageStatus::Failed;
            }

            $topStatus = $json['status'] ?? null;
            if (is_string($topStatus) && in_array(strtolower($topStatus), ['failed', 'error', 'rejected'], true)) {
                return WapiMessageStatus::Failed;
            }

            $messages = data_get($json, 'messages');
            if (is_array($messages)) {
                foreach ($messages as $m) {
                    if (! is_array($m)) {
                        continue;
                    }
                    if (! empty($m['errors'])) {
                        return WapiMessageStatus::Failed;
                    }
                    $st = $m['message_status'] ?? $m['status'] ?? null;
                    if (is_string($st) && str_contains(strtolower($st), 'fail')) {
                        return WapiMessageStatus::Failed;
                    }
                }
            }

            $dataMessageId = data_get($json, 'data.message_id') ?? data_get($json, 'data.id');
            if (is_string($dataMessageId) && $dataMessageId !== '') {
                if (($json['success'] ?? true) === false) {
                    return WapiMessageStatus::Failed;
                }

                return WapiMessageStatus::Sent;
            }

            if (isset($json['message_id']) || isset($json['id']) || array_key_exists('success', $json)) {
                if (($json['success'] ?? true) === false) {
                    return WapiMessageStatus::Failed;
                }

                return WapiMessageStatus::Sent;
            }
        }

        return WapiMessageStatus::SentPendingConfirmation;
    }
}
