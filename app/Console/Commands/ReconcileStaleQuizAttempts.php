<?php

namespace App\Console\Commands;

use App\Services\Quiz\QuizAttemptLifecycleService;
use Illuminate\Console\Command;

class ReconcileStaleQuizAttempts extends Command
{
    protected $signature = 'quiz:reconcile-stale-attempts
                            {--quiz-id= : Limit to a specific quiz ID}
                            {--student-id= : Limit to a specific student ID}';

    protected $description = 'إلغاء محاولات الاختبار العالقة (بدون أسئلة أو منتهية الوقت دون إجابات)';

    public function handle(QuizAttemptLifecycleService $lifecycle): int
    {
        $quizId = $this->option('quiz-id') ? (int) $this->option('quiz-id') : null;
        $studentId = $this->option('student-id') ? (int) $this->option('student-id') : null;

        $abandoned = $lifecycle->reconcileEmptyInProgressAttempts($quizId, $studentId)
            + $lifecycle->reconcileExpiredInProgressAttempts($quizId, $studentId);

        $this->info("تم إلغاء {$abandoned} محاولة عالقة (فارغة أو منتهية الوقت دون إجابات).");

        return self::SUCCESS;
    }
}
