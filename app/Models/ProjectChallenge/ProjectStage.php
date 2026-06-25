<?php

namespace App\Models\ProjectChallenge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_challenge_id',
        'title',
        'description',
        'sort_order',
        'duration_days',
        'due_at',
        'max_score',
        'weight',
        'is_optional',
        'unlock_after_previous',
        'allowed_link_types',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'duration_days' => 'integer',
        'due_at' => 'datetime',
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_optional' => 'boolean',
        'unlock_after_previous' => 'boolean',
        'allowed_link_types' => 'array',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ProjectChallenge::class, 'project_challenge_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ProjectStageSubmission::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_optional', false);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function getAllowedLinkTypes(): array
    {
        return $this->allowed_link_types
            ?? array_keys(config('project_challenges.link_types', []));
    }
}
