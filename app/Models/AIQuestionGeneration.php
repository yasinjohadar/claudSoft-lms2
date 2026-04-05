<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIQuestionGeneration extends Model
{
    use HasFactory;

    protected $table = 'ai_question_generations';

    protected $fillable = [
        'user_id',
        'course_id',
        'lesson_id',
        'source_type',
        'source_content',
        'question_type',
        'number_of_questions',
        'difficulty_level',
        'ai_model_id',
        'laravel_ai_model_id',
        'status',
        'generated_questions',
        'tokens_used',
        'cost',
        'error_message',
    ];

    protected $casts = [
        'number_of_questions' => 'integer',
        'tokens_used' => 'integer',
        'cost' => 'float',
        'generated_questions' => 'array',
    ];

    /**
     * Source types
     */
    public const SOURCE_TYPES = [
        'lesson_content' => 'محتوى الدرس',
        'manual_text' => 'نص يدوي',
        'topic' => 'موضوع',
    ];

    /**
     * Question types
     */
    public const QUESTION_TYPES = [
        'single_choice' => 'اختيار واحد',
        'multiple_choice' => 'اختيار متعدد',
        'true_false' => 'صح/خطأ',
        'short_answer' => 'إجابة قصيرة',
        'mixed' => 'مختلط',
    ];

    /**
     * Difficulty levels
     */
    public const DIFFICULTIES = [
        'easy' => 'سهل',
        'medium' => 'متوسط',
        'hard' => 'صعب',
        'mixed' => 'مختلط',
    ];

    /**
     * Statuses
     */
    public const STATUSES = [
        'pending' => 'قيد الانتظار',
        'processing' => 'قيد المعالجة',
        'completed' => 'مكتمل',
        'failed' => 'فشل',
    ];

    /**
     * Get the user who created this generation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the lesson
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    /**
     * Get the AI model used
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'ai_model_id');
    }

    public function laravelAiModel(): BelongsTo
    {
        return $this->belongsTo(LaravelAiModel::class, 'laravel_ai_model_id');
    }
}
