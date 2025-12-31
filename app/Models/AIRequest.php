<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIRequest extends Model
{
    use HasFactory;

    protected $table = 'ai_requests';

    protected $fillable = [
        'provider_id',
        'user_id',
        'request_type',
        'input_data',
        'response_data',
        'tokens_used',
        'input_tokens',
        'output_tokens',
        'cost',
        'status',
        'error_message',
        'model_used',
        'response_time_ms',
    ];

    protected $casts = [
        'input_data' => 'array',
        'response_data' => 'array',
        'tokens_used' => 'integer',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost' => 'decimal:6',
        'response_time_ms' => 'integer',
    ];

    /**
     * Get the provider for this request
     */
    public function provider()
    {
        return $this->belongsTo(AIProvider::class, 'provider_id');
    }

    /**
     * Get the user who made this request
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope for completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed requests
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope by request type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('request_type', $type);
    }

    /**
     * Mark request as completed
     */
    public function markAsCompleted(array $responseData, int $tokensUsed, float $cost, ?string $modelUsed = null): void
    {
        $this->update([
            'status' => 'completed',
            'response_data' => $responseData,
            'tokens_used' => $tokensUsed,
            'cost' => $cost,
            'model_used' => $modelUsed,
        ]);
    }

    /**
     * Mark request as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
