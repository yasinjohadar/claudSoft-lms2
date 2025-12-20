<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class OutgoingWebhookLog extends Model
{
    protected $table = 'outgoing_webhook_logs';

    protected $fillable = [
        'endpoint_id',
        'event_type',
        'payload',
        'response_status',
        'response_body',
        'attempt_number',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_status' => 'integer',
        'attempt_number' => 'integer',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the endpoint that owns this log
     */
    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(N8nWebhookEndpoint::class, 'endpoint_id');
    }

    /**
     * Scope for pending logs
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for sent logs
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed logs
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for retrying logs
     */
    public function scopeRetrying(Builder $query): Builder
    {
        return $query->where('status', 'retrying');
    }

    /**
     * Scope for specific event type
     */
    public function scopeByEventType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark log as sent successfully
     */
    public function markAsSent(int $responseStatus, ?string $responseBody = null): bool
    {
        return $this->update([
            'status' => 'sent',
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark log as failed
     */
    public function markAsFailed(string $errorMessage, ?int $responseStatus = null, ?string $responseBody = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
        ]);
    }

    /**
     * Mark log as retrying
     */
    public function markAsRetrying(): bool
    {
        return $this->update([
            'status' => 'retrying',
        ]);
    }

    /**
     * Increment attempt number
     */
    public function incrementAttempt(): bool
    {
        return $this->increment('attempt_number');
    }

    /**
     * Check if this log can be retried
     */
    public function canRetry(): bool
    {
        if (!$this->endpoint) {
            return false;
        }

        return $this->endpoint->shouldRetry($this->attempt_number)
               && in_array($this->status, ['pending', 'retrying', 'failed']);
    }

    /**
     * Check if log was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'sent' && $this->response_status >= 200 && $this->response_status < 300;
    }

    /**
     * Check if log is still pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if log has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get formatted response status with text
     */
    public function getFormattedResponseStatusAttribute(): string
    {
        if (!$this->response_status) {
            return 'N/A';
        }

        $statusTexts = [
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];

        $text = $statusTexts[$this->response_status] ?? 'Unknown';

        return "{$this->response_status} {$text}";
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'sent' => 'success',
            'pending' => 'warning',
            'retrying' => 'info',
            'failed' => 'danger',
            default => 'secondary',
        };
    }
}
