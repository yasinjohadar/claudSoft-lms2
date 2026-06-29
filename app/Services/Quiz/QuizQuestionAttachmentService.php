<?php

namespace App\Services\Quiz;

use App\Models\QuestionBank;
use App\Models\Quiz;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuizQuestionAttachmentService
{
    /**
     * @param  Collection<int, QuestionBank>|array<int, QuestionBank>  $questions
     */
    public function attachQuestionBankItems(Quiz $quiz, Collection|array $questions): int
    {
        $items = $questions instanceof Collection ? $questions : collect($questions);

        if ($items->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($quiz, $items) {
            $lockedQuiz = Quiz::lockForUpdate()->findOrFail($quiz->id);

            $maxOrder = (int) (DB::table('quiz_questions')
                ->where('quiz_id', $lockedQuiz->id)
                ->max('question_order') ?? 0);

            $addedCount = 0;

            foreach ($items as $question) {
                if (! $question instanceof QuestionBank) {
                    continue;
                }

                $exists = DB::table('quiz_questions')
                    ->where('quiz_id', $lockedQuiz->id)
                    ->where('question_id', $question->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $maxOrder++;
                $lockedQuiz->questions()->attach($question->id, [
                    'question_order' => $maxOrder,
                    'question_grade' => $question->default_grade,
                    'is_required' => false,
                ]);
                $addedCount++;
            }

            if ($addedCount > 0) {
                $lockedQuiz->update(['max_score' => $lockedQuiz->calculateMaxScore()]);
            }

            return $addedCount;
        });
    }
}
