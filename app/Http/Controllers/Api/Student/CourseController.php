<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Services\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * API للطالب: عرض الكورسات المسجّل فيها مع الأقسام والدروس/الفيديوهات.
 * يتطلب مصادقة (Bearer token) عبر Laravel Sanctum.
 */
class CourseController extends Controller
{
    /**
     * كتالوج الكورسات: كل الكورسات المنشورة والمرئية (للعرض في Flutter حتى لو لم يكن الطالب مسجّلاً).
     * يتضمّن is_enrolled و enrollment إن كان الطالب مسجّلاً في الكورس.
     */
    public function catalog(Request $request): JsonResponse
    {
        Log::channel('single')->info('[Student API] catalog: request started', [
            'has_user' => $request->user() !== null,
            'user_id' => $request->user()?->id,
            'origin' => $request->header('Origin'),
        ]);

        try {
            $user = $request->user();

            $courses = Course::query()
                ->published()
                ->visible()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'title', 'slug', 'description', 'short_description', 'image', 'level', 'language', 'duration_in_hours', 'is_free', 'sort_order']);

            $enrollments = CourseEnrollment::query()
                ->where('student_id', $user->id)
                ->whereIn('enrollment_status', ['active', 'completed'])
                ->get()
                ->keyBy('course_id');

            $list = $courses->map(function (Course $course) use ($enrollments) {
            $enrollment = $enrollments->get($course->id);
            $item = [
                'id' => (int) $course->id,
                'title' => (string) $course->title,
                'slug' => (string) $course->slug,
                'description' => $course->description !== null ? (string) $course->description : null,
                'short_description' => $course->short_description !== null ? (string) $course->short_description : null,
                'image' => $course->image ? url($course->image) : null,
                'level' => $course->level !== null ? (string) $course->level : null,
                'language' => $course->language !== null ? (string) $course->language : null,
                'duration_in_hours' => $course->duration_in_hours !== null ? (float) $course->duration_in_hours : null,
                'is_free' => (bool) $course->is_free,
                'sort_order' => $course->sort_order !== null ? (int) $course->sort_order : null,
                'is_enrolled' => (bool) $enrollment,
                'enrollment' => null,
            ];
            if ($enrollment) {
                $item['enrollment'] = [
                    'enrollment_id' => (int) $enrollment->id,
                    'enrollment_status' => (string) $enrollment->enrollment_status,
                    'completion_percentage' => (float) $enrollment->completion_percentage,
                    'last_accessed_at' => $enrollment->last_accessed_at?->toIso8601String(),
                ];
            }
            return $item;
        })->values();

