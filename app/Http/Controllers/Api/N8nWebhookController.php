<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\N8nIncomingWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class N8nWebhookController extends Controller
{
    public function __construct(
        public N8nIncomingWebhookService $webhookService
    ) {
    }

    /**
     * Handle incoming webhook from n8n
     */
    public function incoming(Request $request): JsonResponse
    {
        // Get handler type from header or payload
        $handlerType = $request->header('X-Handler-Type')
                    ?? $request->input('handler_type');

        if (!$handlerType) {
            return response()->json([
                'success' => false,
                'message' => 'Handler type not specified. Use X-Handler-Type header or handler_type in payload.',
            ], 400);
        }

        // Get payload
        $payload = $request->all();

        // Remove handler_type from payload if it was in the body
        unset($payload['handler_type']);

        // Process the webhook
        $result = $this->webhookService->process($handlerType, $payload);

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Get available handlers documentation
     */
    public function handlers(): JsonResponse
    {
        $handlers = $this->webhookService->getAvailableHandlers();

        return response()->json([
            'success' => true,
            'handlers' => $handlers,
            'count' => count($handlers),
        ]);
    }

    /**
     * Get specific handler documentation
     */
    public function handlerDocs(string $handlerType): JsonResponse
    {
        $documentation = $this->webhookService->getHandlerDocumentation($handlerType);

        if (!$documentation) {
            return response()->json([
                'success' => false,
                'message' => 'Handler not found or inactive',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'handler' => $documentation,
        ]);
    }
}
