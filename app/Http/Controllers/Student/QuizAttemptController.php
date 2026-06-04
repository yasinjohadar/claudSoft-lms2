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
            ->where('enrollment_status', 'active')
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

        $filteredQuizzes = (clone $query)->get();
        $filteredIds = $filteredQuizzes->pluck('id');

        $stats = [
            'total' => Quiz::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->where('is_visible', true)
                ->available()
                ->count(),
            'filtered' => $filteredQuizzes->count(),
            'can_attempt' => $filteredQuizzes->filter(fn ($quiz) => $quiz->canAttempt($studentId))->count(),
            'attempted' => $filteredIds->isEmpty() ? 0 : QuizAttempt::where('student_id', $studentId)
                ->whereIn('quiz_id', $filteredIds)
                ->distinct()
                ->count('quiz_id'),
        ];

        $courses = \App\Models\Course::whereIn('id',
            Quiz::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->where('is_visible', true)
                ->distinct()
                ->pluck('course_id')
        )->orderBy('title')->get(['id', 'title']);

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

        return view('student.pages.quizzes.index', compact('quizzes', 'courses', 'stats'));
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
        $quiz = Quiz::with(['settings', 'quizQuestions.question.questionType'])->findOrFail($id);

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
                    ->with('question.questionType')
                    ->first();

                if (!$quizQuestion || !$quizQuestion->question) {
                    \Illuminate\Support\Facades\Log::warning('Quiz question not found', [
                        'quiz_id' => $quiz->id,
                        'question_id' => $questionId,
                    ]);
                    continue; // Skip if question not found
                }

                // Get question_type_id - required field
                $questionTypeId = $quizQuestion->question->question_type_id;
                if (!$questionTypeId) {
                    \Illuminate\Support\Facades\Log::warning('Question has no question_type_id', [
                        'question_id' => $questionId,
                    ]);
                    continue; // Skip if question has no type
                }

                // Get max_score from quizQuestion (question_grade) or question default_grade or 1.0
                $maxScore = $quizQuestion->getGrade(); // This method handles null values and returns 1.0 as default

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

        // Verify ownership - use type casting for consistency
        if ((int)$attempt->student_id !== (int)$studentId) {
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
        if (empty($attempt->questions_order)) {
            // Fallback: get questions directly from quiz
            \Log::warning('Quiz attempt has empty questions_order', [
                'attempt_id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id
            ]);
            
            $questions = $attempt->quiz->quizQuestions()
                ->with('question.questionType', 'question.options')
                ->get()
                ->map(function($quizQuestion) use ($attempt) {
                    if (!$quizQuestion->question) {
                        return null;
                    }
                    
                    $question = $quizQuestion->question;
                    
                    // #region agent log
                    \Log::info('DEBUG: Before loading options (fallback)', [
                        'question_id' => $question->id,
                        'question_type' => $question->questionType->name ?? 'unknown',
                        'options_relation_loaded' => $question->relationLoaded('options'),
                        'options_count_before' => $question->options ? $question->options->count() : 0,
                        'hypothesisId' => 'A'
                    ]);
                    // #endregion
                    
                // تأكد من تحميل options بشكل صريح مع الترتيب
                if (!$question->relationLoaded('options')) {
                    $question->load(['options' => function($query) {
                        $query->orderBy('option_order', 'asc');
                    }]);
                } else {
                    // إذا كانت محملة، تأكد من الترتيب
                    $question->setRelation('options', $question->options->sortBy('option_order')->values());
                }
                
                // #region agent log
                \Log::info('DEBUG: After loading options (fallback)', [
                    'question_id' => $question->id,
                    'options_relation_loaded' => $question->relationLoaded('options'),
                    'options_count_after' => $question->options ? $question->options->count() : 0,
                    'options_ids' => $question->options ? $question->options->pluck('id')->toArray() : [],
                    'options_texts' => $question->options ? $question->options->pluck('option_text')->take(3)->toArray() : [],
                    'hypothesisId' => 'A'
                ]);
                // #endregion
                    
                    $question->setRelation('pivot', (object)[
                        'question_grade' => $quizQuestion->question_grade ?? $question->default_grade ?? 1.0
                    ]);
                    return $question;
                })->filter();
        } else {
            $questions = collect($attempt->questions_order)->map(function($questionId) use ($attempt) {
                $quizQuestion = $attempt->quiz->quizQuestions()
                    ->where('question_id', $questionId)
                    ->with('question.questionType', 'question.options')
                    ->first();

                if (!$quizQuestion) {
                    \Log::warning('Quiz question not found in questions_order', [
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId
                    ]);
                    return null;
                }

                $response = $attempt->responses()
                    ->where('question_id', $questionId)
                    ->first();

                // Get the question with pivot data
                $question = $quizQuestion->question;

                // إذا كان السؤال نفسه محذوفاً من بنك الأسئلة
                if (!$question) {
                    \Log::warning('QuizAttempt: question is null for quizQuestion in questions_order', [
                        'attempt_id' => $attempt->id,
                        'quiz_id' => $attempt->quiz_id,
                        'question_id_from_order' => $questionId,
                        'quiz_question_id' => $quizQuestion->id ?? null,
                    ]);
                    return null;
                }
                
                // #region agent log
                \Log::info('DEBUG: Before loading options', [
                    'question_id' => $question->id,
                    'question_type' => $question->questionType->name ?? 'unknown',
                    'options_relation_loaded' => $question->relationLoaded('options'),
                    'options_count_before' => $question->options ? $question->options->count() : 0,
                    'hypothesisId' => 'A'
                ]);
                // #endregion
                
                // تأكد من تحميل options بشكل صريح مع الترتيب
                if (!$question->relationLoaded('options')) {
                    $question->load(['options' => function($query) {
                        $query->orderBy('option_order', 'asc');
                    }]);
                } else {
                    // إذا كانت محملة، تأكد من الترتيب
                    $question->setRelation('options', $question->options->sortBy('option_order')->values());
                }
                
                // #region agent log
                \Log::info('DEBUG: After loading options', [
                    'question_id' => $question->id,
                    'options_relation_loaded' => $question->relationLoaded('options'),
                    'options_count_after' => $question->options ? $question->options->count() : 0,
                    'options_ids' => $question->options ? $question->options->pluck('id')->toArray() : [],
                    'options_texts' => $question->options ? $question->options->pluck('option_text')->take(3)->toArray() : [],
                    'hypothesisId' => 'A'
                ]);
                // #endregion
                
                // Logging للتشخيص
                \Log::debug('Question options loaded', [
                    'question_id' => $question->id,
                    'question_type' => $question->questionType->name ?? 'unknown',
                    'options_count' => $question->options->count(),
                    'options_ids' => $question->options->pluck('id')->toArray(),
                ]);
                
                // Add pivot data for grade
                $question->setRelation('pivot', (object)[
                    'question_grade' => $quizQuestion->question_grade ?? $question->default_grade ?? 1.0
                ]);

                return $question;
            })->filter();
        }

        // Calculate remaining time
        $remainingTime = null;
        if ($attempt->quiz->time_limit && $attempt->started_at) {
            $elapsedSeconds = $attempt->started_at->diffInSeconds(now());
            $totalSeconds = $attempt->quiz->time_limit * 60;
            $remainingTime = max(0, $totalSeconds - $elapsedSeconds);
            
            // Log for debugging
            \Log::info('Quiz Timer Calculation', [
                'attempt_id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id,
                'time_limit' => $attempt->quiz->time_limit,
                'started_at' => $attempt->started_at->toDateTimeString(),
                'now' => now()->toDateTimeString(),
                'elapsed_seconds' => $elapsedSeconds,
                'total_seconds' => $totalSeconds,
                'remaining_seconds' => $remainingTime
            ]);
        } else {
            \Log::warning('Quiz timer not calculated', [
                'attempt_id' => $attempt->id,
                'has_time_limit' => !empty($attempt->quiz->time_limit),
                'has_started_at' => !empty($attempt->started_at)
            ]);
        }

        // Debug: Log questions and options
        \Log::info('Quiz Questions Loaded', [
            'attempt_id' => $attempt->id,
            'questions_count' => $questions->count(),
            'questions_details' => $questions->map(function($q) {
                return [
                    'id' => $q->id,
                    'text' => substr($q->question_text ?? '', 0, 50),
                    'type' => $q->questionType->name ?? 'unknown',
                    'options_loaded' => $q->relationLoaded('options'),
                    'options_count' => $q->options->count(),
                    'options_sample' => $q->options->take(2)->map(function($o) {
                        return [
                            'id' => $o->id,
                            'text' => substr($o->option_text ?? '', 0, 30),
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ]);

        return view('student.pages.quizzes.take', compact('attempt', 'questions', 'remainingTime'));
    }

    /**
     * Save answer for a question.
     */
    public function saveAnswer(Request $request, $attemptId)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);
        $studentId = auth()->id();

        // Verify ownership - use type casting for consistency
        if ((int)$attempt->student_id !== (int)$studentId) {
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

        // Support both old format (response_text, selected_option_ids) and new format (answer)
        $validated = $request->validate([
            'question_id' => 'required|exists:question_bank,id',
            'answer' => 'nullable', // New format - can be string, array, or object
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

            // Get question type to determine how to save
            $question = $response->question ?? \App\Models\QuestionBank::with('questionType')->find($validated['question_id']);
            $questionType = $question->questionType->name ?? '';

            // If answer parameter is provided, use it (new format like QuestionModule)
            if ($request->has('answer')) {
                $answer = $validated['answer'];
                
                // Convert JSON string to array if needed
                if (is_string($answer)) {
                    $decoded = json_decode($answer, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $answer = $decoded;
                    }
                }

                // Save based on question type
                if (in_array($questionType, ['multiple_choice_single', 'true_false'])) {
                    // Single value - save as selected_option_ids array or response_text
                    if (is_array($answer)) {
                        $response->update([
                            'selected_option_ids' => $answer,
                            'response_data' => ['answer' => $answer],
                        ]);
                    } else {
                        $response->update([
                            'response_text' => (string)$answer,
                            'response_data' => ['answer' => $answer],
                        ]);
                    }
                } elseif ($questionType === 'multiple_choice_multiple') {
                    // Multiple values - save as selected_option_ids array
                    $answerArray = is_array($answer) ? $answer : [$answer];
                    $response->update([
                        'selected_option_ids' => $answerArray,
                        'response_data' => ['answer' => $answerArray],
                    ]);
                } elseif (in_array($questionType, ['short_answer', 'essay'])) {
                    // Text answer
                    $response->update([
                        'response_text' => is_string($answer) ? $answer : (is_array($answer) ? json_encode($answer, JSON_UNESCAPED_UNICODE) : (string)$answer),
                        'response_data' => ['answer' => $answer],
                    ]);
                } elseif (in_array($questionType, ['numerical', 'calculated'])) {
                    // Numerical answer - save as response_text (string representation of number)
                    $response->update([
                        'response_text' => is_string($answer) ? $answer : (is_numeric($answer) ? (string)$answer : ''),
                        'response_data' => ['answer' => $answer, 'numeric_value' => is_numeric($answer) ? (float)$answer : null],
                    ]);
                } elseif ($questionType === 'fill_blanks') {
                    $map = is_array($answer) ? $answer : ['0' => $answer];
                    $response->update([
                        'response_data' => ['answer' => $map],
                    ]);
                } else {
                    // Complex types (matching, ordering, drag_drop) - save as response_data
                    $response->update([
                        'response_data' => is_array($answer) ? $answer : ['answer' => $answer],
                    ]);
                }
            } else {
                // Old format - use existing fields
                $response->update([
                    'response_text' => $validated['response_text'] ?? $response->response_text,
                    'response_data' => $validated['response_data'] ?? $response->response_data,
                    'selected_option_ids' => $validated['selected_option_ids'] ?? $response->selected_option_ids,
                    'time_spent' => $validated['time_spent'] ?? $response->time_spent,
                    'marked_for_review' => $validated['marked_for_review'] ?? $response->marked_for_review,
                ]);
            }

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

        // Verify ownership - use type casting for consistency
        if ((int)$attempt->student_id !== (int)$studentId) {
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

        // Verify ownership - use type casting for consistency
        if ((int)$attempt->student_id !== (int)$studentId) {
            return back()->withErrors(['error' => 'غير مصرح لك بالوصول إلى هذه المحاولة']);
        }

        // Verify attempt is in progress
        if ($attempt->status !== 'in_progress') {
            return back()->withErrors(['error' => 'هذه المحاولة قد تم تسليمها بالفعل']);
        }

        DB::beginTransaction();
        try {
            // Save answers from request if provided (fallback if AJAX saves failed)
            if ($request->has('answers') && is_array($request->answers)) {
                foreach ($request->answers as $questionId => $answerJson) {
                    try {
                        $answer = json_decode($answerJson, true);
                        if ($answer === null && json_last_error() !== JSON_ERROR_NONE) {
                            // If JSON decode fails, treat as string
                            $answer = $answerJson;
                        }
                        
                        $response = $attempt->responses()->where('question_id', $questionId)->first();
                        if ($response) {
                            // Get question type
                            $question = $response->question ?? \App\Models\QuestionBank::with('questionType')->find($questionId);
                            $questionType = $question->questionType->name ?? '';
                            
                            // Save based on question type (same logic as saveAnswer)
                            if (in_array($questionType, ['multiple_choice_single', 'true_false'])) {
                                if (is_array($answer)) {
                                    $response->update([
                                        'selected_option_ids' => $answer,
                                        'response_data' => ['answer' => $answer],
                                    ]);
                                } else {
                                    $response->update([
                                        'response_text' => (string)$answer,
                                        'response_data' => ['answer' => $answer],
                                    ]);
                                }
                            } elseif ($questionType === 'multiple_choice_multiple') {
                                $answerArray = is_array($answer) ? $answer : [$answer];
                                $response->update([
                                    'selected_option_ids' => $answerArray,
                                    'response_data' => ['answer' => $answerArray],
                                ]);
                            } elseif (in_array($questionType, ['short_answer', 'essay'])) {
                                $response->update([
                                    'response_text' => is_string($answer) ? $answer : (is_array($answer) ? json_encode($answer, JSON_UNESCAPED_UNICODE) : (string)$answer),
                                    'response_data' => ['answer' => $answer],
                                ]);
                            } elseif ($questionType === 'fill_blanks') {
                                $map = is_array($answer) ? $answer : ['0' => $answer];
                                $response->update([
                                    'response_data' => ['answer' => $map],
                                ]);
                            } else {
                                $response->update([
                                    'response_data' => is_array($answer) ? $answer : ['answer' => $answer],
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        // Continue with other answers even if one fails
                        \Illuminate\Support\Facades\Log::error('Error saving answer from request in submit', [
                            'question_id' => $questionId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                // Reload responses after saving
                $attempt->load(['responses.question.questionType', 'responses.question.options']);
            }

            // Calculate time spent
            $timeSpent = $attempt->calculateTimeSpent();

            // Submit attempt
            $attempt->submit();
            $attempt->update(['time_spent' => $timeSpent]);

            // Reload responses with relationships before grading
            $attempt->load(['responses.question.questionType', 'responses.question.options']);

            // Log before grading
            \Log::info('=== QUIZ AUTO-GRADING START ===', [
                'attempt_id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id,
                'student_id' => $studentId,
                'responses_count' => $attempt->responses->count(),
            ]);

            // Auto-grade all auto-gradable questions (only those that don't require manual grading)
            foreach ($attempt->responses as $response) {
                $questionType = $response->question->questionType->name ?? '';
                
                // Only auto-grade questions that don't require manual grading
                // short_answer and essay require manual grading
                $requiresManualGrading = in_array($questionType, ['short_answer', 'essay']);
                
                // Improved check if response has an answer
                $hasAnswer = false;
                $answerDetails = [];
                
                // Check response_data (for complex question types)
                if (!empty($response->response_data)) {
                    if (is_array($response->response_data)) {
                        // Check if it's not empty array and has actual values
                        $hasValues = false;
                        foreach ($response->response_data as $key => $value) {
                            if ($value !== null && $value !== '' && $value !== []) {
                                $hasValues = true;
                                break;
                            }
                        }
                        if ($hasValues) {
                            $hasAnswer = true;
                            $answerDetails['source'] = 'response_data';
                            $answerDetails['data'] = $response->response_data;
                        }
                    } else {
                        $hasAnswer = true;
                        $answerDetails['source'] = 'response_data';
                        $answerDetails['data'] = $response->response_data;
                    }
                }
                
                // Check selected_option_ids (for multiple choice, true/false)
                if (!$hasAnswer && !empty($response->selected_option_ids)) {
                    if (is_array($response->selected_option_ids)) {
                        $hasAnswer = !empty(array_filter($response->selected_option_ids));
                    } else {
                        $hasAnswer = true;
                    }
                    if ($hasAnswer) {
                        $answerDetails['source'] = 'selected_option_ids';
                        $answerDetails['data'] = $response->selected_option_ids;
                    }
                }
                
                // Check response_text (for text-based answers)
                if (!$hasAnswer && !empty($response->response_text)) {
                    $text = trim($response->response_text);
                    if ($text !== '' && $text !== 'null' && $text !== '[]') {
                        $hasAnswer = true;
                        $answerDetails['source'] = 'response_text';
                        $answerDetails['data'] = $text;
                    }
                }
                
                \Log::info('Response grading check', [
                    'response_id' => $response->id,
                    'question_id' => $response->question_id,
                    'question_type' => $questionType,
                    'requires_manual_grading' => $requiresManualGrading,
                    'has_answer' => $hasAnswer,
                    'answer_details' => $answerDetails,
                    'is_correct_before' => $response->is_correct,
                    'score_obtained_before' => $response->score_obtained,
                ]);
                
                if (!$requiresManualGrading && $hasAnswer) {
                    try {
                        $response->autoGrade();
                        // Reload to get updated values
                        $response->refresh();
                        \Log::info('Response auto-graded successfully', [
                            'response_id' => $response->id,
                            'question_id' => $response->question_id,
                            'question_type' => $questionType,
                            'is_correct_after' => $response->is_correct,
                            'score_obtained_after' => $response->score_obtained,
                            'auto_graded' => $response->auto_graded,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Error auto-grading response', [
                            'response_id' => $response->id,
                            'question_id' => $response->question_id,
                            'question_type' => $questionType,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                } elseif ($requiresManualGrading) {
                    \Log::info('Response requires manual grading', [
                        'response_id' => $response->id,
                        'question_id' => $response->question_id,
                        'question_type' => $questionType,
                    ]);
                } elseif (!$hasAnswer) {
                    \Log::warning('Response has no answer, skipping auto-grade', [
                        'response_id' => $response->id,
                        'question_id' => $response->question_id,
                        'question_type' => $questionType,
                    ]);
                }
            }
            
            \Log::info('=== QUIZ AUTO-GRADING END ===', [
                'attempt_id' => $attempt->id,
            ]);

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
        // Verify ownership - use type casting for consistency
        if ((int)$attempt->student_id !== (int)$studentId) {
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
            ->where('enrollment_status', 'active')
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

        // Verify ownership - use type casting for consistency
        if ((int)$attempt->student_id !== (int)$studentId) {
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
