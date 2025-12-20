<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizResponse extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quiz_responses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attempt_id',
        'question_id',
        'question_type_id',
        'response_text',
        'response_data',
        'selected_option_ids',
        'is_correct',
        'score_obtained',
        'max_score',
        'time_spent',
        'marked_for_review',
        'answer_order',
        'feedback',
        'auto_graded',
        'graded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'response_data' => 'array',
        'selected_option_ids' => 'array',
        'is_correct' => 'boolean',
        'score_obtained' => 'decimal:2',
        'max_score' => 'decimal:2',
        'time_spent' => 'integer',
        'marked_for_review' => 'boolean',
        'answer_order' => 'integer',
        'auto_graded' => 'boolean',
        'graded_at' => 'datetime',
    ];

    /**
     * Get the attempt that owns this response.
     */
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Get the question for this response.
     */
    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }

    /**
     * Get the question type for this response.
     */
    public function questionType()
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }

    /**
     * Scope a query to only include correct responses.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope a query to only include incorrect responses.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope a query to only include graded responses.
     */
    public function scopeGraded($query)
    {
        return $query->whereNotNull('score_obtained');
    }

    /**
     * Scope a query to only include ungraded responses.
     */
    public function scopeUngraded($query)
    {
        return $query->whereNull('score_obtained');
    }

    /**
     * Scope a query to only include auto-graded responses.
     */
    public function scopeAutoGraded($query)
    {
        return $query->where('auto_graded', true);
    }

    /**
     * Scope a query to only include manually graded responses.
     */
    public function scopeManuallyGraded($query)
    {
        return $query->where('auto_graded', false)
            ->whereNotNull('score_obtained');
    }

    /**
     * Scope a query to only include marked for review.
     */
    public function scopeMarkedForReview($query)
    {
        return $query->where('marked_for_review', true);
    }

    /**
     * Check if response is correct.
     */
    public function isCorrect(): bool
    {
        return $this->is_correct === true;
    }

    /**
     * Check if response is graded.
     */
    public function isGraded(): bool
    {
        return $this->score_obtained !== null;
    }

    /**
     * Check if response was auto-graded.
     */
    public function isAutoGraded(): bool
    {
        return $this->auto_graded;
    }

    /**
     * Check if response is marked for review.
     */
    public function isMarkedForReview(): bool
    {
        return $this->marked_for_review;
    }

    /**
     * Get percentage score.
     */
    public function getPercentageScore(): float
    {
        if ($this->max_score <= 0) {
            return 0;
        }

        return ($this->score_obtained / $this->max_score) * 100;
    }

    /**
     * Mark for review.
     */
    public function markForReview(): void
    {
        $this->update(['marked_for_review' => true]);
    }

    /**
     * Unmark for review.
     */
    public function unmarkForReview(): void
    {
        $this->update(['marked_for_review' => false]);
    }

    /**
     * Grade the response automatically.
     */
    public function autoGrade(): void
    {
        $questionType = $this->questionType->name ?? '';
        $isCorrect = false;
        $scoreObtained = 0;

        switch ($questionType) {
            case 'multiple_choice_single':
                $isCorrect = $this->gradeMultipleChoiceSingle();
                $scoreObtained = $isCorrect ? $this->max_score : 0;
                break;

            case 'multiple_choice_multiple':
                [$isCorrect, $scoreObtained] = $this->gradeMultipleChoiceMultiple();
                break;

            case 'true_false':
                $isCorrect = $this->gradeTrueFalse();
                $scoreObtained = $isCorrect ? $this->max_score : 0;
                break;

            case 'short_answer':
                $isCorrect = $this->gradeShortAnswer();
                $scoreObtained = $isCorrect ? $this->max_score : 0;
                break;

            case 'numerical':
                $isCorrect = $this->gradeNumerical();
                $scoreObtained = $isCorrect ? $this->max_score : 0;
                break;

            case 'matching':
                [$isCorrect, $scoreObtained] = $this->gradeMatching();
                break;

            case 'ordering':
                $isCorrect = $this->gradeOrdering();
                $scoreObtained = $isCorrect ? $this->max_score : 0;
                break;

            case 'fill_blanks':
                [$isCorrect, $scoreObtained] = $this->gradeFillBlanks();
                break;

            default:
                // Essay and calculated questions require manual grading
                return;
        }

        $this->update([
            'is_correct' => $isCorrect,
            'score_obtained' => $scoreObtained,
            'auto_graded' => true,
            'graded_at' => now(),
        ]);
    }

    /**
     * Grade multiple choice single answer.
     */
    private function gradeMultipleChoiceSingle(): bool
    {
        if (empty($this->selected_option_ids)) {
            return false;
        }

        $selectedOptionId = $this->selected_option_ids[0] ?? null;

        if (!$selectedOptionId) {
            return false;
        }

        $option = QuestionOption::find($selectedOptionId);

        return $option && $option->is_correct;
    }

    /**
     * Grade multiple choice multiple answers.
     */
    private function gradeMultipleChoiceMultiple(): array
    {
        if (empty($this->selected_option_ids)) {
            return [false, 0];
        }

        $correctOptions = $this->question->options()->where('is_correct', true)->pluck('id')->toArray();
        $selectedOptions = $this->selected_option_ids;

        // Check if all correct options are selected and no incorrect options
        $isFullyCorrect = count(array_diff($correctOptions, $selectedOptions)) === 0
            && count(array_diff($selectedOptions, $correctOptions)) === 0;

        if ($isFullyCorrect) {
            return [true, $this->max_score];
        }

        // Partial credit: calculate based on correct selections
        $correctSelections = count(array_intersect($correctOptions, $selectedOptions));
        $totalCorrect = count($correctOptions);

        if ($totalCorrect === 0) {
            return [false, 0];
        }

        $partialScore = ($correctSelections / $totalCorrect) * $this->max_score;

        return [$isFullyCorrect, $partialScore];
    }

    /**
     * Grade true/false question.
     */
    private function gradeTrueFalse(): bool
    {
        return $this->gradeMultipleChoiceSingle();
    }

    /**
     * Grade short answer.
     */
    private function gradeShortAnswer(): bool
    {
        if (empty($this->response_text)) {
            return false;
        }

        $metadata = $this->question->metadata ?? [];
        $correctAnswers = $metadata['correct_answers'] ?? [];
        $caseSensitive = $metadata['case_sensitive'] ?? false;

        if (empty($correctAnswers)) {
            return false;
        }

        $studentAnswer = trim($this->response_text);

        if (!$caseSensitive) {
            $studentAnswer = mb_strtolower($studentAnswer);
        }

        foreach ($correctAnswers as $correctAnswer) {
            $compare = $caseSensitive ? $correctAnswer : mb_strtolower($correctAnswer);

            if ($studentAnswer === $compare) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grade numerical answer.
     */
    private function gradeNumerical(): bool
    {
        if (empty($this->response_text)) {
            return false;
        }

        $metadata = $this->question->metadata ?? [];
        $correctAnswer = $metadata['correct_answer'] ?? null;
        $tolerance = $metadata['tolerance'] ?? 0;

        if ($correctAnswer === null) {
            return false;
        }

        $studentAnswer = floatval($this->response_text);
        $correctValue = floatval($correctAnswer);

        $difference = abs($studentAnswer - $correctValue);

        return $difference <= $tolerance;
    }

    /**
     * Grade matching question.
     */
    private function gradeMatching(): array
    {
        if (empty($this->response_data)) {
            return [false, 0];
        }

        $pairs = $this->response_data['pairs'] ?? [];
        $correctPairs = 0;
        $totalPairs = 0;

        $options = $this->question->options;

        foreach ($pairs as $pair) {
            $leftId = $pair['left'] ?? null;
            $rightId = $pair['right'] ?? null;

            if (!$leftId || !$rightId) {
                continue;
            }

            $totalPairs++;

            // Find if this pair is correct
            $option = $options->firstWhere('id', $leftId);

            if ($option && $option->match_pair_id == $rightId) {
                $correctPairs++;
            }
        }

        if ($totalPairs === 0) {
            return [false, 0];
        }

        $isFullyCorrect = $correctPairs === $totalPairs;
        $partialScore = ($correctPairs / $totalPairs) * $this->max_score;

        return [$isFullyCorrect, $partialScore];
    }

    /**
     * Grade ordering/sequencing question.
     */
    private function gradeOrdering(): bool
    {
        if (empty($this->response_data)) {
            return false;
        }

        $sequence = $this->response_data['sequence'] ?? [];

        if (empty($sequence)) {
            return false;
        }

        $correctSequence = $this->question->options()
            ->orderBy('option_order')
            ->pluck('id')
            ->toArray();

        return $sequence === $correctSequence;
    }

    /**
     * Grade fill in the blanks.
     */
    private function gradeFillBlanks(): array
    {
        if (empty($this->response_data)) {
            return [false, 0];
        }

        $answers = $this->response_data['answers'] ?? [];
        $metadata = $this->question->metadata ?? [];
        $correctAnswers = $metadata['correct_answers'] ?? [];
        $caseSensitive = $metadata['case_sensitive'] ?? false;

        if (empty($correctAnswers) || empty($answers)) {
            return [false, 0];
        }

        $correctCount = 0;
        $totalBlanks = count($correctAnswers);

        foreach ($answers as $index => $answer) {
            $correctAnswer = $correctAnswers[$index] ?? null;

            if ($correctAnswer === null) {
                continue;
            }

            $studentAnswer = trim($answer);
            $compare = $caseSensitive ? $correctAnswer : mb_strtolower($correctAnswer);

            if (!$caseSensitive) {
                $studentAnswer = mb_strtolower($studentAnswer);
            }

            if ($studentAnswer === $compare) {
                $correctCount++;
            }
        }

        $isFullyCorrect = $correctCount === $totalBlanks;
        $partialScore = ($correctCount / $totalBlanks) * $this->max_score;

        return [$isFullyCorrect, $partialScore];
    }
}
