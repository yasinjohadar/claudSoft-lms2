<?php

namespace App\Services\Reports;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds factual JSON for AI student progress reports scoped to one course.
 * All scores and counts come from the database only.
 */
class StudentCourseReportDataBuilder
{
    public const STRATEGY_BEST = 'best';

    public const STRATEGY_LATEST = 'latest';

    /**
     * @return array<string, mixed>
     */
    public function build(
        User $student,
        Course $course,
        string $attemptStrategy = self::STRATEGY_BEST,
        ?Carbon $since = null,
    ): array {
        $enrollment = $student->courseEnrollments()
            ->where('course_id', $course->id)
            ->first();

        $quizzes = $this->quizzesForCourse($course)->get();
        $quizRows = [];
        $percentagesForAvg = [];
        $lateCount = 0;
        $withAttempt = 0;

        foreach ($quizzes as $quiz) {
            $attemptsQuery = QuizAttempt::query()
                ->where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->where(function ($q) {
                    $q->where('is_completed', true)
                        ->orWhereIn('status', ['graded', 'submitted']);
                })
                ->with('quiz');

            if ($since) {
                $attemptsQuery->where(function ($q) use ($since) {
                    $q->where('submitted_at', '>=', $since)
                        ->orWhere('updated_at', '>=', $since);
                });
            }

            /** @var Collection<int, QuizAttempt> $attempts */
            $attempts = $attemptsQuery->get();

            if ($attempts->isEmpty()) {
                $quizRows[] = [
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'completed_attempts_count' => 0,
                    'selected_percentage' => null,
                    'passed' => null,
                    'is_late' => null,
                    'submitted_at' => null,
                ];

                continue;
            }

            $withAttempt++;
            $selected = $attemptStrategy === self::STRATEGY_LATEST
                ? $attempts->sortByDesc(fn (QuizAttempt $a) => $a->submitted_at ?? $a->updated_at)->first()
                : $attempts->sortByDesc(fn (QuizAttempt $a) => (float) ($a->percentage_score ?? 0))->first();

            $pct = $selected->percentage_score !== null ? round((float) $selected->percentage_score, 2) : null;
            if ($pct !== null) {
                $percentagesForAvg[] = $pct;
            }
            if ($selected->is_late) {
                $lateCount++;
            }

            $quizRows[] = [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
                'completed_attempts_count' => $attempts->count(),
                'selected_percentage' => $pct,
                'passed' => $selected->passed,
                'is_late' => (bool) $selected->is_late,
                'submitted_at' => $selected->submitted_at?->toIso8601String(),
            ];
        }

        $avgPct = count($percentagesForAvg) > 0
            ? round(array_sum($percentagesForAvg) / count($percentagesForAvg), 2)
            : null;

        return [
            'schema_version' => 1,
            'generated_at' => now()->toIso8601String(),
            'attempt_strategy' => $attemptStrategy,
            'since' => $since?->toIso8601String(),
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
            ],
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
            ],
            'enrollment' => $enrollment ? [
                'enrollment_status' => $enrollment->enrollment_status,
                'completion_percentage' => $enrollment->completion_percentage !== null
                    ? round((float) $enrollment->completion_percentage, 2)
                    : null,
                'course_grade' => $enrollment->grade !== null ? (float) $enrollment->grade : null,
                'last_accessed_at' => $enrollment->last_accessed_at?->toIso8601String(),
            ] : [
                'enrollment_status' => null,
                'note' => 'no_enrollment_record_for_this_course',
            ],
            'quizzes' => $quizRows,
            'summary' => [
                'quizzes_in_course' => $quizzes->count(),
                'quizzes_with_at_least_one_attempt' => $withAttempt,
                'average_percentage_across_attempts_shown' => $avgPct,
                'late_submissions_among_selected_attempts' => $lateCount,
            ],
        ];
    }

    /**
     * Quizzes that belong to the course (direct course_id or lesson inside the course).
     */
    public function quizzesForCourse(Course $course)
    {
        return Quiz::query()
            ->where(function ($q) use ($course) {
                $q->where('course_id', $course->id)
                    ->orWhereHas('lesson', function ($lessonQ) use ($course) {
                        $lessonQ->whereHas('module', function ($modQ) use ($course) {
                            $modQ->where('course_id', $course->id);
                        });
                    });
            })
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
