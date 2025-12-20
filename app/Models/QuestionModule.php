<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'is_published',
        'is_visible',
        'available_from',
        'available_until',
        'time_limit',
        'shuffle_questions',
        'show_results',
        'pass_percentage',
        'attempts_allowed',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_visible' => 'boolean',
        'shuffle_questions' => 'boolean',
        'show_results' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'pass_percentage' => 'decimal:2',
    ];

    // Relationships

    /**
     * Get all of the module's course modules.
     */
    public function courseModules()
    {
        return $this->morphMany(CourseModule::class, 'modulable');
    }

    /**
     * Get the primary course module for this question module.
     */
    public function module()
    {
        return $this->morphOne(CourseModule::class, 'modulable');
    }

    /**
     * Get the questions associated with this question module.
     */
    public function questions()
    {
        return $this->belongsToMany(QuestionBank::class, 'question_module_questions', 'question_module_id', 'question_id')
            ->withPivot(['question_order', 'question_grade'])
            ->withTimestamps()
            ->orderBy('question_module_questions.question_order');
    }

    /**
     * Get the user who created the question module.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the question module.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all attempts for this question module.
     */
    public function attempts()
    {
        return $this->hasMany(QuestionModuleAttempt::class);
    }

    /**
     * Get student's attempts for this question module.
     */
    public function studentAttempts($studentId)
    {
        return $this->attempts()->where('student_id', $studentId)->orderBy('attempt_number', 'desc');
    }

    /**
     * Get student's latest attempt.
     */
    public function getLatestAttempt($studentId)
    {
        return $this->studentAttempts($studentId)->first();
    }

    /**
     * Check if student can attempt.
     */
    public function canStudentAttempt($studentId): bool
    {
        $attemptsCount = $this->studentAttempts($studentId)->where('status', 'completed')->count();
        return $attemptsCount < $this->attempts_allowed;
    }

    // Scopes

    /**
     * Scope a query to only include published question modules.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include visible question modules.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include available question modules based on dates.
     */
    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('available_until')->orWhere('available_until', '>=', $now);
        });
    }

    // Helper Methods

    /**
     * Check if the question module is available.
     */
    public function isAvailable(): bool
    {
        $now = now();

        if ($this->available_from && $this->available_from > $now) {
            return false;
        }

        if ($this->available_until && $this->available_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get total questions count.
     */
    public function getQuestionsCount(): int
    {
        return $this->questions()->count();
    }

    /**
     * Get total grade for all questions.
     */
    public function getTotalGrade(): float
    {
        return $this->questions()->sum('question_module_questions.question_grade') ?? 0;
    }

    /**
     * Check if module has questions.
     */
    public function hasQuestions(): bool
    {
        return $this->questions()->exists();
    }

    /**
     * Add question to module.
     */
    public function addQuestion(int $questionId, float $grade = 1.0, ?int $order = null): void
    {
        if ($order === null) {
            $order = $this->questions()->count() + 1;
        }

        $this->questions()->attach($questionId, [
            'question_order' => $order,
            'question_grade' => $grade,
        ]);
    }

    /**
     * Remove question from module.
     */
    public function removeQuestion(int $questionId): void
    {
        $this->questions()->detach($questionId);
    }

    /**
     * Update question settings in module.
     */
    public function updateQuestionSettings(int $questionId, array $settings): void
    {
        $this->questions()->updateExistingPivot($questionId, $settings);
    }

    /**
     * Reorder questions.
     */
    public function reorderQuestions(array $questionIds): void
    {
        foreach ($questionIds as $order => $questionId) {
            $this->questions()->updateExistingPivot($questionId, [
                'question_order' => $order + 1,
            ]);
        }
    }
}
