<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\StudentCourseAiReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudyReportApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        $paginator = StudentCourseAiReport::query()
            ->where('student_id', $userId)
            ->with(['course', 'courseGroup'])
            ->latest()
            ->paginate($perPage);

        $enrolledCourses = Course::query()
            ->whereHas('enrollments', fn ($q) => $q->where('student_id', $userId)->where('enrollment_status', 'active'))
            ->orderBy('title')
            ->get(['id', 'title']);

        return response()->json([
            'success' => true,
            'data' => [
                'reports' => collect($paginator->items())->map(fn ($r) => $this->serialize($r))->values(),
                'enrolled_courses' => $enrolledCourses,
                'stats' => [
                    'total_reports' => StudentCourseAiReport::where('student_id', $userId)->count(),
                    'recent' => StudentCourseAiReport::where('student_id', $userId)->where('created_at', '>=', now()->subDays(30))->count(),
                ],
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, StudentCourseAiReport $report): JsonResponse
    {
        if ((int) $report->student_id !== (int) $request->user()->id) {
            abort(403);
        }
        $report->load(['course', 'courseGroup']);

        return response()->json(['success' => true, 'data' => $this->serialize($report, true)]);
    }

    private function serialize(StudentCourseAiReport $report, bool $detailed = false): array
    {
        $data = [
            'id' => $report->id,
            'course_id' => $report->course_id,
            'course_title' => $report->course?->title,
            'title' => $report->course?->title,
            'created_at' => $report->created_at?->toIso8601String(),
        ];
        if ($detailed) {
            $data['content'] = $report->narrative;
            $data['facts'] = $report->facts;
            $data['meta'] = $report->meta;
        }
        return $data;
    }
}
