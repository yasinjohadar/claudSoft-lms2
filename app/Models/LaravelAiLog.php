<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaravelAiLog extends Model
{
    protected $table = 'laravel_ai_logs';

    protected $fillable = [
        'laravel_ai_model_id',
        'user_id',
        'operation',
        'request_payload',
        'response_payload',
        'status',
        'error_message',
        'latency_ms',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'latency_ms' => 'integer',
    ];

    public function laravelAiModel(): BelongsTo
    {
        return $this->belongsTo(LaravelAiModel::class, 'laravel_ai_model_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
