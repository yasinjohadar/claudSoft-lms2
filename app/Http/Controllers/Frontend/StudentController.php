<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of all students
     */
    public function index()
    {
        $students = User::role('student')
                       ->where('is_active', true)
                       ->orderBy('created_at', 'desc')
                       ->paginate(12);

        return view('frontend.pages.students', compact('students'));
    }

    /**
     * Display the specified student details
     */
    public function show($id)
    {
        $student = User::role('student')
                      ->where('is_active', true)
                      ->with([
                          'courseEnrollments.course',
                          'badges',
                          'achievements',
                          'userBadges.badge',
                          'userAchievements.achievement'
                      ])
                      ->findOrFail($id);

        // Get enrollments with course details
        $enrollments = $student->courseEnrollments()
            ->with('course')
            ->orderBy('enrollment_date', 'desc')
            ->get();

        // Get certificates (completed enrollments with certificates)
        $certificates = $enrollments->filter(function($enrollment) {
            return $enrollment->certificate_issued && $enrollment->isCompleted();
        });

        // Get badges
        $badges = $student->badges()->orderBy('user_badges.awarded_at', 'desc')->get();

        // Get achievements
        $achievements = $student->achievements()
            ->wherePivot('status', 'completed')
            ->orderBy('user_achievements.completed_at', 'desc')
            ->get();

        // Statistics
        $stats = [
            'total_courses' => $enrollments->count(),
            'completed_courses' => $enrollments->where('enrollment_status', 'completed')->count(),
            'active_courses' => $enrollments->where('enrollment_status', 'active')->count(),
            'certificates_count' => $certificates->count(),
            'badges_count' => $badges->count(),
            'achievements_count' => $achievements->count(),
        ];

        return view('frontend.pages.student-details', compact(
            'student',
            'enrollments',
            'certificates',
            'badges',
            'achievements',
            'stats'
        ));
    }
}
