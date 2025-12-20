<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoWatched
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $video;
    public $watchDuration;

    /**
     * Create a new event instance.
     */
    public function __construct($user, $video, $watchDuration = null)
    {
        $this->user = $user;
        $this->video = $video;
        $this->watchDuration = $watchDuration;
    }
}
