<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\ModuleCompletion;
use Illuminate\Http\Request;

class ModuleCompletionReportController extends Controller
{
    /**
     * List students with progress/completion records for a course module.
     */
    public function index(Request $request, Course $course, CourseModule $module)
    {
        if ((int) $module->course_id !== (int) $course->id) {
            abort(404);
        }

        $statusFilter = $request->get('status', 'completed');
        if (! in_array($statusFilter, ['completed', 'in_progress', 'any'], true)) {
            $statusFilter = 'completed';
        }

        $applyStatus = function ($query) use ($statusFilter) {
            if ($statusFilter === 'completed') {
                $query->where('completion_status', 'completed');
            } elseif ($statusFilter === 'in_progress') {
                $query->where('completion_status', 'in_progress');
            } else {
                $query->whereIn('completion_status', ['in_progress', 'completed']);
            }
        };

        $applySearch = function ($query) use ($request) {
            if (! $request->filled('search')) {
                return;
            }
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        };

        $queryWithoutGroup = ModuleCompletion::query()
            ->where('module_id', $module->id)
            ->with(['student']);
        $applyStatus($queryWithoutGroup);
        $applySearch($queryWithoutGroup);

        $totalWithoutGroupFilter = (clone $queryWithoutGroup)->count();

        $queryStatusOnly = ModuleCompletion::query()
            ->where('module_id', $module->id);
        $applyStatus($queryStatusOnly);
        $totalStatusOnly = (clone $queryStatusOnly)->count();

        $listQuery = ModuleCompletion::query()
            ->where('module_id', $module->id)
            ->with(['student']);
        $applyStatus($listQuery);
        $applySearch($listQuery);

        $allowedGroupIds = $course->groups()->pluck('course_groups.id')->all();

        $groupFilterActive = false;
        if ($request->filled('group_id')) {
            $groupId = (int) $request->input('group_id');
            if ($groupId > 0 && in_array($groupId, $allowedGroupIds, true)) {
                $groupFilterActive = true;
                $listQuery->whereHas('student.courseGroupMemberships', function ($q) use ($groupId) {
                    $q->where('group_id', $groupId);
                });
            }
        }

        $completions = $listQuery
            ->orderByRaw('CASE WHEN completed_at IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('completed_at')
            ->orderByDesc('last_accessed_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $course->load(['groups' => function ($q) {
            $q->orderBy('name');
        }]);

        $searchActive = $request->filled('search');

        $viewData = compact(
            'course',
            'module',
            'completions',
            'statusFilter',
            'totalWithoutGroupFilter',
            'totalStatusOnly',
            'groupFilterActive',
            'searchActive'
        );

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.pages.courses.partials.module-completions-results', $viewData)->render(),
            ]);
        }

        return view('admin.pages.courses.module-completions', $viewData);
    }
}
