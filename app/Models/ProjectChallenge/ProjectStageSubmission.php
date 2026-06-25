<?php

namespace App\Models\ProjectChallenge;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectStageSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_team_id',
        'project_stage_id',
        'submitted_by',
        'status',
        'progress_percent',
        'score',
        'max_score',
        'feedback',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'resubmit_deadline',
        'reject_reason',
    ];

    protected $casts = [
        'progress_percent' => 'decimal:2',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'resubmit_deadline' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(ProjectTeam::class, 'project_team_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function links(): HasMany
    {
        return $this->hasMany(ProjectSubmissionLink::class)->orderBy('sort_order');
    }

    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review', 'approved', 'rejected', 'resubmit_required']);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function needsResubmit(): bool
    {
        return $this->status === 'resubmit_required';
    }

    public function isUnderReview(): bool
    {
        return in_array($this->status, ['submitted', 'under_review'], true);
    }
}
