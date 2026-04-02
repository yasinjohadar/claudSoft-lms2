<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
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
                'all_groups' => $allGroups->map(function ($group) {
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
                'all_groups' => $allGroups->map(function ($group) {
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
     * List course groups for bulk restrictions UI (no module context).
     */
    public function getCourseRestrictionGroups(Course $course)
    {
        try {
            $allGroups = $course->groups()->get();

            return response()->json([
                'success' => true,
                'all_groups' => $allGroups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                    ];
                })->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل المجموعات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Coverage of each course group across selected modules (for bulk merge UI).
     */
    public function getCourseRestrictionBulkState(Request $request, Course $course)
    {
        try {
            $validated = $request->validate([
                'module_ids' => 'required|array|min:1',
                'module_ids.*' => 'integer|exists:course_modules,id',
            ]);

            $moduleIds = array_values(array_unique(array_map('intval', $validated['module_ids'])));
            $modules = CourseModule::where('course_id', $course->id)
                ->whereIn('id', $moduleIds)
                ->get();

            if ($modules->count() !== count($moduleIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'بعض الوحدات المحددة لا تتبع هذا الكورس.',
                ], 422);
            }

            $n = $modules->count();
            $allGroups = $course->groups()->get();

            $counts = ModuleAccessRestriction::query()
                ->whereIn('module_id', $moduleIds)
                ->where('restriction_type', 'group')
                ->where('access_type', 'allow')
                ->selectRaw('restriction_id, COUNT(DISTINCT module_id) as module_count')
                ->groupBy('restriction_id')
                ->pluck('module_count', 'restriction_id');

            $groupCoverage = $allGroups->map(function ($group) use ($counts, $n) {
                $c = (int) ($counts[$group->id] ?? 0);

                return [
                    'id' => $group->id,
                    'all_have' => $n > 0 && $c === $n,
                    'any_have' => $c > 0,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'all_groups' => $allGroups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                    ];
                })->values(),
                'group_coverage' => $groupCoverage,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getCourseRestrictionBulkState', [
                'course_id' => $course->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Merge group allow restrictions on many modules: add and/or remove only (preserve other groups).
     */
    public function syncBulkModuleRestrictions(Request $request, Course $course)
    {
        try {
            Log::info('Syncing bulk module restrictions (merge)', [
                'course_id' => $course->id,
                'request_data' => $request->all(),
            ]);

            $validated = $request->validate([
                'module_ids' => 'required|array|min:1',
                'module_ids.*' => 'integer|exists:course_modules,id',
                'add_group_ids' => 'nullable|array',
                'add_group_ids.*' => 'integer|exists:course_groups,id',
                'remove_group_ids' => 'nullable|array',
                'remove_group_ids.*' => 'integer|exists:course_groups,id',
            ]);

            $moduleIds = array_values(array_unique(array_map('intval', $validated['module_ids'])));
            $modules = CourseModule::where('course_id', $course->id)
                ->whereIn('id', $moduleIds)
                ->get();

            if ($modules->count() !== count($moduleIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'بعض الوحدات المحددة لا تتبع هذا الكورس.',
                ], 422);
            }

            $addIds = $this->normalizeGroupIdsForCourse($course, $validated['add_group_ids'] ?? null);
            $removeIds = $this->normalizeGroupIdsForCourse($course, $validated['remove_group_ids'] ?? null);
            $addIds = array_values(array_diff($addIds, $removeIds));

            if ($addIds === [] && $removeIds === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يُرسل أي تغيير (إضافة أو إزالة).',
                ], 422);
            }

            DB::beginTransaction();
            try {
                foreach ($modules as $module) {
                    foreach ($removeIds as $gid) {
                        ModuleAccessRestriction::where('module_id', $module->id)
                            ->where('restriction_type', 'group')
                            ->where('access_type', 'allow')
                            ->where('restriction_id', $gid)
                            ->delete();
                    }
                    foreach ($addIds as $gid) {
                        ModuleAccessRestriction::firstOrCreate(
                            [
                                'module_id' => $module->id,
                                'restriction_type' => 'group',
                                'restriction_id' => $gid,
                                'access_type' => 'allow',
                            ],
                            []
                        );
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $perModuleGroups = [];
            foreach ($modules as $module) {
                $perModuleGroups[(string) $module->id] = $this->getModuleGroupsPayload($module);
            }

            Log::info('Bulk module restrictions merged', [
                'course_id' => $course->id,
                'module_ids' => $moduleIds,
                'add_group_ids' => $addIds,
                'remove_group_ids' => $removeIds,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث قيود ' . count($moduleIds) . ' وحدة بنجاح',
                'updated_module_ids' => $moduleIds,
                'add_group_ids' => $addIds,
                'remove_group_ids' => $removeIds,
                'per_module_groups' => $perModuleGroups,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات: ' . implode(', ', $e->validator->errors()->all()),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in syncBulkModuleRestrictions', [
                'course_id' => $course->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
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

                // Verify the restrictions were saved and get group information
                $savedRestrictions = SectionAccessRestriction::where('section_id', $section->id)
                    ->where('restriction_type', 'group')
                    ->where('access_type', 'allow')
                    ->with('group')
                    ->get();

                // Get group names for response
                $savedGroupIds = $savedRestrictions->pluck('restriction_id')->toArray();
                $groups = CourseGroup::whereIn('id', $savedGroupIds)->get(['id', 'name', 'description']);

                Log::info('Restrictions saved successfully', [
                    'section_id' => $section->id,
                    'saved_count' => $savedRestrictions->count(),
                    'saved_group_ids' => $savedGroupIds,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث القيود بنجاح',
                    'restricted_count' => count($groupIds),
                    'saved_group_ids' => $savedGroupIds,
                    'groups' => $groups->map(function ($group) {
                        return [
                            'id' => $group->id,
                            'name' => $group->name,
                            'description' => $group->description,
                        ];
                    })->toArray(),
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
                $course = $module->course;
                $groupIds = $this->normalizeGroupIdsForCourse($course, $validated['group_ids'] ?? null);

                Log::info('Processing module restrictions', [
                    'module_id' => $module->id,
                    'filtered_group_ids' => $groupIds,
                ]);

                $result = $this->replaceModuleGroupAllows($module, $groupIds);

                DB::commit();

                Log::info('Restrictions saved successfully', [
                    'module_id' => $module->id,
                    'saved_group_ids' => $result['saved_group_ids'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث القيود بنجاح',
                    'restricted_count' => count($groupIds),
                    'saved_group_ids' => $result['saved_group_ids'],
                    'groups' => $result['groups'],
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

    /**
     * @return array<int>
     */
    private function normalizeGroupIdsForCourse(Course $course, ?array $requestGroupIds): array
    {
        // get() ثم pluck: تجنب SELECT id الغامض (جدول course_group_courses يحتوي id أيضاً)
        $validGroupIds = $course->groups()->get()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $raw = array_map('intval', $requestGroupIds ?? []);

        return array_values(array_unique(array_intersect($raw, $validGroupIds)));
    }

    /**
     * Replace group allow restrictions for one module (no transaction).
     *
     * @param  array<int>  $filteredGroupIds
     * @return array{saved_group_ids: array<int>, groups: array<int, array<string, mixed>>}
     */
    private function replaceModuleGroupAllows(CourseModule $module, array $filteredGroupIds): array
    {
        ModuleAccessRestriction::where('module_id', $module->id)
            ->where('restriction_type', 'group')
            ->where('access_type', 'allow')
            ->delete();

        foreach ($filteredGroupIds as $groupId) {
            ModuleAccessRestriction::create([
                'module_id' => $module->id,
                'restriction_type' => 'group',
                'restriction_id' => $groupId,
                'access_type' => 'allow',
            ]);
        }

        $savedGroupIds = ModuleAccessRestriction::where('module_id', $module->id)
            ->where('restriction_type', 'group')
            ->where('access_type', 'allow')
            ->pluck('restriction_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return [
            'saved_group_ids' => $savedGroupIds,
            'groups' => $this->groupsPayloadForIds($savedGroupIds),
        ];
    }

    /**
     * Group allow payload for one module (for badges / per_module_groups).
     *
     * @return array<int, array<string, mixed>>
     */
    private function getModuleGroupsPayload(CourseModule $module): array
    {
        $savedGroupIds = ModuleAccessRestriction::where('module_id', $module->id)
            ->where('restriction_type', 'group')
            ->where('access_type', 'allow')
            ->pluck('restriction_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return $this->groupsPayloadForIds($savedGroupIds);
    }

    /**
     * @param  array<int>  $groupIds
     * @return array<int, array<string, mixed>>
     */
    private function groupsPayloadForIds(array $groupIds): array
    {
        if ($groupIds === []) {
            return [];
        }

        return CourseGroup::whereIn('id', $groupIds)
            ->get(['id', 'name', 'description'])
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                ];
            })
            ->values()
            ->all();
    }
}
