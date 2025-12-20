<?php

namespace App\Listeners\Gamification;

use App\Models\User;
use App\Models\Competition;
use App\Services\Gamification\CompetitionService;
use Illuminate\Support\Facades\Log;

class UpdateCompetitionListener
{
    protected CompetitionService $competitionService;

    public function __construct(CompetitionService $competitionService)
    {
        $this->competitionService = $competitionService;
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

            // الحصول على المنافسات النشطة للمستخدم
            $activeCompetitions = $this->competitionService->getUserActiveCompetitions($user);

            if ($activeCompetitions->isEmpty()) {
                return;
            }

            $eventClass = get_class($event);

            foreach ($activeCompetitions as $competition) {
                $this->updateCompetitionProgress($user, $competition, $event, $eventClass);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update competition progress', [
                'error' => $e->getMessage(),
                'event' => get_class($event),
            ]);
        }
    }

    /**
     * تحديث تقدم المنافسة بناءً على الحدث
     */
    protected function updateCompetitionProgress(User $user, Competition $competition, $event, string $eventClass): void
    {
        $stats = $user->stats;

        switch ($competition->type) {
            case 'points':
                // تحديث النقاط الحالية
                $this->competitionService->updateParticipantProgress(
                    $user,
                    $competition,
                    $stats->total_points
                );
                break;

            case 'xp':
                // تحديث XP الحالي
                $this->competitionService->updateParticipantProgress(
                    $user,
                    $competition,
                    $stats->total_xp
                );
                break;

            case 'lessons':
                // تحديث عدد الدروس
                if ($this->isLessonEvent($eventClass)) {
                    $this->competitionService->updateParticipantProgress(
                        $user,
                        $competition,
                        $stats->lessons_completed
                    );
                }
                break;

            case 'quizzes':
                // تحديث عدد الاختبارات
                if ($this->isQuizEvent($eventClass)) {
                    $this->competitionService->updateParticipantProgress(
                        $user,
                        $competition,
                        $stats->quizzes_completed
                    );
                }
                break;

            case 'streak':
                // تحديث السلسلة
                if ($this->isStreakEvent($eventClass)) {
                    $this->competitionService->updateParticipantProgress(
                        $user,
                        $competition,
                        $stats->current_streak
                    );
                }
                break;
        }
    }

    /**
     * فحص نوع الحدث
     */
    protected function isLessonEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'LessonCompleted') ||
               str_contains($eventClass, 'LessonViewed');
    }

    protected function isQuizEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'QuizCompleted') ||
               str_contains($eventClass, 'QuizPassed');
    }

    protected function isStreakEvent(string $eventClass): bool
    {
        return str_contains($eventClass, 'StreakUpdated') ||
               str_contains($eventClass, 'DailyLogin');
    }
}
