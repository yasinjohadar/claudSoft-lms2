<?php

namespace App\Events;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Quiz $quiz,
        public int $attemptId,
        public int $attemptNumber
    ) {}
}
