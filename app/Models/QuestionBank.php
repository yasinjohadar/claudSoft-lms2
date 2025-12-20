<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionBank extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'question_bank';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'question_type_id',
        'question_text',
        'question_image',
        'explanation',
        'default_grade',
        'difficulty_level',
        'metadata',
        'tags',
        'times_used',
        'average_score',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'default_grade' => 'decimal:2',
        'metadata' => 'array',
        'tags' => 'array',
        'times_used' => 'integer',
        'average_score' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the course that owns the question.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the question type.
     */
    public function questionType()
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }

    /**
     * Get the user who created the question.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the question.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the options for the question.
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id')->orderBy('option_order');
    }

    /**
     * Get the quiz questions that use this question.
     */
    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class, 'question_id');
    }

    /**
     * Get the quiz responses for this question.
     */
    public function responses()
    {
        return $this->hasMany(QuizResponse::class, 'question_id');
    }

    /**
     * Get the quizzes that use this question.
     */
    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_questions', 'question_id', 'quiz_id')
            ->withPivot('question_order', 'question_grade', 'is_required')
            ->withTimestamps();
    }

    /**
     * Get the pools that contain this question.
     */
    public function pools()
    {
        return $this->belongsToMany(QuestionPool::class, 'question_pool_items', 'question_id', 'pool_id')
            ->withTimestamps();
    }

    /**
     * Get the course sections that use this question.
     */
    public function courseSections()
    {
        return $this->belongsToMany(CourseSection::class, 'course_section_questions', 'question_id', 'course_section_id')
            ->withPivot(['question_order', 'question_grade', 'is_required', 'settings'])
            ->withTimestamps();
    }

    /**
     * Get programming languages for this question.
     */
    public function programmingLanguages()
    {
        return $this->belongsToMany(ProgrammingLanguage::class, 'question_programming_language', 'question_id', 'programming_language_id')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include global questions (no course).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('course_id');
    }

    /**
     * Scope a query by course.
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where(function($q) use ($courseId) {
            $q->where('course_id', $courseId)
              ->orWhereNull('course_id');
        });
    }

    /**
     * Scope a query by question type.
     */
    public function scopeByType($query, $typeId)
    {
        return $query->where('question_type_id', $typeId);
    }

    /**
     * Scope a query by difficulty level.
     */
    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    /**
     * Scope a query by tag.
     */
    public function scopeByTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Check if question requires manual grading.
     */
    public function requiresManualGrading(): bool
    {
        return $this->questionType->requires_manual_grading ?? false;
    }

    /**
     * Check if question supports auto-grading.
     */
    public function supportsAutoGrading(): bool
    {
        return $this->questionType->supports_auto_grading ?? true;
    }

    /**
     * Get the correct options for this question.
     */
    public function getCorrectOptions()
    {
        return $this->options()->where('is_correct', true)->get();
    }

    /**
     * Get the question type name.
     */
    public function getTypeName(): string
    {
        return $this->questionType->name ?? 'unknown';
    }

    /**
     * Get the question type display name.
     */
    public function getTypeDisplayName(): string
    {
        return $this->questionType->display_name ?? 'غير معروف';
    }

    /**
     * Increment times used counter.
     */
    public function incrementTimesUsed(): void
    {
        $this->increment('times_used');
    }

    /**
     * Update average score.
     */
    public function updateAverageScore(): void
    {
        $avgScore = $this->responses()
            ->whereNotNull('score_obtained')
            ->avg('score_obtained');

        if ($avgScore !== null) {
            $this->update(['average_score' => $avgScore]);
        }
    }

    /**
     * Get metadata value by key.
     */
    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set metadata value by key.
     */
    public function setMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        $this->save();
    }

    /**
     * Check if question is global (available for all courses).
     */
    public function isGlobal(): bool
    {
        return $this->course_id === null;
    }
}
