<?php

namespace App\Services\Gamification;

use App\Models\Badge;
use App\Models\CourseEnrollment;
use App\Models\CourseGroup;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\Reports\StudentWeeklyReportService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BadgeManualAwardService
{
    public const TARGET_TYPES = [
        'single',
        'multiple',
        'group',
        'multiple_groups',
        'course',
        'course_group',
    ];

    public const TARGET_TYPE_LABELS = [
        'single' => 'طالب واحد',
        'multiple' => 'عدة طلاب',
        'group' => 'مجموعة كاملة',
        'multiple_groups' => 'عدة مجموعات',
        'course' => 'كورس كامل (كل المسجّلين)',
        'course_group' => 'كورس + مجموعة',
    ];

    public function __construct(
        protected BadgeService $badgeService,
        protected StudentWeeklyReportService $weeklyReportService
    ) {}

    /**
     * حل قائمة الطلاب المستهدفين حسب نوع الاستهداف
     */
    public function resolveTargetStudents(string $targetType, array $params): Collection
    {
        $students = match ($targetType) {
            'single' => $this->resolveSingle($params),
            'multiple' => $this->resolveMultiple($params),
            'group' => $this->resolveGroup($params),
            'multiple_groups' => $this->resolveMultipleGroups($params),
            'course' => $this->resolveCourse($params),
            'course_group' => $this->resolveCourseGroup($params),
            default => throw ValidationException::withMessages([
                'target_type' => 'نوع الاستهداف غير مدعوم.',
            ]),
        };

        return $this->filterStudentsOnly($students)->unique('id')->values();
    }

    /**
     * معاينة عدد المستهدفين ومن يملك الشارة مسبقاً
     */
    public function preview(Badge $badge, string $targetType, array $params): array
    {
        $students = $this->resolveTargetStudents($targetType, $params);
        $total = $students->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'already_have' => 0,
                'will_award' => 0,
            ];
        }

        $alreadyHave = UserBadge::query()
            ->where('badge_id', $badge->id)
            ->whereIn('user_id', $students->pluck('id'))
            ->count();

        return [
            'total' => $total,
            'already_have' => $alreadyHave,
            'will_award' => max(0, $total - $alreadyHave),
        ];
    }

    /**
     * منح الشارة لمجموعة طلاب
     */
    public function awardToStudents(Badge $badge, Collection $students, array $metadata = []): array
    {
        $awarded = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($students as $student) {
            try {
                $result = $this->badgeService->awardBadge(
                    $student,
                    $badge,
                    null,
                    null,
                    $metadata
                );

                if ($result) {
                    $awarded++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $failed++;
                report($e);
            }
        }

        return [
            'awarded' => $awarded,
            'skipped' => $skipped,
            'failed' => $failed,
            'total' => $students->count(),
        ];
    }

    protected function resolveSingle(array $params): Collection
    {
        $userId = (int) ($params['user_id'] ?? 0);

        if ($userId <= 0) {
            throw ValidationException::withMessages([
                'user_id' => 'يرجى اختيار طالب.',
            ]);
        }

        $user = User::query()->find($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => 'الطالب المحدد غير موجود.',
            ]);
        }

        return collect([$user]);
    }

    protected function resolveMultiple(array $params): Collection
    {
        $userIds = collect($params['user_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (count($userIds) === 0) {
            throw ValidationException::withMessages([
                'user_ids' => 'يرجى اختيار طالب واحد على الأقل.',
            ]);
        }

        return User::query()->whereIn('id', $userIds)->get();
    }

    public function targetPayloadFromRequest(array $input): array
    {
        return [
            'user_id' => $input['user_id'] ?? null,
            'user_ids' => $input['user_ids'] ?? [],
            'group_id' => $input['group_id'] ?? null,
            'group_ids' => $input['group_ids'] ?? [],
            'course_id' => $input['course_id'] ?? null,
        ];
    }

    protected function resolveGroup(array $params): Collection
    {
        $groupId = (int) ($params['group_id'] ?? 0);

        if ($groupId <= 0) {
            throw ValidationException::withMessages([
                'group_id' => 'يرجى اختيار مجموعة.',
            ]);
        }

        $group = CourseGroup::query()->find($groupId);

        if (!$group) {
            throw ValidationException::withMessages([
                'group_id' => 'المجموعة المحددة غير موجودة.',
            ]);
        }

        return $group->students()->orderBy('name')->get();
    }

    protected function resolveMultipleGroups(array $params): Collection
    {
        $groupIds = collect($params['group_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($groupIds === []) {
            throw ValidationException::withMessages([
                'group_ids' => 'يرجى اختيار مجموعة واحدة على الأقل.',
            ]);
        }

        $students = collect();

        foreach ($groupIds as $groupId) {
            $group = CourseGroup::query()->find($groupId);

            if ($group) {
                $students = $students->merge($group->students()->orderBy('name')->get());
            }
        }

        return $students;
    }

    protected function resolveCourse(array $params): Collection
    {
        $courseId = (int) ($params['course_id'] ?? 0);

        if ($courseId <= 0) {
            throw ValidationException::withMessages([
                'course_id' => 'يرجى اختيار كورس.',
            ]);
        }

        $studentIds = CourseEnrollment::query()
            ->where('course_id', $courseId)
            ->pluck('student_id');

        return User::query()
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get();
    }

    protected function resolveCourseGroup(array $params): Collection
    {
        $courseId = (int) ($params['course_id'] ?? 0);
        $groupId = (int) ($params['group_id'] ?? 0);

        return $this->weeklyReportService->resolveStudentsByCourseAndGroup($courseId, $groupId);
    }

    protected function filterStudentsOnly(Collection $users): Collection
    {
        return $users->filter(function (User $user) {
            return $user->hasRole('student');
        })->values();
    }
}
