<?php

namespace App\Console\Commands;

use App\Services\QuestionModule\QuestionModuleAttemptLifecycleService;
use App\Services\Quiz\QuizAttemptLifecycleService;
use Illuminate\Console\Command;

class ReconcileStaleQuizAttempts extends Command
{
    protected $signature = 'quiz:reconcile-stale-attempts
                            {--quiz-id= : Limit to a specific quiz ID}
                            {--student-id= : Limit to a specific student ID}
                            {--stale-hours=24 : Hours before an in-progress attempt is considered stale}';

    protected $description = 'إلغاء محاولات الاختبار العالقة (بدون أسئلة أو منتهية الوقت دون إجابات)';

    public function handle(
        QuizAttemptLifecycleService $quizLifecycle,
        QuestionModuleAttemptLifecycleService $qmLifecycle
    ): int {
        $quizId = $this->option('quiz-id') ? (int) $this->option('quiz-id') : null;
        $studentId = $this->option('student-id') ? (int) $this->option('student-id') : null;
        $staleHours = max(1, (int) $this->option('stale-hours'));

        $quizAbandoned = $quizLifecycle->reconcileEmptyInProgressAttempts($quizId, $studentId)
            + $quizLifecycle->reconcileExpiredInProgressAttempts($quizId, $studentId)
            + $quizLifecycle->reconcileStaleInProgressAttempts($staleHours, $quizId, $studentId);

        $qmAbandoned = $qmLifecycle->reconcileEmptyInProgressAttempts(null, $studentId)
            + $qmLifecycle->reconcileExpiredInProgressAttempts(null, $studentId)
            + $qmLifecycle->reconcileStaleInProgressAttempts($staleHours, null, $studentId);

        $total = $quizAbandoned + $qmAbandoned;

        $this->info("تم إلغاء {$total} محاولة عالقة (اختبارات: {$quizAbandoned}، وحدات أسئلة: {$qmAbandoned}).");

        return self::SUCCESS;
    }
}
