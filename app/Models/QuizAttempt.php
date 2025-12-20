<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizAttempt extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quiz_attempts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quiz_id',
        'student_id',
        'attempt_number',
        'status',
        'started_at',
        'submitted_at',
        'time_spent',
        'total_score',
        'percentage_score',
        'max_score',
        'passed',
        'grade_status',
        'is_late',
        'questions_order',
        'feedback',
        'graded_by',
        'graded_at',
        'ip_address',
        'user_agent',
        'completed_at',
        'is_completed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attempt_number' => 'integer',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'time_spent' => 'integer',
        'total_score' => 'decimal:2',
        'percentage_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'passed' => 'boolean',
        'is_late' => 'boolean',
        'questions_order' => 'array',
        'graded_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the quiz for this attempt.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * Get the student who made this attempt.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the user who graded this attempt.
     */
    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the responses for this attempt.
     */
    public function responses()
    {
        return $this->hasMany(QuizResponse::class, 'attempt_id');
    }

    /**
     * Get the analytics for this attempt.
     */
    public function analytics()
    {
        return $this->hasOne(QuizAnalytics::class, 'student_id', 'student_id')
            ->where('quiz_id', $this->quiz_id);
    }

    /**
     * Scope a query to only include completed attempts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope a query to only include in-progress attempts.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include submitted attempts.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope a query to only include graded attempts.
     */
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    /**
     * Scope a query to only include passed attempts.
     */
    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    /**
     * Scope a query to only include failed attempts.
     */
    public function scopeFailed($query)
    {
        return $query->where('passed', false);
    }

    /**
     * Scope a query by student.
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query by quiz.
     */
    public function scopeByQuiz($query, $quizId)
    {
        return $query->where('quiz_id', $quizId);
    }

    /**
     * Check if attempt is completed.
     */
    public function isCompleted(): bool
    {
        return $this->is_completed;
    }

    /**
     * Check if attempt is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if attempt is submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if attempt is graded.
     */
    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    /**
     * Check if student passed.
     */
    public function hasPassed(): bool
    {
        return $this->passed === true;
    }

    /**
     * Check if attempt was late.
     */
    public function isLate(): bool
    {
        return $this->is_late;
    }

    /**
     * Mark attempt as completed.
     * IMPORTANT: This is for the "تم الإنجاز" button functionality.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'is_completed' => true,
        ]);
    }

    /**
     * Submit the attempt.
     */
    public function submit(): void
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Calculate time spent in seconds.
     */
    public function calculateTimeSpent(): int
    {
        if ($this->submitted_at && $this->started_at) {
            return $this->started_at->diffInSeconds($this->submitted_at);
        }

        if ($this->started_at) {
            return $this->started_at->diffInSeconds(now());
        }

        return 0;
    }

    /**
     * Calculate total score from responses.
     */
    public function calculateTotalScore(): float
    {
        return $this->responses()->sum('score_obtained') ?? 0;
    }

    /**
     * Calculate percentage score.
     */
    public function calculatePercentageScore(): float
    {
        if ($this->max_score <= 0) {
            return 0;
        }

        return ($this->total_score / $this->max_score) * 100;
    }

    /**
     * Check if student passed based on quiz passing grade.
     */
    public function checkIfPassed(): bool
    {
        return $this->percentage_score >= $this->quiz->passing_grade;
    }

    /**
     * Get the number of answered questions.
     */
    public function getAnsweredCount(): int
    {
        return $this->responses()
            ->whereNotNull('response_text')
            ->orWhereNotNull('response_data')
            ->count();
    }

    /**
     * Get the number of correct responses.
     */
    public function getCorrectCount(): int
    {
        return $this->responses()->where('is_correct', true)->count();
    }

    /**
     * Get the number of incorrect responses.
     */
    public function getIncorrectCount(): int
    {
        return $this->responses()->where('is_correct', false)->count();
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentage(): float
    {
        $totalQuestions = $this->quiz->getQuestionCount();

        if ($totalQuestions === 0) {
            return 0;
        }

        $answered = $this->getAnsweredCount();

        return ($answered / $totalQuestions) * 100;
    }

    /**
     * Check if all questions are answered.
     */
    public function isFullyAnswered(): bool
    {
        return $this->getCompletionPercentage() >= 100;
    }

    /**
     * Get time spent in human-readable format.
     */
    public function getTimeSpentHumanReadable(): string
    {
        if (!$this->time_spent) {
            return '0 دقيقة';
        }

        $hours = floor($this->time_spent / 3600);
        $minutes = floor(($this->time_spent % 3600) / 60);
        $seconds = $this->time_spent % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . ' ساعة';
        }

        if ($minutes > 0) {
            $parts[] = $minutes . ' دقيقة';
        }

        if ($seconds > 0 && $hours === 0) {
            $parts[] = $seconds . ' ثانية';
        }

        return implode(' و ', $parts);
    }

    /**
     * Grade the attempt (calculate scores).
     */
    public function grade(): void
    {
        // Calculate total score from all responses
        $totalScore = $this->calculateTotalScore();
        $percentageScore = $this->max_score > 0 ? ($totalScore / $this->max_score) * 100 : 0;
        $passed = $percentageScore >= $this->quiz->passing_grade;

        // Check if all questions are graded
        $totalResponses = $this->responses()->count();
        $gradedResponses = $this->responses()->whereNotNull('score_obtained')->count();

        $gradeStatus = 'not_graded';
        if ($gradedResponses === $totalResponses && $totalResponses > 0) {
            $gradeStatus = 'fully_graded';
        } elseif ($gradedResponses > 0) {
            $gradeStatus = 'partially_graded';
        }

        $this->update([
            'total_score' => $totalScore,
            'percentage_score' => $percentageScore,
            'passed' => $passed,
            'grade_status' => $gradeStatus,
            'status' => $gradeStatus === 'fully_graded' ? 'graded' : 'submitted',
        ]);
    }
}
