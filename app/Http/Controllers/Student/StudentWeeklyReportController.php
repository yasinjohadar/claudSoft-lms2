<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\StudentWeeklyReport;
use App\Services\Reports\StudentWeeklyReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentWeeklyReportController extends Controller
{
    public function __construct(private readonly StudentWeeklyReportService $reportService)
    {
    }

    public function index()
    {
        $baseQuery = StudentWeeklyReport::query()->where('student_id', auth()->id());

        $reports = (clone $baseQuery)
            ->latest('due_at')
            ->latest('id')
            ->paginate(15);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)->where('status', StudentWeeklyReport::STATUS_DRAFT)->count(),
            'submitted' => (clone $baseQuery)->where('status', StudentWeeklyReport::STATUS_SUBMITTED)->count(),
            'reviewed' => (clone $baseQuery)->where('status', StudentWeeklyReport::STATUS_REVIEWED)->count(),
            'closed' => (clone $baseQuery)->where('status', StudentWeeklyReport::STATUS_CLOSED)->count(),
            'with_feedback' => (clone $baseQuery)->whereNotNull('admin_feedback')->count(),
        ];

        return view('student.weekly-reports.index', compact('reports', 'stats'));
    }

    public function show(StudentWeeklyReport $report)
    {
        abort_if($report->student_id !== auth()->id(), 403);

        $report->load('selectedLessons.lesson', 'selectedLessons.module', 'selectedLessons.course');
        $courses = $this->reportService->resolveCoursesForStudentReport((int) auth()->id(), $report);
        $groupedSelections = $this->reportService->groupSelectedLessonsByCourse($report);
        $canEdit = $report->isEditableByStudent();
        $wasSubmitted = $report->wasSubmittedByStudent();

        return view('student.weekly-reports.show', compact('report', 'courses', 'groupedSelections', 'canEdit', 'wasSubmitted'));
    }

    public function save(Request $request, StudentWeeklyReport $report)
    {
        abort_if($report->student_id !== auth()->id(), 403);

        $payload = $this->validatedPayload($request, $report);
        $this->reportService->saveStudentReport($report, $payload);

        return back()->with('success', 'تم حفظ التقرير بنجاح.');
    }

    public function submit(Request $request, StudentWeeklyReport $report)
    {
        abort_if($report->student_id !== auth()->id(), 403);

        $payload = $this->validatedPayload($request, $report);
        $this->reportService->submitReport($report, $payload);

        return back()->with('success', 'تم إرسال التقرير للأدمن.');
    }

    public function lessons(Request $request, Course $course)
    {
        $reportId = (int) $request->query('report_id', 0);
        abort_unless($reportId > 0, 403);

        $report = StudentWeeklyReport::query()->findOrFail($reportId);
        abort_if($report->student_id !== auth()->id(), 403);
        abort_unless(
            $this->reportService->isCourseAllowedForStudentReport((int) auth()->id(), $report, (int) $course->id),
            403
        );

        $modules = DB::table('course_modules')
            ->where('course_modules.course_id', $course->id)
            ->whereNull('course_modules.deleted_at')
            ->select('course_modules.id', 'course_modules.title', 'course_modules.module_type')
            ->orderBy('course_modules.sort_order')
            ->orderBy('course_modules.title')
            ->get();

        return response()->json($modules);
    }

    private function validatedPayload(Request $request, StudentWeeklyReport $report): array
    {
        $validated = $request->validate([
            'student_details' => ['nullable', 'string'],
            'student_notes' => ['nullable', 'string'],
            'lessons' => ['nullable', 'array'],
            'lessons.*.course_id' => ['required_with:lessons', 'integer', 'exists:courses,id'],
            'lessons.*.module_id' => ['nullable', 'integer', 'exists:course_modules,id'],
            'lessons.*.module_ids' => ['nullable', 'array'],
            'lessons.*.module_ids.*' => ['integer', 'exists:course_modules,id'],
        ]);

        $validated['lessons'] = $this->reportService->flattenLessonsPayload($validated['lessons'] ?? []);

        foreach ($validated['lessons'] as $entry) {
            $courseId = (int) ($entry['course_id'] ?? 0);
            $moduleId = (int) ($entry['module_id'] ?? 0);

            if (!$this->reportService->isCourseAllowedForStudentReport((int) auth()->id(), $report, $courseId)) {
                abort(403);
            }

            if ($moduleId <= 0) {
                continue;
            }
        }

        return $validated;
    }
}
