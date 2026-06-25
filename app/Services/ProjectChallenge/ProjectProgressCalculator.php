<?php

namespace App\Services\ProjectChallenge;

use App\Models\ProjectChallenge\ProjectStage;
use App\Models\ProjectChallenge\ProjectStageSubmission;
use App\Models\ProjectChallenge\ProjectTeam;

class ProjectProgressCalculator
{
    /**
     * Calculate weighted progress from approved submissions and persist on the team.
     */
    public function calculateAndPersist(ProjectTeam $team): float
    {
        $progress = $this->calculate($team);

        $team->update(['progress_percent' => $progress]);

        return $progress;
    }

    public function calculate(ProjectTeam $team): float
    {
        $team->loadMissing('challenge.stages');

        $stages = $team->challenge->stages;
        $totalWeight = $stages->sum(fn (ProjectStage $stage) => (float) $stage->weight);

        if ($totalWeight <= 0) {
            return 0.0;
        }

        $approvedSubmissions = ProjectStageSubmission::query()
            ->where('project_team_id', $team->id)
            ->where('status', 'approved')
            ->get()
            ->keyBy('project_stage_id');

        $weightedScore = 0.0;

        foreach ($stages as $stage) {
            $submission = $approvedSubmissions->get($stage->id);

            if (! $submission || ! $submission->score || ! $stage->max_score) {
                continue;
            }

            $maxScore = (float) ($submission->max_score ?? $stage->max_score);
            if ($maxScore <= 0) {
                continue;
            }

            $ratio = min(1.0, (float) $submission->score / $maxScore);
            $weightedScore += $ratio * (float) $stage->weight;
        }

        return round(($weightedScore / $totalWeight) * 100, 2);
    }
}
