<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quiz_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quiz_id',
        'question_id',
        'question_pool_id',
        'questions_to_select',
        'question_order',
        'question_grade',
        'is_required',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'questions_to_select' => 'integer',
        'question_order' => 'integer',
        'question_grade' => 'decimal:2',
        'is_required' => 'boolean',
    ];

    /**
     * Get the quiz that owns this quiz question.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * Get the question for this quiz question.
     */
    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }

    /**
     * Get the question pool for this quiz question.
     */
    public function questionPool()
    {
        return $this->belongsTo(QuestionPool::class, 'question_pool_id');
    }

    /**
     * Scope a query ordered by question_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('question_order');
    }

    /**
     * Scope a query to only include required questions.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Check if this uses a question pool.
     */
    public function usesPool(): bool
    {
        return $this->question_pool_id !== null;
    }

    /**
     * Check if this is a direct question.
     */
    public function isDirectQuestion(): bool
    {
        return $this->question_id !== null;
    }

    /**
     * Get the grade for this question.
     * Returns custom grade if set, otherwise question's default grade.
     */
    public function getGrade(): float
    {
        if ($this->question_grade !== null) {
            return (float) $this->question_grade;
        }

        if ($this->question) {
            return (float) $this->question->default_grade;
        }

        return 1.0;
    }

    /**
     * Get random questions from pool if this uses a pool.
     */
    public function getQuestionsFromPool()
    {
        if (!$this->usesPool()) {
            return collect();
        }

        return $this->questionPool
            ->getRandomQuestions($this->questions_to_select);
    }
}
