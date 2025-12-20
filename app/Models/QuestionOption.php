<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'question_options';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question_id',
        'option_text',
        'option_image',
        'is_correct',
        'grade_percentage',
        'option_order',
        'match_pair_id',
        'feedback',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'grade_percentage' => 'decimal:2',
        'option_order' => 'integer',
        'match_pair_id' => 'integer',
    ];

    /**
     * Get the question that owns the option.
     */
    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }

    /**
     * Scope a query to only include correct options.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope a query to only include incorrect options.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope a query ordered by option_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('option_order');
    }

    /**
     * Check if this option is correct.
     */
    public function isCorrect(): bool
    {
        return $this->is_correct;
    }

    /**
     * Get the grade percentage for this option.
     */
    public function getGradePercentage(): float
    {
        return (float) $this->grade_percentage;
    }

    /**
     * Calculate score for this option if selected.
     */
    public function calculateScore(float $maxScore): float
    {
        if (!$this->is_correct) {
            return 0;
        }

        return ($maxScore * $this->grade_percentage) / 100;
    }
}
