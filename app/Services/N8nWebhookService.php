<?php

namespace App\Services;

use App\Models\N8nWebhookEndpoint;
use App\Models\OutgoingWebhookLog;
use App\Jobs\SendWebhookToN8n;
use Illuminate\Support\Facades\Log;

class N8nWebhookService
{
    /**
     * Trigger a webhook for a specific event type
     */
    public function trigger(string $eventType, array $payload, ?array $metadata = null): void
    {
        try {
            // Find all active endpoints for this event type
            $endpoints = N8nWebhookEndpoint::active()
                ->byEventType($eventType)
                ->get();

            if ($endpoints->isEmpty()) {
                Log::info("No active endpoints found for event type: {$eventType}");
                return;
            }

            // Dispatch webhook job for each endpoint
            foreach ($endpoints as $endpoint) {
                $this->sendToEndpoint($endpoint, $eventType, $payload, $metadata);
            }
        } catch (\Exception $e) {
            Log::error("Error triggering n8n webhooks", [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send webhook to a specific endpoint
     */
    public function sendToEndpoint(
        N8nWebhookEndpoint $endpoint,
        string $eventType,
        array $payload,
        ?array $metadata = null
    ): OutgoingWebhookLog {
        // Create log entry
        $log = OutgoingWebhookLog::create([
            'endpoint_id' => $endpoint->id,
            'event_type' => $eventType,
            'payload' => array_merge($payload, [
                'event_type' => $eventType,
                'timestamp' => now()->toIso8601String(),
                'metadata' => $metadata ?? [],
            ]),
            'status' => 'pending',
            'attempt_number' => 1,
        ]);

        // Dispatch the job to queue
        SendWebhookToN8n::dispatch($endpoint, $log);

        Log::info("Webhook job dispatched", [
            'endpoint_id' => $endpoint->id,
            'log_id' => $log->id,
            'event_type' => $eventType,
        ]);

        return $log;
    }

    /**
     * Retry a failed webhook
     */
    public function retry(OutgoingWebhookLog $log): bool
    {
        if (!$log->endpoint) {
            Log::error("Cannot retry webhook: endpoint not found", [
                'log_id' => $log->id,
            ]);
            return false;
        }

        if (!$log->canRetry()) {
            Log::warning("Cannot retry webhook: max attempts reached", [
                'log_id' => $log->id,
                'attempts' => $log->attempt_number,
            ]);
            return false;
        }

        // Reset status and increment attempt
        $log->update(['status' => 'pending']);
        $log->incrementAttempt();

        // Dispatch the job again
        SendWebhookToN8n::dispatch($log->endpoint, $log);

        Log::info("Webhook retry dispatched", [
            'log_id' => $log->id,
            'attempt' => $log->attempt_number,
        ]);

        return true;
    }

    /**
     * Retry all failed webhooks for an endpoint
     */
    public function retryFailedForEndpoint(N8nWebhookEndpoint $endpoint): int
    {
        $failedLogs = $endpoint->logs()
            ->where('status', 'failed')
            ->where('attempt_number', '<', $endpoint->retry_attempts)
            ->get();

        $retriedCount = 0;

        foreach ($failedLogs as $log) {
            if ($this->retry($log)) {
                $retriedCount++;
            }
        }

        return $retriedCount;
    }

    /**
     * Get statistics for an endpoint
     */
    public function getEndpointStats(N8nWebhookEndpoint $endpoint): array
    {
        $logs = $endpoint->logs();

        return [
            'total' => $logs->count(),
            'sent' => $logs->where('status', 'sent')->count(),
            'pending' => $logs->where('status', 'pending')->count(),
            'failed' => $logs->where('status', 'failed')->count(),
            'retrying' => $logs->where('status', 'retrying')->count(),
            'success_rate' => $endpoint->success_rate,
            'last_sent_at' => $endpoint->last_sent_at,
        ];
    }

    /**
     * Get overall webhook statistics
     */
    public function getOverallStats(): array
    {
        $endpoints = N8nWebhookEndpoint::withCount('logs')->get();
        $allLogs = OutgoingWebhookLog::query();

        return [
            'endpoints' => [
                'total' => $endpoints->count(),
                'active' => $endpoints->where('is_active', true)->count(),
                'inactive' => $endpoints->where('is_active', false)->count(),
            ],
            'logs' => [
                'total' => $allLogs->count(),
                'sent' => $allLogs->clone()->where('status', 'sent')->count(),
                'pending' => $allLogs->clone()->where('status', 'pending')->count(),
                'failed' => $allLogs->clone()->where('status', 'failed')->count(),
                'retrying' => $allLogs->clone()->where('status', 'retrying')->count(),
            ],
            'recent' => [
                'today' => OutgoingWebhookLog::whereDate('created_at', today())->count(),
                'this_week' => OutgoingWebhookLog::where('created_at', '>=', now()->subWeek())->count(),
                'this_month' => OutgoingWebhookLog::where('created_at', '>=', now()->subMonth())->count(),
            ],
        ];
    }

    /**
     * Test an endpoint by sending a test payload
     */
    public function testEndpoint(N8nWebhookEndpoint $endpoint): OutgoingWebhookLog
    {
        $testPayload = [
            'test' => true,
            'message' => 'This is a test webhook from your LMS',
            'endpoint_name' => $endpoint->name,
            'timestamp' => now()->toIso8601String(),
        ];

        return $this->sendToEndpoint(
            $endpoint,
            'system.test',
            $testPayload,
            ['test' => true]
        );
    }
}
