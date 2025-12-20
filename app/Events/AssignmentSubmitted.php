<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $assignment;
    public $submission;

    /**
     * Create a new event instance.
     */
    public function __construct($user, $assignment, $submission)
    {
        $this->user = $user;
        $this->assignment = $assignment;
        $this->submission = $submission;
    }
}
