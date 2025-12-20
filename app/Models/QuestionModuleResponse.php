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
                $isCorrect = $this->gradeShortAnswer($question, $studentAnswer);
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
                // For essay and other manual grading types
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
        if (!isset($studentAnswer['selected_option'])) {
            return false;
        }

        $correctOption = $question->options()->where('is_correct', true)->first();

        if (!$correctOption) {
            return false;
        }

        return $studentAnswer['selected_option'] == $correctOption->id;
    }

    /**
     * Grade multiple choice multiple answers.
     */
    private function gradeMultipleChoiceMultiple($question, $studentAnswer)
    {
        if (!isset($studentAnswer['selected_options']) || !is_array($studentAnswer['selected_options'])) {
            return false;
        }

        $correctOptions = $question->options()->where('is_correct', true)->pluck('id')->toArray();
        $selectedOptions = $studentAnswer['selected_options'];

        sort($correctOptions);
        sort($selectedOptions);

        return $correctOptions === $selectedOptions;
    }

    /**
     * Grade true/false question.
     */
    private function gradeTrueFalse($question, $studentAnswer)
    {
        if (!isset($studentAnswer['answer'])) {
            return false;
        }

        $correctOption = $question->options()->where('is_correct', true)->first();

        if (!$correctOption) {
            return false;
        }

        $correctAnswer = strtolower($correctOption->option_text) === 'صح' ? 'true' : 'false';

        return $studentAnswer['answer'] === $correctAnswer;
    }

    /**
     * Grade short answer question.
     */
    private function gradeShortAnswer($question, $studentAnswer)
    {
        if (!isset($studentAnswer['answer'])) {
            return false;
        }

        $correctAnswers = $question->options()->where('is_correct', true)->pluck('option_text')->toArray();
        $studentAnswerText = trim(strtolower($studentAnswer['answer']));

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
