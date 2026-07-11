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
 * Hides courses belonging to groups where the student has a pending membership
 * request and the group setting hide_courses_until_membership_approved is on.
 */
class StudentCourseVisibilityService
{
    public const PENDING_MESSAGE = 'طلبكم قيد المعالجة حالياً — الكورسات مخفية الآن ولن تظهر إلا بعد مراجعة طلب الانضمام والموافقة عليه من الإدارة.';

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
     * Course IDs that must be hidden for this student (linked to gated pending groups).
     *
     * @return list<int>
     */
    public function hiddenCourseIds(User $student): array
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
}
