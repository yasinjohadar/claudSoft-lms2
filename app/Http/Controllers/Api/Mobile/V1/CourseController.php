<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Resources\Mobile\V1\CourseDetailResource;
use App\Http\Resources\Mobile\V1\EnrolledCourseResource;
use App\Models\CourseEnrollment;
use App\Models\ModuleCompletion;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\Student\StudentCourseVisibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * الكورسات المسجّل فيها الطالب (نشطة أو مكتملة) لتطبيق الجوال.
 */
class CourseController extends BaseMobileController
{
    private const STATUSES = ['active', 'completed'];

    public function __construct(
        protected StudentCourseVisibilityService $visibility,
        protected AccessControlService $accessControl,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:active,completed'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $enrollments = $this->enrollmentsQuery($request->user())
            ->when($request->input('status'), fn (Builder $q, string $status) => $q->where('enrollment_status', $status))
            ->with([
                'course:id,title,slug,short_description,image,level,duration_in_hours,instructor_id',
                'course.instructor:id,name,name_ar',
            ])
            ->orderByRaw('last_accessed_at IS NULL')
            ->orderByDesc('last_accessed_at')
            ->orderByDesc('enrollment_date')
            ->paginate((int) $request->input('per_page', 15));

        return $this->success(
            EnrolledCourseResource::collection($enrollments->getCollection())->resolve($request),
            null,
            $this->paginationMeta($enrollments)
        );
    }

    public function show(Request $request, int $course): JsonResponse
    {
        $user = $request->user();

        $enrollment = $this->enrollmentsQuery($user)
            ->where('course_id', $course)
            ->with([
                'course.instructor:id,name,name_ar',
                'course.sections' => fn ($q) => $q->where('is_visible', true)->orderBy('sort_order')->orderBy('order_index'),
                'course.sections.course',
                'course.sections.modules' => fn ($q) => $q->where('course_modules.is_visible', true)->orderBy('sort_order'),
                'course.sections.modules.section',
            ])
            ->first();

        if (! $enrollment) {
            return $this->error(__('mobile.course_not_found'), 404);
        }

        $this->filterAccessibleContent($enrollment, $user);

        $moduleIds = $enrollment->course->sections->flatMap(fn ($s) => $s->modules->pluck('id'));
        $completions = ModuleCompletion::query()
            ->where('student_id', $user->id)
            ->whereIn('module_id', $moduleIds)
            ->get(['module_id', 'completion_status'])
            ->keyBy('module_id');

        $enrollment->touchLastAccessed();

        return $this->success((new CourseDetailResource($enrollment))->withCompletions($completions));
    }

    private function enrollmentsQuery(User $user): Builder
    {
        $hiddenCourseIds = $this->visibility->hiddenCourseIds($user);

        return CourseEnrollment::query()
            ->where('student_id', $user->id)
            ->whereIn('enrollment_status', self::STATUSES)
            ->whereHas('course')
            ->when($hiddenCourseIds !== [], fn (Builder $q) => $q->whereNotIn('course_id', $hiddenCourseIds));
    }

    /**
     * يخفي الأقسام والوحدات غير المتاحة للطالب (نفس منطق لوحة الطالب).
     * أي خطأ في التحقق يؤدي لإخفاء العنصر تجنباً لتجاوز القيود.
     */
    private function filterAccessibleContent(CourseEnrollment $enrollment, User $user): void
    {
        $sections = $enrollment->course->sections->filter(function ($section) use ($user) {
            try {
                if (! $this->accessControl->canAccessSection($section, $user)['can_access']) {
                    return false;
                }
            } catch (\Throwable $e) {
                Log::warning('Mobile API: section access check failed', ['section_id' => $section->id, 'error' => $e->getMessage()]);

                return false;
            }

            $section->setRelation('modules', $section->modules->filter(function ($module) use ($user) {
                try {
                    return $this->accessControl->canAccessModule($module, $user)['can_access'];
                } catch (\Throwable $e) {
                    Log::warning('Mobile API: module access check failed', ['module_id' => $module->id, 'error' => $e->getMessage()]);

                    return false;
                }
            })->values());

            return true;
        })->values();

        $enrollment->course->setRelation('sections', $sections);
    }
}
