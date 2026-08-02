<?php

namespace App\Http\Controllers\Api\Student;

use App\Events\N8nWebhookEvent;
use App\Events\StudentEnrolledInCourse;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Services\AccessControlService;
use App\Services\Student\StudentCourseVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * API للطالب: عرض الكورسات المسجّل فيها مع الأقسام والدروس/الفيديوهات.
 * يتطلب مصادقة (Bearer token) عبر Laravel Sanctum.
 */
class CourseController extends Controller
{
    /**
     * كتالوج الكورسات المنشورة: متاح بدون توكن؛ مع Bearer لطالب يُعرض التسجيل والتقدم.
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
            $enrollments = collect();

            if ($user && $user->hasRole('student')) {
                $enrollments = CourseEnrollment::query()
                    ->where('student_id', $user->id)
                    ->whereIn('enrollment_status', ['active', 'completed'])
                    ->get()
                    ->keyBy('course_id');
            }

            $courses = Course::query()
                ->published()
                ->visible()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'title', 'slug', 'description', 'short_description', 'image', 'level', 'language', 'duration_in_hours', 'is_free', 'sort_order']);

            $list = $courses->map(function (Course $course) use ($enrollments) {
                $enrollment = $enrollments->get($course->id);

                return $this->catalogCoursePayload($course, $enrollment);
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
     * تفاصيل كورس واحد من الكتالوج (نفس شكل عنصر القائمة في data).
     */
    public function catalogShow(Request $request, int $id): JsonResponse
    {
        $course = Course::query()
            ->published()
            ->visible()
            ->whereKey($id)
            ->first(['id', 'title', 'slug', 'description', 'short_description', 'image', 'level', 'language', 'duration_in_hours', 'is_free', 'sort_order']);

        if (! $course) {
            return response()->json([
                'success' => false,
                'message' => 'الكورس غير موجود أو غير متاح.',
            ], 404);
        }

        $enrollment = null;
        $user = $request->user();
        if ($user && $user->hasRole('student')) {
            $enrollment = CourseEnrollment::query()
                ->where('student_id', $user->id)
                ->where('course_id', $course->id)
                ->whereIn('enrollment_status', ['active', 'completed'])
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => $this->catalogCoursePayload($course, $enrollment),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogCoursePayload(Course $course, ?CourseEnrollment $enrollment): array
    {
        $item = [
            'id' => (int) $course->id,
            'title' => (string) $course->title,
            'slug' => (string) $course->slug,
            'description' => $course->description !== null ? (string) $course->description : null,
            'short_description' => $course->short_description !== null ? (string) $course->short_description : null,
            'image' => $course->image ? course_image_url($course->image) : null,
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
    }

    /**
     * قائمة الكورسات المرتبطة بالطالب (المسجّل فيها) مع الأقسام والوحدات (دروس/فيديو).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $accessControl = new AccessControlService;
        $visibility = app(StudentCourseVisibilityService::class);
        $hiddenCourseIds = $visibility->hiddenCourseIds($user);

        $enrollmentsQuery = CourseEnrollment::query()
            ->where('student_id', $user->id)
            ->where('enrollment_status', 'active');

        if ($hiddenCourseIds !== []) {
            $enrollmentsQuery->whereNotIn('course_id', $hiddenCourseIds);
        }

        $enrollments = $enrollmentsQuery
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
            if (! $course) {
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
                                    // Log error and hide the module to avoid accidental bypass.
                                    Log::channel('single')->warning('[Student API] AccessControlService error for module', [
                                        'module_id' => $module->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }
                            // Preserve sort order
                            $accessibleModules = $accessibleModules->sortBy('sort_order')->values();
                            $section->setRelation('modules', $accessibleModules);
                            $accessibleSections->push($section);
                        }
                    } catch (\Throwable $e) {
                        // Log error and hide the section to avoid accidental bypass.
                        Log::channel('single')->warning('[Student API] AccessControlService error for section', [
                            'section_id' => $section->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                // Preserve sort order for sections
                $accessibleSections = $accessibleSections->sortBy('sort_order')->sortBy('order_index')->values();
                $course->setRelation('sections', $accessibleSections);
            } catch (\Throwable $e) {
                // Log error and return course without sections to avoid accidental bypass.
                Log::channel('single')->error('[Student API] AccessControlService error for course', [
                    'course_id' => $course->id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                $course->setRelation('sections', collect());
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
                        $content = [
                            'id' => (int) $modulable->id,
                            'title' => $modulable->title !== null ? (string) $modulable->title : null,
                            'description' => $modulable->description !== null ? (string) $modulable->description : null,
                            'content' => $modulable->content !== null ? (string) $modulable->content : null,
                            'reading_time' => isset($modulable->reading_time) ? (int) $modulable->reading_time : null,
                            // Bunny: دائماً الرابط الموقّع من getEmbedUrl() — لا نُرجع URL خام بدون token (يسبب 403).
                            'video_url' => $modulable instanceof \App\Models\Video
                                ? $modulable->getEmbedUrl()
                                : (isset($modulable->video_url) ? (string) $modulable->video_url : null),
                            'video_path' => isset($modulable->video_path) ? (string) $modulable->video_path : null,
                            'duration' => isset($modulable->duration) ? (int) $modulable->duration : null,
                            'thumbnail' => isset($modulable->thumbnail) ? (string) $modulable->thumbnail : null,
                        ];
                        if ($modulable instanceof \App\Models\Resource) {
                            $content['resource_url'] = $modulable->resource_url !== null ? (string) $modulable->resource_url : null;
                            $content['display_mode'] = $modulable->display_mode !== null ? (string) $modulable->display_mode : 'external';
                        }
                        if ($modulable instanceof \App\Models\Quiz) {
                            $quiz = $modulable;
                            $quiz->loadMissing('settings');
                            $studentId = (int) $user->id;
                            $content['quiz_id'] = (int) $quiz->id;
                            $content['course_id'] = (int) $quiz->course_id;
                            $content['quiz_type'] = $quiz->quiz_type !== null ? (string) $quiz->quiz_type : null;
                            $content['time_limit_minutes'] = $quiz->time_limit;
                            $content['max_score'] = (float) $quiz->max_score;
                            $content['passing_grade'] = (float) $quiz->passing_grade;
                            $content['attempts_allowed'] = $quiz->attempts_allowed;
                            $content['question_count'] = $quiz->getQuestionCount();
                            $content['is_available'] = $quiz->isAvailable();
                            $content['requires_password'] = (bool) ($quiz->settings && $quiz->settings->requiresPassword());
                            $content['can_attempt'] = $quiz->canAttempt($studentId);
                            $content['remaining_attempts'] = $quiz->getRemainingAttempts($studentId);
                            $currentAttempt = $quiz->attempts()
                                ->where('student_id', $studentId)
                                ->where('status', 'in_progress')
                                ->first();
                            $content['current_attempt_id'] = $currentAttempt ? (int) $currentAttempt->id : null;
                        }
                        $item['content'] = $content;
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
                'image' => $course->image ? course_image_url($course->image) : null,
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
                'pending_membership_notices' => $visibility->pendingNotices($user),
            ],
        ]);
    }

    public function enroll(Request $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($id);
            $student = $request->user();

            if (! $course->is_published || ! $course->is_visible) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الكورس غير متاح للتسجيل',
                ], 422);
            }

            $existing = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'أنت مسجل بالفعل في هذا الكورس',
                ], 422);
            }

            if ($course->enrollment_type === 'invite_only') {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الكورس يتطلب دعوة للتسجيل',
                ], 422);
            }

            if ($course->max_students) {
                $count = $course->enrollments()->where('enrollment_status', 'active')->count();
                if ($count >= $course->max_students) {
                    return response()->json([
                        'success' => false,
                        'message' => 'الكورس مكتمل العدد',
                    ], 422);
                }
            }

            if ($course->enrollment_start_date && now() < $course->enrollment_start_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يبدأ التسجيل بعد',
                ], 422);
            }

            if ($course->enrollment_end_date && now() > $course->enrollment_end_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهى وقت التسجيل',
                ], 422);
            }

            $enrollmentStatus = $course->enrollment_type === 'by_approval' ? 'pending' : 'active';

            $enrollment = CourseEnrollment::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'enrollment_date' => now(),
                'enrollment_status' => $enrollmentStatus,
                'enrolled_by' => $student->id,
                'completion_percentage' => 0,
            ]);

            DB::commit();

            if ($enrollmentStatus === 'active') {
                event(new N8nWebhookEvent('student.enrolled', [
                    'student_id' => $enrollment->student_id,
                    'student_name' => $student->name,
                    'student_email' => $student->email,
                    'course_id' => $enrollment->course_id,
                    'course_title' => $course->title,
                    'enrollment_id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date->toIso8601String(),
                    'enrolled_by' => $enrollment->enrolled_by,
                ]));
                event(new StudentEnrolledInCourse($student, $course, $enrollment));
            }

            $message = $enrollmentStatus === 'pending'
                ? 'تم إرسال طلب التسجيل بنجاح. في انتظار الموافقة'
                : 'تم التسجيل في الكورس بنجاح';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'enrollment_status' => $enrollmentStatus,
                    'enrollment_id' => $enrollment->id,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('single')->error('[Student API] enroll failed', ['e' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التسجيل',
            ], 500);
        }
    }

    public function unenroll(Request $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $course = Course::findOrFail($id);
            $student = $request->user();

            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (! $enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'أنت غير مسجل في هذا الكورس',
                ], 404);
            }

            if ($enrollment->enrollment_status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إلغاء التسجيل من كورس مكتمل',
                ], 422);
            }

            $enrollment->delete();
            DB::commit();

            event(new N8nWebhookEvent('student.unenrolled', [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'student_email' => $student->email,
                'course_id' => $course->id,
                'course_title' => $course->title,
                'unenrolled_at' => now()->toIso8601String(),
            ]));

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء التسجيل من الكورس',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء التسجيل',
            ], 500);
        }
    }
}
