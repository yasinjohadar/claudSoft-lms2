<?php

namespace App\Services\Gamification;

use App\Models\AssignmentSubmission;
use App\Models\CourseEnrollment;
use App\Models\DailyStreak;
use App\Models\ModuleCompletion;
use App\Models\QuizAttempt;
use App\Models\User;
use Carbon\Carbon;

class UserGamificationStatSyncService
{
    /**
     * مزامنة إحصائيات gamification للمستخدم من مصادر البيانات الفعلية.
     *
     * @return array{updated: bool, changes: array<string, int>}
     */
    public function syncUserStats(User $user): array
    {
        $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

        $lessonsCount = ModuleCompletion::query()
            ->where('student_id', $user->id)
            ->where('completion_status', 'completed')
            ->whereHas('module')
            ->count();

        $coursesCount = CourseEnrollment::query()
            ->where('student_id', $user->id)
            ->where('completion_percentage', '>=', 100)
            ->count();

        $quizzesCount = QuizAttempt::query()
            ->where('student_id', $user->id)
            ->where('is_completed', true)
            ->count();

        $perfectScoresCount = QuizAttempt::query()
            ->where('student_id', $user->id)
            ->where('is_completed', true)
            ->where('percentage_score', '>=', 100)
            ->count();

        $assignmentsCount = AssignmentSubmission::query()
            ->where('student_id', $user->id)
            ->whereIn('status', ['submitted', 'graded', 'returned'])
            ->count();

        $streakCounts = $this->recalculateStreakCounts($user->id);

        $changes = [];
        $this->queueStatUpdate($changes, 'lessons_completed', (int) ($stats->lessons_completed ?? 0), $lessonsCount);
        $this->queueStatUpdate($changes, 'courses_completed', (int) ($stats->courses_completed ?? 0), $coursesCount);
        $this->queueStatUpdate($changes, 'quizzes_completed', (int) ($stats->quizzes_completed ?? 0), $quizzesCount);
        $this->queueStatUpdate($changes, 'perfect_scores', (int) ($stats->perfect_scores ?? 0), $perfectScoresCount);
        $this->queueStatUpdate($changes, 'assignments_submitted', (int) ($stats->assignments_submitted ?? 0), $assignmentsCount);
        $this->queueStatUpdate($changes, 'current_streak', (int) ($stats->current_streak ?? 0), $streakCounts['current_streak']);
        $this->queueStatUpdate($changes, 'longest_streak', (int) ($stats->longest_streak ?? 0), $streakCounts['longest_streak']);

        if ($changes !== []) {
            $stats->update($changes);
            $user->unsetRelation('stats');
        }

        return [
            'updated' => $changes !== [],
            'changes' => $changes,
        ];
    }

    protected function queueStatUpdate(array &$updates, string $field, int $current, int $calculated): void
    {
        if ($current !== $calculated) {
            $updates[$field] = $calculated;
        }
    }

    /**
     * @return array{current_streak: int, longest_streak: int}
     */
    protected function recalculateStreakCounts(int $userId): array
    {
        $dates = DailyStreak::query()
            ->where('user_id', $userId)
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->startOfDay())
            ->values();

        if ($dates->isEmpty()) {
            return ['current_streak' => 0, 'longest_streak' => 0];
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $currentStreak = 0;
        $firstDate = $dates->first();

        if ($firstDate->equalTo($today) || $firstDate->equalTo($yesterday)) {
            $expected = $firstDate->copy();
            foreach ($dates as $date) {
                if (!$date->equalTo($expected)) {
                    break;
                }
                $currentStreak++;
                $expected->subDay();
            }
        }

        $longestStreak = 0;
        $run = 0;
        $previous = null;

        foreach ($dates->sort()->values() as $date) {
            if ($previous && $date->equalTo($previous->copy()->addDay())) {
                $run++;
            } else {
                $run = 1;
            }

            $longestStreak = max($longestStreak, $run);
            $previous = $date;
        }

        return [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
        ];
    }
}
