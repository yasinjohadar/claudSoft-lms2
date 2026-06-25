<?php

namespace App\Services\ProjectChallenge;

use App\Models\ProjectChallenge\ProjectStage;
use App\Models\ProjectChallenge\ProjectStageSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectGradingService
{
    public function __construct(
        protected ProjectProgressCalculator $progressCalculator,
        protected ProjectActivityLogger $activityLogger,
        protected ProjectNotificationService $notifications
    ) {}

    public function gradeSubmission(
        ProjectStageSubmission $submission,
        float $score,
        ?string $feedback,
        User $reviewer,
        ?float $progressPercent = null,
        string $status = 'approved'
    ): ProjectStageSubmission {
        if (! in_array($submission->status, ['submitted', 'under_review', 'resubmit_required'], true)) {
            throw new \RuntimeException('لا يمكن تقييم هذا التسليم');
        }

        $submission->loadMissing(['team.challenge.stages', 'stage']);

        $maxScore = (float) ($submission->max_score ?? $submission->stage->max_score);
        $normalizedScore = min($score, $maxScore);

        return DB::transaction(function () use (
            $submission,
            $normalizedScore,
            $maxScore,
            $feedback,
            $reviewer,
            $progressPercent,
            $status
        ) {
            $submission->update([
                'status' => $status,
                'score' => $normalizedScore,
                'max_score' => $maxScore,
                'feedback' => $feedback,
                'progress_percent' => $progressPercent,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $team = $submission->team;
            $totalScore = ProjectStageSubmission::query()
                ->where('project_team_id', $team->id)
                ->where('status', 'approved')
                ->sum('score');

            $team->update(['total_score' => $totalScore]);
            $this->progressCalculator->calculateAndPersist($team);

            if ($status === 'approved') {
                $this->unlockNextStageIfSequential($team, $submission->stage);
            }

            $this->activityLogger->log($team, 'stage.graded', $reviewer, [
                'stage_id' => $submission->project_stage_id,
                'submission_id' => $submission->id,
                'score' => $normalizedScore,
                'status' => $status,
            ]);

            $team->load('activeMembers.user');
            $this->notifications->notifyMany(
                $team->activeMembers->pluck('user'),
                ProjectNotificationService::EVENT_STAGE_GRADED,
                [
                    'team_name' => $team->name,
                    'challenge_title' => $team->challenge->title,
                    'stage_title' => $submission->stage->title,
                    'score' => $normalizedScore,
                    'max_score' => $maxScore,
                    'action_url' => route('student.project-teams.workspace', $team->id),
                ]
            );

            return $submission->fresh(['links', 'stage', 'team']);
        });
    }

    protected function unlockNextStageIfSequential($team, ProjectStage $currentStage): void
    {
        $settings = $team->challenge->getDefaultSettings();

        if (! ($settings['unlock_stages_sequentially'] ?? true) && ! $currentStage->unlock_after_previous) {
            return;
        }

        $stages = $team->challenge->stages->sortBy('sort_order')->values();
        $currentIndex = $stages->search(fn (ProjectStage $s) => $s->id === $currentStage->id);

        if ($currentIndex === false) {
            return;
        }

        $next = $stages->get($currentIndex + 1);

        if ($next && $next->isLocked()) {
            $next->update(['status' => 'open']);
        }
    }
}
