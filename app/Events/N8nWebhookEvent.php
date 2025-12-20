<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class N8nWebhookEvent
{
    use Dispatchable, SerializesModels;

    public string $eventType;
    public array $payload;
    public ?array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(string $eventType, array $payload, ?array $metadata = null)
    {
        $this->eventType = $eventType;
        $this->payload = $payload;
        $this->metadata = $metadata ?? [];
    }

    /**
     * Get the event type
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * Get the payload
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Get the metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata ?? [];
    }
}
