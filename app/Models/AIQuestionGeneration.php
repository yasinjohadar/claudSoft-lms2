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
        'quiz_id',
        'lesson_id',
        'lesson_name',
        'programming_language_id',
        'source_type',
        'source_content',
        'question_type',
        'question_type_ids',
        'number_of_questions',
        'difficulty_level',
        'default_grade',
        'ai_model_id',
        'laravel_ai_model_id',
        'status',
        'generated_questions',
        'saved_indices',
        'saved_question_ids',
        'tokens_used',
        'cost',
        'error_message',
    ];

    protected $casts = [
        'number_of_questions' => 'integer',
        'tokens_used' => 'integer',
        'cost' => 'float',
        'default_grade' => 'decimal:2',
        'generated_questions' => 'array',
        'question_type_ids' => 'array',
        'saved_indices' => 'array',
        'saved_question_ids' => 'array',
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

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
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

    public function programmingLanguage(): BelongsTo
    {
        return $this->belongsTo(ProgrammingLanguage::class, 'programming_language_id');
    }

    public function usesQuestionBankFields(): bool
    {
        return $this->programming_language_id !== null
            && is_array($this->question_type_ids)
            && count($this->question_type_ids) > 0;
    }

    public function getSavedIndices(): array
    {
        return array_map('intval', $this->saved_indices ?? []);
    }

    public function isIndexSaved(int $index): bool
    {
        return in_array($index, $this->getSavedIndices(), true);
    }

    public function getSavedQuestionBankId(int $index): ?int
    {
        $map = $this->saved_question_ids ?? [];

        return isset($map[(string) $index]) ? (int) $map[(string) $index] : null;
    }
}
