<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIQuestionSolution extends Model
{
    use HasFactory;

    protected $table = 'ai_question_solutions';

    protected $fillable = [
        'question_id',
        'ai_model_id',
        'solution',
        'explanation',
        'confidence_score',
        'is_verified',
        'verified_by',
        'verified_at',
        'tokens_used',
        'cost',
    ];

    protected $casts = [
        'confidence_score' => 'float',
        'is_verified' => 'boolean',
        'tokens_used' => 'integer',
        'cost' => 'float',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the question
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }

    /**
     * Get the AI model used
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'ai_model_id');
    }

    /**
     * Get the verifier
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Verify the solution
     */
    public function verify(User $verifier): bool
    {
        return $this->update([
            'is_verified' => true,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }

    /**
     * Scope a query to only include solutions for a specific question
     */
    public function scopeForQuestion($query, int $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    /**
     * Scope a query to only include verified solutions
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}



