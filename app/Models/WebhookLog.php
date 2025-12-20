<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $table = 'webhook_logs';

    protected $fillable = [
        'source',
        'event_type',
        'payload',
        'headers',
        'status',
        'response',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
    ];

    // Scopes
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Helper Methods
    public function markAsProcessed(?string $response = null): void
    {
        $this->update([
            'status' => 'processed',
            'response' => $response,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'response' => $error,
        ]);
    }
}