            Log::channel('single')->info('[Student API] catalog: success', [
                'courses_count' => $list->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'courses' => $list,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::channel('single')->error('[Student API] catalog: exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * قائمة الكورسات المرتبطة بالطالب (المسجّل فيها) مع الأقسام والوحدات (دروس/فيديو).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $accessControl = new AccessControlService();

        $enrollments = CourseEnrollment::query()
            ->where('student_id', $user->id)
            ->where('enrollment_status', 'active')
            ->with([
                'course' => function ($q) {
                    $q->select([
                        'id', 'title', 'slug', 'description', 'short_description',
                        'image', 'level', 'language', 'duration_in_hours', 'is_free',
                        'is_published', 'is_visible', 'sort_order',
                    ]);
                },
                'course.sections' => function ($q) {
                    $q->where('is_visible', true)
                        ->orderBy('sort_order')
                        ->orderBy('order_index');
                },
                'course.sections.course', // Load course relation for AccessControlService
                'course.sections.modules' => function ($q) {
                    $q->where('course_modules.is_visible', true)
                        ->orderBy('sort_order');
                },
                'course.sections.modules.section', // Load section relation for AccessControlService
                'course.sections.modules.modulable',
            ])
            ->orderByDesc('last_accessed_at')
            ->get();

        $courses = $enrollments->map(function (CourseEnrollment $enrollment) use ($user, $accessControl) {
            $course = $enrollment->course;
            if (!$course) {
                return null;
            }

            // Filter sections and modules based on access restrictions (like web dashboard)
            try {
                $accessibleSections = collect();
                foreach ($course->sections as $section) {
                    try {
                        $sectionAccess = $accessControl->canAccessSection($section, $user);
                        if ($sectionAccess['can_access']) {
                            // Filter modules within accessible sections
                            $accessibleModules = collect();
                            foreach ($section->modules as $module) {
                                try {
                                    $moduleAccess = $accessControl->canAccessModule($module, $user);
                                    if ($moduleAccess['can_access']) {
                                        $accessibleModules->push($module);
                                    }
                                } catch (\Throwable $e) {
                                    // Log error but continue (fail-safe)
                                    Log::channel('single')->warning('[Student API] AccessControlService error for module', [
                                        'module_id' => $module->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                    // Include module if access check fails (fail-safe)
                                    $accessibleModules->push($module);
                                }
                            }
                            // Preserve sort order
                            $accessibleModules = $accessibleModules->sortBy('sort_order')->values();
                            $section->setRelation('modules', $accessibleModules);
                            $accessibleSections->push($section);
                        }
                    } catch (\Throwable $e) {
                        // Log error but continue (fail-safe)
                        Log::channel('single')->warning('[Student API] AccessControlService error for section', [
                            'section_id' => $section->id,
                            'error' => $e->getMessage(),
                        ]);
                        // Include section if access check fails (fail-safe)
                        $accessibleSections->push($section);
                    }
                }
                // Preserve sort order for sections
                $accessibleSections = $accessibleSections->sortBy('sort_order')->sortBy('order_index')->values();
                $course->setRelation('sections', $accessibleSections);
            } catch (\Throwable $e) {
                // Log error but continue with all sections (fail-safe)
                Log::channel('single')->error('[Student API] AccessControlService error for course', [
                    'course_id' => $course->id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                // Keep all sections if access control fails (fail-safe)
            }

            $sections = $course->sections->map(function ($section) use ($user) {
                $modules = $section->modules->map(function ($module) use ($user) {
                    $modulable = $module->modulable;
                    $completion = $module->getCompletionFor($user);

                    $item = [
                        'id' => (int) $module->id,
                        'section_id' => (int) $module->section_id,
                        'module_type' => (string) $module->module_type,
                        'title' => $module->title !== null ? (string) $module->title : null,
                        'description' => $module->description !== null ? (string) $module->description : null,
                        'sort_order' => $module->sort_order !== null ? (int) $module->sort_order : null,
                        'estimated_duration' => $module->estimated_duration !== null ? (int) $module->estimated_duration : null,
                        'completion_type' => $module->completion_type !== null ? (string) $module->completion_type : null,
                        'is_completed' => (bool) ($completion && $completion->completion_status === 'completed'),
                        'content' => null,
                    ];

                    if ($modulable) {
                        $item['content'] = [
                            'id' => (int) $modulable->id,
                            'title' => $modulable->title !== null ? (string) $modulable->title : null,
                            'description' => $modulable->description !== null ? (string) $modulable->description : null,
                            'content' => $modulable->content !== null ? (string) $modulable->content : null,
                            'reading_time' => $modulable->reading_time !== null ? (int) $modulable->reading_time : null,
                            'video_url' => $modulable->video_url !== null ? (string) $modulable->video_url : null,
                            'video_path' => $modulable->video_path !== null ? (string) $modulable->video_path : null,
                            'duration' => $modulable->duration !== null ? (int) $modulable->duration : null,
                            'thumbnail' => $modulable->thumbnail !== null ? (string) $modulable->thumbnail : null,
                        ];
                    }

                    return $item;
                })->values();

                return [
                    'id' => (int) $section->id,
                    'course_id' => (int) $section->course_id,
                    'title' => $section->title !== null ? (string) $section->title : null,
                    'description' => $section->description !== null ? (string) $section->description : null,
                    'sort_order' => $section->sort_order !== null ? (int) $section->sort_order : null,
                    'order_index' => $section->order_index !== null ? (int) $section->order_index : null,
                    'lessons' => $modules,
                ];
            })->values();

            return [
                'id' => (int) $course->id,
                'title' => (string) $course->title,
                'slug' => (string) $course->slug,
                'description' => $course->description !== null ? (string) $course->description : null,
                'short_description' => $course->short_description !== null ? (string) $course->short_description : null,
                'image' => $course->image ? url($course->image) : null,
                'level' => $course->level !== null ? (string) $course->level : null,
                'language' => $course->language !== null ? (string) $course->language : null,
                'duration_in_hours' => $course->duration_in_hours !== null ? (float) $course->duration_in_hours : null,
                'is_free' => (bool) $course->is_free,
                'enrollment' => [
                    'enrollment_id' => (int) $enrollment->id,
                    'enrollment_status' => (string) $enrollment->enrollment_status,
                    'completion_percentage' => (float) $enrollment->completion_percentage,
                    'last_accessed_at' => $enrollment->last_accessed_at?->toIso8601String(),
                ],
                'sections' => $sections,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'courses' => $courses,
            ],
        ]);
    }
}
