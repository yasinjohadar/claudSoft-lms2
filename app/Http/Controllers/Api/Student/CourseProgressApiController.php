<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\CourseProgressController as WebCourseProgressController;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\ModuleCompletion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * تقدم الكورس، الشهادة، وتقرير PDF — للجوال (Sanctum + ?token= على مسارات الطباعة).
 */
class CourseProgressApiController extends Controller
{
    public function progressOverview(Request $request): JsonResponse
    {
        $student = $request->user();

        $enrollments = CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('enrollment_status', ['active', 'completed'])
            ->with('course')
            ->get()
            ->filter(fn (CourseEnrollment $enrollment) => $enrollment->course !== null)
            ->values();

        $items = $enrollments->map(function (CourseEnrollment $enrollment) use ($student) {
            $course = $enrollment->course;

            // Keep alignment with web calculation logic.
            $completionPercentage = (float) $enrollment->calculateCompletionPercentage();
            $enrollment->refresh();

            $modules = $course->modules()->select('course_modules.id', 'course_modules.module_type')->get();
            $moduleIds = $modules->pluck('id');

            $completedIds = ModuleCompletion::query()
                ->where('student_id', $student->id)
                ->whereIn('module_id', $moduleIds)
                ->where('completion_status', 'completed')
                ->pluck('module_id')
                ->flip();

            $totalVideos = $modules->whereIn('module_type', ['video'])->count();
            $completedVideos = $modules
                ->whereIn('module_type', ['video'])
                ->filter(fn ($module) => $completedIds->has($module->id))
                ->count();

            $totalQuizzes = $modules->whereIn('module_type', ['quiz', 'question_module', 'assignment', 'programming_challenge'])->count();
            $completedQuizzes = $modules
                ->whereIn('module_type', ['quiz', 'question_module', 'assignment', 'programming_challenge'])
                ->filter(fn ($module) => $completedIds->has($module->id))
                ->count();

            $totalWeb = $modules->whereIn('module_type', ['lesson', 'resource', 'forum', 'live_session'])->count();
            $completedWeb = $modules
                ->whereIn('module_type', ['lesson', 'resource', 'forum', 'live_session'])
                ->filter(fn ($module) => $completedIds->has($module->id))
                ->count();

            return [
                'course_id' => (string) $course->id,
                'title' => (string) $course->title,
                'description' => (string) ($course->short_description ?: $course->description ?: ''),
                'progress' => round((float) $completionPercentage, 2),
                'status' => $completionPercentage >= 100 ? 'completed' : ($completionPercentage > 0 ? 'in_progress' : 'not_started'),
                'completed_videos' => $completedVideos,
                'total_videos' => $totalVideos,
                'completed_web' => $completedWeb,
                'total_web' => $totalWeb,
                'completed_quizzes' => $completedQuizzes,
                'total_quizzes' => $totalQuizzes,
                'last_accessed_at' => optional($enrollment->last_accessed_at)->toIso8601String(),
            ];
        })->sortByDesc('progress')->values();

        $totalCourses = $items->count();
        $completedCourses = $items->where('status', 'completed')->count();
        $averageProgress = $totalCourses > 0 ? round($items->avg('progress'), 2) : 0.0;

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_courses' => $totalCourses,
                    'completed_courses' => $completedCourses,
                    'average_progress' => $averageProgress,
                ],
                'courses' => $items,
            ],
        ]);
    }

    public function progress(Request $request, string $courseId): JsonResponse
    {
        $web = app(WebCourseProgressController::class);
        $res = $web->getStats((int) $courseId);
        $payload = json_decode($res->getContent(), true) ?? [];
        if (empty($payload['success'])) {
            return response()->json([
                'success' => false,
                'message' => $payload['message'] ?? 'غير متاح',
            ], $res->getStatusCode() >= 400 ? $res->getStatusCode() : 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $payload['stats'],
            ],
        ]);
    }

    public function certificate(Request $request, string $courseId): Response|JsonResponse
    {
        try {
            $student = $request->user();
            $course = Course::findOrFail($courseId);

            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (! $enrollment) {
                return response()->json(['success' => false, 'message' => 'أنت غير مسجل في هذا الكورس'], 404);
            }

            if (! $enrollment->isCompleted()) {
                return response()->json(['success' => false, 'message' => 'يجب إكمال الكورس أولاً للحصول على الشهادة'], 422);
            }

            if (! $enrollment->hasPassed()) {
                return response()->json(['success' => false, 'message' => 'لم تحقق النسبة المطلوبة للنجاح'], 422);
            }

            if (! $enrollment->certificate_issued) {
                $enrollment->issueCertificate();
            }

            $data = [
                'student' => $student,
                'course' => $course,
                'enrollment' => $enrollment,
                'issued_date' => now(),
                'certificate_number' => 'CERT-'.$course->id.'-'.$student->id.'-'.time(),
            ];

            $pdf = Pdf::loadView('certificates.course-certificate', $data);

            return $pdf->download("certificate-{$course->slug}-{$student->id}.pdf");
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الشهادة',
            ], 500);
        }
    }

    public function exportReport(Request $request, string $courseId): Response|JsonResponse
    {
        try {
            $student = $request->user();
            $course = Course::with(['sections.modules'])->findOrFail($courseId);

            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (! $enrollment) {
                return response()->json(['success' => false, 'message' => 'أنت غير مسجل في هذا الكورس'], 404);
            }

            $enrollment->calculateCompletionPercentage();

            $sectionsProgress = [];
            foreach ($course->sections as $section) {
                $modulesProgress = [];
                foreach ($section->modules as $module) {
                    $completion = $module->getCompletionFor($student);
                    $modulesProgress[] = [
                        'module' => $module,
                        'completion' => $completion,
                        'is_completed' => $module->isCompletedBy($student),
                    ];
                }

                $sectionsProgress[] = [
                    'section' => $section,
                    'modules' => $modulesProgress,
                ];
            }

            $data = [
                'student' => $student,
                'course' => $course,
                'enrollment' => $enrollment,
                'sectionsProgress' => $sectionsProgress,
                'generated_at' => now(),
            ];

            $pdf = Pdf::loadView('reports.course-progress', $data);

            return $pdf->download("progress-report-{$course->slug}-{$student->id}.pdf");
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء التقرير',
            ], 500);
        }
    }
}
