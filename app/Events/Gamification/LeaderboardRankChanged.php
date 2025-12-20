<?php

namespace App\Events\Gamification;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaderboardRankChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public $leaderboard;
    public int $oldRank;
    public int $newRank;

    public function __construct(User $user, $leaderboard, int $oldRank, int $newRank)
    {
        $this->user = $user;
        $this->leaderboard = $leaderboard;
        $this->oldRank = $oldRank;
        $this->newRank = $newRank;
    }
}
