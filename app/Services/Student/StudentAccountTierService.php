<?php

namespace App\Services\Student;

use App\Models\CampEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class StudentAccountTierService
{
    public function resolve(User $user): string
    {
        return $this->isGold($user) ? 'gold' : 'silver';
    }

    public function isGold(User $user): bool
    {
        return CampEnrollment::query()
            ->where('student_id', $user->id)
            ->approved()
            ->whereHas('camp')
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
        $goldSubquery = CampEnrollment::query()
            ->approved()
            ->whereHas('camp')
            ->select('student_id');

        if ($tier === 'gold') {
            $query->whereIn('id', $goldSubquery);
        } elseif ($tier === 'silver') {
            $query->whereNotIn('id', $goldSubquery);
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

        $goldIds = CampEnrollment::query()
            ->approved()
            ->whereHas('camp')
            ->whereIn('student_id', $userIds)
            ->pluck('student_id')
            ->unique()
            ->flip()
            ->all();

        $tiers = [];
        foreach ($userIds as $id) {
            $tiers[$id] = isset($goldIds[$id]) ? 'gold' : 'silver';
        }

        return $tiers;
    }
}
