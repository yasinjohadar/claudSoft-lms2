<?php

namespace App\Services\ProjectChallenge;

use App\Models\User;
use App\Services\Notifications\NotificationHubService;

class ProjectNotificationService
{
    public const EVENT_TEAM_JOIN_REQUESTED = 'project.team.join_requested';

    public const EVENT_TEAM_JOIN_APPROVED = 'project.team.join_approved';

    public const EVENT_TEAM_JOIN_REJECTED = 'project.team.join_rejected';

    public const EVENT_STAGE_SUBMITTED = 'project.stage.submitted';

    public const EVENT_STAGE_GRADED = 'project.stage.graded';

    public const EVENT_SHOWCASE_PUBLISHED = 'project.showcase.published';

    public const EVENT_COMMENT_POSTED = 'project.comment.posted';

    public function __construct(
        protected NotificationHubService $hub
    ) {}

    public function notifyJoinRequested(User $recipient, array $data = []): array
    {
        return $this->hub->sendToUser($recipient, self::EVENT_TEAM_JOIN_REQUESTED, $data);
    }

    public function notifyJoinApproved(User $recipient, array $data = []): array
    {
        return $this->hub->sendToUser($recipient, self::EVENT_TEAM_JOIN_APPROVED, $data);
    }

    public function notifyJoinRejected(User $recipient, array $data = []): array
    {
        return $this->hub->sendToUser($recipient, self::EVENT_TEAM_JOIN_REJECTED, $data);
    }

    public function notifyStageSubmitted(User $recipient, array $data = []): array
    {
        return $this->hub->sendToUser($recipient, self::EVENT_STAGE_SUBMITTED, $data);
    }

    public function notifyStageGraded(User $recipient, array $data = []): array
    {
        return $this->hub->sendToUser($recipient, self::EVENT_STAGE_GRADED, $data);
    }

    public function notifyShowcasePublished(User $recipient, array $data = []): array
    {
        return $this->hub->sendToUser($recipient, self::EVENT_SHOWCASE_PUBLISHED, $data);
    }

    public function notifyCommentPosted(User $recipient, array $data = []): array
    {
        return $this->hub->sendToUser($recipient, self::EVENT_COMMENT_POSTED, $data);
    }

    /**
     * @param  iterable<int, User>  $recipients
     */
    public function notifyMany(iterable $recipients, string $eventKey, array $data = []): int
    {
        return $this->hub->sendToUsers($recipients, $eventKey, $data);
    }
}
