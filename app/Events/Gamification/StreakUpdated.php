<?php

namespace App\Events\Gamification;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreakUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public int $currentStreak;
    public int $longestStreak;

    public function __construct(User $user, int $currentStreak, int $longestStreak)
    {
        $this->user = $user;
        $this->currentStreak = $currentStreak;
        $this->longestStreak = $longestStreak;
    }
}
