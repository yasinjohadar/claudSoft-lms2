<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIStudentFeedback extends Model
{
    use HasFactory;

    protected $table = 'ai_student_feedback';

    protected $fillable = [
        'student_id',
        'quiz_attempt_id',
        'feedback_type',
        'feedback_text',
        'suggestions',
        'ai_model_id',
        'tokens_used',
        'cost',
    ];

    protected $casts = [
        'suggestions' => 'array',
        'tokens_used' => 'integer',
        'cost' => 'float',
    ];

    /**
     * Feedback types
     */
    public const FEEDBACK_TYPES = [
        'performance' => 'الأداء',
        'general' => 'عام',
        'improvement' => 'التحسين',
    ];

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the quiz attempt
     */
    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    /**
     * Get the AI model used
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'ai_model_id');
    }
}

