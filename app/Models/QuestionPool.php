<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionPool extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'question_pools';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'course_id',
        'created_by',
    ];

    /**
     * Get the course that owns the pool.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the user who created the pool.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the pool items for this pool.
     */
    public function poolItems()
    {
        return $this->hasMany(QuestionPoolItem::class, 'pool_id');
    }

    /**
     * Get the questions in this pool.
     */
    public function questions()
    {
        return $this->belongsToMany(QuestionBank::class, 'question_pool_items', 'pool_id', 'question_id')
            ->withTimestamps();
    }

    /**
     * Get the quiz questions that use this pool.
     */
    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class, 'question_pool_id');
    }

    /**
     * Scope a query by course.
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope a query to only include global pools (no course).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('course_id');
    }

    /**
     * Get the count of questions in this pool.
     */
    public function getQuestionCount(): int
    {
        return $this->poolItems()->count();
    }

    /**
     * Add a question to this pool.
     */
    public function addQuestion(int $questionId): void
    {
        if (!$this->poolItems()->where('question_id', $questionId)->exists()) {
            $this->poolItems()->create(['question_id' => $questionId]);
        }
    }

    /**
     * Remove a question from this pool.
     */
    public function removeQuestion(int $questionId): void
    {
        $this->poolItems()->where('question_id', $questionId)->delete();
    }

    /**
     * Get random questions from this pool.
     */
    public function getRandomQuestions(int $count = 1)
    {
        return $this->questions()
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit($count)
            ->get();
    }

    /**
     * Check if pool is global (available for all courses).
     */
    public function isGlobal(): bool
    {
        return $this->course_id === null;
    }
}
