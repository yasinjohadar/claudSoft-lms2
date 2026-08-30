<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Groq Provider Service
 *
 * متوافق مع واجهة OpenAI API (chat/completions)
 *
 * @see https://console.groq.com/docs
 */
class GroqProviderService extends AIProviderService
{
    private const BASE_URL = 'https://api.groq.com/openai/v1';

    /**
     * إرسال رسالة في محادثة (Chat Completions)
     */
    public function chat(array $messages, array $options = []): array
    {
        $baseUrl = $this->getBaseUrl() ?? self::BASE_URL;
        $endpoint = $this->getApiEndpoint() ?? '/chat/completions';

        $apiKey = $this->getApiKey();
        if (! $apiKey) {
            $error = 'Groq API Key غير موجود. يرجى إدخال API Key في حقل \"مفتاح API\" وحفظ النموذج أولاً.';
            $this->setLastError($error);

            return [
                'success' => false,
                'error' => $error,
            ];
        }

        // تنظيف model_key من المسافات
        $modelKey = trim($this->model->model_key);

        $payload = [
            'model' => $modelKey,
            'messages' => $messages,
            'max_tokens' => (int) ($options['max_tokens'] ?? $this->model->max_tokens),
            'temperature' => (float) ($options['temperature'] ?? $this->model->temperature),
        ];

        try {
            $fullUrl = rtrim($baseUrl, '/').'/'.ltrim($endpoint, '/');

            Log::info('Groq API Request', [
                'url' => $fullUrl,
                'base_url' => $baseUrl,
                'endpoint' => $endpoint,
                'model' => $modelKey,
                'max_tokens' => $payload['max_tokens'],
                'payload' => $payload,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.trim($apiKey),
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->timeout(500)->post($fullUrl, $payload);

            // تحويل الـ response body إلى UTF-8 بشكل صحيح
            $rawBody = $response->body();

            // التحقق من الترميز وإصلاحه إذا لزم الأمر
            if (! mb_check_encoding($rawBody, 'UTF-8')) {
                // محاولة تحويل الترميز
                $body = mb_convert_encoding($rawBody, 'UTF-8', 'auto');
                // إذا فشل التحويل، استخدم utf8_encode كحل بديل
                if (! mb_check_encoding($body, 'UTF-8')) {
                    $body = mb_convert_encoding($rawBody, 'UTF-8', ['UTF-8', 'ISO-8859-1', 'Windows-1256']);
                }
            } else {
                $body = $rawBody;
            }

            // تنظيف النص من الأحرف غير الصالحة في UTF-8
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');

            Log::info('Groq API Response', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'body_length' => strlen($body),
                'body_preview' => mb_substr($body, 0, 500),
                'encoding_valid' => mb_check_encoding($body, 'UTF-8'),
            ]);

            if ($response->successful()) {
                try {
                    $data = json_decode($body, true, 512, JSON_INVALID_UTF8_IGNORE);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('Groq JSON decode error', [
                            'error' => json_last_error_msg(),
                            'error_code' => json_last_error(),
                            'body_preview' => mb_substr($body, 0, 500),
                        ]);
                        $this->setLastError('خطأ في تحليل رد Groq: '.json_last_error_msg());

                        return [
                            'success' => false,
                            'error' => 'خطأ في تحليل رد Groq',
                        ];
                    }

                    $content = $data['choices'][0]['message']['content'] ?? '';

                    // التحقق من ترميز المحتوى المستخرج
                    if (! empty($content) && ! mb_check_encoding($content, 'UTF-8')) {
                        $content = mb_convert_encoding($content, 'UTF-8', 'auto');
                    }

                    Log::info('Groq content extracted', [
                        'content_length' => strlen($content),
                        'content_preview' => mb_substr($content, 0, 500),
                        'encoding_valid' => mb_check_encoding($content, 'UTF-8'),
                    ]);

                    return [
                        'success' => true,
                        'content' => $content,
                        'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                        'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                        'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                        'model_used' => $data['model'] ?? $this->model->model_key,
                    ];
                } catch (\JsonException $e) {
                    Log::error('Groq JSON exception: '.$e->getMessage(), [
                        'body_preview' => mb_substr($body, 0, 500),
                    ]);
                    $this->setLastError('خطأ في تحليل رد Groq: '.$e->getMessage());

                    return [
                        'success' => false,
                        'error' => 'خطأ في تحليل رد Groq',
                    ];
                }
            }

            // معالجة الأخطاء
            $errorData = $response->json();

            // Groq قد يعيد أخطاء بصيغ مختلفة
            if (isset($errorData['error'])) {
                $errorMessage = $errorData['error']['message'] ?? ($errorData['error']['msg'] ?? 'خطأ غير معروف');
                $errorType = $errorData['error']['type'] ?? null;
                $errorCode = $errorData['error']['code'] ?? null;
            } elseif (isset($errorData['msg'])) {
                $errorMessage = $errorData['msg'];
                $errorType = null;
                $errorCode = $errorData['code'] ?? null;
            } elseif (isset($errorData['message'])) {
                $errorMessage = $errorData['message'];
                $errorType = null;
                $errorCode = null;
            } else {
                $errorMessage = $response->body() ?? 'خطأ غير معروف';
                $errorType = null;
                $errorCode = null;
            }

            Log::error('Groq API Error', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'type' => $errorType,
                'code' => $errorCode,
                'response_body' => $response->body(),
                'error_data' => $errorData,
            ]);

            // رسائل خطأ واضحة بالعربية
            $friendlyMessage = $this->getFriendlyErrorMessage($response->status(), $errorMessage, $errorType);

            $this->setLastError($friendlyMessage, $response->status());

            return [
                'success' => false,
                'error' => $friendlyMessage,
                'status_code' => $response->status(),
                'raw_error' => $errorMessage,
            ];
        } catch (ConnectionException $e) {
            Log::error('Groq API Connection Exception: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $error = 'خطأ في الاتصال بخادم Groq. يرجى التحقق من الاتصال بالإنترنت والمحاولة مرة أخرى.';
            $this->setLastError($error);

            return [
                'success' => false,
                'error' => $error,
            ];
        } catch (\Exception $e) {
            Log::error('Groq API Exception: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $error = 'خطأ في الاتصال: '.$e->getMessage();
            $this->setLastError($error);

            return [
                'success' => false,
                'error' => $error,
            ];
        }
    }

    /**
     * الحصول على رسالة خطأ واضحة
     */
    private function getFriendlyErrorMessage(int $statusCode, string $errorMessage, ?string $errorType = null): string
    {
        // تنظيف رسالة الخطأ من النقاط الزائدة في البداية
        $errorMessage = ltrim($errorMessage, '. ');

        if ($statusCode === 401) {
            return 'API Key غير صحيح أو منتهي الصلاحية. يرجى التحقق من API Key من Groq Console.';
        } elseif ($statusCode === 404) {
            return 'Model Key غير صحيح أو غير متاح. تأكد من أن Model Key صحيح.';
        } elseif ($statusCode === 429) {
            return 'تم تجاوز حد الاستخدام. يرجى الانتظار قليلاً ثم المحاولة مرة أخرى، أو التحقق من خطة Groq الخاصة بك.';
        } elseif ($statusCode === 500 || $statusCode === 502 || $statusCode === 503) {
            return 'خطأ في خادم Groq. يرجى المحاولة مرة أخرى لاحقاً.';
        } elseif ($errorType === 'insufficient_quota' || stripos($errorMessage, 'quota') !== false) {
            return 'رصيد Groq غير كافٍ. يرجى إضافة رصيد إلى حسابك من Groq Console.';
        } elseif ($errorType === 'invalid_request_error' || stripos($errorMessage, 'invalid') !== false || stripos($errorMessage, 'payload') !== false) {
            $message = 'طلب غير صحيح: '.$errorMessage;
            $message .= "\n\n💡 نصائح:";
            $message .= "\n- تأكد من أن Model Key صحيح (مثل: llama-3.3-70b-versatile, qwen/qwen3-32b)";
            $message .= "\n- تأكد من أن API Key صحيح من: https://console.groq.com/keys";
            $message .= "\n- تأكد من أن Base URL صحيح: https://api.groq.com/openai/v1";
            $message .= "\n- تأكد من أن API Endpoint صحيح: /chat/completions";

            return $message;
        }

        return 'خطأ من Groq: '.$errorMessage;
    }

    /**
     * توليد نص بسيط من prompt واحد
     */
    public function generateText(string $prompt, array $options = []): string
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];

