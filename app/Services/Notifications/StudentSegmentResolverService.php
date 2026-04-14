<?php

namespace App\Services\Notifications;

use App\Models\CourseEnrollment;
use App\Models\CourseGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class StudentSegmentResolverService
{
    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, User>
     */
    public function resolve(array $filters): Collection
    {
        $query = User::query()->role('student');

        if (! empty($filters['student_ids']) && is_array($filters['student_ids'])) {
            $query->whereIn('id', $filters['student_ids']);
        }

        if (! empty($filters['course_id'])) {
            $courseId = (int) $filters['course_id'];
            $studentIds = CourseEnrollment::query()
                ->where('course_id', $courseId)
                ->when(! empty($filters['enrollment_status']), function ($q) use ($filters) {
                    $q->where('enrollment_status', $filters['enrollment_status']);
                }, function ($q) {
                    $q->where('enrollment_status', 'active');
                })
                ->pluck('student_id');

            $query->whereIn('id', $studentIds);
        }

        if (! empty($filters['group_id'])) {
            $group = CourseGroup::query()->find((int) $filters['group_id']);
            if ($group) {
                $query->whereIn('id', $group->students()->pluck('users.id'));
            } else {
                $query->whereRaw('1=0');
            }
        }

        return $query->get();
    }
}
