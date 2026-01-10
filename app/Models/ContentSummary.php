<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentSummary extends Model
{
    use HasFactory;

    protected $table = 'content_summaries';

    protected $fillable = [
        'summarizable_type',
        'summarizable_id',
        'summary_text',
        'summary_type',
        'ai_model_id',
        'tokens_used',
        'cost',
        'created_by',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'cost' => 'float',
    ];

    /**
     * Get the summarizable model (polymorphic)
     */
    public function summarizable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the AI model used
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'ai_model_id');
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}



