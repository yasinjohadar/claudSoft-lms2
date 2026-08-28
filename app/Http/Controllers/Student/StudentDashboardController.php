<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\CourseGroupMember;
use App\Models\QuestionModuleAttempt;
use App\Services\Student\StudentAccountTierService;
use App\Services\Student\StudentCourseVisibilityService;

class StudentDashboardController extends Controller
{
    public function __construct(
        private StudentCourseVisibilityService $courseVisibility,
        private StudentAccountTierService $accountTierService,
    ) {}

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

        // Course Stats (exclude courses gated by pending group membership)
        $enrollments = $this->courseVisibility->excludeHiddenEnrollments(
            CourseEnrollment::where('student_id', $student->id)
                ->where('enrollment_status', 'active')
                ->with('course')
                ->get(),
            $student
        );

        $courseStats = [
            'total_courses' => $enrollments->count(),
            'in_progress' => $enrollments->where('completion_percentage', '>', 0)->where('completion_percentage', '<', 100)->count(),
            'completed' => $enrollments->where('completion_percentage', '>=', 100)->count(),
        ];

        $inProgressCourses = $enrollments
            ->filter(function ($enrollment) {
                $progress = (float) ($enrollment->completion_percentage ?? 0);
                return $progress > 0 && $progress < 100;
            })
            ->sortByDesc('updated_at')
            ->take(5)
            ->values();

        $pendingMembershipNotices = $this->courseVisibility->pendingNotices($student);

        $activeCampMemberships = CourseGroupMember::query()
            ->where('student_id', $student->id)
            ->whereHas('group', fn ($q) => $q->where('is_camp', true))
            ->with('group')
            ->get()
            ->sortBy(fn ($member) => [
                $member->group->hasEnded() ? 1 : 0,
                $member->group->end_date?->timestamp ?? PHP_INT_MAX,
            ])
            ->values();

        $accountTier = $this->accountTierService->resolve($student);

        return view('student.dashboard', compact(
            'questionModuleStats',
            'courseStats',
            'inProgressCourses',
            'pendingMembershipNotices',
            'activeCampMemberships',
            'accountTier',
        ));
    }
}
