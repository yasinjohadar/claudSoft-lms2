<?php

namespace App\Http\Controllers\Api\Student;

use App\Events\QuizStarted;
use App\Events\StudentActivityTracked;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use App\Services\Api\StudentQuizApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * واجهة REST للاختبارات (Quiz) للتطبيق — لا تعدّل مسارات الويب.
 */
class QuizApiController extends Controller
{
    public function __construct(
        protected StudentQuizApiService $quizApi
    ) {}

    public function preview(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $quiz = Quiz::with(['settings', 'course'])->find($id);

        if (! $quiz) {
            return response()->json(['success' => false, 'message' => 'الاختبار غير موجود.'], 404);
        }

        if (! $this->quizApi->userCanAccessQuiz($user, $quiz)) {
            return response()->json(['success' => false, 'message' => 'ليس لديك صلاحية لهذا الاختبار.'], 403);
        }

        if (! $quiz->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'الاختبار غير متاح حالياً.'], 403);
        }

        StudentActivityTracked::dispatch($user, 'student.quiz.previewed', [
            'quiz_id' => $quiz->id,
            'course_id' => $quiz->course_id,
            'quiz_title' => $quiz->title,
        ]);

        $studentId = (int) $user->id;
        $currentAttempt = $quiz->attempts()
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();

        $requiresPassword = $quiz->settings && $quiz->settings->requiresPassword();

        return response()->json([
            'success' => true,
            'data' => [
                'quiz' => [
                    'id' => (int) $quiz->id,
                    'course_id' => (int) $quiz->course_id,
                    'title' => (string) $quiz->title,
                    'description' => $quiz->description !== null ? (string) $quiz->description : null,
                    'instructions' => $quiz->instructions !== null ? (string) $quiz->instructions : null,
                    'quiz_type' => $quiz->quiz_type !== null ? (string) $quiz->quiz_type : null,
                    'time_limit_minutes' => $quiz->time_limit,
                    'max_score' => (float) $quiz->max_score,
                    'passing_grade' => (float) $quiz->passing_grade,
                    'attempts_allowed' => $quiz->attempts_allowed,
                    'remaining_attempts' => $quiz->getRemainingAttempts($studentId),
                    'can_attempt' => $quiz->canAttempt($studentId),
                    'requires_password' => (bool) $requiresPassword,
                    'question_count' => $quiz->getQuestionCount(),
                ],
                'current_attempt_id' => $currentAttempt ? (int) $currentAttempt->id : null,
            ],
        ]);
    }

    public function start(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $quiz = Quiz::with(['settings', 'quizQuestions.question.questionType'])->findOrFail($id);

        if (! $this->quizApi->userCanAccessQuiz($user, $quiz)) {
            return response()->json(['success' => false, 'message' => 'ليس لديك صلاحية لهذا الاختبار.'], 403);
        }

        if (! $quiz->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'الاختبار غير متاح حالياً.'], 403);
        }

        $studentId = (int) $user->id;

        if ($quiz->settings && $quiz->settings->requiresPassword()) {
            $request->validate(['quiz_password' => 'required|string']);
            if (! $quiz->settings->verifyPassword($request->input('quiz_password'))) {
                return response()->json(['success' => false, 'message' => 'كلمة مرور الاختبار غير صحيحة.'], 422);
            }
        }

        if (! $quiz->canAttempt($studentId)) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك بدء محاولة جديدة.'], 403);
        }

        $existing = $quiz->attempts()
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'data' => [
                    'attempt_id' => (int) $existing->id,
                    'attempt_number' => (int) $existing->attempt_number,
                    'resumed' => true,
                ],
            ]);
        }

        DB::beginTransaction();
        try {
            $attemptNumber = $quiz->attempts()
                ->where('student_id', $studentId)
                ->count() + 1;

            $questionIds = $quiz->quizQuestions()->pluck('question_id')->toArray();
            if ($quiz->shuffle_questions) {
                shuffle($questionIds);
            }

            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $studentId,
                'attempt_number' => $attemptNumber,
                'status' => 'in_progress',
                'started_at' => now(),
                'max_score' => $quiz->max_score,
                'questions_order' => $questionIds,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_completed' => false,
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

                $maxScore = $quizQuestion->getGrade();

                QuizResponse::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                    'question_type_id' => $questionTypeId,
                    'max_score' => $maxScore,
                    'answer_order' => $index + 1,
                    'marked_for_review' => false,
                ]);
            }

            DB::commit();

            QuizStarted::dispatch($user, $quiz, (int) $attempt->id, (int) $attempt->attempt_number);

            return response()->json([
                'success' => true,
                'data' => [
                    'attempt_id' => (int) $attempt->id,
                    'attempt_number' => (int) $attempt->attempt_number,
                    'resumed' => false,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'تعذر بدء المحاولة.',
            ], 500);
        }
    }

    public function showAttempt(Request $request, int $attemptId): JsonResponse
    {
        $user = $request->user();
        $attempt = QuizAttempt::with(['quiz.settings'])->findOrFail($attemptId);

        if ((int) $attempt->student_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح.'], 403);
        }

        if ($this->quizApi->autoSubmitIfTimeExpired($attempt)) {
            $attempt->refresh();
        }

        if ($attempt->status !== 'in_progress') {
            $attempt->load(['responses.question.questionType']);

            return response()->json([
                'success' => true,
                'data' => [
                    'completed' => true,
                    'attempt_id' => (int) $attempt->id,
                    'quiz_id' => (int) $attempt->quiz_id,
                    'status' => $attempt->status,
                    'total_score' => (float) $attempt->total_score,
                    'max_score' => (float) $attempt->max_score,
                    'percentage_score' => (float) $attempt->percentage_score,
                    'passed' => (bool) $attempt->passed,
                    'question_results' => $attempt->responses->map(function (QuizResponse $r) {
                        return [
                            'question_id' => (int) $r->question_id,
                            'is_correct' => $r->is_correct,
                            'score_obtained' => $r->score_obtained !== null ? (float) $r->score_obtained : null,
                            'max_score' => (float) $r->max_score,
                            'requires_manual_grading' => in_array($r->question->questionType->name ?? '', ['short_answer', 'essay'], true),
                        ];
                    })->values()->all(),
                ],
            ]);
        }

        $questions = $this->quizApi->orderedQuestionsForAttempt($attempt);
        $serialized = $questions->map(fn ($q) => $this->quizApi->serializeQuestionForClient($q))->values()->all();

        $remainingSeconds = null;
        if ($attempt->quiz->time_limit && $attempt->started_at) {
            $elapsed = $attempt->started_at->diffInSeconds(now());
            $total = $attempt->quiz->time_limit * 60;
            $remainingSeconds = max(0, $total - $elapsed);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'attempt_id' => (int) $attempt->id,
                'quiz_id' => (int) $attempt->quiz_id,
                'status' => $attempt->status,
                'remaining_seconds' => $remainingSeconds,
                'questions' => $serialized,
            ],
        ]);
    }

    public function saveAnswer(Request $request, int $attemptId): JsonResponse
    {
        $user = $request->user();
        $attempt = QuizAttempt::findOrFail($attemptId);

        if ((int) $attempt->student_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح.'], 403);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json(['success' => false, 'message' => 'المحاولة غير نشطة.'], 400);
        }

        $validated = $request->validate([
            'question_id' => 'required|integer|exists:question_bank,id',
            'answer' => 'nullable',
        ]);

        $response = $attempt->responses()
            ->where('question_id', $validated['question_id'])
            ->first();

        if (! $response) {
            return response()->json(['success' => false, 'message' => 'السؤال غير ضمن هذه المحاولة.'], 404);
        }

        $this->quizApi->applyAnswerPayloadToResponse($response, $validated['answer'] ?? null);

        return response()->json(['success' => true, 'message' => 'تم حفظ الإجابة.']);
    }

    public function submit(Request $request, int $attemptId): JsonResponse
    {
        $user = $request->user();
        $attempt = QuizAttempt::with(['quiz', 'responses'])->findOrFail($attemptId);

        if ((int) $attempt->student_id !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح.'], 403);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json(['success' => false, 'message' => 'تم تسليم هذه المحاولة مسبقاً.'], 400);
        }

        $answers = $request->input('answers', []);
        if (! is_array($answers)) {
            return response()->json(['success' => false, 'message' => 'صيغة الإجابات غير صالحة.'], 422);
        }

        try {
            $attempt = $this->quizApi->submitAndGradeAttempt($attempt, $user, $answers);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر تسليم الاختبار.',
            ], 500);
        }

        $attempt->load(['responses.question.questionType']);

        $questionResults = $attempt->responses->map(function (QuizResponse $r) {
            return [
                'question_id' => (int) $r->question_id,
                'is_correct' => $r->is_correct,
                'score_obtained' => $r->score_obtained !== null ? (float) $r->score_obtained : null,
                'max_score' => (float) $r->max_score,
                'requires_manual_grading' => in_array($r->question->questionType->name ?? '', ['short_answer', 'essay'], true),
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'attempt_id' => (int) $attempt->id,
                'status' => $attempt->status,
                'total_score' => (float) $attempt->total_score,
                'max_score' => (float) $attempt->max_score,
                'percentage_score' => (float) $attempt->percentage_score,
                'passed' => (bool) $attempt->passed,
                'grade_status' => $attempt->grade_status,
                'time_spent_seconds' => (int) $attempt->time_spent,
                'question_results' => $questionResults,
            ],
        ]);
    }

    /**
     * Get all quiz attempts for the authenticated user.
     * GET /api/student/quizzes/attempts
     */
    public function myAttempts(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = (int) $user->id;

        $perPage = (int) $request->input('per_page', 20);
        $status = $request->input('status'); // completed, in_progress
        $result = $request->input('result'); // passed, failed

        $query = QuizAttempt::with(['quiz.course'])
            ->where('student_id', $studentId)
            ->whereHas('quiz')
            ->orderBy('started_at', 'desc');

        if ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'in_progress') {
            $query->where('status', 'in_progress');
        }

        if ($result === 'passed') {
            $query->where('passed', true);
        } elseif ($result === 'failed') {
            $query->where('passed', false);
        }

        $attempts = $query->paginate($perPage);

        $items = $attempts->items();
        $formatted = array_map(function ($attempt) {
            return [
                'id' => (int) $attempt->id,
                'quiz_id' => (int) $attempt->quiz_id,
                'quiz_title' => $attempt->quiz?->title ?? '',
                'course_id' => $attempt->quiz?->course_id ? (int) $attempt->quiz->course_id : null,
                'course_title' => $attempt->quiz?->course?->title ?? '',
                'status' => (string) $attempt->status,
                'passed' => (bool) $attempt->passed,
                'total_score' => (float) $attempt->total_score,
                'max_score' => (float) $attempt->max_score,
                'percentage_score' => (float) $attempt->percentage_score,
                'started_at' => $attempt->started_at?->toIso8601String(),
                'completed_at' => $attempt->completed_at?->toIso8601String(),
                'time_spent' => $attempt->time_spent,
            ];
        }, $items);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $formatted,
                'total' => $attempts->total(),
                'per_page' => $attempts->perPage(),
                'current_page' => $attempts->currentPage(),
                'last_page' => $attempts->lastPage(),
                'has_more' => $attempts->hasMorePages(),
            ],
        ]);
    }
}
