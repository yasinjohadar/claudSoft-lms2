<?php

namespace App\Services\Student;

use App\Models\CourseGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class StudentAccountTierService
{
    public function resolve(User $user): string
    {
        return $this->isGold($user) ? 'gold' : 'silver';
    }

    /**
     * Gold = member of at least one paid camp-classified group (is_camp).
     * Silver = only ordinary groups, or not in any group.
     */
    public function isGold(User $user): bool
    {
        return CourseGroup::query()
            ->where('is_camp', true)
            ->whereHas('students', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->exists();
    }

    public function label(string $tier): string
    {
        return match ($tier) {
            'gold' => 'ذهبي',
            'silver' => 'فضي',
            default => $tier,
        };
    }

    /**
     * @param  Builder<User>  $query
     */
    public function applyUserQueryTierFilter(Builder $query, string $tier): void
    {
        $goldStudentIds = $this->goldStudentIdsQuery();

        if ($tier === 'gold') {
            $query->whereIn('id', $goldStudentIds);
        } elseif ($tier === 'silver') {
            $query->whereNotIn('id', $goldStudentIds);
        }
    }

    /**
     * @param  iterable<int, User|int>  $users
     * @return array<int, string>
     */
    public function tiersForUsers(iterable $users): array
    {
        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user instanceof User ? (int) $user->id : (int) $user;
        }
        $userIds = array_values(array_unique(array_filter($userIds)));

        if ($userIds === []) {
            return [];
        }

        $goldIds = $this->goldStudentIdsQuery()
            ->whereIn('course_group_members.student_id', $userIds)
            ->pluck('course_group_members.student_id')
            ->unique()
            ->flip()
            ->all();

        $tiers = [];
        foreach ($userIds as $id) {
            $tiers[$id] = isset($goldIds[$id]) ? 'gold' : 'silver';
        }

        return $tiers;
    }

    private function goldStudentIdsQuery(): QueryBuilder
    {
        return DB::table('course_group_members')
            ->join('course_groups', 'course_groups.id', '=', 'course_group_members.group_id')
            ->where('course_groups.is_camp', true)
            ->whereNull('course_groups.deleted_at')
            ->select('course_group_members.student_id');
    }
}
