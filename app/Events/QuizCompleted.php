<?php

namespace App\Events;

use App\Models\User;
use App\Models\Quiz;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public Quiz $quiz;
    public int $score;
    public int $totalQuestions;
    public ?int $attemptId;
    public ?int $timeTaken;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user,
        Quiz $quiz,
        int $score,
        int $totalQuestions,
        ?int $attemptId = null,
        ?int $timeTaken = null
    ) {
        $this->user = $user;
        $this->quiz = $quiz;
        $this->score = $score;
        $this->totalQuestions = $totalQuestions;
        $this->attemptId = $attemptId;
        $this->timeTaken = $timeTaken;
    }
}
