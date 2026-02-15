<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'short_description' => $course->short_description,
                'image' => $course->image ? url($course->image) : null,
                'level' => $course->level,
                'language' => $course->language,
                'duration_in_hours' => $course->duration_in_hours,
                'is_free' => $course->is_free,
                'is_enrolled' => (bool) $enrollment,
                'enrollment' => null,
            ];
            if ($enrollment) {
                $item['enrollment'] = [
                    'enrollment_id' => $enrollment->id,
                    'enrollment_status' => $enrollment->enrollment_status,
                    'completion_percentage' => (float) $enrollment->completion_percentage,
                    'last_accessed_at' => $enrollment->last_accessed_at?->toIso8601String(),
                ];
            }
            return $item;
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'courses' => $list,
            ],
        ]);
    }

    /**
     * قائمة الكورسات المرتبطة بالطالب (المسجّل فيها) مع الأقسام والوحدات (دروس/فيديو).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

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
                        ->orderBy('order_index')
                        ->select(['id', 'course_id', 'title', 'description', 'sort_order', 'order_index', 'is_visible']);
                },
                'course.sections.modules' => function ($q) {
                    $q->where('course_modules.is_visible', true)
                        ->orderBy('sort_order')
                        ->select([
                            'course_modules.id', 'course_modules.course_id', 'course_modules.section_id',
                            'course_modules.module_type', 'course_modules.modulable_id', 'course_modules.modulable_type',
                            'course_modules.title', 'course_modules.description', 'course_modules.sort_order',
                            'course_modules.estimated_duration', 'course_modules.completion_type',
                        ]);
                },
                'course.sections.modules.modulable',
            ])
            ->orderByDesc('last_accessed_at')
            ->get();

        $courses = $enrollments->map(function (CourseEnrollment $enrollment) use ($user) {
            $course = $enrollment->course;
            if (!$course) {
                return null;
            }

            $sections = $course->sections->map(function ($section) use ($user) {
                $modules = $section->modules->map(function ($module) use ($user) {
                    $modulable = $module->modulable;
                    $completion = $module->getCompletionFor($user);

                    $item = [
                        'id' => $module->id,
                        'section_id' => $module->section_id,
                        'module_type' => $module->module_type,
                        'title' => $module->title,
                        'description' => $module->description,
                        'sort_order' => $module->sort_order,
                        'estimated_duration' => $module->estimated_duration,
                        'completion_type' => $module->completion_type,
                        'is_completed' => $completion && $completion->completion_status === 'completed',
                        'content' => null,
                    ];

                    if ($modulable) {
                        $item['content'] = [
                            'id' => $modulable->id,
                            'title' => $modulable->title ?? null,
                            'description' => $modulable->description ?? null,
                            'content' => $modulable->content ?? null,
                            'reading_time' => $modulable->reading_time ?? null,
                            'video_url' => $modulable->video_url ?? null,
                            'video_path' => $modulable->video_path ?? null,
                            'duration' => $modulable->duration ?? null,
                            'thumbnail' => $modulable->thumbnail ?? null,
                        ];
                    }

                    return $item;
                })->values();

                return [
                    'id' => $section->id,
                    'course_id' => $section->course_id,
                    'title' => $section->title,
                    'description' => $section->description,
                    'sort_order' => $section->sort_order,
                    'order_index' => $section->order_index,
                    'lessons' => $modules,
                ];
            })->values();

            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'short_description' => $course->short_description,
                'image' => $course->image ? url($course->image) : null,
                'level' => $course->level,
                'language' => $course->language,
                'duration_in_hours' => $course->duration_in_hours,
                'is_free' => $course->is_free,
                'enrollment' => [
                    'enrollment_id' => $enrollment->id,
                    'enrollment_status' => $enrollment->enrollment_status,
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
