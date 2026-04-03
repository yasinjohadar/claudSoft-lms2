<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ModuleCompletion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseModuleCompletionSummaryController extends Controller
{
    /**
     * Per-module count of students with completed status for the course.
     * Optional group filter: counts and denominator use students active in course and in that group.
     */
    public function index(Request $request, Course $course): View|JsonResponse
    {
        $course->load([
            'sections' => function ($q) {
                $q->orderBy('order_index')->orderBy('id');
            },
            'sections.modules' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            },
            'groups' => function ($q) {
                $q->orderBy('name');
            },
        ]);

        $allowedGroupIds = $course->groups->pluck('id')->all();

        $groupFilterActive = false;
        $selectedGroupId = null;
        if ($request->filled('group_id')) {
            $groupId = (int) $request->input('group_id');
            if ($groupId > 0 && in_array($groupId, $allowedGroupIds, true)) {
                $groupFilterActive = true;
                $selectedGroupId = $groupId;
            }
        }

        $moduleIds = $course->sections->flatMap->modules->pluck('id')->unique()->values();

        $completedByModule = collect();
        if ($moduleIds->isNotEmpty()) {
            $agg = ModuleCompletion::query()
                ->whereIn('module_id', $moduleIds)
                ->where('completion_status', 'completed');
            if ($groupFilterActive) {
                $agg->whereHas('student.courseGroupMemberships', function ($q) use ($selectedGroupId) {
                    $q->where('group_id', $selectedGroupId);
                });
            }
            $completedByModule = (clone $agg)
                ->selectRaw('module_id, COUNT(*) as c')
                ->groupBy('module_id')
                ->pluck('c', 'module_id');
        }

        if ($groupFilterActive) {
            $denominatorCount = User::query()
                ->whereHas('courseEnrollments', function ($q) use ($course) {
                    $q->where('course_id', $course->id)
                        ->where('enrollment_status', 'active');
                })
                ->whereHas('courseGroupMemberships', function ($q) use ($selectedGroupId) {
                    $q->where('group_id', $selectedGroupId);
                })
                ->count();
        } else {
            $denominatorCount = $course->enrollments()->where('enrollment_status', 'active')->count();
        }

        $totalModules = $moduleIds->count();

        $viewData = compact(
            'course',
            'completedByModule',
            'denominatorCount',
            'totalModules',
            'groupFilterActive',
            'selectedGroupId'
        );

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.pages.courses.partials.module-completion-summary-body', $viewData)->render(),
            ]);
        }

        return view('admin.pages.courses.module-completion-summary', $viewData);
    }
}
