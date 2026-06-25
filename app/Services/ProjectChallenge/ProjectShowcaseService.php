<?php

namespace App\Services\ProjectChallenge;

use App\Models\ProjectChallenge\ProjectShowcase;
use App\Models\ProjectChallenge\ProjectStageSubmission;
use App\Models\ProjectChallenge\ProjectTeam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectShowcaseService
{
    public function __construct(
        protected ProjectActivityLogger $activityLogger,
        protected ProjectNotificationService $notifications
    ) {}

    public function canPublish(ProjectTeam $team): bool
    {
        $team->loadMissing('challenge.stages');

        $threshold = (float) ($team->challenge->showcase_threshold ?? 100);
        if ((float) $team->progress_percent < $threshold) {
            return false;
        }

        $mandatoryStageIds = $team->challenge->stages
            ->where('is_optional', false)
            ->pluck('id');

        if ($mandatoryStageIds->isEmpty()) {
            return true;
        }

        $approvedCount = ProjectStageSubmission::query()
            ->where('project_team_id', $team->id)
            ->whereIn('project_stage_id', $mandatoryStageIds)
            ->where('status', 'approved')
            ->count();

        return $approvedCount >= $mandatoryStageIds->count();
    }

    public function publish(ProjectTeam $team, array $data, User $publisher): ProjectShowcase
    {
        if (! $this->canPublish($team)) {
            throw new \RuntimeException('لم يستوفِ الفريق شروط نشر العرض');
        }

        if (! $team->hasMember($publisher->id)) {
            throw new \RuntimeException('يجب أن تكون عضواً في الفريق');
        }

        return DB::transaction(function () use ($team, $data, $publisher) {
            $title = $data['title'] ?? $team->name;
            $slug = $this->uniqueShowcaseSlug($title);

            $showcase = ProjectShowcase::updateOrCreate(
                ['project_team_id' => $team->id],
                [
                    'project_challenge_id' => $team->project_challenge_id,
                    'title' => $title,
                    'slug' => $slug,
                    'summary' => $data['summary'] ?? $team->description,
                    'github_url' => $data['github_url'] ?? null,
                    'demo_url' => $data['demo_url'] ?? null,
                    'video_url' => $data['video_url'] ?? null,
                    'cover_image' => $data['cover_image'] ?? $team->logo,
                    'screenshots' => $data['screenshots'] ?? null,
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );

            $this->activityLogger->log($team, 'showcase.published', $publisher, [
                'showcase_id' => $showcase->id,
                'slug' => $showcase->slug,
            ]);

            $team->load('activeMembers.user');
            $this->notifications->notifyMany(
                $team->activeMembers->pluck('user'),
                ProjectNotificationService::EVENT_SHOWCASE_PUBLISHED,
                [
                    'team_name' => $team->name,
                    'showcase_title' => $showcase->title,
                    'action_url' => route('student.community-projects.show', $showcase->slug),
                ]
            );

            return $showcase->fresh();
        });
    }

    public function unpublish(ProjectShowcase $showcase, ?User $actor = null): ProjectShowcase
    {
        $showcase->update([
            'status' => 'hidden',
            'published_at' => null,
        ]);

        if ($actor) {
            $this->activityLogger->log($showcase->team, 'showcase.unpublished', $actor, [
                'showcase_id' => $showcase->id,
            ]);
        }

        return $showcase->fresh();
    }

    protected function uniqueShowcaseSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'project';
        $slug = $base;
        $counter = 1;

        while (ProjectShowcase::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
