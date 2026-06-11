<?php

namespace App\Events;

use App\Models\CourseModule;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoWatched
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public CourseModule $module,
        public float $watchPercentage,
        public int $watchedSeconds = 0,
        public int $totalSeconds = 0
    ) {}
}
