<?php

namespace App\Services\ProgrammingChallenge;

use App\Models\CourseModule;
use App\Models\ProgrammingChallengeAttempt;
use App\Models\ProgrammingChallengeSubmission;
use App\Services\CodeExecution\CodeExecutionService;

class ChallengeAutoGradingService
{
    public function __construct(
        protected CodeExecutionService $executionService
    ) {}

    /**
     * Auto-grade a submission using test cases (auto / hybrid modes).
     */
    public function gradeSubmission(
        ProgrammingChallengeAttempt $attempt,
        ProgrammingChallengeSubmission $submission,
        array $files
    ): array {
        $challenge = $attempt->challenge;

        if (! $challenge->isCodeRunner()) {
            return [
                'graded' => false,
                'message' => 'التقييم الآلي متاح فقط لتحديات تشغيل الكود',
            ];
        }

        if (! in_array($challenge->grading_mode, ['auto', 'hybrid'], true)) {
            return [
                'graded' => false,
                'message' => 'وضع التقييم لا يدعم التصحيح الآلي',
            ];
        }

        if (! $this->executionService->isAvailable()) {
            return [
                'graded' => false,
                'message' => 'خدمة التقييم الآلي غير متاحة (Piston)',
                'suite' => null,
            ];
        }

        $suite = $this->executionService->runTestSuite($challenge, $files, $attempt, $submission);
        $score = (float) ($suite['score'] ?? 0);
        $maxScore = (float) ($suite['max_score'] ?? $attempt->max_score ?? $challenge->max_score);
        $allPassed = ($suite['passed'] ?? 0) === ($suite['total'] ?? 0) && ($suite['total'] ?? 0) > 0;

        if ($challenge->grading_mode === 'auto') {
            $attempt->update([
                'status' => 'graded',
                'score' => min($score, $maxScore),
                'grade_status' => 'auto_graded',
                'graded_at' => now(),
                'feedback' => $suite['message'],
            ]);

            if ($attempt->course_module_id) {
                $this->markModuleCompleted($attempt);
            }
        } else {
            $attempt->update([
                'status' => 'submitted',
                'score' => min($score, $maxScore),
                'grade_status' => 'auto_graded',
                'feedback' => $suite['message'],
            ]);
        }

        return [
            'graded' => true,
            'message' => $suite['message'],
            'mode' => $challenge->grading_mode,
            'all_passed' => $allPassed,
            'score' => min($score, $maxScore),
            'max_score' => $maxScore,
            'passed' => $suite['passed'],
            'total' => $suite['total'],
            'results' => collect($suite['results'] ?? [])
                ->map(fn ($r) => [
                    'test_case_id' => $r['test_case_id'],
                    'passed' => $r['passed'],
                    'is_hidden' => $r['is_hidden'],
                    'message' => $r['message'],
                    'duration_ms' => $r['duration_ms'] ?? null,
                ])
                ->values()
                ->all(),
            'suite' => $suite,
        ];
    }

    protected function markModuleCompleted(ProgrammingChallengeAttempt $attempt): void
    {
        $module = CourseModule::find($attempt->course_module_id);
        if (! $module) {
            return;
        }

        $module->markAsCompletedBy($attempt->student, (float) $attempt->score);
    }
}
