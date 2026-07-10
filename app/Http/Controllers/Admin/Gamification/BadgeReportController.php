<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use App\Services\Gamification\BadgeDistributionReportService;
use App\Services\Gamification\GamificationStudentScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeReportController extends Controller
{
    public function __construct(
        protected BadgeDistributionReportService $reportService,
        protected GamificationStudentScopeService $studentScopeService
    ) {}

    public function distribution(Request $request)
    {
        $courseId = (int) $request->input('course_id', 0);
        $groupId = (int) $request->input('group_id', 0);
        $rarity = $request->filled('rarity') ? (string) $request->input('rarity') : null;
        $search = $request->filled('q') ? trim((string) $request->input('q')) : null;

        $stats = $this->reportService->buildScopeStats($courseId, $groupId);
        $badges = $this->reportService->paginateBadgeDistribution($courseId, $groupId, $rarity, $search);
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $allGroups = $this->studentScopeService->resolveGroupOptions($courseId);

        if ($request->ajax()) {
            return response()->json([
                'stats' => view('admin.pages.gamification.badges.reports.partials.scope-stats', [
                    'stats' => $stats,
                    'context' => 'distribution',
                ])->render(),
                'table' => view('admin.pages.gamification.badges.reports.partials.distribution-table', compact('badges'))->render(),
                'pagination' => $badges->hasPages() ? $badges->links()->render() : '',
                'total' => $badges->total(),
                'group_options' => view('admin.pages.gamification.badges.reports.partials.group-options', [
                    'allGroups' => $allGroups,
                ])->render(),
            ]);
        }

        return view('admin.pages.gamification.badges.reports.distribution', compact(
            'stats',
            'badges',
            'courses',
            'allGroups'
        ));
    }

    public function students(Request $request)
    {
        $courseId = (int) $request->input('course_id', 0);
        $groupId = (int) $request->input('group_id', 0);
        $search = $request->filled('q') ? trim((string) $request->input('q')) : null;

        $stats = $this->reportService->buildScopeStats($courseId, $groupId);
        $students = $this->reportService->paginateStudentsReport($courseId, $groupId, $search);
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $allGroups = $this->studentScopeService->resolveGroupOptions($courseId);

        if ($request->ajax()) {
            return response()->json([
                'stats' => view('admin.pages.gamification.badges.reports.partials.scope-stats', [
                    'stats' => $stats,
                    'context' => 'students',
                ])->render(),
                'table' => view('admin.pages.gamification.badges.reports.partials.students-table', compact('students'))->render(),
                'pagination' => $students->hasPages() ? $students->links()->render() : '',
                'total' => $students->total(),
                'group_options' => view('admin.pages.gamification.badges.reports.partials.group-options', [
                    'allGroups' => $allGroups,
                ])->render(),
            ]);
        }

        return view('admin.pages.gamification.badges.reports.students', compact(
            'stats',
            'students',
            'courses',
            'allGroups'
        ));
    }

    public function studentDetail(Request $request, User $user)
    {
        $courseId = (int) $request->input('course_id', 0);
        $groupId = (int) $request->input('group_id', 0);

        $detail = $this->reportService->buildStudentDetail($user, $courseId, $groupId);

        return view('admin.pages.gamification.badges.reports.partials.student-detail', $detail);
    }

    public function courseGroups(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::query()->findOrFail($request->integer('course_id'));
        $groups = $course->groups()->orderBy('course_groups.name')->get();

        return response()->json(
            $groups->map(fn (CourseGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])->values()
        );
    }

    public function statistics()
    {
        return redirect()->route('admin.gamification.badges.reports.distribution');
    }
}
