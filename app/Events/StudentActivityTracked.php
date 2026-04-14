<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentActivityTracked
{
    use Dispatchable, SerializesModels;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public User $user,
        public string $activityKey,
        public array $context = []
    ) {}
}
