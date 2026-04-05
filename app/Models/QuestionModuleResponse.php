<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionModuleResponse extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'student_answer',
        'is_correct',
        'score_obtained',
        'max_score',
        'feedback',
        'time_spent',
    ];

    protected $casts = [
        'student_answer' => 'array',
        'is_correct' => 'boolean',
        'score_obtained' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    /**
     * Get the attempt for this response.
     */
    public function attempt()
    {
        return $this->belongsTo(QuestionModuleAttempt::class, 'attempt_id');
    }

    /**
     * Get the question for this response.
     */
    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }

    /**
     * Grade the response based on question type.
     */
    public function gradeResponse()
    {
        $question = $this->question;
        $studentAnswer = $this->student_answer;
        $questionType = $question->questionType->name;

        $isCorrect = false;
        $score = 0;

        switch ($questionType) {
            case 'multiple_choice_single':
                $isCorrect = $this->gradeMultipleChoiceSingle($question, $studentAnswer);
                break;

            case 'multiple_choice_multiple':
                $isCorrect = $this->gradeMultipleChoiceMultiple($question, $studentAnswer);
                break;

            case 'true_false':
                $isCorrect = $this->gradeTrueFalse($question, $studentAnswer);
                break;

            case 'short_answer':
            case 'essay':
                // Manual grading required - do not auto-grade
                $isCorrect = null;
                break;

            case 'ordering':
                $isCorrect = $this->gradeOrdering($question, $studentAnswer);
                break;

            case 'matching':
                $isCorrect = $this->gradeMatching($question, $studentAnswer);
                break;

            case 'fill_blanks':
                $isCorrect = $this->gradeFillBlanks($question, $studentAnswer);
                break;

            default:
                // For other manual grading types
                $isCorrect = null;
                break;
        }

        if ($isCorrect === true) {
            $score = $this->max_score;
        } elseif ($isCorrect === false) {
            $score = 0;
        } else {
            // Manual grading required
            $score = null;
        }

        $this->update([
            'is_correct' => $isCorrect,
            'score_obtained' => $score,
        ]);

        return $isCorrect;
    }

    /**
     * Grade multiple choice single answer.
     */
    private function gradeMultipleChoiceSingle($question, $studentAnswer)
    {
        // Handle both formats: direct ID or array with 'selected_option' key
        $selectedOptionId = null;
        
        if (is_array($studentAnswer)) {
            // New format: array with 'selected_option' key
            $selectedOptionId = $studentAnswer['selected_option'] ?? null;
        } else {
            // Old format: direct ID (string or int)
            $selectedOptionId = $studentAnswer;
        }
        
        if (!$selectedOptionId) {
            return false;
        }

        $correctOption = $question->options()->where('is_correct', true)->first();

        if (!$correctOption) {
            return false;
        }

        return (int)$selectedOptionId == (int)$correctOption->id;
    }

    /**
     * Grade multiple choice multiple answers.
     */
    private function gradeMultipleChoiceMultiple($question, $studentAnswer)
    {
        // Handle both formats: direct array or array with 'selected_options' key
        $selectedOptions = null;
        
        if (is_array($studentAnswer)) {
            if (isset($studentAnswer['selected_options']) && is_array($studentAnswer['selected_options'])) {
                // New format: array with 'selected_options' key
                $selectedOptions = $studentAnswer['selected_options'];
            } elseif (isset($studentAnswer[0])) {
                // Old format: direct array of IDs
                $selectedOptions = $studentAnswer;
            }
        }
        
        if (!$selectedOptions || !is_array($selectedOptions) || empty($selectedOptions)) {
            return false;
        }

        $correctOptions = $question->options()->where('is_correct', true)->pluck('id')->toArray();
        
        // Convert to int for comparison
        $selectedOptions = array_map('intval', $selectedOptions);
        $correctOptions = array_map('intval', $correctOptions);

        sort($correctOptions);
        sort($selectedOptions);

        return $correctOptions === $selectedOptions;
    }

    /**
     * Grade true/false question.
     */
    private function gradeTrueFalse($question, $studentAnswer)
    {
        // Log input for debugging
        \Log::info('=== GRADING TRUE/FALSE ===', [
            'response_id' => $this->id,
            'question_id' => $question->id,
            'student_answer_raw' => $studentAnswer,
            'student_answer_type' => gettype($studentAnswer),
            'student_answer_is_array' => is_array($studentAnswer),
        ]);
        
        // Handle multiple formats: direct value, array with key, or option ID
        $answer = null;
        
        if (is_array($studentAnswer)) {
            $answer = $studentAnswer['answer'] ?? $studentAnswer['selected_option'] ?? null;
            // If still null, try to get first value
            if ($answer === null && !empty($studentAnswer)) {
                $answer = array_values($studentAnswer)[0] ?? null;
            }
        } else {
            // Direct value - could be string 'true'/'false' or option ID
            $answer = $studentAnswer;
        }
        
        if (!$answer) {
            \Log::warning('No answer found for true/false question', [
                'response_id' => $this->id,
                'question_id' => $question->id,
            ]);
            return false;
        }

        // Load all options
        $options = $question->options()->get();
        $correctOption = $options->where('is_correct', true)->first();

        if (!$correctOption) {
            \Log::warning('No correct option found for true/false question', [
                'response_id' => $this->id,
                'question_id' => $question->id,
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
            } elseif ($answerStr === 'خطأ' || $answerStr === 'false' || $answerStr === '0' || $answerStr === 'خطأ') {
                $answerValue = 'false';
            }
        }
        
        if ($answerValue === null) {
            \Log::warning('Could not determine answer value', [
                'response_id' => $this->id,
                'question_id' => $question->id,
                'answer' => $answer,
                'answer_type' => gettype($answer),
            ]);
            return false;
        }

        // Get correct answer - handle HTML tags in option text
        $correctOptionText = strtolower(trim(strip_tags($correctOption->option_text)));
        $correctAnswer = ($correctOptionText === 'صح' || $correctOptionText === 'true' || $correctOptionText === '1' || $correctOptionText === 'صحيح') ? 'true' : 'false';
        
        $isCorrect = $answerValue === $correctAnswer;
        
        \Log::info('=== TRUE/FALSE GRADING RESULT ===', [
            'response_id' => $this->id,
            'question_id' => $question->id,
            'student_answer_value' => $answerValue,
            'correct_answer_value' => $correctAnswer,
            'correct_option_text' => $correctOptionText,
            'correct_option_text_original' => $correctOption->option_text,
            'is_correct' => $isCorrect,
        ]);

        return $isCorrect;
    }

    /**
     * Grade short answer question.
     */
    private function gradeShortAnswer($question, $studentAnswer)
    {
        // Handle both formats: direct text or array with 'answer' key
        $answerText = null;
        
        if (is_array($studentAnswer)) {
            $answerText = $studentAnswer['answer'] ?? null;
        } else {
            // Direct text answer
            $answerText = $studentAnswer;
        }
        
        if (!$answerText || trim($answerText) === '') {
            return false;
        }

        $correctAnswers = $question->options()->where('is_correct', true)->pluck('option_text')->toArray();
        $studentAnswerText = trim(strtolower($answerText));

        foreach ($correctAnswers as $correctAnswer) {
            if (trim(strtolower($correctAnswer)) === $studentAnswerText) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grade ordering question.
     */
    private function gradeOrdering($question, $studentAnswer)
    {
        // Student answer should be an array of option IDs in the order they arranged them
        if (!is_array($studentAnswer) || empty($studentAnswer)) {
            return false;
        }

        // Get correct order from options sorted by option_order
        $correctOrder = $question->options()
            ->orderBy('option_order')
            ->pluck('id')
            ->toArray();

        // Compare arrays
        return $studentAnswer === $correctOrder;
    }

    /**
     * Grade matching question.
     */
    private function gradeMatching($question, $studentAnswer)
    {
        // Student answer should be an array of [prompt_id => option_id]
        if (!is_array($studentAnswer) || empty($studentAnswer)) {
            return false;
        }

        // Get all correct matches
        $correctMatches = $question->options()
            ->where('is_correct', true)
            ->get()
            ->keyBy('id')
            ->toArray();

        $allCorrect = true;

        foreach ($studentAnswer as $promptId => $selectedOptionId) {
            // Find the correct option for this prompt
            $correctOption = collect($correctMatches)->first(function($option) use ($promptId) {
                return isset($option['match_prompt_id']) && $option['match_prompt_id'] == $promptId;
            });

            if (!$correctOption || $correctOption['id'] != $selectedOptionId) {
                $allCorrect = false;
                break;
            }
        }

        return $allCorrect;
    }

    /**
     * Grade fill in the blanks question (مصدر السؤال: بنك الأسئلة — انظر QuestionBank).
     */
    private function gradeFillBlanks($question, $studentAnswer)
    {
        if (! is_array($studentAnswer) || empty($studentAnswer)) {
            return false;
        }

        return $question->matchesFillBlanksAnswer($studentAnswer);
    }
}
