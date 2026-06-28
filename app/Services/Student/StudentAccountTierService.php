<?php

namespace App\Services\Student;

use App\Models\CampEnrollment;
use App\Models\User;

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
}
