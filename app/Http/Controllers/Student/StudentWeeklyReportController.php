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
        $reports = StudentWeeklyReport::query()
            ->where('student_id', auth()->id())
            ->latest('due_at')
            ->latest('id')
            ->paginate(15);

        return view('student.weekly-reports.index', compact('reports'));
    }

    public function show(StudentWeeklyReport $report)
    {
        abort_if($report->student_id !== auth()->id(), 403);

        $report->load('selectedLessons.lesson', 'selectedLessons.module', 'selectedLessons.course');
        $courses = Course::query()
            ->whereHas('enrollments', fn ($q) => $q->where('student_id', auth()->id()))
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('student.weekly-reports.show', compact('report', 'courses'));
    }

    public function save(Request $request, StudentWeeklyReport $report)
    {
        abort_if($report->student_id !== auth()->id(), 403);

        $payload = $this->validatedPayload($request);
        $this->reportService->saveStudentReport($report, $payload);

        return back()->with('success', 'تم حفظ التقرير بنجاح.');
    }

    public function submit(Request $request, StudentWeeklyReport $report)
    {
        abort_if($report->student_id !== auth()->id(), 403);

        $payload = $this->validatedPayload($request);
        $this->reportService->submitReport($report, $payload);

        return back()->with('success', 'تم إرسال التقرير للأدمن.');
    }

    public function lessons(Course $course)
    {
        $isEnrolled = $course->enrollments()->where('student_id', auth()->id())->exists();
        abort_unless($isEnrolled, 403);

        $modules = DB::table('course_modules')
            ->where('course_modules.course_id', $course->id)
            ->whereNull('course_modules.deleted_at')
            ->select('course_modules.id', 'course_modules.title', 'course_modules.module_type')
            ->orderBy('course_modules.sort_order')
            ->orderBy('course_modules.title')
            ->get();

        return response()->json($modules);
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'student_details' => ['nullable', 'string'],
            'student_notes' => ['nullable', 'string'],
            'lessons' => ['nullable', 'array'],
            'lessons.*.course_id' => ['required_with:lessons', 'integer', 'exists:courses,id'],
            'lessons.*.module_id' => ['required_with:lessons', 'integer', 'exists:course_modules,id'],
            'lessons.*.lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ]);
    }
}

