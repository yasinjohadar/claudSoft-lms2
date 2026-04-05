<?php

namespace App\Services\AiNew;

use App\Models\LaravelAiLog;
use App\Models\LaravelAiModel;
use Illuminate\Contracts\Auth\Authenticatable;

class LaravelAiRequestLogger
{
    private const PAYLOAD_MAX_LEN = 2000;

    public function logSuccess(
        LaravelAiModel $model,
        ?Authenticatable $user,
        string $operation,
        mixed $requestPayload,
        mixed $responsePayload,
        int $latencyMs,
    ): LaravelAiLog {
        return LaravelAiLog::create([
            'laravel_ai_model_id' => $model->id,
            'user_id' => $user?->getAuthIdentifier(),
            'operation' => $operation,
            'request_payload' => $this->truncatePayload($requestPayload),
            'response_payload' => $this->truncatePayload($responsePayload),
            'status' => 'success',
            'error_message' => null,
            'latency_ms' => $latencyMs,
        ]);
    }

    public function logFailure(
        LaravelAiModel $model,
        ?Authenticatable $user,
        string $operation,
        mixed $requestPayload,
        string $errorMessage,
        int $latencyMs,
    ): LaravelAiLog {
        return LaravelAiLog::create([
            'laravel_ai_model_id' => $model->id,
            'user_id' => $user?->getAuthIdentifier(),
            'operation' => $operation,
            'request_payload' => $this->truncatePayload($requestPayload),
            'response_payload' => null,
            'status' => 'failure',
            'error_message' => mb_substr($errorMessage, 0, 5000),
            'latency_ms' => $latencyMs,
        ]);
    }

    private function truncatePayload(mixed $payload): mixed
    {
        if ($payload === null) {
            return null;
        }

        if (is_string($payload)) {
            return mb_strlen($payload) > self::PAYLOAD_MAX_LEN
                ? mb_substr($payload, 0, self::PAYLOAD_MAX_LEN).'…'
                : $payload;
        }

        if (! is_array($payload)) {
            $payload = ['value' => $payload];
        }

        array_walk_recursive($payload, function (&$v): void {
            if (is_string($v) && mb_strlen($v) > self::PAYLOAD_MAX_LEN) {
                $v = mb_substr($v, 0, self::PAYLOAD_MAX_LEN).'…';
            }
        });

        return $payload;
    }
}
