<?php

namespace App\Events\Gamification;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AchievementUnlocked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public $achievement;

    public function __construct(User $user, $achievement)
    {
        $this->user = $user;
        $this->achievement = $achievement;
    }
}
