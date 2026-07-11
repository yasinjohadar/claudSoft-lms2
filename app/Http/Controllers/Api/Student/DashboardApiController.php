<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CourseEnrollment;
use App\Models\QuestionModuleAttempt;
use App\Models\QuizAttempt;
use App\Services\Student\StudentCourseVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function __construct(
        private StudentCourseVisibilityService $courseVisibility,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        $activeEnrollments = $this->courseVisibility->excludeHiddenEnrollments(
            CourseEnrollment::query()
                ->where('student_id', $student->id)
                ->where('enrollment_status', 'active')
                ->with('course')
                ->get(),
            $student
        );

        $pendingMembershipNotices = $this->courseVisibility->pendingNotices($student);

        $courseStats = [
            'total_courses' => $activeEnrollments->count(),
            'in_progress' => $activeEnrollments
                ->filter(fn ($e) => (float) ($e->completion_percentage ?? 0) > 0
                    && (float) ($e->completion_percentage ?? 0) < 100)
                ->count(),
            'completed' => $activeEnrollments
                ->filter(fn ($e) => (float) ($e->completion_percentage ?? 0) >= 100)
                ->count(),
        ];

        $qmAttempts = QuestionModuleAttempt::where('student_id', $student->id)
            ->where('status', 'completed')
            ->get();

        $lastAttempt = $qmAttempts->sortByDesc('completed_at')->first();

        $questionModuleStats = [
            'total_attempts' => $qmAttempts->count(),
            'passed_attempts' => $qmAttempts->where('is_passed', true)->count(),
            'average_score' => round((float) ($qmAttempts->avg('percentage') ?? 0), 1),
            'last_attempt' => $lastAttempt ? [
                'percentage' => round((float) ($lastAttempt->percentage ?? 0), 1),
                'is_passed' => (bool) $lastAttempt->is_passed,
                'completed_at' => optional($lastAttempt->completed_at)?->toIso8601String(),
            ] : null,
        ];

        $quizAttempts = QuizAttempt::where('student_id', $student->id)
            ->whereIn('status', ['completed', 'submitted'])
            ->get();

        $certificatesCount = Certificate::where('user_id', $student->id)
            ->where('status', 'active')
            ->count();

        $completionValues = $activeEnrollments->map(fn ($e) => (float) ($e->completion_percentage ?? 0));
        $overallProgress = $completionValues->count() > 0
            ? round($completionValues->avg(), 1)
            : 0;

        $inProgressCourses = $activeEnrollments
            ->filter(fn ($e) => (float) ($e->completion_percentage ?? 0) > 0
                && (float) ($e->completion_percentage ?? 0) < 100)
            ->sortByDesc('updated_at')
            ->take(5)
            ->values()
            ->map(fn ($e) => [
                'id' => $e->course_id,
                'title' => $e->course?->title,
                'progress' => round((float) ($e->completion_percentage ?? 0), 1),
            ]);

        $enrollmentChart = $activeEnrollments
            ->groupBy(fn ($e) => optional($e->created_at)->format('Y-m'))
            ->map(fn ($group, $month) => [
                'month' => $month,
                'count' => $group->count(),
            ])
            ->values()
            ->take(6);

        return response()->json([
            'success' => true,
            'data' => [
                'course_stats' => $courseStats,
                'question_module_stats' => $questionModuleStats,
                'in_progress_courses' => $inProgressCourses,
                'pending_membership_notices' => $pendingMembershipNotices,
                'enrollment_chart' => $enrollmentChart,
                'stats' => [
                    'total_courses' => $courseStats['total_courses'],
                    'active_courses' => $courseStats['in_progress'],
                    'completed_courses' => $courseStats['completed'],
                    'overall_progress' => $overallProgress,
                    'total_certificates' => $certificatesCount,
                    'quiz_attempts' => $quizAttempts->count(),
                    'quiz_completed' => $quizAttempts->count(),
                    'question_module_attempts' => $questionModuleStats['total_attempts'],
                    'question_module_passed' => $questionModuleStats['passed_attempts'],
                    'average_quiz_score' => $questionModuleStats['average_score'],
                ],
                'summary' => [
                    ['label' => 'كورسات مسجّلة', 'value' => (string) $courseStats['total_courses']],
                    ['label' => 'قيد التقدم', 'value' => (string) $courseStats['in_progress']],
                    ['label' => 'كورسات مكتملة', 'value' => (string) $courseStats['completed']],
                    ['label' => 'محاولات اختبار', 'value' => (string) $questionModuleStats['total_attempts']],
                ],
            ],
        ]);
    }
}
