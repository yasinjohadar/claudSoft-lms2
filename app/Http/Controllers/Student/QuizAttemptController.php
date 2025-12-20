<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use App\Models\QuizSettings;
use App\Models\QuizAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\QuizCompleted;

class QuizAttemptController extends Controller
{
    /**
     * Display available quizzes for student.
     */
    public function index(Request $request)
    {
        $studentId = auth()->id();

        // Get enrolled courses
        $enrolledCourseIds = auth()->user()->enrollments()
            ->where('status', 'active')
            ->pluck('course_id');

        // Get quizzes for enrolled courses
        $query = Quiz::with(['course', 'lesson'])
            ->whereIn('course_id', $enrolledCourseIds)
            ->where('is_published', true)
            ->where('is_visible', true)
            ->available()
            ->orderBy('due_date', 'asc');

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by type
        if ($request->filled('quiz_type')) {
            $query->where('quiz_type', $request->quiz_type);
        }

        $quizzes = $query->paginate(15);

        // Add attempt information for each quiz
        $quizzes->getCollection()->transform(function($quiz) use ($studentId) {
            $quiz->student_attempts_count = $quiz->attempts()
                ->where('student_id', $studentId)
                ->count();

            $quiz->best_attempt = $quiz->attempts()
                ->where('student_id', $studentId)
                ->where('is_completed', true)
                ->orderBy('percentage_score', 'desc')
                ->first();

            $quiz->can_attempt = $quiz->canAttempt($studentId);
            $quiz->remaining_attempts = $quiz->getRemainingAttempts($studentId);

            return $quiz;
        });

        return view('student.pages.quizzes.index', compact('quizzes'));
    }

    /**
     * Show quiz details before starting.
     */
    public function show($id)
    {
        $studentId = auth()->id();
        $quiz = Quiz::with(['course', 'lesson', 'settings', 'quizQuestions.question.questionType'])
            ->findOrFail($id);

        // Check if student can access this quiz
        if (!$this->canAccessQuiz($quiz, $studentId)) {
            return redirect()->route('student.quizzes.index')
                ->withErrors(['error' => 'ليس لديك صلاحية للوصول إلى هذا الاختبار']);
        }

        // Get student's previous attempts
        $attempts = $quiz->attempts()
            ->where('student_id', $studentId)
            ->orderBy('attempt_number', 'desc')
            ->get();

        // Check if can attempt
        $canAttempt = $quiz->canAttempt($studentId);
        $remainingAttempts = $quiz->getRemainingAttempts($studentId);

        // Get current in-progress attempt if exists
        $currentAttempt = $quiz->attempts()
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();

        return view('student.pages.quizzes.show', compact(
            'quiz',
            'attempts',
            'canAttempt',
            'remainingAttempts',
            'currentAttempt'
        ));
    }

    /**
     * Start a new quiz attempt.
     */
    public function start(Request $request, $id)
    {
        $studentId = auth()->id();
        $quiz = Quiz::with(['settings', 'quizQuestions'])->findOrFail($id);

        // Validate quiz password if required
        if ($quiz->settings && $quiz->settings->requiresPassword()) {
            $request->validate([
                'quiz_password' => 'required|string',
            ]);

            if (!$quiz->settings->verifyPassword($request->quiz_password)) {
                return back()->withErrors(['quiz_password' => 'كلمة المرور غير صحيحة']);
            }
        }

        // Check if can attempt
        if (!$quiz->canAttempt($studentId)) {
            return back()->withErrors(['error' => 'لا يمكنك بدء محاولة جديدة للاختبار']);
        }

        // Check for existing in-progress attempt
        $existingAttempt = $quiz->attempts()
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            return redirect()->route('student.quizzes.take', $existingAttempt->id)
                ->with('info', 'لديك محاولة قيد التقدم، يمكنك متابعتها');
        }

