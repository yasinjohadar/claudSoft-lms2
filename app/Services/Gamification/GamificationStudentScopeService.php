<?php

namespace App\Services\Gamification;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class GamificationStudentScopeService
{
    public function applyUserIdScope(Builder $query, string $userIdColumn, int $courseId = 0, int $groupId = 0): void
    {
        $subQuery = $this->buildStudentIdsSubquery($courseId, $groupId);

        if ($subQuery === null) {
            return;
        }

        $query->whereIn($userIdColumn, $subQuery);
    }

    /**
     * @return QueryBuilder|null null = no scope restriction (all students)
     */
    public function buildStudentIdsSubquery(int $courseId = 0, int $groupId = 0): ?QueryBuilder
    {
        if ($courseId <= 0 && $groupId <= 0) {
            return null;
        }

        if ($courseId > 0 && $groupId > 0) {
            $isLinked = DB::table('course_group_courses')
                ->where('course_id', $courseId)
                ->where('group_id', $groupId)
                ->exists();

            if (! $isLinked) {
                return DB::query()->selectRaw('0 as student_id')->whereRaw('1 = 0');
            }

            return DB::table('course_group_members')
                ->select('student_id')
                ->where('group_id', $groupId);
        }

        if ($courseId > 0) {
            return DB::table('course_enrollments')
                ->select('student_id')
                ->where('course_id', $courseId);
        }

        return DB::table('course_group_members')
            ->select('student_id')
            ->where('group_id', $groupId);
    }

    public function countStudentsInScope(int $courseId = 0, int $groupId = 0): int
    {
        return $this->studentsInScopeQuery($courseId, $groupId)->count();
    }

    public function scopedStudentIdsSubquery(int $courseId = 0, int $groupId = 0): QueryBuilder
    {
        return $this->studentsInScopeQuery($courseId, $groupId)
            ->select('users.id')
            ->toBase();
    }

    public function studentsInScopeQuery(int $courseId = 0, int $groupId = 0): Builder
    {
        $query = User::query()
            ->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', 'student'));

        $subQuery = $this->buildStudentIdsSubquery($courseId, $groupId);

        if ($subQuery !== null) {
            $query->whereIn('id', $subQuery);
        }

        return $query;
    }

    public function resolveGroupOptions(int $courseId = 0)
    {
        if ($courseId > 0) {
            $course = Course::query()->find($courseId);

            return $course
                ? $course->groups()->orderBy('course_groups.name')->get()
                : collect();
        }

        return CourseGroup::query()->orderBy('name')->get(['id', 'name']);
    }

    public function isCourseGroupLinked(int $courseId, int $groupId): bool
    {
        if ($courseId <= 0 || $groupId <= 0) {
            return false;
        }

        return DB::table('course_group_courses')
            ->where('course_id', $courseId)
            ->where('group_id', $groupId)
            ->exists();
    }
}
