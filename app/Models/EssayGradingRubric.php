<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EssayGradingRubric extends Model
{
    use HasFactory;

    protected $table = 'essay_grading_rubrics';

    protected $fillable = [
        'question_id',
        'criteria',
        'max_score',
        'ai_grading_enabled',
        'ai_prompt',
        'instructions',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'criteria' => 'array',
        'ai_prompt' => 'array',
        'max_score' => 'decimal:2',
        'ai_grading_enabled' => 'boolean',
    ];

    /**
     * Get the question this rubric belongs to
     */
    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }

    /**
     * Get the user who created this rubric
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this rubric
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get default criteria structure
     */
    public static function getDefaultCriteria(): array
    {
        return [
            'content' => [
                'weight' => 40,
                'description' => 'محتوى الإجابة وشموليتها',
            ],
            'structure' => [
                'weight' => 20,
                'description' => 'البنية والتنظيم',
            ],
            'language' => [
                'weight' => 20,
                'description' => 'استخدام اللغة والقواعد',
            ],
            'accuracy' => [
                'weight' => 20,
                'description' => 'دقة المعلومات',
            ],
        ];
    }
}
