<?php

namespace App\Services\Api;

use App\Events\QuizCompleted;
use App\Models\CourseModule;
use App\Models\QuestionBank;
use App\Models\Quiz;
use App\Models\QuizAnalytics;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\QuizResponse;
use App\Models\User;
use App\Services\Api\StudentModuleProgressApiService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * منطق مشترك لـ API الاختبارات (لا يُستدعى من واجهات الويب الحالية).
 */
class StudentQuizApiService
{
    public function userCanAccessQuiz(User $user, Quiz $quiz): bool
    {
        $isEnrolled = $user->enrollments()
            ->where('course_id', $quiz->course_id)
            ->where('enrollment_status', 'active')
            ->exists();

        if (! $isEnrolled) {
            return false;
        }

        if (! $quiz->is_published || ! $quiz->is_visible) {
            return false;
        }

        return true;
    }

    /**
     * @return Collection<int, QuestionBank>
     */
    public function orderedQuestionsForAttempt(QuizAttempt $attempt): Collection
    {
        $attempt->loadMissing(['quiz.quizQuestions']);

        if (empty($attempt->questions_order)) {
            return $attempt->quiz->quizQuestions()
                ->with(['question.questionType', 'question.options' => fn ($q) => $q->orderBy('option_order')])
                ->get()
                ->map(function (QuizQuestion $qq) {
                    $q = $qq->question;
                    if (! $q) {
                        return null;
                    }
                    $q->setRelation('pivot', (object) [
                        'question_grade' => $qq->getGrade(),
                    ]);

                    return $q;
                })
                ->filter();
        }

        return collect($attempt->questions_order)->map(function ($questionId) use ($attempt) {
            $quizQuestion = $attempt->quiz->quizQuestions()
                ->where('question_id', $questionId)
                ->with(['question.questionType', 'question.options' => fn ($q) => $q->orderBy('option_order')])
                ->first();

            if (! $quizQuestion || ! $quizQuestion->question) {
                return null;
            }

            $question = $quizQuestion->question;
            $question->setRelation('pivot', (object) [
                'question_grade' => $quizQuestion->getGrade(),
            ]);

            return $question;
        })->filter();
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeQuestionForClient(QuestionBank $question): array
    {
        $typeName = $question->questionType?->name ?? '';

        $options = $question->options->map(function ($opt) {
            return [
                'id' => (int) $opt->id,
                'option_text' => (string) $opt->option_text,
                'option_order' => (int) $opt->option_order,
                'option_image' => $opt->option_image ? (string) $opt->option_image : null,
                'feedback' => $opt->feedback ? (string) $opt->feedback : null,
            ];
        })->values()->all();

        return [
            'id' => (int) $question->id,
            'question_text' => (string) $question->question_text,
            'question_image' => $question->question_image ? (string) $question->question_image : null,
            'question_type' => $typeName,
            'max_score' => isset($question->pivot->question_grade)
                ? (float) $question->pivot->question_grade
                : (float) ($question->default_grade ?? 1.0),
            'metadata' => $question->metadata,
            'options' => $options,
        ];
    }

    public function applyAnswerPayloadToResponse(QuizResponse $response, mixed $answer): void
    {
        $question = $response->question ?? QuestionBank::with('questionType')->find($response->question_id);
        if (! $question) {
            return;
        }

        $questionType = $question->questionType->name ?? '';

        if (is_string($answer)) {
            $decoded = json_decode($answer, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $answer = $decoded;
            }
        }

        if (in_array($questionType, ['multiple_choice_single', 'true_false'], true)) {
            if (is_array($answer)) {
                $response->update([
                    'selected_option_ids' => $answer,
                    'response_data' => ['answer' => $answer],
                ]);
            } else {
                $response->update([
                    'response_text' => (string) $answer,
                    'response_data' => ['answer' => $answer],
                ]);
            }
        } elseif ($questionType === 'multiple_choice_multiple') {
            $answerArray = is_array($answer) ? $answer : [$answer];
            $response->update([
                'selected_option_ids' => $answerArray,
                'response_data' => ['answer' => $answerArray],
            ]);
        } elseif (in_array($questionType, ['short_answer', 'essay'], true)) {
            $response->update([
                'response_text' => is_string($answer) ? $answer : (is_array($answer) ? json_encode($answer, JSON_UNESCAPED_UNICODE) : (string) $answer),
                'response_data' => ['answer' => $answer],
            ]);
        } elseif (in_array($questionType, ['numerical', 'calculated'], true)) {
            $response->update([
                'response_text' => is_string($answer) ? $answer : (is_numeric($answer) ? (string) $answer : ''),
                'response_data' => ['answer' => $answer, 'numeric_value' => is_numeric($answer) ? (float) $answer : null],
            ]);
        } elseif ($questionType === 'fill_blanks') {
            $map = is_array($answer) ? $answer : ['0' => $answer];
            $response->update([
                'response_data' => ['answer' => $map],
            ]);
        } elseif ($questionType === 'matching') {
            // matching answer is a Map<int, String> from Flutter, sent as Map<String, String>
            $response->update([
                'response_data' => is_array($answer) ? $answer : ['answer' => $answer],
            ]);
        } else {
            $response->update([
                'response_data' => is_array($answer) ? $answer : ['answer' => $answer],
            ]);
        }
    }

    /**
     * @param  array<int|string, mixed>  $answersByQuestionId
     */
    public function submitAndGradeAttempt(QuizAttempt $attempt, User $user, array $answersByQuestionId): QuizAttempt
    {
        DB::beginTransaction();
        try {
            foreach ($answersByQuestionId as $questionId => $answer) {
                try {
                    $qid = (int) $questionId;
                    $response = $attempt->responses()->where('question_id', $qid)->first();
                    if ($response) {
                        $this->applyAnswerPayloadToResponse($response, $answer);
                    }
                } catch (\Throwable $e) {
                    Log::error('[Quiz API] Error saving answer in submit', [
                        'question_id' => $questionId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $attempt->load(['responses.question.questionType', 'responses.question.options']);
            $timeSpent = $attempt->calculateTimeSpent();
            $attempt->submit();
            $attempt->update(['time_spent' => $timeSpent]);

            foreach ($attempt->responses as $response) {
                $questionType = $response->question->questionType->name ?? '';
                $requiresManualGrading = in_array($questionType, ['short_answer', 'essay'], true);

                $hasAnswer = $this->responseHasAnswer($response);

                if (! $requiresManualGrading && $hasAnswer) {
                    try {
                        $response->autoGrade();
                        $response->refresh();
                    } catch (\Throwable $e) {
                        Log::error('[Quiz API] autoGrade failed', [
                            'response_id' => $response->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $attempt->grade();

            $analytics = QuizAnalytics::firstOrNew([
                'student_id' => $user->id,
                'quiz_id' => $attempt->quiz_id,
                'course_id' => $attempt->quiz->course_id,
            ]);
            $analytics->recalculate();

            $eventScore = (int) round((float) ($attempt->total_score ?? 0));

            QuizCompleted::dispatch(
                $user,
                $attempt->quiz,
                $eventScore,
                $attempt->quiz->quizQuestions()->count(),
                $attempt->id,
                $timeSpent
            );

            event(new \App\Events\N8nWebhookEvent('quiz.completed', [
                'student_id' => $user->id,
                'student_name' => $user->name,
                'student_email' => $user->email,
                'quiz_id' => $attempt->quiz_id,
                'quiz_title' => $attempt->quiz->title ?? null,
                'course_id' => $attempt->quiz->course_id ?? null,
                'attempt_id' => $attempt->id,
                'score' => (int) round((float) ($attempt->total_score ?? 0)),
                'total_questions' => $attempt->quiz->quizQuestions()->count(),
                'time_spent' => $timeSpent,
                'completed_at' => now()->toIso8601String(),
            ]));

            DB::commit();
            $attempt->refresh();
            $this->syncQuizModuleCompletion($attempt, $user);

            return $attempt;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * يحدّث تقدم الكورس: يحدد وحدة الـ quiz كمكتملة (مثل «تم الإنجاز» من الويب).
     */
    protected function syncQuizModuleCompletion(QuizAttempt $attempt, User $user): void
    {
        try {
            $attempt->loadMissing('quiz');
            $quiz = $attempt->quiz;
            if (! $quiz) {
                return;
            }

            $module = CourseModule::query()
                ->where('course_id', $quiz->course_id)
                ->where('module_type', 'quiz')
                ->where('modulable_id', $quiz->id)
                ->where(function ($q) {
                    $q->where('modulable_type', Quiz::class)
                        ->orWhere('modulable_type', 'App\\Models\\Quiz');
                })
                ->first();

            if (! $module) {
                return;
            }

            app(StudentModuleProgressApiService::class)->markModuleComplete($user, (int) $module->id);
        } catch (\Throwable $e) {
            Log::warning('[Quiz API] syncQuizModuleCompletion failed', [
                'attempt_id' => $attempt->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * تسليم تلقائي عند انتهاء الوقت (مثل واجهة الويب).
     */
    public function autoSubmitIfTimeExpired(QuizAttempt $attempt): bool
    {
        if ($attempt->status !== 'in_progress') {
            return false;
        }

        $quiz = $attempt->quiz;
        if (! $quiz || ! $quiz->time_limit || ! $attempt->started_at) {
            return false;
        }

        $elapsedMinutes = $attempt->started_at->diffInMinutes(now());
        if ($elapsedMinutes <= $quiz->time_limit) {
            return false;
        }

        DB::beginTransaction();
        try {
            $timeSpent = $attempt->calculateTimeSpent();
            $attempt->submit();
            $attempt->update(['time_spent' => $timeSpent]);

            $attempt->load('responses.questionType');
            foreach ($attempt->responses as $response) {
                $questionType = $response->questionType->name ?? '';
                if (in_array($questionType, ['essay', 'calculated'], true)) {
                    continue;
                }
                try {
                    $response->autoGrade();
                } catch (\Throwable) {
                    // continue
                }
            }

            $attempt->grade();

            $analytics = QuizAnalytics::firstOrNew([
                'student_id' => $attempt->student_id,
                'quiz_id' => $attempt->quiz_id,
                'course_id' => $attempt->quiz->course_id,
            ]);
            $analytics->recalculate();

            DB::commit();
            $attempt->refresh();
            $student = User::find($attempt->student_id);
            if ($student) {
                $this->syncQuizModuleCompletion($attempt, $student);
            }

            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Quiz API] autoSubmitIfTimeExpired failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function responseHasAnswer(QuizResponse $response): bool
    {
        if (! empty($response->response_data) && is_array($response->response_data)) {
            foreach ($response->response_data as $value) {
                if ($value !== null && $value !== '' && $value !== []) {
                    return true;
                }
            }
        }

        if (! empty($response->selected_option_ids)) {
            if (is_array($response->selected_option_ids)) {
                return ! empty(array_filter($response->selected_option_ids));
            }

            return true;
        }

        if (! empty($response->response_text)) {
            $text = trim((string) $response->response_text);

            return $text !== '' && $text !== 'null' && $text !== '[]';
        }

        return false;
    }
}
