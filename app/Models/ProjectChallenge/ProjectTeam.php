<?php

namespace App\Models\ProjectChallenge;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_challenge_id',
        'name',
        'slug',
        'logo',
        'description',
        'leader_id',
        'status',
        'total_score',
        'progress_percent',
        'admin_unlocked_stage_ids',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'progress_percent' => 'decimal:2',
        'admin_unlocked_stage_ids' => 'array',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ProjectChallenge::class, 'project_challenge_id');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectTeamMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(ProjectTeamMember::class)->where('status', 'active');
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(ProjectTeamJoinRequest::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectTeamInvitation::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ProjectStageSubmission::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class)->orderByDesc('created_at');
    }

    public function showcase(): HasOne
    {
        return $this->hasOne(ProjectShowcase::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDisqualified(): bool
    {
        return $this->status === 'disqualified';
    }

    public function memberCount(): int
    {
        return $this->activeMembers()->count();
    }

    public function hasMember(int $userId): bool
    {
        return $this->activeMembers()->where('user_id', $userId)->exists();
    }

    public function canAcceptMembers(): bool
    {
        $maxMembers = $this->challenge?->max_members;

        if ($maxMembers === null) {
            return true;
        }

        return $this->memberCount() < $maxMembers;
    }

    public function hasAdminUnlockedStage(int $stageId): bool
    {
        return in_array($stageId, $this->admin_unlocked_stage_ids ?? [], true);
    }
}
