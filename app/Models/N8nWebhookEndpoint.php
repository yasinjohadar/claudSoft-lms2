<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class N8nWebhookEndpoint extends Model
{
    protected $table = 'n8n_webhook_endpoints';

    protected $fillable = [
        'name',
        'event_type',
        'url',
        'secret_key',
        'is_active',
        'retry_attempts',
        'timeout',
        'headers',
        'metadata',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'retry_attempts' => 'integer',
        'timeout' => 'integer',
        'headers' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get all outgoing webhook logs for this endpoint
     */
    public function logs(): HasMany
    {
        return $this->hasMany(OutgoingWebhookLog::class, 'endpoint_id');
    }

    /**
     * Get only active endpoints
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter by event type
     */
    public function scopeByEventType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType)
                     ->orWhere('event_type', '*'); // Wildcard endpoints
    }

    /**
     * Get recent logs for this endpoint
     */
    public function scopeWithRecentLogs(Builder $query, int $limit = 10): Builder
    {
        return $query->with(['logs' => function ($q) use ($limit) {
            $q->latest()->limit($limit);
        }]);
    }

    /**
     * Check if endpoint is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if endpoint should retry on failure
     */
    public function shouldRetry(int $currentAttempt): bool
    {
        return $this->is_active && $currentAttempt < $this->retry_attempts;
    }

    /**
     * Get success rate for this endpoint
     */
    public function getSuccessRateAttribute(): float
    {
        $totalLogs = $this->logs()->count();

        if ($totalLogs === 0) {
            return 0;
        }

        $successfulLogs = $this->logs()->where('status', 'sent')->count();

        return round(($successfulLogs / $totalLogs) * 100, 2);
    }

    /**
     * Get total sent count
     */
    public function getTotalSentAttribute(): int
    {
        return $this->logs()->where('status', 'sent')->count();
    }

    /**
     * Get total failed count
     */
    public function getTotalFailedAttribute(): int
    {
        return $this->logs()->where('status', 'failed')->count();
    }

    /**
     * Get last sent timestamp
     */
    public function getLastSentAtAttribute(): ?string
    {
        $lastLog = $this->logs()->where('status', 'sent')->latest('sent_at')->first();

        return $lastLog?->sent_at;
    }

    /**
     * Deactivate the endpoint
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Activate the endpoint
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Get headers merged with defaults
     */
    public function getMergedHeaders(): array
    {
        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        return array_merge($defaultHeaders, $this->headers ?? []);
    }
}
