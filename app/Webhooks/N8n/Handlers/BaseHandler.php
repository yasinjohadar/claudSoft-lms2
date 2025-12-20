<?php

namespace App\Webhooks\N8n\Handlers;

use Illuminate\Support\Facades\Log;

abstract class BaseHandler
{
    /**
     * Handle the incoming webhook payload
     *
     * @param array $payload The webhook payload
     * @return array Response data
     * @throws \Exception
     */
    abstract public function handle(array $payload): array;

    /**
     * Validate the payload
     *
     * @param array $payload
     * @param array $requiredFields
     * @return void
     * @throws \Exception
     */
    protected function validate(array $payload, array $requiredFields): void
    {
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($payload[$field]) || $payload[$field] === null || $payload[$field] === '') {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new \Exception('Missing required fields: ' . implode(', ', $missingFields));
        }
    }

    /**
     * Log success
     */
    protected function logSuccess(string $message, array $context = []): void
    {
        Log::info("[n8n Handler] {$message}", array_merge($context, [
            'handler' => static::class,
        ]));
    }

    /**
     * Log error
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[n8n Handler] {$message}", array_merge($context, [
            'handler' => static::class,
        ]));
    }

    /**
     * Create success response
     */
    protected function success(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Create error response
     */
    protected function error(string $message, array $errors = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];
    }
}
