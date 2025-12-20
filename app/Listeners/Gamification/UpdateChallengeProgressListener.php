<?php

namespace App\Listeners\Gamification;

use App\Models\User;
use App\Models\Challenge;
use App\Services\Gamification\ChallengeService;
use Illuminate\Support\Facades\Log;

class UpdateChallengeProgressListener
{
    protected ChallengeService $challengeService;

    public function __construct(ChallengeService $challengeService)
    {
        $this->challengeService = $challengeService;
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

            // تعيين التحديات اليومية تلقائياً إذا لم تكن موجودة
            $this->challengeService->assignDailyChallenges($user);

            // الحصول على التحديات النشطة للمستخدم
            $activeChallenges = $this->challengeService->getActiveChallenges($user);

            foreach ($activeChallenges as $userChallenge) {
                $challenge = $userChallenge->challenge;

                // تحديث التقدم بناءً على نوع التحدي
                $this->updateChallengeBasedOnEvent($user, $challenge, $event);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update challenge progress', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
            ]);
        }
    }

    /**
     * تحديث التحدي بناءً على نوع الحدث
     */
    protected function updateChallengeBasedOnEvent(User $user, Challenge $challenge, $event): void
    {
        $eventClass = get_class($event);

        // تحديات إكمال الدروس
        if ($this->isLessonCompletedEvent($eventClass) && $challenge->target_type === 'lessons_completed') {
            $this->challengeService->updateProgress($user, $challenge, 1);
        }

        // تحديات مشاهدة الفيديوهات
        if ($this->isVideoWatchedEvent($eventClass) && $challenge->target_type === 'videos_watched') {
            $this->challengeService->updateProgress($user, $challenge, 1);
        }

        // تحديات اجتياز الاختبارات
        if ($this->isQuizPassedEvent($eventClass) && $challenge->target_type === 'quizzes_passed') {
            $this->challengeService->updateProgress($user, $challenge, 1);
        }

        // تحديات الدرجات الكاملة
        if ($this->isPerfectScoreEvent($eventClass) && $challenge->target_type === 'perfect_scores') {
            $this->challengeService->updateProgress($user, $challenge, 1);
        }

        // تحديات كسب النقاط
        if ($this->isPointsEarnedEvent($eventClass) && $challenge->target_type === 'points_earned') {
            $points = $event->points ?? 0;
            $this->challengeService->updateProgress($user, $challenge, $points);
        }

        // تحديات السلاسل
        if ($this->isStreakEvent($eventClass) && $challenge->target_type === 'login_streak') {
            $streak = $user->stats->current_streak ?? 0;
            // نحدث القيمة الحالية مباشرة
            $userChallenge = $user->challenges()
                ->where('challenge_id', $challenge->id)
                ->where('status', 'active')
                ->first();

            if ($userChallenge && $streak > $userChallenge->current_progress) {
                $diff = $streak - $userChallenge->current_progress;
                $this->challengeService->updateProgress($user, $challenge, $diff);
            }
        }

        // تحديات إنهاء الكورسات
        if ($this->isCourseCompletedEvent($eventClass) && $challenge->target_type === 'courses_completed') {
            $this->challengeService->updateProgress($user, $challenge, 1);
        }

        // تحديات كسب الشارات
        if ($this->isBadgeEarnedEvent($eventClass) && $challenge->target_type === 'badges_earned') {
            $this->challengeService->updateProgress($user, $challenge, 1);
        }

        // تحديات إكمال الإنجازات
        if ($this->isAchievementCompletedEvent($eventClass) && $challenge->target_type === 'achievements_completed') {
            $this->challengeService->updateProgress($user, $challenge, 1);
        }
    }

    /**
     * فحص نوع الحدث
     */
    protected function isLessonCompletedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'LessonCompleted') ||
               str_contains($eventClass, 'LessonViewed');
    }

    protected function isVideoWatchedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'VideoWatched') ||
               str_contains($eventClass, 'VideoCompleted');
    }

    protected function isQuizPassedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'QuizPassed') ||
               str_contains($eventClass, 'QuizCompleted');
    }

    protected function isPerfectScoreEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'PerfectScore') ||
               str_contains($eventClass, 'QuizPerfectScore');
    }

    protected function isPointsEarnedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'PointsAwarded') ||
               str_contains($eventClass, 'PointsEarned');
    }

    protected function isStreakEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'StreakUpdated') ||
               str_contains($eventClass, 'DailyLogin');
    }

    protected function isCourseCompletedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'CourseCompleted') ||
               str_contains($eventClass, 'CourseFinished');
    }

    protected function isBadgeEarnedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'BadgeAwarded') ||
               str_contains($eventClass, 'BadgeEarned');
    }

    protected function isAchievementCompletedEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'AchievementCompleted') ||
               str_contains($eventClass, 'AchievementUnlocked');
    }
}