        DB::beginTransaction();
        try {
            // Calculate attempt number
            $attemptNumber = $quiz->attempts()
                ->where('student_id', $studentId)
                ->count() + 1;

            // Prepare questions order
            $questionIds = $quiz->quizQuestions()->pluck('question_id')->toArray();

            if ($quiz->shuffle_questions) {
                shuffle($questionIds);
            }

            // Create attempt
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

            // Create responses for all questions
            foreach ($questionIds as $index => $questionId) {
                $quizQuestion = $quiz->quizQuestions()
                    ->where('question_id', $questionId)
                    ->first();

                QuizResponse::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                    'question_type_id' => $quizQuestion->question->question_type_id,
                    'max_score' => $quizQuestion->max_score,
                    'answer_order' => $index + 1,
                    'marked_for_review' => false,
                ]);
            }

            DB::commit();

            return redirect()->route('student.quizzes.take', $attempt->id)
                ->with('success', 'تم بدء الاختبار بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء بدء الاختبار: ' . $e->getMessage()]);
        }
    }

    /**
     * Display quiz taking interface.
     */
    public function take($attemptId)
    {
        $attempt = QuizAttempt::with([
            'quiz.settings',
            'quiz.quizQuestions.question.questionType',
            'quiz.quizQuestions.question.options',
            'responses'
        ])->findOrFail($attemptId);

        $studentId = auth()->id();

        // Verify ownership
        if ($attempt->student_id !== $studentId) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه المحاولة');
        }

        // Check if attempt is still in progress
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.quizzes.review.show', $attemptId)
                ->with('info', 'هذه المحاولة قد تم تسليمها بالفعل');
        }

        // Check time limit
        if ($attempt->quiz->time_limit) {
            $elapsedMinutes = $attempt->started_at->diffInMinutes(now());

            if ($elapsedMinutes > $attempt->quiz->time_limit) {
                // Auto-submit if time expired
                $this->autoSubmit($attempt);

                return redirect()->route('student.quizzes.review.show', $attemptId)
                    ->with('warning', 'انتهى وقت الاختبار وتم تسليمه تلقائياً');
            }
        }

        // Get questions in the order specified for this attempt
        $orderedQuestions = collect($attempt->questions_order)->map(function($questionId) use ($attempt) {
            $quizQuestion = $attempt->quiz->quizQuestions()
                ->where('question_id', $questionId)
                ->with('question.options')
                ->first();

            $response = $attempt->responses()
                ->where('question_id', $questionId)
                ->first();

            return [
                'quiz_question' => $quizQuestion,
                'response' => $response,
            ];
        });

        return view('student.pages.quizzes.take', compact('attempt', 'orderedQuestions'));
    }

    /**
     * Save answer for a question.
     */
    public function saveAnswer(Request $request, $attemptId)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);
        $studentId = auth()->id();

        // Verify ownership
        if ($attempt->student_id !== $studentId) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول إلى هذه المحاولة'
            ], 403);
        }

        // Verify attempt is in progress
        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حفظ الإجابة، المحاولة غير نشطة'
            ], 400);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:question_bank,id',
            'response_text' => 'nullable|string',
            'response_data' => 'nullable|array',
            'selected_option_ids' => 'nullable|array',
            'time_spent' => 'nullable|integer|min:0',
            'marked_for_review' => 'nullable|boolean',
        ]);

        try {
            $response = $attempt->responses()
                ->where('question_id', $validated['question_id'])
                ->firstOrFail();

            $response->update([
                'response_text' => $validated['response_text'] ?? null,
                'response_data' => $validated['response_data'] ?? null,
                'selected_option_ids' => $validated['selected_option_ids'] ?? null,
                'time_spent' => $validated['time_spent'] ?? $response->time_spent,
                'marked_for_review' => $validated['marked_for_review'] ?? $response->marked_for_review,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الإجابة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ الإجابة'
            ], 500);
        }
    }

    /**
     * Mark question for review.
     */
    public function markForReview(Request $request, $attemptId, $questionId)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);
        $studentId = auth()->id();

        // Verify ownership
        if ($attempt->student_id !== $studentId) {
            return response()->json(['success' => false], 403);
        }

        $response = $attempt->responses()
            ->where('question_id', $questionId)
            ->firstOrFail();

        $response->update([
            'marked_for_review' => $request->input('marked', true),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Submit quiz attempt.
     */
    public function submit(Request $request, $attemptId)
    {
        $attempt = QuizAttempt::with(['quiz', 'responses'])->findOrFail($attemptId);
        $studentId = auth()->id();

        // Verify ownership
        if ($attempt->student_id !== $studentId) {
            return back()->withErrors(['error' => 'غير مصرح لك بالوصول إلى هذه المحاولة']);
        }

        // Verify attempt is in progress
        if ($attempt->status !== 'in_progress') {
            return back()->withErrors(['error' => 'هذه المحاولة قد تم تسليمها بالفعل']);
        }

        DB::beginTransaction();
        try {
            // Calculate time spent
            $timeSpent = $attempt->calculateTimeSpent();

            // Submit attempt
            $attempt->submit();
            $attempt->update(['time_spent' => $timeSpent]);

            // Auto-grade all auto-gradable questions
            foreach ($attempt->responses as $response) {
                $questionType = $response->questionType->name ?? '';

                // Skip essay and calculated questions (require manual grading)
                if (in_array($questionType, ['essay', 'calculated'])) {
                    continue;
                }

                $response->autoGrade();
            }

            // Calculate final scores
            $attempt->grade();

            // Update or create analytics
            $analytics = QuizAnalytics::firstOrNew([
                'student_id' => $studentId,
                'quiz_id' => $attempt->quiz_id,
                'course_id' => $attempt->quiz->course_id,
            ]);

            $analytics->recalculate();

            // Dispatch QuizCompleted event for gamification
            QuizCompleted::dispatch(
                auth()->user(),
                $attempt->quiz,
                $attempt->points_earned ?? 0,
                $attempt->quiz->quizQuestions()->count(),
                $attempt->id,
                $timeSpent
            );

            // Dispatch n8n webhook event
            event(new \App\Events\N8nWebhookEvent('quiz.completed', [
                'student_id' => auth()->id(),
                'student_name' => auth()->user()->name,
                'student_email' => auth()->user()->email,
                'quiz_id' => $attempt->quiz_id,
                'quiz_title' => $attempt->quiz->title ?? null,
                'course_id' => $attempt->quiz->course_id ?? null,
                'attempt_id' => $attempt->id,
                'score' => $attempt->points_earned ?? 0,
                'total_questions' => $attempt->quiz->quizQuestions()->count(),
                'time_spent' => $timeSpent,
                'completed_at' => now()->toIso8601String(),
            ]));

            DB::commit();

            return redirect()->route('student.quizzes.review.show', $attemptId)
                ->with('success', 'تم تسليم الاختبار بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء تسليم الاختبار: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark attempt as completed (for "تم الإنجاز" button).
     * This is different from submit - it marks the task as done in the student's progress.
     */
    public function markCompleted(Request $request, $attemptId)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);
        $studentId = auth()->id();

        // Verify ownership
        if ($attempt->student_id !== $studentId) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك'
            ], 403);
        }

        // Can only mark as completed if already submitted/graded
        if (!in_array($attempt->status, ['submitted', 'graded'])) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسليم الاختبار أولاً'
            ], 400);
        }

        try {
            $attempt->markAsCompleted();

            return response()->json([
                'success' => true,
                'message' => 'تم وضع علامة الإنجاز بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ'
            ], 500);
        }
    }

    /**
     * Auto-submit attempt when time expires.
     */
    private function autoSubmit(QuizAttempt $attempt): void
    {
        DB::beginTransaction();
        try {
            $timeSpent = $attempt->calculateTimeSpent();

            $attempt->submit();
            $attempt->update(['time_spent' => $timeSpent]);

            // Auto-grade
            foreach ($attempt->responses as $response) {
                $questionType = $response->questionType->name ?? '';

                if (in_array($questionType, ['essay', 'calculated'])) {
                    continue;
                }

                $response->autoGrade();
            }

            $attempt->grade();

            // Update analytics
            $analytics = QuizAnalytics::firstOrNew([
                'student_id' => $attempt->student_id,
                'quiz_id' => $attempt->quiz_id,
                'course_id' => $attempt->quiz->course_id,
            ]);

            $analytics->recalculate();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if student can access quiz.
     */
    private function canAccessQuiz(Quiz $quiz, int $studentId): bool
    {
        // Check enrollment
        $isEnrolled = auth()->user()->enrollments()
            ->where('course_id', $quiz->course_id)
            ->where('status', 'active')
            ->exists();

        if (!$isEnrolled) {
            return false;
        }

        // Check if published and visible
        if (!$quiz->is_published || !$quiz->is_visible) {
            return false;
        }

        return true;
    }

    /**
     * Get attempt progress (AJAX).
     */
    public function getProgress($attemptId)
    {
        $attempt = QuizAttempt::with('responses')->findOrFail($attemptId);
        $studentId = auth()->id();

        if ($attempt->student_id !== $studentId) {
            return response()->json(['success' => false], 403);
        }

        $totalQuestions = $attempt->responses()->count();
        $answeredQuestions = $attempt->responses()
            ->where(function($q) {
                $q->whereNotNull('response_text')
                  ->orWhereNotNull('response_data')
                  ->orWhereNotNull('selected_option_ids');
            })
            ->count();

        $markedForReview = $attempt->responses()
            ->where('marked_for_review', true)
            ->count();

        $timeSpent = $attempt->calculateTimeSpent();
        $timeLimit = $attempt->quiz->time_limit ? $attempt->quiz->time_limit * 60 : null;
        $timeRemaining = $timeLimit ? max(0, $timeLimit - $timeSpent) : null;

        return response()->json([
            'success' => true,
            'progress' => [
                'total_questions' => $totalQuestions,
                'answered_questions' => $answeredQuestions,
                'marked_for_review' => $markedForReview,
                'completion_percentage' => $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0,
                'time_spent' => $timeSpent,
                'time_remaining' => $timeRemaining,
            ]
        ]);
    }
}
