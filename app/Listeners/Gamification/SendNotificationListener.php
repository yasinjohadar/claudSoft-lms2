<?php

namespace App\Listeners\Gamification;

use App\Models\User;
use App\Services\Gamification\NotificationService;
use Illuminate\Support\Facades\Log;

class SendNotificationListener
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event
     */
    public function handle($event): void
    {
        try {
            $user = $event->user ?? null;

            if (!$user || !$user instanceof User) {
                return;
            }

            $eventClass = get_class($event);

            // إشعارات الشارات
            if ($this->isBadgeEvent($eventClass) && isset($event->badge)) {
                $this->notificationService->notifyBadgeEarned($user, $event->badge);
            }

            // إشعارات الإنجازات
            if ($this->isAchievementEvent($eventClass) && isset($event->achievement)) {
                $this->notificationService->notifyAchievementUnlocked($user, $event->achievement);
            }

            // إشعارات المستوى
            if ($this->isLevelUpEvent($eventClass) && isset($event->newLevel)) {
                $this->notificationService->notifyLevelUp($user, $event->newLevel);
            }

            // إشعارات النقاط الكبيرة
            if ($this->isPointsEvent($eventClass) && isset($event->points) && isset($event->reason)) {
                $this->notificationService->notifyPointsEarned($user, $event->points, $event->reason);
            }

            // إشعارات السلسلة
            if ($this->isStreakEvent($eventClass) && isset($event->streak)) {
                $this->notificationService->notifyStreakMilestone($user, $event->streak);
            }

            // إشعارات التحديات
            if ($this->isChallengeCompletedEvent($eventClass) && isset($event->challenge)) {
                $this->notificationService->notifyChallengeCompleted($user, $event->challenge);
            }

            // إشعارات الصداقة
            if ($this->isFriendRequestEvent($eventClass) && isset($event->sender)) {
                $this->notificationService->notifyFriendRequest($user, $event->sender);
            }

            if ($this->isFriendAcceptedEvent($eventClass) && isset($event->friend)) {
                $this->notificationService->notifyFriendAccepted($user, $event->friend);
            }

            // إشعارات المنافسات
            if ($this->isCompetitionWonEvent($eventClass) && isset($event->competition)) {
                $this->notificationService->notifyCompetitionWon($user, $event->competition);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
            ]);
        }
    }

    /**
     * فحص أنواع الأحداث
     */
    protected function isBadgeEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'BadgeAwarded') ||
               str_contains($eventClass, 'BadgeEarned');
    }

    protected function isAchievementEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'AchievementCompleted') ||
               str_contains($eventClass, 'AchievementUnlocked');
    }

    protected function isLevelUpEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'LevelUp') ||
               str_contains($eventClass, 'UserLeveledUp');
    }

    protected function isPointsEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'PointsAwarded') ||
               str_contains($eventClass, 'PointsEarned');
    }

    protected function isStreakEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'StreakUpdated') ||
               str_contains($eventClass, 'StreakMilestone');
    }

    protected function isChallengeCompletedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'ChallengeCompleted');
    }

    protected function isFriendRequestEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'FriendRequestSent');
    }

    protected function isFriendAcceptedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'FriendRequestAccepted');
    }

    protected function isCompetitionWonEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'CompetitionWon');
    }
}
