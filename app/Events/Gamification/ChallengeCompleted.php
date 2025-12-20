<?php

namespace App\Events\Gamification;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChallengeCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public $challenge;
    public $userChallenge;

    public function __construct(User $user, $challenge, $userChallenge = null)
    {
        $this->user = $user;
        $this->challenge = $challenge;
        $this->userChallenge = $userChallenge;
    }
}
