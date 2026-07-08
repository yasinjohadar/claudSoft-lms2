<?php

namespace App\Services\QuestionModule;

use App\Models\QuestionModule;
use App\Models\QuestionModuleAttempt;
use Illuminate\Support\Collection;

class QuestionModuleAttemptLifecycleService
{
    /**
     * Abandon in-progress attempts that have no questions (invalid / stale).
     *
     * @return int Number of attempts abandoned
     */
    public function reconcileEmptyInProgressAttempts(?int $questionModuleId = null, ?int $studentId = null): int
    {
        $query = QuestionModuleAttempt::query()
            ->where('status', 'in_progress');

        if ($questionModuleId !== null) {
            $query->where('question_module_id', $questionModuleId);
        }

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        $abandoned = 0;

        $query->get()->each(function (QuestionModuleAttempt $attempt) use (&$abandoned) {
            $order = $attempt->question_order;
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
    public function reconcileExpiredInProgressAttempts(?int $questionModuleId = null, ?int $studentId = null): int
    {
        $query = QuestionModuleAttempt::query()
            ->with(['questionModule', 'responses'])
            ->where('status', 'in_progress');

        if ($questionModuleId !== null) {
            $query->where('question_module_id', $questionModuleId);
        }

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        $abandoned = 0;

        $query->get()->each(function (QuestionModuleAttempt $attempt) use (&$abandoned) {
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
        ?int $questionModuleId = null,
        ?int $studentId = null
    ): int {
        $query = QuestionModuleAttempt::query()
            ->with('responses')
            ->where('status', 'in_progress')
            ->where('started_at', '<=', now()->subHours($staleHours));

        if ($questionModuleId !== null) {
            $query->where('question_module_id', $questionModuleId);
        }

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        $abandoned = 0;

        $query->get()->each(function (QuestionModuleAttempt $attempt) use (&$abandoned) {
            if ($attempt->hasAnsweredResponses()) {
                return;
            }

            $attempt->abandon();
            $abandoned++;
        });

        return $abandoned;
    }

    /**
     * Reconcile stale attempts for a question module/student before showing intro or starting.
     */
    public function reconcileForStudent(QuestionModule $questionModule, int $studentId): int
    {
        return $this->reconcileEmptyInProgressAttempts($questionModule->id, $studentId)
            + $this->reconcileExpiredInProgressAttempts($questionModule->id, $studentId)
            + $this->reconcileStaleInProgressAttempts(24, $questionModule->id, $studentId);
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
    public function resolveExpiredAttempt(QuestionModuleAttempt $attempt): ?string
    {
        if ($attempt->status !== 'in_progress' || ! $attempt->isTimeExpired()) {
            return null;
        }

        $attempt->loadMissing(['responses', 'questionModule']);

        if (! $attempt->hasAnsweredResponses()) {
            $attempt->abandon();

            return 'abandoned';
        }

        return 'auto_submit';
    }

    /**
     * @return Collection<int, QuestionModuleAttempt>
     */
    public function getInProgressAttempts(QuestionModule $questionModule, int $studentId): Collection
    {
        return $questionModule->attempts()
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->get();
    }

    /**
     * Reclassify completed attempts that have zero answers as abandoned (data repair).
     *
     * @return int Number of attempts reclassified
     */
    public function reclassifyEmptyCompletedAttempts(
        ?int $questionModuleId = null,
        ?int $studentId = null,
        bool $dryRun = false
    ): int {
        $query = QuestionModuleAttempt::query()
            ->with('responses')
            ->where('status', 'completed');

        if ($questionModuleId !== null) {
            $query->where('question_module_id', $questionModuleId);
        }

        if ($studentId !== null) {
            $query->where('student_id', $studentId);
        }

        $reclassified = 0;

        $query->get()->each(function (QuestionModuleAttempt $attempt) use (&$reclassified, $dryRun) {
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
}
