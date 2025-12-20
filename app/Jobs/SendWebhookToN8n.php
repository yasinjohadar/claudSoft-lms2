<?php

namespace App\Jobs;

use App\Models\N8nWebhookEndpoint;
use App\Models\OutgoingWebhookLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SendWebhookToN8n implements ShouldQueue
{
    use Queueable;

    public int $tries = 1; // Will be set from endpoint config
    public int $timeout = 120;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public N8nWebhookEndpoint $endpoint,
        public OutgoingWebhookLog $log
    ) {
        $this->tries = $endpoint->retry_attempts;
        $this->timeout = $endpoint->timeout + 10; // Add buffer
        $this->onQueue(config('webhooks.n8n.outgoing.queue_name', 'webhooks'));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Mark as retrying if this is not the first attempt
            if ($this->log->attempt_number > 1) {
                $this->log->markAsRetrying();
            }

            // Prepare the payload with signature
            $payload = $this->log->payload;
            $timestamp = now()->timestamp;
            $signature = $this->generateSignature($payload, $timestamp);

            // Get headers
            $headers = array_merge(
                $this->endpoint->getMergedHeaders(),
                [
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Timestamp' => $timestamp,
                    'X-Event-Type' => $this->log->event_type,
                ]
            );

            // Send the webhook
            $response = Http::withHeaders($headers)
                ->timeout($this->endpoint->timeout)
                ->when(!config('webhooks.n8n.outgoing.verify_ssl', true), function ($http) {
                    return $http->withoutVerifying();
                })
                ->post($this->endpoint->url, $payload);

            // Log the response
            $statusCode = $response->status();
            $responseBody = $response->body();

            // Check if successful (2xx status codes)
            if ($response->successful()) {
                $this->log->markAsSent($statusCode, $responseBody);

                Log::info("n8n webhook sent successfully", [
                    'endpoint_id' => $this->endpoint->id,
                    'log_id' => $this->log->id,
                    'event_type' => $this->log->event_type,
                    'status' => $statusCode,
                ]);
            } else {
                // Non-2xx response
                $errorMessage = "HTTP {$statusCode}: {$responseBody}";

                if ($this->log->canRetry()) {
                    // Will retry
                    $this->log->incrementAttempt();
                    throw new Exception($errorMessage);
                } else {
                    // No more retries
                    $this->log->markAsFailed($errorMessage, $statusCode, $responseBody);

                    Log::error("n8n webhook failed after all retries", [
                        'endpoint_id' => $this->endpoint->id,
                        'log_id' => $this->log->id,
                        'event_type' => $this->log->event_type,
                        'error' => $errorMessage,
                    ]);
                }
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            if ($this->log->canRetry()) {
                // Increment attempt and re-throw to trigger Laravel retry
                $this->log->incrementAttempt();

                Log::warning("n8n webhook attempt failed, will retry", [
                    'endpoint_id' => $this->endpoint->id,
                    'log_id' => $this->log->id,
                    'attempt' => $this->log->attempt_number,
                    'error' => $errorMessage,
                ]);

                throw $e;
            } else {
                // No more retries
                $this->log->markAsFailed($errorMessage);

                Log::error("n8n webhook failed permanently", [
                    'endpoint_id' => $this->endpoint->id,
                    'log_id' => $this->log->id,
                    'error' => $errorMessage,
                ]);
            }
        }
    }

    /**
     * Generate HMAC signature for the payload
     */
    private function generateSignature(array $payload, int $timestamp): string
    {
        $data = json_encode($payload) . $timestamp;
        return hash_hmac('sha256', $data, $this->endpoint->secret_key);
    }

    /**
     * Handle job failure
     */
    public function failed(?Exception $exception): void
    {
        $this->log->markAsFailed(
            $exception ? $exception->getMessage() : 'Unknown error'
        );

        Log::error("n8n webhook job failed permanently", [
            'endpoint_id' => $this->endpoint->id,
            'log_id' => $this->log->id,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
