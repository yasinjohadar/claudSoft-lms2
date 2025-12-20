<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class N8nIncomingWebhookHandler extends Model
{
    protected $table = 'n8n_incoming_webhook_handlers';

    protected $fillable = [
        'handler_type',
        'handler_class',
        'description',
        'required_fields',
        'optional_fields',
        'is_active',
        'example_payload',
    ];

    protected $casts = [
        'required_fields' => 'array',
        'optional_fields' => 'array',
        'is_active' => 'boolean',
        'example_payload' => 'array',
    ];

    /**
     * Get only active handlers
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter by handler type
     */
    public function scopeByHandlerType(Builder $query, string $handlerType): Builder
    {
        return $query->where('handler_type', $handlerType);
    }

    /**
     * Get handler instance
     */
    public function getHandlerInstance(): ?object
    {
        if (!$this->handler_class || !class_exists($this->handler_class)) {
            return null;
        }

        return app($this->handler_class);
    }

    /**
     * Get handler class name
     */
    public function getHandlerClass(): string
    {
        return $this->handler_class;
    }

    /**
     * Validate payload fields
     */
    public function validateFields(array $payload): array
    {
        $errors = [];
        $requiredFields = $this->required_fields ?? [];

        // Check required fields
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field]) || $payload[$field] === null || $payload[$field] === '') {
                $errors[] = "Missing required field: {$field}";
            }
        }

        return $errors;
    }

    /**
     * Check if payload is valid
     */
    public function isPayloadValid(array $payload): bool
    {
        return empty($this->validateFields($payload));
    }

    /**
     * Get all fields (required + optional)
     */
    public function getAllFields(): array
    {
        $required = $this->required_fields ?? [];
        $optional = $this->optional_fields ?? [];

        return array_merge($required, $optional);
    }

    /**
     * Check if handler is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Activate the handler
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the handler
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Get formatted documentation
     */
    public function getDocumentationAttribute(): array
    {
        return [
            'handler_type' => $this->handler_type,
            'description' => $this->description,
            'handler_class' => $this->handler_class,
            'endpoint' => route('api.webhooks.n8n.incoming'),
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'X-N8N-Signature' => 'HMAC signature (if configured)',
                'X-Handler-Type' => $this->handler_type,
            ],
            'required_fields' => $this->required_fields ?? [],
            'optional_fields' => $this->optional_fields ?? [],
            'example_payload' => $this->example_payload ?? [],
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Get short description (first 100 chars)
     */
    public function getShortDescriptionAttribute(): string
    {
        if (!$this->description) {
            return 'No description available';
        }

        return strlen($this->description) > 100
            ? substr($this->description, 0, 100) . '...'
            : $this->description;
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }
}
