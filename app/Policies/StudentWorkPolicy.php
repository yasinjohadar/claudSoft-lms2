<?php

namespace App\Policies;

use App\Models\StudentWork;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentWorkPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'student']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StudentWork $studentWork): bool
    {
        // Admin can view all, students can only view their own
        return $user->hasRole('admin') || $user->id === $studentWork->student_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'student']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StudentWork $studentWork): bool
    {
        // Admin can update all, students can only update their own if not approved
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id === $studentWork->student_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StudentWork $studentWork): bool
    {
        // Admin can delete all, students can only delete their own if draft
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->id === $studentWork->student_id && $studentWork->status === 'draft';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StudentWork $studentWork): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StudentWork $studentWork): bool
    {
        return $user->hasRole('admin');
    }
}
