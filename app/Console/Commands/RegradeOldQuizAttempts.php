<?php

namespace App\Console\Commands;

use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegradeOldQuizAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quiz:regrade-old-attempts 
                            {--attempt-id= : Regrade specific attempt by ID}
                            {--quiz-id= : Regrade all attempts for a specific quiz}
                            {--force : Force regrade even if already auto-graded}
                            {--limit=100 : Maximum number of attempts to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إعادة تصحيح المحاولات القديمة للاختبارات باستخدام منطق التصحيح التلقائي الجديد';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء إعادة تصحيح المحاولات القديمة...');

        $attemptId = $this->option('attempt-id');
        $quizId = $this->option('quiz-id');
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        // Build query - include all completed attempts (not in_progress)
        $query = QuizAttempt::with([
            'responses.question.questionType',
            'responses.question.options'
        ])->where('status', '!=', 'in_progress');

        if ($attemptId) {
            $query->where('id', $attemptId);
        } elseif ($quizId) {
            $query->where('quiz_id', $quizId);
        }

        $attempts = $query->limit($limit)->get();

        if ($attempts->isEmpty()) {
            $this->warn('لم يتم العثور على محاولات لإعادة التصحيح.');
            return 0;
        }

        $this->info("تم العثور على {$attempts->count()} محاولة لإعادة التصحيح.");

        $bar = $this->output->createProgressBar($attempts->count());
        $bar->start();

        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($attempts as $attempt) {
            try {
                DB::beginTransaction();

                $regradedCount = 0;
                $skippedResponseCount = 0;

                foreach ($attempt->responses as $response) {
                    if ($response->question === null) {
                        $skippedResponseCount++;

                        continue;
                    }

                    $questionType = $response->question->questionType->name ?? '';

                    // Skip essay and short_answer (require manual grading)
                    if (in_array($questionType, ['essay', 'short_answer'])) {
                        $skippedResponseCount++;
                        continue;
                    }

                    // Skip if already auto-graded and force is not set
                    if (!$force && $response->auto_graded) {
                        $skippedResponseCount++;
                        continue;
                    }

                    // Check if response has an answer
                    $hasAnswer = false;

                    // Check response_data
                    if (!empty($response->response_data)) {
                        if (is_array($response->response_data)) {
                            foreach ($response->response_data as $key => $value) {
                                if ($value !== null && $value !== '' && $value !== []) {
                                    $hasAnswer = true;
                                    break;
                                }
                            }
                        } else {
                            $hasAnswer = true;
                        }
                    }

                    // Check selected_option_ids
                    if (!$hasAnswer && !empty($response->selected_option_ids)) {
                        if (is_array($response->selected_option_ids)) {
                            $hasAnswer = !empty(array_filter($response->selected_option_ids));
                        } else {
                            $hasAnswer = true;
                        }
                    }

                    // Check response_text
                    if (!$hasAnswer && !empty($response->response_text)) {
                        $text = trim($response->response_text);
                        if ($text !== '' && $text !== 'null' && $text !== '[]') {
                            $hasAnswer = true;
                        }
                    }

                    if ($hasAnswer) {
                        try {
                            $response->autoGrade();
                            $response->refresh();
                            $regradedCount++;
                        } catch (\Exception $e) {
                            Log::error('Error regrading response', [
                                'response_id' => $response->id,
                                'question_id' => $response->question_id,
                                'question_type' => $questionType,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        $skippedResponseCount++;
                    }
                }

                // Recalculate attempt scores
                $attempt->grade();
                $attempt->refresh();

                DB::commit();

                if ($regradedCount > 0) {
                    $successCount++;
                    Log::info('Attempt regraded successfully', [
                        'attempt_id' => $attempt->id,
                        'quiz_id' => $attempt->quiz_id,
                        'regraded_responses' => $regradedCount,
                        'skipped_responses' => $skippedResponseCount,
                        'final_score' => $attempt->score_obtained,
                        'final_percentage' => $attempt->percentage_score,
                    ]);
                } else {
                    $skippedCount++;
                }

                $bar->advance();
            } catch (\Exception $e) {
                DB::rollBack();
                $errorCount++;
                Log::error('Error regrading attempt', [
                    'attempt_id' => $attempt->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("تم إعادة تصحيح {$successCount} محاولة بنجاح.");
        if ($skippedCount > 0) {
            $this->warn("تم تخطي {$skippedCount} محاولة (لم تحتاج إعادة تصحيح).");
        }
        if ($errorCount > 0) {
            $this->error("حدث خطأ في {$errorCount} محاولة.");
        }

        return 0;
    }
}

