<?php

namespace App\Services\Student;

use App\Models\Course;
use App\Models\GroupMembershipRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Hides courses from students when:
 * - they have a pending membership request on a group that gates courses until approval, or
 * - every group they belong to that links the course has pivot is_visible = false
 *   (if any membership group shows the course, it remains visible).
 */
class StudentCourseVisibilityService
{
    public const PENDING_MESSAGE = 'طلبكم قيد المعالجة حالياً — الكورسات مخفية الآن ولن تظهر إلا بعد مراجعة طلب الانضمام والموافقة عليه من الإدارة.';

    public const GROUP_COURSE_HIDDEN_MESSAGE = 'هذا الكورس مخفي عن مجموعتك حالياً ولا يمكن الوصول إليه.';

    /**
     * Pending membership requests for groups that gate course visibility.
     *
     * @return Collection<int, GroupMembershipRequest>
     */
    public function pendingGatedMemberships(User $student): Collection
    {
        return GroupMembershipRequest::query()
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->whereHas('group.registrationSettings', function ($query) {
                $query->where('hide_courses_until_membership_approved', true);
            })
            ->with(['group.registrationSettings'])
            ->get();
    }

    /**
     * Course IDs that must be hidden for this student.
     *
     * @return list<int>
     */
    public function hiddenCourseIds(User $student): array
    {
        return array_values(array_unique(array_merge(
            $this->pendingGatedHiddenCourseIds($student),
            $this->groupPivotHiddenCourseIds($student),
        )));
    }

    /**
     * @return list<int>
     */
    public function pendingGatedHiddenCourseIds(User $student): array
    {
        $groupIds = $this->pendingGatedMemberships($student)->pluck('group_id')->filter()->unique()->values();

        if ($groupIds->isEmpty()) {
            return [];
        }

        return DB::table('course_group_courses')
            ->whereIn('group_id', $groupIds->all())
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Courses that are hidden in every group membership that links them for this student.
     * If the student is also in another group where the same course is visible, it stays shown.
     *
     * @return list<int>
     */
    public function groupPivotHiddenCourseIds(User $student): array
    {
        $membershipLinks = DB::table('course_group_courses')
            ->join('course_group_members', 'course_group_members.group_id', '=', 'course_group_courses.group_id')
            ->where('course_group_members.student_id', $student->id)
            ->select([
                'course_group_courses.course_id',
                'course_group_courses.is_visible',
            ])
            ->get();

        if ($membershipLinks->isEmpty()) {
            return [];
        }

        $visibleCourseIds = [];
        $hiddenCourseIds = [];

        foreach ($membershipLinks as $link) {
            $courseId = (int) $link->course_id;
            $isVisible = filter_var($link->is_visible, FILTER_VALIDATE_BOOLEAN);

            if ($isVisible) {
                $visibleCourseIds[$courseId] = true;
            } else {
                $hiddenCourseIds[$courseId] = true;
            }
        }

        return array_values(array_filter(
            array_keys($hiddenCourseIds),
            fn (int $courseId) => ! isset($visibleCourseIds[$courseId])
        ));
    }

    /**
     * Notices to show on dashboard / my courses.
     *
     * @return list<array{group_id: int, group_name: ?string, diploma_name: ?string, message: string}>
     */
    public function pendingNotices(User $student): array
    {
        return $this->pendingGatedMemberships($student)
            ->map(function (GroupMembershipRequest $request) {
                $settings = $request->group?->registrationSettings;

                return [
                    'group_id' => (int) $request->group_id,
                    'group_name' => $request->group?->name,
                    'diploma_name' => $settings?->diploma_name,
                    'message' => self::PENDING_MESSAGE,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Builder|Relation|Collection  $enrollments
     * @return Builder|Relation|Collection
     */
    public function excludeHiddenEnrollments(Builder|Relation|Collection $enrollments, User $student): Builder|Relation|Collection
    {
        $hiddenIds = $this->hiddenCourseIds($student);

        if ($hiddenIds === []) {
            return $enrollments;
        }

        if ($enrollments instanceof Collection) {
            return $enrollments
                ->reject(fn ($enrollment) => in_array((int) $enrollment->course_id, $hiddenIds, true))
                ->values();
        }

        return $enrollments->whereNotIn('course_id', $hiddenIds);
    }

    public function isCourseHiddenForStudent(Course|int $course, User $student): bool
    {
        $courseId = $course instanceof Course ? (int) $course->id : (int) $course;

        return in_array($courseId, $this->hiddenCourseIds($student), true);
    }

    public function hideReasonForCourse(Course|int $course, User $student): ?string
    {
        $courseId = $course instanceof Course ? (int) $course->id : (int) $course;

        if (in_array($courseId, $this->pendingGatedHiddenCourseIds($student), true)) {
            return self::PENDING_MESSAGE;
        }

        if (in_array($courseId, $this->groupPivotHiddenCourseIds($student), true)) {
            return self::GROUP_COURSE_HIDDEN_MESSAGE;
        }

        return null;
    }
}
