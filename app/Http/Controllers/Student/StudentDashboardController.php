<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuestionModuleAttempt;
use App\Models\CourseEnrollment;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        // Question Module Stats
        $attempts = QuestionModuleAttempt::where('student_id', $student->id)
            ->where('status', 'completed')
            ->get();

        $questionModuleStats = [
            'total_attempts' => $attempts->count(),
            'passed_attempts' => $attempts->where('is_passed', true)->count(),
            'average_score' => round($attempts->avg('percentage') ?? 0, 1),
            'last_attempt' => $attempts->sortByDesc('completed_at')->first(),
        ];

        // Course Stats
        $enrollments = CourseEnrollment::where('student_id', $student->id)
            ->where('enrollment_status', 'active')
            ->with('course')
            ->get();

        $courseStats = [
            'total_courses' => $enrollments->count(),
            'in_progress' => $enrollments->where('completion_percentage', '>', 0)->where('completion_percentage', '<', 100)->count(),
            'completed' => $enrollments->where('completion_percentage', '>=', 100)->count(),
        ];

        return view('student.dashboard', compact('questionModuleStats', 'courseStats'));
    }
}
