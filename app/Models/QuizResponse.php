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
                // Short answer questions require manual grading (similar to essay)
                // They can have multiple correct answers and need human judgment
                return;

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

            case 'drag_drop':
                [$isCorrect, $scoreObtained] = $this->gradeDragDrop();
                break;

            case 'calculated':
                // Calculated questions - similar to numerical but with formula evaluation
                $isCorrect = $this->gradeCalculated();
                $scoreObtained = $isCorrect ? $this->max_score : 0;
                break;

            case 'essay':
                // Essay questions require manual grading
                return;

            default:
                // Unknown question type - skip auto-grading
                \Log::warning('Unknown question type for auto-grading', [
                    'response_id' => $this->id,
                    'question_type' => $questionType,
                ]);
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
        // Support both formats: selected_option_ids (old) and response_data['answer'] (new)
        $selectedOptionId = null;
        
        if (!empty($this->selected_option_ids)) {
            $selectedOptionId = is_array($this->selected_option_ids) ? $this->selected_option_ids[0] : $this->selected_option_ids;
        } elseif (!empty($this->response_data)) {
            $answer = $this->response_data['answer'] ?? null;
            if ($answer !== null) {
                if (is_array($answer)) {
                    $selectedOptionId = $answer[0] ?? null;
                } else {
                    $selectedOptionId = $answer;
                }
            }
        }

        if (!$selectedOptionId) {
            return false;
        }

        // Find the selected option and check if it's correct
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
        // Log input for debugging
        \Log::info('=== QUIZ GRADING TRUE/FALSE ===', [
            'response_id' => $this->id,
            'question_id' => $this->question_id,
            'response_text' => $this->response_text,
            'response_data' => $this->response_data,
            'selected_option_ids' => $this->selected_option_ids,
        ]);
        
        // Handle multiple formats: direct value, array with key, or option ID
        $answer = null;
        
        // Try to get answer from different sources
        if (!empty($this->response_data)) {
            $answer = $this->response_data['answer'] ?? null;
            if (is_array($answer) && !empty($answer)) {
                $answer = array_values($answer)[0] ?? null;
            }
        }
        
        if ($answer === null && !empty($this->selected_option_ids)) {
            $answer = is_array($this->selected_option_ids) ? $this->selected_option_ids[0] : $this->selected_option_ids;
        }
        
        if ($answer === null && !empty($this->response_text)) {
            $answer = $this->response_text;
        }
        
        if (!$answer) {
            \Log::warning('No answer found for true/false question', [
                'response_id' => $this->id,
                'question_id' => $this->question_id,
            ]);
            return false;
        }

        // Load all options
        $options = $this->question->options()->get();
        $correctOption = $options->where('is_correct', true)->first();

        if (!$correctOption) {
            \Log::warning('No correct option found for true/false question', [
                'response_id' => $this->id,
                'question_id' => $this->question_id,
            ]);
            return false;
        }

        // Convert answer to 'true' or 'false' string
        $answerValue = null;
        
        // If answer is numeric, it might be an option ID
        if (is_numeric($answer)) {
            $selectedOption = $options->find($answer);
            if ($selectedOption) {
                // Convert option text to 'true' or 'false'
                $optionText = strtolower(trim(strip_tags($selectedOption->option_text)));
                \Log::info('Numeric answer - found option', [
                    'option_id' => $answer,
                    'option_text' => $optionText,
                    'option_text_original' => $selectedOption->option_text,
                ]);
                $answerValue = ($optionText === 'صح' || $optionText === 'true' || $optionText === '1' || $optionText === 'صحيح') ? 'true' : 'false';
            } else {
                \Log::warning('Numeric answer but option not found', [
                    'option_id' => $answer,
                    'available_options' => $options->pluck('id', 'option_text')->toArray(),
                ]);
            }
        } else {
            // Direct string value
            $answerStr = strtolower(trim(strip_tags((string)$answer)));
            \Log::info('String answer processing', [
                'answer_original' => $answer,
                'answer_cleaned' => $answerStr,
            ]);
            if ($answerStr === 'صح' || $answerStr === 'true' || $answerStr === '1' || $answerStr === 'صحيح') {
                $answerValue = 'true';
            } elseif ($answerStr === 'خطأ' || $answerStr === 'false' || $answerStr === '0') {
                $answerValue = 'false';
            }
        }
        
        if ($answerValue === null) {
            \Log::warning('Could not determine answer value', [
                'response_id' => $this->id,
                'question_id' => $this->question_id,
                'answer' => $answer,
                'answer_type' => gettype($answer),
            ]);
            return false;
        }

        // Get correct answer - handle HTML tags in option text
        $correctOptionText = strtolower(trim(strip_tags($correctOption->option_text)));
        $correctAnswer = ($correctOptionText === 'صح' || $correctOptionText === 'true' || $correctOptionText === '1' || $correctOptionText === 'صحيح') ? 'true' : 'false';
        
        $isCorrect = $answerValue === $correctAnswer;
        
        \Log::info('=== QUIZ TRUE/FALSE GRADING RESULT ===', [
            'response_id' => $this->id,
            'question_id' => $this->question_id,
            'student_answer_value' => $answerValue,
            'correct_answer_value' => $correctAnswer,
            'correct_option_text' => $correctOptionText,
            'correct_option_text_original' => $correctOption->option_text,
            'is_correct' => $isCorrect,
        ]);

        return $isCorrect;
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
        $metadata = $this->question->metadata ?? [];
        $correctAnswer = $metadata['correct_answer'] ?? null;
        $tolerance = $metadata['tolerance'] ?? 0;

        if ($correctAnswer === null) {
            return false;
        }

        // Get student answer from response_text or response_data
        $studentAnswer = null;
        if (!empty($this->response_text)) {
            $studentAnswer = $this->response_text;
        } elseif (!empty($this->response_data)) {
            if (isset($this->response_data['answer'])) {
                $studentAnswer = $this->response_data['answer'];
            } elseif (isset($this->response_data['numeric_value'])) {
                $studentAnswer = $this->response_data['numeric_value'];
            }
        }

        if ($studentAnswer === null || !is_numeric($studentAnswer)) {
            return false;
        }

        $studentValue = floatval($studentAnswer);
        $correctValue = floatval($correctAnswer);

        $difference = abs($studentValue - $correctValue);

        return $difference <= $tolerance;
    }

    /**
     * Grade calculated question.
     */
    private function gradeCalculated(): bool
    {
        if (empty($this->response_data) && empty($this->response_text)) {
            return false;
        }

        $metadata = $this->question->metadata ?? [];
        $formula = $metadata['formula'] ?? null;
        $correctAnswer = $metadata['correct_answer'] ?? null;
        $tolerance = $metadata['tolerance'] ?? 0;

        if ($correctAnswer === null) {
            return false;
        }

        // Get student answer from response_data or response_text
        $studentAnswer = null;
        if (!empty($this->response_data)) {
            if (isset($this->response_data['answer'])) {
                $studentAnswer = $this->response_data['answer'];
            } elseif (isset($this->response_data['numeric_value'])) {
                $studentAnswer = $this->response_data['numeric_value'];
            }
        } elseif (!empty($this->response_text)) {
            $studentAnswer = $this->response_text;
        }

        if ($studentAnswer === null) {
            return false;
        }

        // Convert to numeric value
        if (is_array($studentAnswer)) {
            // If it's an array, try to get numeric value
            $studentAnswer = $studentAnswer['numeric_value'] ?? $studentAnswer['answer'] ?? null;
        }

        if (!is_numeric($studentAnswer)) {
            $studentAnswer = floatval($studentAnswer);
        }

        if (!is_numeric($studentAnswer)) {
            return false;
        }

        $studentValue = floatval($studentAnswer);
        $correctValue = floatval($correctAnswer);

        $difference = abs($studentValue - $correctValue);

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

        // Support both formats: 'pairs' (old) and 'answer' (new from QuestionModule format)
        $answerData = $this->response_data['answer'] ?? $this->response_data['pairs'] ?? $this->response_data;
        
        // If answer is an object/array with option IDs as keys, convert to pairs format
        $pairs = [];
        if (is_array($answerData)) {
            // Check if it's in the new format (option_id => feedback value)
            $isNewFormat = false;
            foreach ($answerData as $key => $value) {
                if (is_numeric($key) && is_string($value)) {
                    $isNewFormat = true;
                    // Convert to pairs: option_id => feedback
                    $option = $this->question->options->find($key);
                    if ($option) {
                        $pairs[] = [
                            'left' => $key,
                            'right' => $value, // This is the feedback value
                        ];
                    }
                } elseif (isset($value['left']) && isset($value['right'])) {
                    // Already in pairs format
                    $pairs[] = $value;
                }
            }
        }

        $correctPairs = 0;
        $totalPairs = 0;

        $options = $this->question->options;

        foreach ($pairs as $pair) {
            $leftId = $pair['left'] ?? null;
            $rightValue = $pair['right'] ?? null;

            if (!$leftId || !$rightValue) {
                continue;
            }

            $totalPairs++;

            // Find if this pair is correct
            $option = $options->firstWhere('id', $leftId);

            // Check if the right value matches the option's feedback
            if ($option && $option->feedback == $rightValue) {
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

        // Support both formats: 'sequence' (old) and 'answer' (new from QuestionModule format)
        $sequence = $this->response_data['answer'] ?? $this->response_data['sequence'] ?? [];

        if (empty($sequence)) {
            return false;
        }

        // If sequence is a JSON string, decode it
        if (is_string($sequence)) {
            $decoded = json_decode($sequence, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $sequence = $decoded;
            }
        }

        $correctSequence = $this->question->options()
            ->orderBy('option_order')
            ->pluck('id')
            ->toArray();

        // Compare arrays (both should be arrays of IDs)
        if (!is_array($sequence) || !is_array($correctSequence)) {
            return false;
        }

        return $sequence === $correctSequence;
    }

    /**
     * خريطة فراغات الطالب من response_data: يدعم ['answer'=>[...]] و ['answers'=>...] والشكل المسطح {0:"x"} كما يحفظه QuizAttemptController للأنواع المعقدة.
     *
     * @return array<int, mixed>
     */
    private function getFillBlanksAnswerMapFromResponseData(): array
    {
        $raw = $this->response_data;
        if (! is_array($raw) || $raw === []) {
            return [];
        }

        if (isset($raw['answer']) && is_array($raw['answer'])) {
            return $raw['answer'];
        }

        if (isset($raw['answers']) && is_array($raw['answers'])) {
            return $raw['answers'];
        }

        $reserved = ['answer', 'answers', 'numeric_value'];
        $out = [];
        foreach ($raw as $k => $v) {
            if (in_array($k, $reserved, true)) {
                continue;
            }
            if (is_int($k)) {
                $out[$k] = $v;
            } elseif (is_string($k) && $k !== '' && ctype_digit($k)) {
                $out[(int) $k] = $v;
            }
        }

        return $out;
    }

    /**
     * Grade fill in the blanks.
     *
     * أسئلة بنك الأسئلة تُصحَّح من جدول الخيارات (is_correct + option_order) عند وجود خيارات صحيحة؛
     * وإلا يُستخدم metadata.correct_answers للتوافق مع البيانات القديمة.
     */
    private function gradeFillBlanks(): array
    {
        if (empty($this->response_data)) {
            return [false, 0];
        }

        $answers = $this->getFillBlanksAnswerMapFromResponseData();

        if ($answers === []) {
            return [false, 0];
        }

        $question = $this->question;
        if ($question === null) {
            return [false, 0];
        }

        $usesBankOptions = $question->options()->where('is_correct', true)->exists();

        if ($usesBankOptions) {
            $summary = $question->summarizeFillBlanksFromOptions($answers);
            if ($summary['blank_count'] < 1) {
                return [false, 0];
            }

            $isFullyCorrect = $summary['all_filled']
                && $summary['correct_count'] === $summary['blank_count'];
            $partialScore = ($summary['correct_count'] / $summary['blank_count']) * $this->max_score;

            return [$isFullyCorrect, $partialScore];
        }

        $metadata = $question->metadata ?? [];
        $correctAnswers = $metadata['correct_answers'] ?? [];
        $caseSensitive = $metadata['case_sensitive'] ?? false;

        if (empty($correctAnswers)) {
            return [false, 0];
        }

        $correctCount = 0;
        $totalBlanks = count($correctAnswers);

        foreach ($answers as $index => $answer) {
            $correctAnswer = $correctAnswers[$index] ?? null;

            if ($correctAnswer === null) {
                continue;
            }

            $studentAnswer = trim((string) $answer);
            $compare = $caseSensitive ? $correctAnswer : mb_strtolower((string) $correctAnswer);

            if (! $caseSensitive) {
                $studentAnswer = mb_strtolower($studentAnswer);
            }

            if ($studentAnswer === $compare) {
                $correctCount++;
            }
        }

        $isFullyCorrect = $correctCount === $totalBlanks;
        $partialScore = $totalBlanks > 0 ? ($correctCount / $totalBlanks) * $this->max_score : 0;

        return [$isFullyCorrect, $partialScore];
    }

    /**
     * Grade drag and drop question.
     */
    private function gradeDragDrop(): array
    {
        if (empty($this->response_data)) {
            return [false, 0];
        }

        // Support both formats: 'answer' (new from QuestionModule format) and direct response_data
        $answerData = $this->response_data['answer'] ?? $this->response_data;

        if (empty($answerData) || !is_array($answerData)) {
            return [false, 0];
        }

        $correctPairs = 0;
        $totalPairs = 0;

        $options = $this->question->options;

        // Format: option_id => feedback_value
        foreach ($answerData as $optionId => $feedbackValue) {
            if (!is_numeric($optionId)) {
                continue;
            }

            $totalPairs++;

            // Find the option
            $option = $options->find($optionId);

            // Check if the feedback value matches the option's feedback
            if ($option && $option->feedback == $feedbackValue) {
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
     * Auto-grade essay using AI
     *
     * @param string|null $providerName
     * @return void
     */
    public function autoGradeEssay(?string $providerName = null): void
    {
        // Check if AI grading is enabled for this question
        $question = $this->question;
        if (!$question) {
            return;
        }

        // AI grading removed - skip automatic grading for essay questions
        return;

        try {
            $essayGradingService = app(\App\Services\AI\EssayGradingService::class);
            
            $studentAnswer = $this->response_text ?? '';
            if (empty($studentAnswer)) {
                return;
            }

            $result = $essayGradingService->gradeEssay(
                $this->id,
                $question->id,
                $studentAnswer,
                $providerName
            );

            // The grading service already updates the response
            // This method is just a wrapper for convenience
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI essay grading failed', [
                'response_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            
            // Don't throw - allow manual grading fallback
        }
    }
}
