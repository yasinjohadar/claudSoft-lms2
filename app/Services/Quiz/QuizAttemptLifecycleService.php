<?php

namespace App\Services\Quiz;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Collection;

class QuizAttemptLifecycleService
{
    /**
     * Abandon in-progress attempts that have no questions (invalid / stale).
     *
     * @return int Number of attempts abandoned
     */
    public function reconcileEmptyInProgressAttempts(?int $quizId = null, ?int $studentId = null): int
    {
        $query = QuizAttempt::query()
            ->realAttempts()
            ->where('status', 'in_progress');

        if ($quizId !== null) {
            $query->where('quiz_id', $quizId);
        }

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        $abandoned = 0;

        $query->get()->each(function (QuizAttempt $attempt) use (&$abandoned) {
            $order = $attempt->questions_order;
            if ($order !== null && $order !== [] && count((array) $order) > 0) {
                return;
            }

            $attempt->abandon();
            $abandoned++;
        });

        return $abandoned;
    }

    /**
     * Abandon expired in-progress attempts that have no saved answers.
     *
     * @return int Number of attempts abandoned
     */
    public function reconcileExpiredInProgressAttempts(?int $quizId = null, ?int $studentId = null): int
    {
        $query = QuizAttempt::query()
            ->realAttempts()
            ->with(['quiz', 'responses'])
            ->where('status', 'in_progress');

        if ($quizId !== null) {
            $query->where('quiz_id', $quizId);
        }

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        $abandoned = 0;

        $query->get()->each(function (QuizAttempt $attempt) use (&$abandoned) {
            if (! $attempt->isTimeExpired()) {
                return;
            }

            if ($attempt->hasAnsweredResponses()) {
                return;
            }

            $attempt->abandon();
            $abandoned++;
        });

        return $abandoned;
    }

    /**
     * Abandon stale in-progress attempts older than the given hours with no answers.
     *
     * @return int Number of attempts abandoned
     */
    public function reconcileStaleInProgressAttempts(
        int $staleHours = 24,
        ?int $quizId = null,
        ?int $studentId = null
    ): int {
        $query = QuizAttempt::query()
            ->realAttempts()
            ->with('responses')
            ->where('status', 'in_progress')
            ->where('started_at', '<=', now()->subHours($staleHours));

        if ($quizId !== null) {
            $query->where('quiz_id', $quizId);
        }

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        $abandoned = 0;

        $query->get()->each(function (QuizAttempt $attempt) use (&$abandoned) {
            if ($attempt->hasAnsweredResponses()) {
                return;
            }

            $attempt->abandon();
            $abandoned++;
        });

        return $abandoned;
    }

    /**
     * Reconcile stale attempts for a quiz/student before showing intro or starting.
     */
    public function reconcileForStudent(Quiz $quiz, int $studentId): int
    {
        return $this->reconcileEmptyInProgressAttempts($quiz->id, $studentId)
            + $this->reconcileExpiredInProgressAttempts($quiz->id, $studentId)
            + $this->reconcileStaleInProgressAttempts(24, $quiz->id, $studentId);
    }

    /**
     * Reconcile all stale in-progress attempts platform-wide.
     */
    public function reconcileAll(int $staleHours = 24): int
    {
        return $this->reconcileEmptyInProgressAttempts()
            + $this->reconcileExpiredInProgressAttempts()
            + $this->reconcileStaleInProgressAttempts($staleHours);
    }

    /**
     * Resolve an expired in-progress attempt.
     *
     * @return null|'abandoned'|'auto_submit'
     */
    public function resolveExpiredAttempt(QuizAttempt $attempt): ?string
    {
        if ($attempt->status !== 'in_progress' || ! $attempt->isTimeExpired()) {
            return null;
        }

        $attempt->loadMissing(['responses', 'quiz']);

        if (! $attempt->hasAnsweredResponses()) {
            $attempt->abandon();

            return 'abandoned';
        }

        return 'auto_submit';
    }

    /**
     * Reclassify finished attempts that have zero answers as abandoned (data repair).
     *
     * @return int Number of attempts reclassified
     */
    public function reclassifyEmptyFinishedAttempts(
        ?int $quizId = null,
        ?int $studentId = null,
        bool $dryRun = false
    ): int {
        $query = QuizAttempt::query()
            ->realAttempts()
            ->with('responses')
            ->whereIn('status', Quiz::FINISHED_ATTEMPT_STATUSES);

        if ($quizId !== null) {
            $query->where('quiz_id', $quizId);
        }

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        $reclassified = 0;

        $query->get()->each(function (QuizAttempt $attempt) use (&$reclassified, $dryRun) {
            if ($attempt->hasAnsweredResponses()) {
                return;
            }

            if (! $dryRun) {
                $attempt->abandon();
            }

            $reclassified++;
        });

        return $reclassified;
    }

    /**
     * @return Collection<int, QuizAttempt>
     */
    public function getInProgressAttempt(Quiz $quiz, int $studentId): Collection
    {
        return $quiz->attempts()
            ->realAttempts()
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->get();
    }

    /**
     * Reconcile stale/empty attempts for a quiz (safe cleanup).
     *
     * @return array{empty_in_progress: int, expired_in_progress: int, stale_in_progress: int, empty_finished: int, total: int}
     */
    public function reconcileProblematicAttemptsForQuiz(int $quizId, int $staleHours = 24): array
    {
        $emptyInProgress = $this->reconcileEmptyInProgressAttempts($quizId);
        $expiredInProgress = $this->reconcileExpiredInProgressAttempts($quizId);
        $staleInProgress = $this->reconcileStaleInProgressAttempts($staleHours, $quizId);
        $emptyFinished = $this->reclassifyEmptyFinishedAttempts($quizId);

        return [
            'empty_in_progress' => $emptyInProgress,
            'expired_in_progress' => $expiredInProgress,
            'stale_in_progress' => $staleInProgress,
            'empty_finished' => $emptyFinished,
            'total' => $emptyInProgress + $expiredInProgress + $staleInProgress + $emptyFinished,
        ];
    }

    /**
     * Soft-delete all real student attempts for a quiz so everyone can start fresh.
     *
     * @return int Number of attempts removed
     */
    public function resetAllAttemptsForQuiz(int $quizId): int
    {
        $deleted = 0;

        QuizAttempt::query()
            ->realAttempts()
            ->where('quiz_id', $quizId)
            ->orderBy('id')
            ->chunkById(100, function ($attempts) use (&$deleted) {
                foreach ($attempts as $attempt) {
                    $attempt->delete();
                    $deleted++;
                }
            });

        return $deleted;
    }

    /**
     * Abandon all in-progress attempts for a quiz (keeps completed attempts).
     *
     * @return int Number of attempts abandoned
     */
    public function abandonAllInProgressAttemptsForQuiz(int $quizId): int
    {
        $abandoned = 0;

        QuizAttempt::query()
            ->realAttempts()
            ->where('quiz_id', $quizId)
            ->where('status', 'in_progress')
            ->orderBy('id')
            ->chunkById(100, function ($attempts) use (&$abandoned) {
                foreach ($attempts as $attempt) {
                    $attempt->abandon();
                    $abandoned++;
                }
            });

        return $abandoned;
    }
}
