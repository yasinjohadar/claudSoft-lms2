<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\ModuleAccessRestriction;
use App\Models\SectionAccessRestriction;
use App\Services\Notifications\NotificationHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $allSectionIds = [];
        foreach ($courses as $course) {
            foreach ($course->sections as $section) {
                $allSectionIds[] = $section->id;
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

        $restrictionsBySection = SectionAccessRestriction::whereIn('section_id', $allSectionIds)
            ->where('restriction_type', 'group')
            ->where('access_type', 'allow')
            ->get()
            ->groupBy('section_id');

        $groupId = (int) $group->id;
        $totalLessons = 0;
        $allowedCount = 0;

        foreach ($courses as $course) {
            foreach ($course->sections as $section) {
                $sectionAllowIds = ($restrictionsBySection->get($section->id) ?? collect())
                    ->pluck('restriction_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $sectionRestricted = count($sectionAllowIds) > 0;
                $sectionAllowed = ! $sectionRestricted || in_array($groupId, $sectionAllowIds, true);

                $section->setAttribute('is_restricted', $sectionRestricted);
                $section->setAttribute('allowed_for_group', $sectionAllowed);

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
                    // Effective state: a section-level deny cascades to every lesson
                    // inside it, matching AccessControlService::canAccessModule().
                    if ($sectionAllowed && $allowed) {
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
            // Every group member gets notified across several channels (database,
            // realtime, push…). Sending that inline held the toggle request open for
            // many seconds on large groups — the switch looked frozen and unclickable.
            // Run it after the response is flushed so the UI answers immediately.
            app()->terminating(function () use ($group, $module) {
                try {
                    $this->notifyGroupLessonAvailable($group, $module);
                } catch (\Throwable $e) {
                    Log::warning('Group lesson availability notification failed', [
                        'group_id' => $group->id,
                        'module_id' => $module->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });
        }

        return response()->json([
            'success' => true,
            'restricted' => $finalRestricted,
            'allowed' => $finalAllowed,
        ]);
    }

    /**
     * Toggle access for this one group on a whole section, without touching any other group's
     * access. A section-level deny cascades to every lesson inside it for students
     * (see AccessControlService::canAccessModule()), so this is the "hide/show the whole
     * section" control — it does not touch individual ModuleAccessRestriction rows.
     */
    public function toggleSection(Request $request, CourseGroup $group, CourseSection $section)
    {
        $validated = $request->validate([
            'allowed' => 'required|boolean',
        ]);

        $belongsToGroup = $section->course
            ? $section->course->groups()->where('course_groups.id', $group->id)->exists()
            : false;

        abort_unless($belongsToGroup, 404);

        $groupId = (int) $group->id;
        $wantAllowed = (bool) $validated['allowed'];

        $currentAllowIds = SectionAccessRestriction::where('section_id', $section->id)
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
                // Turning on: only meaningful if the section is currently an explicit allow-list.
                // (If it's fully open, it's already allowed — nothing to do.)
                if ($isRestricted) {
                    $newIds = array_values(array_unique([...$currentAllowIds, $groupId]));
                }
            } else {
                if ($isRestricted) {
                    // Just remove this group from the existing explicit list — everyone else untouched.
                    $newIds = array_values(array_diff($currentAllowIds, [$groupId]));
                } else {
                    // Section is fully open: materialize an explicit allow-list of every other
                    // group of this course so their access is preserved, excluding this group.
                    $courseGroupIds = $section->course->groups()->pluck('course_groups.id')
                        ->map(fn ($id) => (int) $id)
                        ->all();
                    $newIds = array_values(array_diff($courseGroupIds, [$groupId]));
                }
            }
        }

        if ($newIds !== $currentAllowIds) {
            DB::transaction(function () use ($section, $newIds) {
                SectionAccessRestriction::where('section_id', $section->id)
                    ->where('restriction_type', 'group')
                    ->where('access_type', 'allow')
                    ->delete();

                foreach ($newIds as $gid) {
                    SectionAccessRestriction::create([
                        'section_id' => $section->id,
                        'restriction_type' => 'group',
                        'restriction_id' => $gid,
                        'access_type' => 'allow',
                    ]);
                }
            });
        }

        $finalRestricted = count($newIds) > 0;
        $finalAllowed = ! $finalRestricted || in_array($groupId, $newIds, true);

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
