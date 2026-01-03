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
use Illuminate\Support\Facades\Log;

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
            
            Log::info('Getting module restrictions', [
                'module_id' => $module->id,
                'restrictions_count' => $restrictions->count(),
                'group_ids' => $groupIds,
            ]);

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
        try {
            // Log incoming request
            Log::info('Syncing section restrictions', [
                'section_id' => $section->id,
                'request_data' => $request->all(),
            ]);

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

                // Convert string IDs to integers if needed
                $groupIds = array_map('intval', $groupIds);

                // Filter to only include valid groups
                $groupIds = array_intersect($groupIds, $validGroupIds);
                $groupIds = array_values(array_unique($groupIds)); // Remove duplicates and reindex

                Log::info('Processing section restrictions', [
                    'section_id' => $section->id,
                    'valid_group_ids' => $validGroupIds,
                    'requested_group_ids' => $validated['group_ids'] ?? [],
                    'filtered_group_ids' => $groupIds,
                ]);

                // Delete all existing group restrictions for this section
                $deletedCount = SectionAccessRestriction::where('section_id', $section->id)
                    ->where('restriction_type', 'group')
                    ->where('access_type', 'allow')
                    ->delete();

                Log::info('Deleted existing restrictions', [
                    'section_id' => $section->id,
                    'deleted_count' => $deletedCount,
                ]);

                // Create new restrictions
                $createdCount = 0;
                foreach ($groupIds as $groupId) {
                    $restriction = SectionAccessRestriction::create([
                        'section_id' => $section->id,
                        'restriction_type' => 'group',
                        'restriction_id' => $groupId,
                        'access_type' => 'allow',
                    ]);
                    $createdCount++;
                    Log::info('Created restriction', [
                        'restriction_id' => $restriction->id,
                        'section_id' => $section->id,
                        'group_id' => $groupId,
                    ]);
                }

                DB::commit();

                // Verify the restrictions were saved
                $savedRestrictions = SectionAccessRestriction::where('section_id', $section->id)
                    ->where('restriction_type', 'group')
                    ->where('access_type', 'allow')
                    ->get();

                Log::info('Restrictions saved successfully', [
                    'section_id' => $section->id,
                    'saved_count' => $savedRestrictions->count(),
                    'saved_group_ids' => $savedRestrictions->pluck('restriction_id')->toArray(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث القيود بنجاح',
                    'restricted_count' => count($groupIds),
                    'saved_group_ids' => $savedRestrictions->pluck('restriction_id')->toArray(),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error syncing section restrictions', [
                    'section_id' => $section->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in syncSectionRestrictions', [
                'section_id' => $section->id,
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->errors(),
            ], 422);
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
        try {
            // Log incoming request
            Log::info('Syncing module restrictions', [
                'module_id' => $module->id,
                'request_data' => $request->all(),
            ]);

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

                // Convert string IDs to integers if needed
                $groupIds = array_map('intval', $groupIds);

                // Filter to only include valid groups
                $groupIds = array_intersect($groupIds, $validGroupIds);
                $groupIds = array_values(array_unique($groupIds)); // Remove duplicates and reindex

                Log::info('Processing module restrictions', [
                    'module_id' => $module->id,
                    'valid_group_ids' => $validGroupIds,
                    'requested_group_ids' => $validated['group_ids'] ?? [],
                    'filtered_group_ids' => $groupIds,
                ]);

                // Delete all existing group restrictions for this module
                $deletedCount = ModuleAccessRestriction::where('module_id', $module->id)
                    ->where('restriction_type', 'group')
                    ->where('access_type', 'allow')
                    ->delete();

                Log::info('Deleted existing restrictions', [
                    'module_id' => $module->id,
                    'deleted_count' => $deletedCount,
                ]);

                // Create new restrictions
                $createdCount = 0;
                foreach ($groupIds as $groupId) {
                    $restriction = ModuleAccessRestriction::create([
                        'module_id' => $module->id,
                        'restriction_type' => 'group',
                        'restriction_id' => $groupId,
                        'access_type' => 'allow',
                    ]);
                    $createdCount++;
                    Log::info('Created restriction', [
                        'restriction_id' => $restriction->id,
                        'module_id' => $module->id,
                        'group_id' => $groupId,
                    ]);
                }

                DB::commit();

                // Verify the restrictions were saved
                $savedRestrictions = ModuleAccessRestriction::where('module_id', $module->id)
                    ->where('restriction_type', 'group')
                    ->where('access_type', 'allow')
                    ->get();

                Log::info('Restrictions saved successfully', [
                    'module_id' => $module->id,
                    'saved_count' => $savedRestrictions->count(),
                    'saved_group_ids' => $savedRestrictions->pluck('restriction_id')->toArray(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث القيود بنجاح',
                    'restricted_count' => count($groupIds),
                    'saved_group_ids' => $savedRestrictions->pluck('restriction_id')->toArray(),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error syncing module restrictions', [
                    'module_id' => $module->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in syncModuleRestrictions', [
                'module_id' => $module->id,
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->errors(),
            ], 422);
        }
    }
}

