<?php

namespace App\Services\Quiz;

use App\Models\Quiz;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class QuizRandomSelectionService
{
    /**
     * @return Collection<int, array{question_id: int, grade: float}>
     */
    public function buildCandidatePool(Quiz $quiz): Collection
    {
        $quiz->loadMissing([
            'quizQuestions.question',
            'quizQuestions.questionPool.poolItems.question',
            'quizQuestions.questionPool.questions',
        ]);

        $candidates = collect();

        foreach ($quiz->quizQuestions as $quizQuestion) {
            if ($quizQuestion->question_id !== null && $quizQuestion->question) {
                $this->addCandidate($candidates, (int) $quizQuestion->question_id, $quizQuestion->getGrade());

                continue;
            }

            if ($quizQuestion->question_pool_id !== null && $quizQuestion->questionPool) {
                $poolQuestions = $quizQuestion->questionPool->questions;

                if ($poolQuestions->isEmpty()) {
                    $poolQuestions = $quizQuestion->questionPool->poolItems
                        ->map(fn ($item) => $item->question)
                        ->filter();
                }

                foreach ($poolQuestions as $question) {
                    if ($question) {
                        $this->addCandidate($candidates, (int) $question->id, (float) $question->default_grade);
                    }
                }
            }
        }

        return $candidates->values();
    }

    /**
     * @return array<int>
     */
    public function getPreviouslyUsedQuestionIds(Quiz $quiz, int $studentId, bool $excludePreview = true): array
    {
        $query = $quiz->attempts()
            ->where('student_id', $studentId)
            ->whereIn('status', ['submitted', 'graded', 'reviewing']);

        if ($excludePreview) {
            $query->realAttempts();
        }

        $used = [];

        foreach ($query->get(['questions_order']) as $attempt) {
            foreach ($attempt->questions_order ?? [] as $questionId) {
                $used[] = (int) $questionId;
            }
        }

        return array_values(array_unique($used));
    }

    public function selectForAttempt(
        Quiz $quiz,
        int $studentId,
        bool $excludePreviousAttempts = true,
        bool $shuffle = true,
    ): QuizRandomSelectionResult {
        if (! $quiz->isRandomPool()) {
            throw new InvalidArgumentException('Quiz is not a random pool quiz.');
        }

        $pool = $this->buildCandidatePool($quiz);
        $poolSize = $pool->count();
        $needed = (int) $quiz->questions_per_attempt;

        if ($needed < 1) {
            throw new InvalidArgumentException('questions_per_attempt must be at least 1.');
        }

        if ($poolSize === 0) {
            throw new InvalidArgumentException('Random pool quiz has no questions in its bank.');
        }

        if ($needed > $poolSize) {
            throw new InvalidArgumentException('questions_per_attempt exceeds pool size.');
        }

        $previouslyUsed = $excludePreviousAttempts
            ? $this->getPreviouslyUsedQuestionIds($quiz, $studentId)
            : [];

        $fresh = $pool->reject(fn (array $c) => in_array($c['question_id'], $previouslyUsed, true));
        $selected = collect();
        $recycled = false;

        if ($fresh->count() >= $needed) {
            $selected = $fresh->shuffle()->take($needed);
        } else {
            $selected = $fresh->values();
            $remaining = $needed - $selected->count();

            if ($remaining > 0) {
                $recycledPool = $pool->reject(
                    fn (array $c) => $selected->contains('question_id', $c['question_id'])
                );

                if ($recycledPool->isNotEmpty()) {
                    $extra = $recycledPool->shuffle()->take($remaining);
                    $selected = $selected->merge($extra);
                    $recycled = true;
                }
            }
        }

        $questionIds = $selected->pluck('question_id')->map(fn ($id) => (int) $id)->all();

        if ($shuffle && count($questionIds) > 1) {
            shuffle($questionIds);
        }

        $gradesById = $selected->keyBy('question_id');
        $maxScore = $questionIds === []
            ? 0.0
            : collect($questionIds)->sum(fn (int $id) => $gradesById->get($id)['grade'] ?? 0.0);

        return new QuizRandomSelectionResult(
            questionIds: $questionIds,
            maxScore: (float) $maxScore,
            recycled: $recycled,
            meta: [
                'pool_size' => $poolSize,
                'excluded_count' => count($previouslyUsed),
                'fresh_available' => $fresh->count(),
            ],
        );
    }

    public function estimateMaxScore(Quiz $quiz): float
    {
        $pool = $this->buildCandidatePool($quiz);
        $needed = (int) ($quiz->questions_per_attempt ?? 0);

        if ($pool->isEmpty() || $needed < 1) {
            return 0.0;
        }

        $topGrades = $pool->pluck('grade')->sortDesc()->take(min($needed, $pool->count()));

        return (float) $topGrades->sum();
    }

    public function validateQuizConfiguration(Quiz $quiz): ?string
    {
        if (! $quiz->isRandomPool()) {
            return null;
        }

        if (! $quiz->questions_per_attempt || $quiz->questions_per_attempt < 1) {
            return 'يجب تحديد عدد الأسئلة لكل محاولة.';
        }

        $poolSize = $this->buildCandidatePool($quiz)->count();

        if ($poolSize === 0) {
            return 'يجب إضافة أسئلة أو مجموعات أسئلة إلى بنك الاختبار.';
        }

        if ($quiz->questions_per_attempt > $poolSize) {
            return "عدد الأسئلة لكل محاولة ({$quiz->questions_per_attempt}) أكبر من حجم البنك ({$poolSize}).";
        }

        return null;
    }

    /**
     * @param  Collection<int, array{question_id: int, grade: float}>  $candidates
     */
    private function addCandidate(Collection $candidates, int $questionId, float $grade): void
    {
        if ($candidates->contains('question_id', $questionId)) {
            return;
        }

        $candidates->push([
            'question_id' => $questionId,
            'grade' => $grade,
        ]);
    }
}
