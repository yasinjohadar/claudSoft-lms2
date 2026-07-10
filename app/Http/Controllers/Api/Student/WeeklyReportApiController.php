<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\StudentWeeklyReport;
use App\Services\Reports\StudentWeeklyReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeeklyReportApiController extends Controller
{
    public function __construct(private readonly StudentWeeklyReportService $reportService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);
        $paginator = StudentWeeklyReport::query()
            ->where('student_id', $request->user()->id)
            ->latest('due_at')
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'reports' => collect($paginator->items())->map(fn ($r) => $this->serialize($r))->values(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, StudentWeeklyReport $report): JsonResponse
    {
        abort_unless($report->belongsToStudentId($request->user()->id), 403);
        $report->load('selectedLessons.lesson', 'selectedLessons.module', 'selectedLessons.course');

        return response()->json(['success' => true, 'data' => $this->serialize($report, true)]);
    }

    public function save(Request $request, StudentWeeklyReport $report): JsonResponse
    {
        abort_unless($report->belongsToStudentId($request->user()->id), 403);
        $this->reportService->saveStudentReport($report, $this->validatedPayload($request));

        return response()->json(['success' => true, 'message' => 'تم حفظ التقرير']);
    }

    public function submit(Request $request, StudentWeeklyReport $report): JsonResponse
    {
        abort_unless($report->belongsToStudentId($request->user()->id), 403);
        $this->reportService->submitReport($report, $this->validatedPayload($request));

        return response()->json(['success' => true, 'message' => 'تم إرسال التقرير']);
    }

    public function lessons(Request $request, Course $course): JsonResponse
    {
        abort_unless($course->enrollments()->where('student_id', $request->user()->id)->exists(), 403);

        $modules = DB::table('course_modules')
            ->where('course_modules.course_id', $course->id)
            ->whereNull('course_modules.deleted_at')
            ->select('course_modules.id', 'course_modules.title', 'course_modules.module_type')
            ->orderBy('course_modules.sort_order')
            ->get();

        return response()->json(['success' => true, 'data' => $modules]);
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'student_notes' => ['nullable', 'string'],
            'challenges' => ['nullable', 'string'],
            'goals' => ['nullable', 'string'],
            'selected_lessons' => ['nullable', 'array'],
            'selected_lessons.*.course_id' => ['required', 'integer'],
            'selected_lessons.*.module_id' => ['required', 'integer'],
            'selected_lessons.*.lesson_id' => ['required', 'integer'],
        ]);
    }

    private function serialize(StudentWeeklyReport $report, bool $detailed = false): array
    {
        $data = [
            'id' => $report->id,
            'title' => $report->title,
            'status' => $report->status,
            'due_at' => $report->due_at?->toIso8601String(),
            'submitted_at' => $report->submitted_at?->toIso8601String(),
        ];
        if ($detailed) {
            $data['student_notes'] = $report->student_notes;
            $data['challenges'] = $report->challenges;
            $data['goals'] = $report->goals;
            $data['selected_lessons'] = $report->selectedLessons;
        }
        return $data;
    }
}
