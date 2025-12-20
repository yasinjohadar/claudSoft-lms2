<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'question_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'requires_manual_grading',
        'supports_auto_grading',
        'icon',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requires_manual_grading' => 'boolean',
        'supports_auto_grading' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all questions of this type.
     */
    public function questions()
    {
        return $this->hasMany(QuestionBank::class, 'question_type_id');
    }

    /**
     * Get all quiz responses of this type.
     */
    public function quizResponses()
    {
        return $this->hasMany(QuizResponse::class, 'question_type_id');
    }

    /**
     * Scope a query to only include active question types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include auto-gradable types.
     */
    public function scopeAutoGradable($query)
    {
        return $query->where('supports_auto_grading', true);
    }

    /**
     * Scope a query to only include manually graded types.
     */
    public function scopeManualGrading($query)
    {
        return $query->where('requires_manual_grading', true);
    }

    /**
     * Check if this question type requires manual grading.
     */
    public function requiresManualGrading(): bool
    {
        return $this->requires_manual_grading;
    }

    /**
     * Check if this question type supports auto-grading.
     */
    public function supportsAutoGrading(): bool
    {
        return $this->supports_auto_grading;
    }
}
