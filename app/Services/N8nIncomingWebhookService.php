<?php

namespace App\Services;

use App\Models\N8nIncomingWebhookHandler;
use Illuminate\Support\Facades\Log;

class N8nIncomingWebhookService
{
    /**
     * Process incoming webhook from n8n
     */
    public function process(string $handlerType, array $payload): array
    {
        try {
            // Find the handler configuration
            $handlerConfig = N8nIncomingWebhookHandler::active()
                ->byHandlerType($handlerType)
                ->first();

            if (!$handlerConfig) {
                Log::warning("No active handler found for type: {$handlerType}");

                return [
                    'success' => false,
                    'message' => "No active handler found for type: {$handlerType}",
                ];
            }

            // Validate payload fields
            $validationErrors = $handlerConfig->validateFields($payload);

            if (!empty($validationErrors)) {
                Log::warning("Payload validation failed", [
                    'handler_type' => $handlerType,
                    'errors' => $validationErrors,
                ]);

                return [
                    'success' => false,
                    'message' => 'Payload validation failed',
                    'errors' => $validationErrors,
                ];
            }

            // Get handler instance
            $handler = $handlerConfig->getHandlerInstance();

            if (!$handler) {
                Log::error("Handler class not found or invalid", [
                    'handler_type' => $handlerType,
                    'handler_class' => $handlerConfig->handler_class,
                ]);

                return [
                    'success' => false,
                    'message' => 'Handler class not found or invalid',
                ];
            }

            // Execute the handler
            $result = $handler->handle($payload);

            Log::info("Incoming webhook processed", [
                'handler_type' => $handlerType,
                'success' => $result['success'] ?? false,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("Incoming webhook processing failed", [
                'handler_type' => $handlerType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Webhook processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get all available handlers with their documentation
     */
    public function getAvailableHandlers(): array
    {
        return N8nIncomingWebhookHandler::active()
            ->get()
            ->map(fn($handler) => $handler->documentation)
            ->toArray();
    }

    /**
     * Get handler documentation by type
     */
    public function getHandlerDocumentation(string $handlerType): ?array
    {
        $handler = N8nIncomingWebhookHandler::active()
            ->byHandlerType($handlerType)
            ->first();

        return $handler?->documentation;
    }
}
