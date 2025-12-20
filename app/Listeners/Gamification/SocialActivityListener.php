<?php

namespace App\Listeners\Gamification;

use App\Models\User;
use App\Services\Gamification\SocialActivityService;
use Illuminate\Support\Facades\Log;

class SocialActivityListener
{
    protected SocialActivityService $socialActivityService;

    public function __construct(SocialActivityService $socialActivityService)
    {
        $this->socialActivityService = $socialActivityService;
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

            // نشر الإنجازات تلقائياً
            if ($this->isAchievementEvent($eventClass) && isset($event->achievement)) {
                $this->socialActivityService->shareAchievement($user, $event->achievement->id);
            }

            // نشر الشارات المهمة تلقائياً
            if ($this->isBadgeEvent($eventClass) && isset($event->badge)) {
                $badge = $event->badge;
                // نشر الشارات النادرة فقط تلقائياً
                if (in_array($badge->rarity, ['epic', 'legendary', 'mythic'])) {
                    $this->socialActivityService->shareBadge($user, $badge->id);
                }
            }

            // نشر المستويات الجديدة
            if ($this->isLevelUpEvent($eventClass) && isset($event->newLevel)) {
                // نشر كل 5 مستويات
                if ($event->newLevel % 5 === 0) {
                    $this->socialActivityService->shareLevelUp($user, $event->newLevel);
                }
            }

            // نشر إكمال الكورسات
            if ($this->isCourseCompletedEvent($eventClass) && isset($event->course)) {
                $this->socialActivityService->shareCourseCompletion($user, $event->course->id);
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle social activity event', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
            ]);
        }
    }

    /**
     * فحص نوع الحدث
     */
    protected function isAchievementEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'AchievementCompleted') ||
               str_contains($eventClass, 'AchievementUnlocked');
    }

    protected function isBadgeEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'BadgeAwarded') ||
               str_contains($eventClass, 'BadgeEarned');
    }

    protected function isLevelUpEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'LevelUp') ||
               str_contains($eventClass, 'UserLeveledUp');
    }

    protected function isCourseCompletedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'CourseCompleted') ||
               str_contains($eventClass, 'CourseFinished');
    }
}
