<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAnalytics extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quiz_analytics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'quiz_id',
        'course_id',
        'total_attempts',
        'completed_attempts',
        'best_score',
        'best_percentage',
        'average_score',
        'average_percentage',
        'total_time_spent',
        'average_time_spent',
        'completion_rate',
        'pass_rate',
        'improvement_rate',
        'strengths',
        'weaknesses',
        'question_performance',
        'first_attempt_at',
        'last_attempt_at',
        'last_updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_attempts' => 'integer',
        'completed_attempts' => 'integer',
        'best_score' => 'decimal:2',
        'best_percentage' => 'decimal:2',
        'average_score' => 'decimal:2',
        'average_percentage' => 'decimal:2',
        'total_time_spent' => 'integer',
        'average_time_spent' => 'integer',
        'completion_rate' => 'decimal:2',
        'pass_rate' => 'decimal:2',
        'improvement_rate' => 'decimal:2',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'question_performance' => 'array',
        'first_attempt_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Get the student for this analytics.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the quiz for this analytics.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * Get the course for this analytics.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the attempts for this student and quiz.
     */
    public function attempts()
    {
        return QuizAttempt::where('student_id', $this->student_id)
            ->where('quiz_id', $this->quiz_id);
    }

    /**
     * Recalculate and update all analytics.
     */
    public function recalculate(): void
    {
        $attempts = $this->attempts()->get();
        $completedAttempts = $attempts->where('is_completed', true);

        // Basic counts
        $this->total_attempts = $attempts->count();
        $this->completed_attempts = $completedAttempts->count();

        if ($completedAttempts->isEmpty()) {
            $this->save();
            return;
        }

        // Score statistics
        $this->best_score = $completedAttempts->max('total_score');
        $this->best_percentage = $completedAttempts->max('percentage_score');
        $this->average_score = $completedAttempts->avg('total_score');
        $this->average_percentage = $completedAttempts->avg('percentage_score');

        // Time statistics
        $this->total_time_spent = $completedAttempts->sum('time_spent');
        $this->average_time_spent = $completedAttempts->avg('time_spent');

        // Completion and pass rates
        $this->completion_rate = ($this->completed_attempts / $this->total_attempts) * 100;
        $passedCount = $completedAttempts->where('passed', true)->count();
        $this->pass_rate = $this->completed_attempts > 0
            ? ($passedCount / $this->completed_attempts) * 100
            : 0;

        // Improvement rate (first vs last)
        $firstAttempt = $completedAttempts->sortBy('attempt_number')->first();
        $lastAttempt = $completedAttempts->sortByDesc('attempt_number')->first();

        if ($firstAttempt && $lastAttempt && $firstAttempt->id !== $lastAttempt->id) {
            $firstScore = $firstAttempt->percentage_score ?? 0;
            $lastScore = $lastAttempt->percentage_score ?? 0;
            $this->improvement_rate = $lastScore - $firstScore;
        } else {
            $this->improvement_rate = 0;
        }

        // Timestamps
        $this->first_attempt_at = $attempts->min('started_at');
        $this->last_attempt_at = $attempts->max('started_at');
        $this->last_updated_at = now();

        // Calculate strengths and weaknesses
        $this->calculateStrengthsWeaknesses();

        // Calculate question performance
        $this->calculateQuestionPerformance();

        $this->save();
    }

    /**
     * Calculate strengths and weaknesses by question type.
     */
    private function calculateStrengthsWeaknesses(): void
    {
        $completedAttemptIds = $this->attempts()
            ->where('is_completed', true)
            ->pluck('id');

        $responses = QuizResponse::whereIn('attempt_id', $completedAttemptIds)
            ->whereNotNull('score_obtained')
            ->with('questionType')
            ->get();

        $typePerformance = [];

        foreach ($responses as $response) {
            $typeName = $response->questionType->name ?? 'unknown';

            if (!isset($typePerformance[$typeName])) {
                $typePerformance[$typeName] = [
                    'type' => $typeName,
                    'display_name' => $response->questionType->display_name ?? $typeName,
                    'total_questions' => 0,
                    'total_score' => 0,
                    'max_score' => 0,
                ];
            }

            $typePerformance[$typeName]['total_questions']++;
            $typePerformance[$typeName]['total_score'] += $response->score_obtained;
            $typePerformance[$typeName]['max_score'] += $response->max_score;
        }

        // Calculate percentages
        foreach ($typePerformance as $type => &$data) {
            $data['percentage'] = $data['max_score'] > 0
                ? ($data['total_score'] / $data['max_score']) * 100
                : 0;
        }

        // Sort by percentage
        uasort($typePerformance, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        // Strengths: top 3 types with >= 70%
        $this->strengths = array_values(array_filter($typePerformance, function($data) {
            return $data['percentage'] >= 70;
        }));
        $this->strengths = array_slice($this->strengths, 0, 3);

        // Weaknesses: bottom 3 types with < 70%
        $weaknesses = array_values(array_filter($typePerformance, function($data) {
            return $data['percentage'] < 70;
        }));
        $this->weaknesses = array_slice(array_reverse($weaknesses), 0, 3);
    }

    /**
     * Calculate performance on individual questions.
     */
    private function calculateQuestionPerformance(): void
    {
        $completedAttemptIds = $this->attempts()
            ->where('is_completed', true)
            ->pluck('id');

        $responses = QuizResponse::whereIn('attempt_id', $completedAttemptIds)
            ->whereNotNull('score_obtained')
            ->get();

        $questionPerformance = [];

        foreach ($responses as $response) {
            $questionId = $response->question_id;

            if (!isset($questionPerformance[$questionId])) {
                $questionPerformance[$questionId] = [
                    'question_id' => $questionId,
                    'attempts' => 0,
                    'correct' => 0,
                    'total_score' => 0,
                    'max_score' => 0,
                ];
            }

            $questionPerformance[$questionId]['attempts']++;

            if ($response->is_correct) {
                $questionPerformance[$questionId]['correct']++;
            }

            $questionPerformance[$questionId]['total_score'] += $response->score_obtained;
            $questionPerformance[$questionId]['max_score'] += $response->max_score;
        }

        // Calculate percentages
        foreach ($questionPerformance as $questionId => &$data) {
            $data['success_rate'] = $data['attempts'] > 0
                ? ($data['correct'] / $data['attempts']) * 100
                : 0;
            $data['average_score'] = $data['max_score'] > 0
                ? ($data['total_score'] / $data['max_score']) * 100
                : 0;
        }

        $this->question_performance = array_values($questionPerformance);
    }

    /**
     * Get time spent in human-readable format.
     */
    public function getTotalTimeSpentHumanReadable(): string
    {
        if (!$this->total_time_spent) {
            return '0 دقيقة';
        }

        $hours = floor($this->total_time_spent / 3600);
        $minutes = floor(($this->total_time_spent % 3600) / 60);

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . ' ساعة';
        }

        if ($minutes > 0) {
            $parts[] = $minutes . ' دقيقة';
        }

        return implode(' و ', $parts) ?: '0 دقيقة';
    }

    /**
     * Get average time spent in human-readable format.
     */
    public function getAverageTimeSpentHumanReadable(): string
    {
        if (!$this->average_time_spent) {
            return '0 دقيقة';
        }

        $minutes = floor($this->average_time_spent / 60);
        $seconds = $this->average_time_spent % 60;

        if ($minutes > 0) {
            return $minutes . ' دقيقة' . ($seconds > 0 ? ' و ' . $seconds . ' ثانية' : '');
        }

        return $seconds . ' ثانية';
    }

    /**
     * Check if student is improving.
     */
    public function isImproving(): bool
    {
        return $this->improvement_rate > 0;
    }

    /**
     * Get improvement status.
     */
    public function getImprovementStatus(): string
    {
        if ($this->improvement_rate > 10) {
            return 'excellent';
        } elseif ($this->improvement_rate > 0) {
            return 'good';
        } elseif ($this->improvement_rate === 0) {
            return 'stable';
        } else {
            return 'declining';
        }
    }
}
