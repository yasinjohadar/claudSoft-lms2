<?php

namespace App\Models\ProjectChallenge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectShowcase extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_team_id',
        'project_challenge_id',
        'title',
        'slug',
        'summary',
        'github_url',
        'demo_url',
        'video_url',
        'cover_image',
        'screenshots',
        'published_at',
        'status',
        'avg_rating',
    ];

    protected $casts = [
        'screenshots' => 'array',
        'published_at' => 'datetime',
        'avg_rating' => 'decimal:2',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(ProjectTeam::class, 'project_team_id');
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ProjectChallenge::class, 'project_challenge_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class)->whereNull('parent_id')->orderBy('created_at');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isHidden(): bool
    {
        return $this->status === 'hidden';
    }
}
