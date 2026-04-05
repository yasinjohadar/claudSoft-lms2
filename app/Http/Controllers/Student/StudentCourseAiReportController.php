<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\StudentCourseAiReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentCourseAiReportController extends Controller
{
    /**
     * صفحة واحدة: كل تقارير الدراسة + روابط سريعة لكل كورس مسجّل.
     */
    public function hub(Request $request): View
    {
        $userId = (int) $request->user()->id;

        $reports = StudentCourseAiReport::query()
            ->where('student_id', $userId)
            ->with(['course', 'courseGroup'])
            ->latest()
            ->paginate(20);

        $enrolledCourses = Course::query()
            ->whereHas('enrollments', function ($q) use ($userId) {
                $q->where('student_id', $userId)
                    ->where('enrollment_status', 'active');
            })
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('student.study-reports.index', compact('reports', 'enrolledCourses'));
    }

    public function index(Request $request, Course $course): View
    {
        $this->authorizeCourse($request, $course);

        $reports = StudentCourseAiReport::query()
            ->where('student_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->latest()
            ->paginate(15);

        return view('student.progress.ai-reports-index', compact('course', 'reports'));
    }

    public function show(Request $request, StudentCourseAiReport $report): View
    {
        if ((int) $report->student_id !== (int) $request->user()->id) {
            abort(403);
        }

        $report->load(['course']);

        return view('student.progress.ai-report', compact('report'));
    }

    private function authorizeCourse(Request $request, Course $course): void
    {
        $ok = CourseEnrollment::query()
            ->active()
            ->where('course_id', $course->id)
            ->where('student_id', $request->user()->id)
            ->exists();

        if (! $ok) {
            abort(403);
        }
    }
}
