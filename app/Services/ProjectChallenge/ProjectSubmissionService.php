<?php

namespace App\Services\ProjectChallenge;

use App\Models\ProjectChallenge\ProjectStage;
use App\Models\ProjectChallenge\ProjectStageSubmission;
use App\Models\ProjectChallenge\ProjectSubmissionLink;
use App\Models\ProjectChallenge\ProjectTeam;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectSubmissionService
{
    public function __construct(
        protected ProjectActivityLogger $activityLogger,
        protected ProjectNotificationService $notifications
    ) {}

    public function getOrCreateDraftSubmission(ProjectTeam $team, ProjectStage $stage): ProjectStageSubmission
    {
        $submission = ProjectStageSubmission::query()
            ->where('project_team_id', $team->id)
            ->where('project_stage_id', $stage->id)
            ->first();

        if ($submission) {
            return $submission;
        }

        return ProjectStageSubmission::create([
            'project_team_id' => $team->id,
            'project_stage_id' => $stage->id,
            'status' => 'draft',
            'max_score' => $stage->max_score,
        ]);
    }

    public function saveDraftLinks(
        ProjectStageSubmission $submission,
        array $links,
        User $user
    ): ProjectStageSubmission {
        if (! in_array($submission->status, ['draft', 'resubmit_required'], true)) {
            throw new \RuntimeException('لا يمكن تعديل تسليم غير قابل للتحرير');
        }

        $submission->loadMissing(['team', 'stage']);

        if (! $submission->team->hasMember($user->id)) {
            throw new \RuntimeException('يجب أن تكون عضواً في الفريق');
        }

        if (! $this->isStageUnlockedForTeam($submission->team, $submission->stage)) {
            throw new \RuntimeException('هذه المرحلة مقفلة');
        }

        return DB::transaction(function () use ($submission, $links) {
            $submission->links()->delete();

            foreach ($links as $index => $link) {
                if (empty($link['url'])) {
                    continue;
                }

                ProjectSubmissionLink::create([
                    'project_stage_submission_id' => $submission->id,
                    'link_type' => $link['link_type'] ?? 'other',
                    'title' => $link['title'] ?? null,
                    'url' => $link['url'],
                    'sort_order' => $link['sort_order'] ?? $index,
                ]);
            }

            return $submission->fresh('links');
        });
    }

    public function submitStage(ProjectStageSubmission $submission, User $user): ProjectStageSubmission
    {
        $submission->loadMissing(['team.challenge', 'stage', 'links']);

        if (! $submission->team->isActive()) {
            throw new \RuntimeException('الفريق غير نشط');
        }

        if (! $submission->team->hasMember($user->id)) {
            throw new \RuntimeException('يجب أن تكون عضواً في الفريق');
        }

        if (! $this->isStageUnlockedForTeam($submission->team, $submission->stage)) {
            throw new \RuntimeException('هذه المرحلة مقفلة');
        }

        if (! in_array($submission->status, ['draft', 'resubmit_required'], true)) {
            throw new \RuntimeException('تم تسليم هذه المرحلة بالفعل');
        }

        if ($submission->links->isEmpty()) {
            throw new \RuntimeException('يجب إضافة رابط واحد على الأقل قبل التسليم');
        }

        return DB::transaction(function () use ($submission, $user) {
            $submission->update([
                'status' => 'submitted',
                'submitted_by' => $user->id,
                'submitted_at' => now(),
            ]);

            $team = $submission->team;
            $stage = $submission->stage;

            $this->activityLogger->log($team, 'stage.submitted', $user, [
                'stage_id' => $stage->id,
                'stage_title' => $stage->title,
                'submission_id' => $submission->id,
            ]);

            $admins = \App\Models\User::role('admin')->get();
            $this->notifications->notifyMany($admins, ProjectNotificationService::EVENT_STAGE_SUBMITTED, [
                'team_name' => $team->name,
                'challenge_title' => $team->challenge->title,
                'stage_title' => $stage->title,
                'action_url' => route('admin.project-grading.show', $submission->id),
            ]);

            return $submission->fresh(['links', 'stage']);
        });
    }

    public function isStageUnlockedForTeam(ProjectTeam $team, ProjectStage $stage): bool
    {
        $team->loadMissing('challenge.stages');

        if ($team->hasAdminUnlockedStage($stage->id)) {
            return true;
        }

        if (! $stage->isOpen()) {
            return false;
        }

        if ($stage->project_challenge_id !== $team->project_challenge_id) {
            return false;
        }

        $settings = $team->challenge->getDefaultSettings();
        $sequential = (bool) ($settings['unlock_stages_sequentially'] ?? true);

        if (! $sequential && ! $stage->unlock_after_previous) {
            return true;
        }

        $stages = $team->challenge->stages->sortBy('sort_order')->values();
        $stageIndex = $stages->search(fn (ProjectStage $s) => $s->id === $stage->id);

        if ($stageIndex === false || $stageIndex === 0) {
            return true;
        }

        for ($i = 0; $i < $stageIndex; $i++) {
            $previous = $stages[$i];

            if ($previous->is_optional) {
                continue;
            }

            $approved = ProjectStageSubmission::query()
                ->where('project_team_id', $team->id)
                ->where('project_stage_id', $previous->id)
                ->where('status', 'approved')
                ->exists();

            if (! $approved) {
                return false;
            }
        }

        return true;
    }
}
