<?php

namespace App\Services\Gamification;

use App\Models\AssignmentSubmission;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\ModuleCompletion;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class PointsBackfillService
{
    public function __construct(
        protected GamificationService $gamificationService,
        protected PointsService $pointsService
    ) {}

    public function backfillStudents(Collection $students, string $reason, ?User $admin = null): array
    {
        $totals = [
            'total_students' => $students->count(),
            'students_processed' => 0,
            'students_with_awards' => 0,
            'activities_awarded' => 0,
            'activities_skipped' => 0,
            'points_awarded' => 0,
        ];

        foreach ($students as $student) {
            $result = $this->backfillStudent($student);
            $totals['students_processed']++;

            if ($result['activities_awarded'] > 0) {
                $totals['students_with_awards']++;
            }

            $totals['activities_awarded'] += $result['activities_awarded'];
            $totals['activities_skipped'] += $result['activities_skipped'];
            $totals['points_awarded'] += $result['points_awarded'];
        }

        return $totals;
    }

    public function backfillStudent(User $student): array
    {
        $stats = [
            'activities_awarded' => 0,
            'activities_skipped' => 0,
            'points_awarded' => 0,
        ];

        $beforePoints = $this->pointsService->getAvailablePoints($student);

        $this->backfillLessons($student, $stats);
        $this->backfillVideos($student, $stats);
        $this->backfillQuizzes($student, $stats);
        $this->backfillAssignments($student, $stats);
        $this->backfillCourses($student, $stats);

        $afterPoints = $this->pointsService->getAvailablePoints($student);
        $stats['points_awarded'] = max(0, $afterPoints - $beforePoints);

        return $stats;
    }

    protected function backfillLessons(User $student, array &$stats): void
    {
        ModuleCompletion::query()
            ->where('student_id', $student->id)
            ->where('completion_status', 'completed')
            ->with('module')
            ->chunkById(100, function ($completions) use ($student, &$stats) {
                foreach ($completions as $completion) {
                    $module = $completion->module;

                    if (! $module || $module->module_type !== 'lesson') {
                        continue;
                    }

                    $lessonId = ($module->modulable_type === Lesson::class && $module->modulable_id)
                        ? (int) $module->modulable_id
                        : (int) $module->id;

                    $result = $this->gamificationService->handleLessonCompletion(
                        $student,
                        $lessonId,
                        ['backfill' => true, 'module_id' => $module->id]
                    );

                    $this->tallyResult($result, $stats);
                }
            });
    }

    protected function backfillVideos(User $student, array &$stats): void
    {
        ModuleCompletion::query()
            ->where('student_id', $student->id)
            ->with('module')
            ->chunkById(100, function ($completions) use ($student, &$stats) {
                foreach ($completions as $completion) {
                    $module = $completion->module;

                    if (! $module || $module->module_type !== 'video') {
                        continue;
                    }

                    $percentage = $this->resolveWatchPercentage($completion);

                    if ($percentage < (int) config('gamification.points.video_watch.min_watch_percentage', 80)) {
                        continue;
                    }

                    $result = $this->gamificationService->handleVideoWatch(
                        $student,
                        $module->id,
                        (int) round($percentage),
                        ['backfill' => true]
                    );

                    $this->tallyResult($result, $stats);
                }
            });
    }

    protected function backfillQuizzes(User $student, array &$stats): void
    {
        QuizAttempt::query()
            ->where('student_id', $student->id)
            ->where(function ($q) {
                $q->where('is_completed', true)
                    ->orWhereNotNull('submitted_at')
                    ->orWhere('status', 'completed');
            })
            ->orderByDesc('submitted_at')
            ->chunkById(100, function ($attempts) use ($student, &$stats) {
                $processedQuizzes = [];

                foreach ($attempts as $attempt) {
                    if (in_array($attempt->quiz_id, $processedQuizzes, true)) {
                        continue;
                    }

                    $processedQuizzes[] = $attempt->quiz_id;

                    $score = (int) round($attempt->total_score ?? 0);
                    $maxScore = (int) round($attempt->max_score ?? 0);
                    $totalQuestions = $maxScore > 0 ? $maxScore : max(1, $score);

                    $result = $this->gamificationService->handleQuizCompletion(
                        $student,
                        (int) $attempt->quiz_id,
                        $score,
                        $totalQuestions,
                        ['backfill' => true, 'attempt_id' => $attempt->id]
                    );

                    $this->tallyResult($result, $stats);
                }
            });
    }

    protected function backfillAssignments(User $student, array &$stats): void
    {
        AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->chunkById(100, function ($submissions) use ($student, &$stats) {
                foreach ($submissions as $submission) {
                    $result = $this->gamificationService->handleAssignmentSubmission(
                        $student,
                        (int) $submission->assignment_id,
                        ['backfill' => true, 'submission_id' => $submission->id]
                    );

                    $this->tallyResult($result, $stats);
                }
            });
    }

    protected function backfillCourses(User $student, array &$stats): void
    {
        CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->where('completion_percentage', '>=', 100)
            ->chunkById(100, function ($enrollments) use ($student, &$stats) {
                foreach ($enrollments as $enrollment) {
                    $result = $this->gamificationService->handleCourseCompletion(
                        $student,
                        (int) $enrollment->course_id,
                        ['backfill' => true]
                    );

                    $this->tallyResult($result, $stats);
                }
            });
    }

    protected function resolveWatchPercentage(ModuleCompletion $completion): float
    {
        if ($completion->completion_status === 'completed') {
            return 100.0;
        }

        $progress = $completion->progress ?? null;

        if (is_array($progress) && isset($progress['percentage'])) {
            return (float) $progress['percentage'];
        }

        if ($completion->completion_percentage !== null) {
            return (float) $completion->completion_percentage;
        }

        return 0.0;
    }

    protected function tallyResult(array $result, array &$stats): void
    {
        if ($result['success'] ?? false) {
            $stats['activities_awarded']++;
        } else {
            $stats['activities_skipped']++;
        }
    }
}
