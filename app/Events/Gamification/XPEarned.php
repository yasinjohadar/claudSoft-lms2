<?php

namespace App\Events\Gamification;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class XPEarned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public int $xp;
    public string $reason;
    public ?string $relatedType;
    public ?int $relatedId;

    public function __construct(
        User $user,
        int $xp,
        string $reason,
        ?string $relatedType = null,
        ?int $relatedId = null
    ) {
        $this->user = $user;
        $this->xp = $xp;
        $this->reason = $reason;
        $this->relatedType = $relatedType;
        $this->relatedId = $relatedId;
    }
}
