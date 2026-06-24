<?php

namespace App\Services\Quiz;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use App\Models\User;
use App\Services\Api\StudentQuizApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuizPreviewFlowService
{
    public function __construct(
        protected StudentQuizApiService $quizApiService
    ) {}

    public function startPreviewAttempt(Quiz $quiz, User $admin, Request $request): QuizAttempt
    {
        $existingAttempt = $quiz->attempts()
            ->preview()
            ->where('student_id', $admin->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return $existingAttempt;
        }

        DB::beginTransaction();

        try {
            $attemptNumber = $quiz->attempts()
                ->preview()
                ->where('student_id', $admin->id)
                ->count() + 1;

            $questionIds = $quiz->quizQuestions()->pluck('question_id')->toArray();

            if ($quiz->shuffle_questions) {
                shuffle($questionIds);
            }

            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $admin->id,
                'attempt_number' => $attemptNumber,
                'status' => 'in_progress',
                'started_at' => now(),
                'max_score' => $quiz->max_score,
                'questions_order' => $questionIds,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_completed' => false,
                'is_preview' => true,
            ]);

            foreach ($questionIds as $index => $questionId) {
                $quizQuestion = $quiz->quizQuestions()
                    ->where('question_id', $questionId)
                    ->with('question.questionType')
                    ->first();

                if (! $quizQuestion || ! $quizQuestion->question) {
                    continue;
                }

                $questionTypeId = $quizQuestion->question->question_type_id;
                if (! $questionTypeId) {
                    continue;
                }

                QuizResponse::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                    'question_type_id' => $questionTypeId,
                    'max_score' => $quizQuestion->getGrade(),
                    'answer_order' => $index + 1,
                    'marked_for_review' => false,
                ]);
            }

            DB::commit();

            return $attempt->fresh(['quiz.settings', 'responses']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @return array{questions: Collection, remainingTime: ?int}
     */
    public function loadTakeData(QuizAttempt $attempt): array
    {
        $attempt->loadMissing([
            'quiz.settings',
            'quiz.quizQuestions.question.questionType',
            'quiz.quizQuestions.question.options',
            'responses',
        ]);

        if ($attempt->quiz->time_limit && $attempt->started_at) {
            $elapsedSeconds = $attempt->started_at->diffInSeconds(now());
            $totalSeconds = $attempt->quiz->time_limit * 60;
            $remainingTime = max(0, $totalSeconds - $elapsedSeconds);

            if ($remainingTime <= 0) {
                $this->submitPreviewAttempt($attempt, []);

                return [
                    'questions' => collect(),
                    'remainingTime' => 0,
                    'timedOut' => true,
                ];
            }
        } else {
            $remainingTime = null;
        }

        $questions = $this->quizApiService->orderedQuestionsForAttempt($attempt);

        $questions = $questions->map(function ($question) use ($attempt) {
            if (! $question->relationLoaded('options')) {
                $question->load(['options' => fn ($q) => $q->orderBy('option_order', 'asc')]);
            } else {
                $question->setRelation('options', $question->options->sortBy('option_order')->values());
            }

            if ($attempt->quiz->shuffle_answers && $question->options->isNotEmpty()) {
                $question->setRelation('options', $question->options->shuffle()->values());
            }

            return $question;
        });

        return [
            'questions' => $questions,
            'remainingTime' => $remainingTime ?? null,
            'timedOut' => false,
        ];
    }

    public function saveAnswer(QuizAttempt $attempt, Request $request): void
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:question_bank,id',
            'answer' => 'nullable',
            'response_text' => 'nullable|string',
            'response_data' => 'nullable|array',
            'selected_option_ids' => 'nullable|array',
            'time_spent' => 'nullable|integer|min:0',
            'marked_for_review' => 'nullable|boolean',
        ]);

        $response = $attempt->responses()
            ->where('question_id', $validated['question_id'])
            ->firstOrFail();

        if ($request->has('answer')) {
            $this->quizApiService->applyAnswerPayloadToResponse($response, $validated['answer']);

            return;
        }

        $response->update([
            'response_text' => $validated['response_text'] ?? $response->response_text,
            'response_data' => $validated['response_data'] ?? $response->response_data,
            'selected_option_ids' => $validated['selected_option_ids'] ?? $response->selected_option_ids,
            'time_spent' => $validated['time_spent'] ?? $response->time_spent,
            'marked_for_review' => $validated['marked_for_review'] ?? $response->marked_for_review,
        ]);
    }

    /**
     * @param  array<int|string, mixed>  $answersByQuestionId
     */
    public function submitPreviewAttempt(QuizAttempt $attempt, array $answersByQuestionId = []): QuizAttempt
    {
        DB::beginTransaction();

        try {
            foreach ($answersByQuestionId as $questionId => $answer) {
                $response = $attempt->responses()->where('question_id', (int) $questionId)->first();
                if ($response) {
                    $this->quizApiService->applyAnswerPayloadToResponse($response, $answer);
                }
            }

            $attempt->load(['responses.question.questionType', 'responses.question.options']);

            $timeSpent = $attempt->calculateTimeSpent();
            $attempt->submit();
            $attempt->update(['time_spent' => $timeSpent]);

            foreach ($attempt->responses as $response) {
                $questionType = $response->question->questionType->name ?? '';
                $requiresManualGrading = in_array($questionType, ['short_answer', 'essay'], true);

                if (! $requiresManualGrading && $this->responseHasAnswer($response)) {
                    try {
                        $response->autoGrade();
                        $response->refresh();
                    } catch (\Throwable) {
                        // continue grading other responses
                    }
                }
            }

            $attempt->grade();

            DB::commit();

            return $attempt->fresh(['quiz.settings', 'quiz.course', 'responses.question.questionType', 'responses.question.options']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @return array{orderedResponses: Collection, stats: array<string, int>}
     */
    public function loadReviewData(QuizAttempt $attempt): array
    {
        $attempt->load([
            'quiz.settings',
            'quiz.course',
            'responses.question.questionType',
            'responses.question.options',
        ]);

        $orderedResponses = collect($attempt->questions_order)->map(function ($questionId) use ($attempt) {
            return $attempt->responses()
                ->where('question_id', $questionId)
                ->with('question.options')
                ->first();
        })->filter();

        $stats = [
            'total_questions' => $orderedResponses->count(),
            'answered' => $orderedResponses->filter(fn ($response) => $this->responseHasAnswer($response))->count(),
            'correct' => $orderedResponses->where('is_correct', true)->count(),
            'incorrect' => $orderedResponses->where('is_correct', false)->count(),
            'graded' => $orderedResponses->whereNotNull('score_obtained')->count(),
            'ungraded' => $orderedResponses->whereNull('score_obtained')->count(),
        ];

        return compact('orderedResponses', 'stats');
    }

    private function responseHasAnswer(QuizResponse $response): bool
    {
        if (! empty($response->response_data)) {
            if (is_array($response->response_data)) {
                foreach ($response->response_data as $value) {
                    if ($value !== null && $value !== '' && $value !== []) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        }

        if (! empty($response->selected_option_ids)) {
            if (is_array($response->selected_option_ids)) {
                return ! empty(array_filter($response->selected_option_ids));
            }

            return true;
        }

        if (! empty($response->response_text)) {
            $text = trim($response->response_text);

            return $text !== '' && $text !== 'null' && $text !== '[]';
        }

        return false;
    }
}