        $result = $this->chat($messages, $options);

        if (! $result['success']) {
            $this->setLastError($result['error'] ?? 'خطأ غير معروف في توليد النص', $result['status_code'] ?? null);

            return '';
        }

        $content = $result['content'] ?? '';

        // تنظيف المحتوى من الأحرف غير الصالحة في UTF-8
        if (! empty($content)) {
            if (! mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'auto');
            }
            // إزالة الأحرف غير الصالحة
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
            // إزالة BOM إذا كان موجوداً
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        }

        return $content;
    }

    /**
     * تقدير عدد الـ tokens بشكل تقريبي
     */
    public function estimateTokens(string $text): int
    {
        // تقدير بسيط: كل 4 أحرف تقريباً = token واحد
        $length = mb_strlen($text);

        return (int) ceil($length / 4);
    }

    /**
     * اختبار الاتصال مع Groq API
     */
    public function testConnection(): bool
    {
        $apiKey = $this->getApiKey();
        if (! $apiKey) {
            $this->setLastError('Groq API Key غير موجود.');

            return false;
        }

        try {
            $baseUrl = $this->getBaseUrl() ?? self::BASE_URL;
            $url = rtrim($baseUrl, '/').'/models';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.trim($apiKey),
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->timeout(60)->get($url);

            if ($response->successful()) {
                return true;
            }

            $data = $response->json();
            $errorMessage = $data['error']['message'] ?? 'فشل اختبار الاتصال بـ Groq';
            $this->setLastError($errorMessage);

            return false;
        } catch (\Throwable $e) {
            $this->setLastError($e->getMessage());

            return false;
        }
    }
}
