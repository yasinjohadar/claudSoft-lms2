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

            $attempt->update(['status' => 'abandoned']);
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

            $attempt->update(['status' => 'abandoned']);
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
            + $this->reconcileExpiredInProgressAttempts($quiz->id, $studentId);
    }

    /**
     * Reconcile all stale in-progress attempts platform-wide.
     */
    public function reconcileAll(): int
    {
        return $this->reconcileEmptyInProgressAttempts()
            + $this->reconcileExpiredInProgressAttempts();
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
            $attempt->update(['status' => 'abandoned']);

            return 'abandoned';
        }

        return 'auto_submit';
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
}
