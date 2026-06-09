<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CourseEnrollment;
use App\Models\QuestionModuleAttempt;
use App\Models\QuizAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        $enrollments = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('enrollment_status', ['active', 'completed'])
            ->with('course')
            ->get();

        $activeEnrollments = $enrollments->where('enrollment_status', 'active');
        $completionValues = $enrollments->map(fn ($e) => (float) ($e->completion_percentage ?? 0));
        $overallProgress = $completionValues->count() > 0
            ? round($completionValues->avg(), 1)
            : 0;

        $qmAttempts = QuestionModuleAttempt::where('student_id', $student->id)
            ->where('status', 'completed')
            ->get();

        $quizAttempts = QuizAttempt::where('student_id', $student->id)
            ->whereIn('status', ['completed', 'submitted'])
            ->get();

        $certificatesCount = Certificate::where('user_id', $student->id)
            ->where('status', 'active')
            ->count();

        $inProgressCourses = $activeEnrollments
            ->filter(fn ($e) => (float) ($e->completion_percentage ?? 0) > 0 && (float) ($e->completion_percentage ?? 0) < 100)
            ->sortByDesc('updated_at')
            ->take(5)
            ->values()
            ->map(fn ($e) => [
                'id' => $e->course_id,
                'title' => $e->course?->title,
                'progress' => round((float) ($e->completion_percentage ?? 0), 1),
            ]);

        $enrollmentChart = $enrollments
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
                'stats' => [
                    'total_courses' => $enrollments->count(),
                    'active_courses' => $activeEnrollments->count(),
                    'completed_courses' => $enrollments->filter(fn ($e) => (float) ($e->completion_percentage ?? 0) >= 100)->count(),
                    'overall_progress' => $overallProgress,
                    'total_certificates' => $certificatesCount,
                    'quiz_attempts' => $quizAttempts->count(),
                    'quiz_completed' => $quizAttempts->count(),
                    'question_module_attempts' => $qmAttempts->count(),
                    'question_module_passed' => $qmAttempts->where('is_passed', true)->count(),
                    'average_quiz_score' => round((float) ($quizAttempts->avg('percentage_score') ?? 0), 1),
                ],
                'in_progress_courses' => $inProgressCourses,
                'enrollment_chart' => $enrollmentChart,
                'summary' => [
                    ['label' => 'إجمالي الكورسات', 'value' => (string) $enrollments->count()],
                    ['label' => 'نسبة الإنجاز', 'value' => $overallProgress . '%'],
                    ['label' => 'الشهادات', 'value' => (string) $certificatesCount],
                    ['label' => 'محاولات الاختبارات', 'value' => (string) $quizAttempts->count()],
                ],
            ],
        ]);
    }
}
