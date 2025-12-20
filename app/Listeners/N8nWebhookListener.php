<?php

namespace App\Listeners;

use App\Events\N8nWebhookEvent;
use App\Services\N8nWebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class N8nWebhookListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        public N8nWebhookService $webhookService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(N8nWebhookEvent $event): void
    {
        try {
            $this->webhookService->trigger(
                $event->getEventType(),
                $event->getPayload(),
                $event->getMetadata()
            );
        } catch (\Exception $e) {
            Log::error("N8nWebhookListener failed", [
                'event_type' => $event->getEventType(),
                'error' => $e->getMessage(),
            ]);

            // Re-throw if you want the listener to retry
            // throw $e;
        }
    }

    /**
     * Determine whether the listener should be queued.
     */
    public function shouldQueue(N8nWebhookEvent $event): bool
    {
        // Queue all webhook events for better performance
        return true;
    }
}
