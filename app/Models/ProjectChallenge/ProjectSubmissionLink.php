<?php

namespace App\Models\ProjectChallenge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubmissionLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_stage_submission_id',
        'link_type',
        'title',
        'url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ProjectStageSubmission::class, 'project_stage_submission_id');
    }

    public function getLinkTypeLabel(): ?string
    {
        return config("project_challenges.link_types.{$this->link_type}");
    }
}
