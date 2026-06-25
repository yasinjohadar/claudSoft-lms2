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

    /**
     * Feather icon class for UI cards (admin theme).
     */
    public function featherIconClass(): string
    {
        $icons = [
            'multiple_choice_single' => 'fe-check-circle',
            'multiple_choice_multiple' => 'fe-check-square',
            'true_false' => 'fe-toggle-right',
            'short_answer' => 'fe-edit-3',
            'essay' => 'fe-file-text',
            'matching' => 'fe-shuffle',
            'fill_blanks' => 'fe-type',
            'fill_blank' => 'fe-type',
            'ordering' => 'fe-list',
            'numerical' => 'fe-hash',
            'calculated' => 'fe-percent',
            'drag_drop' => 'fe-move',
        ];

        $icon = $icons[$this->name] ?? 'fe-help-circle';

        return str_starts_with($icon, 'fe ') ? $icon : 'fe ' . $icon;
    }

    /**
     * CSS modifier slug for type-specific colors.
     */
    public function typeSlug(): string
    {
        return str_replace('_', '-', $this->name);
    }

    /**
     * Short Arabic description for type selection cards.
     */
    public function selectionSummary(): string
    {
        return match ($this->name) {
            'multiple_choice_single' => 'إجابة واحدة صحيحة من عدة خيارات',
            'multiple_choice_multiple' => 'أكثر من إجابة صحيحة',
            'true_false' => 'تحديد صحة العبارة',
            'short_answer' => 'نص قصير يُصحَّح تلقائياً أو يدوياً',
            'essay' => 'إجابة مقالية طويلة',
            'matching' => 'مطابقة العناصر ببعضها',
            'ordering' => 'ترتيب العناصر بالتسلسل',
            'fill_blanks', 'fill_blank' => 'ملء الفراغات في النص',
            'numerical' => 'قيمة رقمية مع هامش خطأ',
            'calculated' => 'سؤال بمعادلة محسوبة',
            'drag_drop' => 'سحب وإفلات العناصر',
            default => (string) ($this->description ?? ''),
        };
    }

    /**
     * Build create URL for question type selection modal.
     */
    public function createUrl(string $context, array $params = []): string
    {
        return match ($context) {
            'quiz' => route('question-bank.create.type', $this->name) . '?' . http_build_query(['quiz_id' => $params['quiz_id'] ?? null]),
            'question-module' => route('question-bank.create.type', $this->name) . '?' . http_build_query(['question_module_id' => $params['question_module_id'] ?? null]),
            'section' => route('sections.questions.create', [$params['section_id'] ?? 0, $this->name]),
            default => route('question-bank.create.type', $this->name),
        };
    }
}
