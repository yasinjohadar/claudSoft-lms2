<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\CourseModule;
use App\Models\SectionAccessRestriction;
use App\Models\ModuleAccessRestriction;
use App\Models\CourseGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessRestrictionController extends Controller
{
    /**
     * Get restrictions for a section.
     *
     * @param CourseSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSectionRestrictions(CourseSection $section)
    {
        try {
            $restrictions = SectionAccessRestriction::where('section_id', $section->id)
                ->where('restriction_type', 'group')
                ->where('access_type', 'allow')
                ->get();

            $groupIds = $restrictions->pluck('restriction_id')->toArray();

            // Get all groups associated with the course
            $course = $section->course;
            $allGroups = $course->groups()->get();

            return response()->json([
                'success' => true,
                'restricted_group_ids' => $groupIds,
                'all_groups' => $allGroups->map(function($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل القيود: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get restrictions for a module.
     *
     * @param CourseModule $module
     * @return \Illuminate\Http\JsonResponse
     */
    public function getModuleRestrictions(CourseModule $module)
    {
        try {
            $restrictions = ModuleAccessRestriction::where('module_id', $module->id)
                ->where('restriction_type', 'group')
                ->where('access_type', 'allow')
                ->get();

            $groupIds = $restrictions->pluck('restriction_id')->toArray();

            // Get all groups associated with the course
            $course = $module->course;
            $allGroups = $course->groups()->get();

            return response()->json([
                'success' => true,
                'restricted_group_ids' => $groupIds,
                'all_groups' => $allGroups->map(function($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل القيود: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync restrictions for a section.
     *
     * @param Request $request
     * @param CourseSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncSectionRestrictions(Request $request, CourseSection $section)
    {
        $validated = $request->validate([
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:course_groups,id',
        ]);

        DB::beginTransaction();
        try {
            // Verify that all groups belong to the same course
            $course = $section->course;
            $validGroupIds = $course->groups()->get()->pluck('id')->toArray();
            $groupIds = $validated['group_ids'] ?? [];

            // Filter to only include valid groups
            $groupIds = array_intersect($groupIds, $validGroupIds);

            // Delete all existing group restrictions for this section
            SectionAccessRestriction::where('section_id', $section->id)
                ->where('restriction_type', 'group')
                ->where('access_type', 'allow')
                ->delete();

            // Create new restrictions
            foreach ($groupIds as $groupId) {
                SectionAccessRestriction::create([
                    'section_id' => $section->id,
                    'restriction_type' => 'group',
                    'restriction_id' => $groupId,
                    'access_type' => 'allow',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث القيود بنجاح',
                'restricted_count' => count($groupIds),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync restrictions for a module.
     *
     * @param Request $request
     * @param CourseModule $module
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncModuleRestrictions(Request $request, CourseModule $module)
    {
        $validated = $request->validate([
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:course_groups,id',
        ]);

        DB::beginTransaction();
        try {
            // Verify that all groups belong to the same course
            $course = $module->course;
            $validGroupIds = $course->groups()->get()->pluck('id')->toArray();
            $groupIds = $validated['group_ids'] ?? [];

            // Filter to only include valid groups
            $groupIds = array_intersect($groupIds, $validGroupIds);

            // Delete all existing group restrictions for this module
            ModuleAccessRestriction::where('module_id', $module->id)
                ->where('restriction_type', 'group')
                ->where('access_type', 'allow')
                ->delete();

            // Create new restrictions
            foreach ($groupIds as $groupId) {
                ModuleAccessRestriction::create([
                    'module_id' => $module->id,
                    'restriction_type' => 'group',
                    'restriction_id' => $groupId,
                    'access_type' => 'allow',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث القيود بنجاح',
                'restricted_count' => count($groupIds),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }
}

