<?php

namespace App\Models\ProjectChallenge;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectChallenge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'cover_image',
        'summary',
        'description',
        'difficulty',
        'project_type',
        'points_total',
        'expected_duration',
        'starts_at',
        'ends_at',
        'max_teams',
        'min_members',
        'max_members',
        'allow_late_join',
        'team_approval_mode',
        'status',
        'is_featured',
        'language',
        'settings',
        'showcase_threshold',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'points_total' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'max_teams' => 'integer',
        'min_members' => 'integer',
        'max_members' => 'integer',
        'allow_late_join' => 'boolean',
        'is_featured' => 'boolean',
        'settings' => 'array',
        'showcase_threshold' => 'integer',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(ProjectStage::class)->orderBy('sort_order');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(ProjectTeam::class);
    }

    public function showcases(): HasMany
    {
        return $this->hasMany(ProjectShowcase::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(ProjectSkill::class, 'project_challenge_skill')
            ->withTimestamps();
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(ProjectTechnology::class, 'project_challenge_technology')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOpen($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        });
    }

    public function scopeOfDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('project_type', $type);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isOpen(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function hasReachedTeamLimit(): bool
    {
        if ($this->max_teams === null) {
            return false;
        }

        return $this->teams()->whereIn('status', ['pending', 'active', 'completed'])->count() >= $this->max_teams;
    }

    public function getDefaultSettings(): array
    {
        return array_merge([
            'allowed_link_types' => array_keys(config('project_challenges.link_types', [])),
            'unlock_stages_sequentially' => true,
        ], $this->settings ?? []);
    }
}
