<?php

namespace App\Console\Commands;

use App\Services\QuestionModule\QuestionModuleAttemptLifecycleService;
use App\Services\Quiz\QuizAttemptLifecycleService;
use Illuminate\Console\Command;

class ReconcileAffectedAssessmentAttempts extends Command
{
    protected $signature = 'assessment:reconcile-affected-attempts
                            {--dry-run : Report reclassification candidates without writing}
                            {--stale-hours=24 : Hours before an in-progress attempt is considered stale}
                            {--quiz-id= : Limit quiz reconciliation to a specific quiz}
                            {--question-module-id= : Limit question-module reconciliation to a specific module}
                            {--student-id= : Limit reconciliation to a specific student}';

    protected $description = 'تنظيف المحاولات العالقة والفارغة في الاختبارات ووحدات الأسئلة';

    public function handle(
        QuizAttemptLifecycleService $quizLifecycle,
        QuestionModuleAttemptLifecycleService $qmLifecycle
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $staleHours = max(1, (int) $this->option('stale-hours'));
        $quizId = $this->option('quiz-id') ? (int) $this->option('quiz-id') : null;
        $questionModuleId = $this->option('question-module-id') ? (int) $this->option('question-module-id') : null;
        $studentId = $this->option('student-id') ? (int) $this->option('student-id') : null;

        if ($dryRun) {
            $this->warn('وضع المعاينة — سيتم عرض محاولات الإنهاء الفارغة فقط دون تعديل قاعدة البيانات.');

            $quizReclassified = $quizLifecycle->reclassifyEmptyFinishedAttempts($quizId, $studentId, true);
            $qmReclassified = $qmLifecycle->reclassifyEmptyCompletedAttempts($questionModuleId, $studentId, true);

            $this->info("Quiz: {$quizReclassified} محاولة منتهية فارغة مرشّحة لإعادة التصنيف.");
            $this->info("Question modules: {$qmReclassified} محاولة منتهية فارغة مرشّحة لإعادة التصنيف.");
            $this->line('لتنظيف المحاولات العالقة (in_progress) شغّل الأمر بدون --dry-run.');

            return self::SUCCESS;
        }

        $quizAbandoned = $quizLifecycle->reconcileEmptyInProgressAttempts($quizId, $studentId)
            + $quizLifecycle->reconcileExpiredInProgressAttempts($quizId, $studentId)
            + $quizLifecycle->reconcileStaleInProgressAttempts($staleHours, $quizId, $studentId);

        $quizReclassified = $quizLifecycle->reclassifyEmptyFinishedAttempts($quizId, $studentId, false);

        $qmAbandoned = $qmLifecycle->reconcileEmptyInProgressAttempts($questionModuleId, $studentId)
            + $qmLifecycle->reconcileExpiredInProgressAttempts($questionModuleId, $studentId)
            + $qmLifecycle->reconcileStaleInProgressAttempts($staleHours, $questionModuleId, $studentId);

        $qmReclassified = $qmLifecycle->reclassifyEmptyCompletedAttempts($questionModuleId, $studentId, false);

        $this->info("Quiz: {$quizAbandoned} محاولة عالقة أُلغيت، {$quizReclassified} محاولة منتهية فارغة أُعيد تصنيفها.");
        $this->info("Question modules: {$qmAbandoned} محاولة عالقة أُلغيت، {$qmReclassified} محاولة منتهية فارغة أُعيد تصنيفها.");
        $this->info('اكتمل التنظيف.');

        return self::SUCCESS;
    }
}
