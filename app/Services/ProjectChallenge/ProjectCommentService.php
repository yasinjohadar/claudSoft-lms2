<?php

namespace App\Services\ProjectChallenge;

use App\Models\ProjectChallenge\ProjectComment;
use App\Models\ProjectChallenge\ProjectCommentLike;
use App\Models\ProjectChallenge\ProjectShowcase;
use App\Models\User;

class ProjectCommentService
{
    public function __construct(
        protected ProjectNotificationService $notifications
    ) {}

    public function addComment(ProjectShowcase $showcase, User $user, string $body): ProjectComment
    {
        if (! $showcase->isPublished()) {
            throw new \RuntimeException('لا يمكن التعليق على عرض غير منشور');
        }

        $comment = ProjectComment::create([
            'project_showcase_id' => $showcase->id,
            'user_id' => $user->id,
            'body' => $body,
        ]);

        $this->notifyShowcaseTeam($showcase, $user, $comment);

        return $comment->fresh('user');
    }

    public function reply(ProjectComment $parent, User $user, string $body): ProjectComment
    {
        if ($parent->is_hidden) {
            throw new \RuntimeException('لا يمكن الرد على تعليق مخفي');
        }

        $parent->loadMissing('showcase');

        if (! $parent->showcase->isPublished()) {
            throw new \RuntimeException('لا يمكن التعليق على عرض غير منشور');
        }

        $reply = ProjectComment::create([
            'project_showcase_id' => $parent->project_showcase_id,
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'body' => $body,
        ]);

        if ($parent->user_id !== $user->id) {
            $this->notifications->notifyCommentPosted($parent->user, [
                'showcase_title' => $parent->showcase->title,
                'commenter_name' => $user->name,
                'action_url' => route('student.community-projects.show', $parent->showcase->slug),
            ]);
        }

        return $reply->fresh('user');
    }

    public function toggleLike(ProjectComment $comment, User $user): array
    {
        $existing = ProjectCommentLike::query()
            ->where('project_comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return ['liked' => false, 'count' => $comment->likes()->count()];
        }

        ProjectCommentLike::create([
            'project_comment_id' => $comment->id,
            'user_id' => $user->id,
        ]);

        return ['liked' => true, 'count' => $comment->likes()->count()];
    }

    protected function notifyShowcaseTeam(ProjectShowcase $showcase, User $commenter, ProjectComment $comment): void
    {
        $showcase->loadMissing('team.activeMembers.user');

        $recipients = $showcase->team->activeMembers
            ->pluck('user')
            ->filter(fn (User $u) => $u->id !== $commenter->id);

        $this->notifications->notifyMany($recipients, ProjectNotificationService::EVENT_COMMENT_POSTED, [
            'showcase_title' => $showcase->title,
            'commenter_name' => $commenter->name,
            'action_url' => route('student.community-projects.show', $showcase->slug),
        ]);
    }
}
