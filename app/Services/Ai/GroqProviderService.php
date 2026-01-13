<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Groq Provider Service
 *
 * متوافق مع واجهة OpenAI API (chat/completions)
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
        if (!$apiKey) {
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
            $fullUrl = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

            Log::info('Groq API Request', [
                'url' => $fullUrl,
                'model' => $modelKey,
                'max_tokens' => $payload['max_tokens'],
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . trim($apiKey),
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->timeout(180)->post($fullUrl, $payload);

            $rawBody = $response->body();

            // التأكد من أن الاستجابة UTF-8
            if (!mb_check_encoding($rawBody, 'UTF-8')) {
                $body = mb_convert_encoding($rawBody, 'UTF-8', 'auto');
                if (!mb_check_encoding($body, 'UTF-8')) {
                    $body = mb_convert_encoding($rawBody, 'UTF-8', ['UTF-8', 'ISO-8859-1', 'Windows-1256']);
                }
            } else {
                $body = $rawBody;
            }

            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');

            $data = json_decode($body, true, 512, JSON_INVALID_UTF8_IGNORE);

            if ($response->successful()) {
                $content = $data['choices'][0]['message']['content'] ?? '';

                Log::info('Groq API Success', [
                    'model' => $data['model'] ?? $modelKey,
                    'tokens' => $data['usage']['total_tokens'] ?? null,
                ]);

                return [
                    'success' => true,
                    'content' => $content,
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                    'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                    'model_used' => $data['model'] ?? $modelKey,
                ];
            }

            $errorMessage = $data['error']['message'] ?? 'Unknown Groq error';
            $errorCode = $data['error']['type'] ?? $response->status();

            Log::error('Groq API Error', [
                'status' => $response->status(),
                'error' => $errorMessage,
                'code' => $errorCode,
                'response' => $data,
            ]);

            $this->setLastError($errorMessage);

            return [
                'success' => false,
                'error' => $errorMessage,
                'code' => $errorCode,
            ];
        } catch (\Throwable $e) {
            Log::error('Groq API Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->setLastError($e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
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

        if (!$result['success']) {
            // التأكد من أن الخطأ محفوظ (chat() يستدعي setLastError بالفعل)
            $this->setLastError($result['error'] ?? $this->getLastError() ?? 'خطأ غير معروف في توليد النص');
            return '';
        }

        $content = $result['content'] ?? '';

        // تنظيف المحتوى من الأحرف غير الصالحة في UTF-8
        if (!empty($content)) {
            if (!mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'auto');
            }
            // إزالة BOM وأحرف غير صالحة
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
            $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content);
        }

        return (string) $content;
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
        if (!$apiKey) {
            $this->setLastError('Groq API Key غير موجود.');
            return false;
        }

        try {
            $baseUrl = $this->getBaseUrl() ?? self::BASE_URL;
            $url = rtrim($baseUrl, '/') . '/models';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . trim($apiKey),
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


