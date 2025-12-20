<?php

namespace App\Events\Gamification;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeEarned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public $badge;

    public function __construct(User $user, $badge)
    {
        $this->user = $user;
        $this->badge = $badge;
    }
}
