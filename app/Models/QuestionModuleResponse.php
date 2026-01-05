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
            return false;
        }

        $correctOption = $question->options()->where('is_correct', true)->first();

        if (!$correctOption) {
            return false;
        }

        // Convert answer to 'true' or 'false' string
        $answerValue = null;
        
        // If answer is numeric, it might be an option ID
        if (is_numeric($answer)) {
            $selectedOption = $question->options()->find($answer);
            if ($selectedOption) {
                // Convert option text to 'true' or 'false'
                $optionText = strtolower(trim($selectedOption->option_text));
                $answerValue = ($optionText === 'صح' || $optionText === 'true' || $optionText === '1') ? 'true' : 'false';
            }
        } else {
            // Direct string value
            $answerStr = strtolower(trim((string)$answer));
            if ($answerStr === 'صح' || $answerStr === 'true' || $answerStr === '1') {
                $answerValue = 'true';
            } elseif ($answerStr === 'خطأ' || $answerStr === 'false' || $answerStr === '0') {
                $answerValue = 'false';
            }
        }
        
        if ($answerValue === null) {
            return false;
        }

        $correctAnswer = strtolower(trim($correctOption->option_text)) === 'صح' ? 'true' : 'false';

        return $answerValue === $correctAnswer;
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
     * Grade fill in the blanks question.
     */
    private function gradeFillBlanks($question, $studentAnswer)
    {
        // Student answer should be an array of [blank_number => answer]
        if (!is_array($studentAnswer) || empty($studentAnswer)) {
            return false;
        }

        // Get all correct answers (options are the blank answers)
        $correctAnswers = $question->options()
            ->where('is_correct', true)
            ->orderBy('option_order')
            ->pluck('option_text')
            ->toArray();

        // Check if all blanks are filled correctly
        foreach ($correctAnswers as $index => $correctAnswer) {
            $blankNumber = $index + 1;

            if (!isset($studentAnswer[$blankNumber])) {
                return false;
            }

            $studentBlankAnswer = trim(strtolower($studentAnswer[$blankNumber]));
            $correctBlankAnswer = trim(strtolower($correctAnswer));

            if ($studentBlankAnswer !== $correctBlankAnswer) {
                return false;
            }
        }

        return true;
    }
}
