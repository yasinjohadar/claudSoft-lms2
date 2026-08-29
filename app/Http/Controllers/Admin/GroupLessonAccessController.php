<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\CourseModule;
use App\Models\ModuleAccessRestriction;
use App\Services\Notifications\NotificationHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupLessonAccessController extends Controller
{
    public function __construct(
        protected NotificationHubService $notificationHub
    ) {}

    /**
     * List every lesson (module) across every course linked to this group,
     * each with its current access state for this specific group.
     */
    public function index(CourseGroup $group)
    {
        $courses = $group->courses()->orderBy('title')->get();

        $courses->load(['sections' => function ($query) {
            $query->orderBy('sort_order')->with('modules');
        }]);

        $allModuleIds = [];
        foreach ($courses as $course) {
            foreach ($course->sections as $section) {
                foreach ($section->modules as $module) {
                    $allModuleIds[] = $module->id;
                }
            }
        }

        $restrictionsByModule = ModuleAccessRestriction::whereIn('module_id', $allModuleIds)
            ->where('restriction_type', 'group')
            ->where('access_type', 'allow')
            ->get()
            ->groupBy('module_id');

        $groupId = (int) $group->id;
        $totalLessons = 0;
        $allowedCount = 0;

        foreach ($courses as $course) {
            foreach ($course->sections as $section) {
                foreach ($section->modules as $module) {
                    $allowIds = ($restrictionsByModule->get($module->id) ?? collect())
                        ->pluck('restriction_id')
                        ->map(fn ($id) => (int) $id)
                        ->all();

                    $restricted = count($allowIds) > 0;
                    $allowed = ! $restricted || in_array($groupId, $allowIds, true);

                    $module->setAttribute('is_restricted', $restricted);
                    $module->setAttribute('allowed_for_group', $allowed);

                    $totalLessons++;
                    if ($allowed) {
                        $allowedCount++;
                    }
                }
            }
        }

        return view('admin.pages.groups.lessons', [
            'group' => $group,
            'courses' => $courses,
            'totalLessons' => $totalLessons,
            'allowedCount' => $allowedCount,
            'excludedCount' => $totalLessons - $allowedCount,
        ]);
    }

    /**
     * Toggle access for this one group on one lesson, without touching any other group's access.
     */
    public function toggle(Request $request, CourseGroup $group, CourseModule $module)
    {
        $validated = $request->validate([
            'allowed' => 'required|boolean',
        ]);

        $belongsToGroup = $module->course
            ? $module->course->groups()->where('course_groups.id', $group->id)->exists()
            : false;

        abort_unless($belongsToGroup, 404);

        $groupId = (int) $group->id;
        $wantAllowed = (bool) $validated['allowed'];

        $currentAllowIds = ModuleAccessRestriction::where('module_id', $module->id)
            ->where('restriction_type', 'group')
            ->where('access_type', 'allow')
            ->pluck('restriction_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $isRestricted = count($currentAllowIds) > 0;
        $currentlyAllowed = ! $isRestricted || in_array($groupId, $currentAllowIds, true);

        $newIds = $currentAllowIds;

        if ($wantAllowed !== $currentlyAllowed) {
            if ($wantAllowed) {
                // Turning on: only meaningful if the lesson is currently an explicit allow-list.
                // (If it's fully open, it's already allowed — nothing to do.)
                if ($isRestricted) {
                    $newIds = array_values(array_unique([...$currentAllowIds, $groupId]));
                }
            } else {
                if ($isRestricted) {
                    // Just remove this group from the existing explicit list — everyone else untouched.
                    $newIds = array_values(array_diff($currentAllowIds, [$groupId]));
                } else {
                    // Lesson is fully open: materialize an explicit allow-list of every other
                    // group of this course so their access is preserved, excluding this group.
                    $courseGroupIds = $module->course->groups()->pluck('course_groups.id')
                        ->map(fn ($id) => (int) $id)
                        ->all();
                    $newIds = array_values(array_diff($courseGroupIds, [$groupId]));
                }
            }
        }

        if ($newIds !== $currentAllowIds) {
            DB::transaction(function () use ($module, $newIds) {
                ModuleAccessRestriction::where('module_id', $module->id)
                    ->where('restriction_type', 'group')
                    ->where('access_type', 'allow')
                    ->delete();

                foreach ($newIds as $gid) {
                    ModuleAccessRestriction::create([
                        'module_id' => $module->id,
                        'restriction_type' => 'group',
                        'restriction_id' => $gid,
                        'access_type' => 'allow',
                    ]);
                }
            });
        }

        $finalRestricted = count($newIds) > 0;
        $finalAllowed = ! $finalRestricted || in_array($groupId, $newIds, true);

        if ($finalAllowed && ! $currentlyAllowed) {
            $this->notifyGroupLessonAvailable($group, $module);
        }

        return response()->json([
            'success' => true,
            'restricted' => $finalRestricted,
            'allowed' => $finalAllowed,
        ]);
    }

    /**
     * Notify every member of this group in real time that a lesson just became available for them.
     */
    private function notifyGroupLessonAvailable(CourseGroup $group, CourseModule $module): void
    {
        $students = $group->students()->get();

        if ($students->isEmpty()) {
            return;
        }

        $course = $module->course;

        $this->notificationHub->sendToUsers($students, 'student.lesson.available', [
            'lesson_title' => $module->title ?? 'الدرس',
            'lesson_id' => $module->id,
            'module_id' => $module->id,
            'course_id' => $course?->id,
            'course_title' => $course?->title ?? 'الكورس',
            'group_id' => $group->id,
            'group_name' => $group->name,
            'action_url' => route('student.learn.module', $module->id),
        ]);
    }
}
