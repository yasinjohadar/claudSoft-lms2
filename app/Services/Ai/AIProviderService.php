<?php

namespace App\Services\Ai;

use App\Models\AIModel;

/**
 * Abstract base class for AI providers
 */
abstract class AIProviderService
{
    protected AIModel $model;

    protected ?string $lastError = null;

    public function __construct(AIModel $model)
    {
        $this->model = $model;
    }

    /**
     * الحصول على آخر خطأ
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * تعيين آخر خطأ
     *
     * Providers translate their errors to Arabic before anyone sees them, which
     * leaves AiErrorClassifier unable to tell a 429 from a 401. Appending the raw
     * status keeps the message readable and the classification reliable.
     */
    protected function setLastError(string $error, ?int $statusCode = null): void
    {
        if ($statusCode !== null && $statusCode > 0 && ! preg_match('/\[HTTP \d+\]/', $error)) {
            $error = rtrim($error).' [HTTP '.$statusCode.']';
        }

        $this->lastError = $error;
    }

    /** Drop the diagnostic suffix added by setLastError() before showing a message to a user. */
    public static function stripDiagnostics(?string $error): string
    {
        return trim((string) preg_replace('/\s*\[HTTP \d+\]\s*$/', '', (string) $error));
    }

    /**
     * إرسال رسالة في محادثة
     */
    abstract public function chat(array $messages, array $options = []): array;

    /**
     * توليد نص من prompt
     */
    abstract public function generateText(string $prompt, array $options = []): string;

    /**
     * تقدير عدد الـ tokens
     */
    abstract public function estimateTokens(string $text): int;

    /**
     * حساب التكلفة
     */
    public function calculateCost(int $tokens): float
    {
        return $this->model->getCost($tokens);
    }

    /**
     * اختبار الاتصال
     */
    abstract public function testConnection(): bool;

    /**
     * الحصول على API Key
     */
    protected function getApiKey(): ?string
    {
        return $this->model->getDecryptedApiKey();
    }

    /**
     * الحصول على Base URL
     */
    protected function getBaseUrl(): ?string
    {
        return $this->model->base_url;
    }

    /**
     * الحصول على API Endpoint
     */
    protected function getApiEndpoint(): ?string
    {
        return $this->model->api_endpoint;
    }
}
