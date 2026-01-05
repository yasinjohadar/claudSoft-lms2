<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionModuleAttempt extends Model
{
    protected $fillable = [
        'question_module_id',
        'student_id',
        'attempt_number',
        'status',
        'started_at',
        'completed_at',
        'time_spent',
        'total_score',
        'percentage',
        'is_passed',
        'question_order',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_passed' => 'boolean',
        'question_order' => 'array',
        'total_score' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    /**
     * Get the question module for this attempt.
     */
    public function questionModule()
    {
        return $this->belongsTo(QuestionModule::class);
    }

    /**
     * Get the student for this attempt.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get all responses for this attempt.
     */
    public function responses()
    {
        return $this->hasMany(QuestionModuleResponse::class, 'attempt_id');
    }

    /**
     * Check if attempt is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if attempt is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Mark attempt as completed.
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Calculate and update scores.
     */
    public function calculateScores()
    {
        // Reload responses to ensure we have the latest data
        $this->load('responses');
        
        // Calculate total score - handle null values as 0
        $totalScore = $this->responses->sum(function($response) {
            return $response->score_obtained ?? 0;
        });
        
        // Calculate max score
        $maxScore = $this->responses->sum(function($response) {
            return $response->max_score ?? 0;
        });
        
        // Calculate percentage
        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

        $passPercentage = $this->questionModule->pass_percentage ?? 50;
        $isPassed = $percentage >= $passPercentage;

        // Log for debugging
        \Log::info('=== CALCULATING SCORES ===', [
            'attempt_id' => $this->id,
            'responses_count' => $this->responses->count(),
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'pass_percentage' => $passPercentage,
            'is_passed' => $isPassed,
            'responses_detail' => $this->responses->map(function($r) {
                return [
                    'id' => $r->id,
                    'question_id' => $r->question_id,
                    'score_obtained' => $r->score_obtained,
                    'max_score' => $r->max_score,
                    'is_correct' => $r->is_correct,
                ];
            })->toArray(),
        ]);

        $this->update([
            'total_score' => $totalScore,
            'percentage' => $percentage,
            'is_passed' => $isPassed,
        ]);
        
        // Refresh to ensure updated values are available
        $this->refresh();
        
        \Log::info('=== SCORES CALCULATED ===', [
            'attempt_id' => $this->id,
            'total_score' => $this->total_score,
            'percentage' => $this->percentage,
            'is_passed' => $this->is_passed,
        ]);
    }

    /**
     * Get remaining time in seconds.
     */
    public function getRemainingTime(): ?int
    {
        if (!$this->questionModule->time_limit) {
            return null;
        }

        if (!$this->started_at) {
            return $this->questionModule->time_limit * 60;
        }

        $timeLimitSeconds = $this->questionModule->time_limit * 60;
        $elapsedSeconds = now()->diffInSeconds($this->started_at);
        $remainingSeconds = $timeLimitSeconds - $elapsedSeconds;

        return max(0, $remainingSeconds);
    }

    /**
     * Check if time is up.
     */
    public function isTimeUp(): bool
    {
        if (!$this->questionModule->time_limit) {
            return false;
        }

        return $this->getRemainingTime() <= 0;
    }
}
