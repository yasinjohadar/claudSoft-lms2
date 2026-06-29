<?php

namespace App\Services\Quiz;

use App\Models\QuestionBank;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use InvalidArgumentException;

class QuizAttemptStartService
{
    public function __construct(
        protected QuizRandomSelectionService $randomSelection,
    ) {}

    /**
     * @return array{question_ids: array<int>, max_score: float, selection_meta: ?array}
     */
    public function prepareStart(Quiz $quiz, int $studentId, bool $isPreview = false): array
    {
        if ($quiz->isRandomPool()) {
            $configError = $this->randomSelection->validateQuizConfiguration($quiz);
            if ($configError) {
                throw new InvalidArgumentException($configError);
            }

            $result = $this->randomSelection->selectForAttempt(
                $quiz,
                $studentId,
                excludePreviousAttempts: ! $isPreview,
                shuffle: (bool) $quiz->shuffle_questions,
            );

            return [
                'question_ids' => $result->questionIds,
                'max_score' => $result->maxScore,
                'selection_meta' => $result->selectionMeta(),
            ];
        }

        $questionIds = $quiz->quizQuestions()
            ->whereNotNull('question_id')
            ->orderBy('question_order')
            ->pluck('question_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($quiz->shuffle_questions && count($questionIds) > 1) {
            shuffle($questionIds);
        }

        return [
            'question_ids' => $questionIds,
            'max_score' => (float) $quiz->max_score,
            'selection_meta' => null,
        ];
    }

    /**
     * @param  array<int>  $questionIds
     */
    public function createResponsesForAttempt(QuizAttempt $attempt, Quiz $quiz, array $questionIds): void
    {
        $gradeMap = $quiz->isRandomPool()
            ? $this->randomSelection->buildCandidatePool($quiz)->pluck('grade', 'question_id')->all()
            : [];

        foreach ($questionIds as $index => $questionId) {
            $questionId = (int) $questionId;
            $quizQuestion = $quiz->quizQuestions()
                ->where('question_id', $questionId)
                ->with('question.questionType')
                ->first();

            if ($quizQuestion && $quizQuestion->question) {
                $maxScore = $quizQuestion->getGrade();
                $questionTypeId = $quizQuestion->question->question_type_id;
            } else {
                $question = QuestionBank::with('questionType')->find($questionId);
                if (! $question || ! $question->question_type_id) {
                    continue;
                }
                $maxScore = (float) ($gradeMap[$questionId] ?? $question->default_grade ?? 1.0);
                $questionTypeId = $question->question_type_id;
            }

            if (! $questionTypeId) {
                continue;
            }

            QuizResponse::create([
                'attempt_id' => $attempt->id,
                'question_id' => $questionId,
                'question_type_id' => $questionTypeId,
                'max_score' => $maxScore,
                'answer_order' => $index + 1,
                'marked_for_review' => false,
            ]);
        }
    }

    public function gradeForQuestion(Quiz $quiz, int $questionId): float
    {
        $quizQuestion = $quiz->quizQuestions()->where('question_id', $questionId)->first();
        if ($quizQuestion) {
            return $quizQuestion->getGrade();
        }

        if ($quiz->isRandomPool()) {
            $match = $this->randomSelection->buildCandidatePool($quiz)->firstWhere('question_id', $questionId);
            if ($match) {
                return (float) $match['grade'];
            }
        }

        $question = QuestionBank::find($questionId);

        return (float) ($question->default_grade ?? 1.0);
    }
}
