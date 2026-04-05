<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use App\Models\QuizAnalytics;
use App\Support\QuizGradingAnswerPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizGradingController extends Controller
{
    /**
     * Display grading dashboard.
     */
    public function index(Request $request)
    {
        $query = QuizAttempt::with(['quiz', 'student'])
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'desc');

        // Filter by quiz
        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Filter by grading status
        if ($request->filled('grade_status')) {
            $query->where('grade_status', $request->grade_status);
        }

        // Search by student name
        if ($request->filled('search')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $attempts = $query->paginate(20);

        // Get quizzes for filter
        $quizzes = Quiz::where('is_published', true)
            ->orderBy('title')
            ->get(['id', 'title']);

        // Statistics
        $stats = [
            'pending_grading' => QuizAttempt::where('status', 'submitted')
                ->where('grade_status', 'not_graded')
                ->count(),
            'partially_graded' => QuizAttempt::where('status', 'submitted')
                ->where('grade_status', 'partially_graded')
                ->count(),
            'fully_graded' => QuizAttempt::where('status', 'graded')
                ->whereDate('graded_at', today())
                ->count(),
        ];

        return view('admin.pages.grading.index', compact('attempts', 'quizzes', 'stats'));
    }

    /**
     * Show grading interface for a specific attempt.
     */
    public function show($attemptId)
    {
        $attempt = QuizAttempt::with([
            'quiz.quizQuestions.question.questionType',
            'student',
            'responses.question.questionType',
            'responses.question.options'
        ])->findOrFail($attemptId);

        // Check if attempt is submitted
        if ($attempt->status !== 'submitted' && $attempt->status !== 'graded') {
            return redirect()->route('grading.index')
                ->withErrors(['error' => 'لا يمكن تصحيح محاولة لم يتم تسليمها بعد']);
        }

        // Auto-grade all auto-gradable questions that haven't been graded yet
        DB::beginTransaction();
        try {
            $regradedCount = 0;
            
            foreach ($attempt->responses as $response) {
                $questionType = $response->question->questionType->name ?? '';
                
                // Skip essay and short_answer (require manual grading)
                if (in_array($questionType, ['essay', 'short_answer'])) {
                    continue;
                }
                
                // Skip if already auto-graded (unless we want to force regrade)
                if ($response->auto_graded && $response->score_obtained !== null) {
                    continue;
                }
                
                // Check if response has an answer
                $hasAnswer = false;
                
                // Check response_data
                if (!empty($response->response_data)) {
                    if (is_array($response->response_data)) {
                        foreach ($response->response_data as $key => $value) {
                            if ($value !== null && $value !== '' && $value !== []) {
                                $hasAnswer = true;
                                break;
                            }
                        }
                    } else {
                        $hasAnswer = true;
                    }
                }
                
                // Check selected_option_ids
                if (!$hasAnswer && !empty($response->selected_option_ids)) {
                    if (is_array($response->selected_option_ids)) {
                        $hasAnswer = !empty(array_filter($response->selected_option_ids));
                    } else {
                        $hasAnswer = true;
                    }
                }
                
                // Check response_text
                if (!$hasAnswer && !empty($response->response_text)) {
                    $text = trim($response->response_text);
                    if ($text !== '' && $text !== 'null' && $text !== '[]') {
                        $hasAnswer = true;
                    }
                }
                
                if ($hasAnswer) {
                    try {
                        $response->autoGrade();
                        $response->refresh();
                        $regradedCount++;
                    } catch (\Exception $e) {
                        \Log::error('Error auto-grading response in show method', [
                            'response_id' => $response->id,
                            'question_id' => $response->question_id,
                            'question_type' => $questionType,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
            
            // Recalculate attempt scores if any responses were regraded
            if ($regradedCount > 0) {
                $attempt->grade();
                $attempt->refresh();
            }
            
            DB::commit();
            
            if ($regradedCount > 0) {
                \Log::info('Auto-graded responses on grading page load', [
                    'attempt_id' => $attempt->id,
                    'regraded_count' => $regradedCount,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error auto-grading in show method', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
            ]);
            // Continue anyway - don't block the page
        }

        // Reload responses after auto-grading
        $attempt->load(['responses.question.questionType', 'responses.question.options']);

        // Get responses that need manual grading (only essay and short_answer)
        $responsesNeedingGrading = $attempt->responses()
            ->whereHas('question.questionType', function($q) {
                $q->whereIn('name', ['essay', 'short_answer']);
            })
            ->where(function($query) {
                $query->whereNull('score_obtained')
                      ->orWhere('auto_graded', false);
            })
            ->with('question.questionType')
            ->get()
            ->filter(function($response) {
                // Additional filter: check if response actually has an answer
                $hasAnswer = false;
                
                if (!empty($response->response_data)) {
                    if (is_array($response->response_data)) {
                        foreach ($response->response_data as $key => $value) {
                            if ($value !== null && $value !== '' && $value !== []) {
                                $hasAnswer = true;
                                break;
                            }
                        }
                    } else {
                        $hasAnswer = true;
                    }
                }
                
                if (!$hasAnswer && !empty($response->selected_option_ids)) {
                    if (is_array($response->selected_option_ids)) {
                        $hasAnswer = !empty(array_filter($response->selected_option_ids));
                    } else {
                        $hasAnswer = true;
                    }
                }
                
                if (!$hasAnswer && !empty($response->response_text)) {
                    $text = trim($response->response_text);
                    if ($text !== '' && $text !== 'null' && $text !== '[]') {
                        $hasAnswer = true;
                    }
                }
                
                return $hasAnswer;
            })
            ->values();

        return view('admin.pages.grading.show', compact('attempt', 'responsesNeedingGrading'));
    }

    /**
     * تقرير HTML لإجابات المحاولة (سؤال، إجابة الطالب، الإجابة الصحيحة، الدرجات).
     */
    public function attemptReport($attemptId)
    {
        $attempt = QuizAttempt::with([
            'quiz',
            'student',
            'responses.question.questionType',
            'responses.question.options',
            'responses.questionType',
        ])->findOrFail($attemptId);

        if (! in_array($attempt->status, ['submitted', 'graded'], true)) {
            return redirect()->route('grading.index')
                ->withErrors(['error' => 'لا يمكن عرض تقرير محاولة لم يتم تسليمها بعد']);
        }

        $responses = $attempt->responses
            ->sortBy([
                ['answer_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $presenter = app(QuizGradingAnswerPresenter::class);

        return view('admin.pages.grading.attempt-report', compact('attempt', 'responses', 'presenter'));
    }

    /**
     * Grade a specific response.
     */
    public function gradeResponse(Request $request, $responseId)
    {
        $response = QuizResponse::with(['attempt', 'question'])->findOrFail($responseId);

        $validated = $request->validate([
            'score_obtained' => 'required|numeric|min:0|max:' . $response->max_score,
            'feedback' => 'nullable|string',
            'is_correct' => 'nullable|in:0,1,true,false,"true","false","1","0"',
        ]);
        
        // Convert is_correct to boolean
        if (isset($validated['is_correct'])) {
            $validated['is_correct'] = filter_var($validated['is_correct'], FILTER_VALIDATE_BOOLEAN);
        }

        DB::beginTransaction();
        try {
            // Update response
            $response->update([
                'score_obtained' => $validated['score_obtained'],
                'feedback' => $validated['feedback'] ?? null,
                'is_correct' => $validated['is_correct'] ?? ($validated['score_obtained'] >= $response->max_score),
                'auto_graded' => false,
                'graded_at' => now(),
            ]);

            // Recalculate attempt scores
            $attempt = $response->attempt;
            $attempt->grade();

            // Update analytics
            $this->updateStudentAnalytics($attempt);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تصحيح الإجابة بنجاح',
                'attempt' => $attempt->fresh(['responses'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التصحيح: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grade multiple responses at once.
     */
    public function gradeBulk(Request $request)
    {
        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*.id' => 'required|exists:quiz_responses,id',
            'responses.*.score_obtained' => 'required|numeric|min:0',
            'responses.*.feedback' => 'nullable|string',
            'responses.*.is_correct' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $attemptIds = [];

            foreach ($validated['responses'] as $responseData) {
                $response = QuizResponse::with('attempt')->findOrFail($responseData['id']);

                // Validate score doesn't exceed max
                if ($responseData['score_obtained'] > $response->max_score) {
                    throw new \Exception('الدرجة المدخلة أكبر من الدرجة القصوى للسؤال');
                }

                $response->update([
                    'score_obtained' => $responseData['score_obtained'],
                    'feedback' => $responseData['feedback'] ?? null,
                    'is_correct' => $responseData['is_correct'] ?? ($responseData['score_obtained'] >= $response->max_score),
                    'auto_graded' => false,
                    'graded_at' => now(),
                ]);

                $attemptIds[] = $response->attempt_id;
            }

            // Recalculate all affected attempts
            $uniqueAttemptIds = array_unique($attemptIds);
            foreach ($uniqueAttemptIds as $attemptId) {
                $attempt = QuizAttempt::find($attemptId);
                if ($attempt) {
                    $attempt->grade();
                    $this->updateStudentAnalytics($attempt);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تصحيح الإجابات بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التصحيح: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete grading for an attempt.
     */
    public function completeGrading(Request $request, $attemptId)
    {
        $attempt = QuizAttempt::with('responses')->findOrFail($attemptId);

        $validated = $request->validate([
            'feedback' => 'nullable|string',
        ]);

        // Helper function to check if response has an answer
        $responseHasAnswer = function($response) {
            $hasAnswer = false;
            
            // Check response_data
            if (!empty($response->response_data)) {
                if (is_array($response->response_data)) {
                    foreach ($response->response_data as $key => $value) {
                        if ($value !== null && $value !== '' && $value !== []) {
                            $hasAnswer = true;
                            break;
                        }
                    }
                } else {
                    $hasAnswer = true;
                }
            }
            
            // Check selected_option_ids
            if (!$hasAnswer && !empty($response->selected_option_ids)) {
                if (is_array($response->selected_option_ids)) {
                    $hasAnswer = !empty(array_filter($response->selected_option_ids));
                } else {
                    $hasAnswer = true;
                }
            }
            
            // Check response_text
            if (!$hasAnswer && !empty($response->response_text)) {
                $text = trim($response->response_text);
                if ($text !== '' && $text !== 'null' && $text !== '[]') {
                    $hasAnswer = true;
                }
            }
            
            return $hasAnswer;
        };

        // Check if all responses WITH ANSWERS are graded (exclude unanswered questions)
        $allResponses = $attempt->responses;
        $responsesWithAnswers = $allResponses->filter($responseHasAnswer);
        $ungradedResponsesWithAnswers = $responsesWithAnswers->filter(function($response) {
            return $response->score_obtained === null;
        });

        if ($ungradedResponsesWithAnswers->count() > 0) {
            return back()->withErrors(['error' => "يوجد {$ungradedResponsesWithAnswers->count()} إجابة لم يتم تصحيحها بعد"]);
        }

        DB::beginTransaction();
        try {
            // Set score_obtained to 0 for responses without answers if not already set
            foreach ($allResponses as $response) {
                if (!$responseHasAnswer($response) && $response->score_obtained === null) {
                    $response->update([
                        'score_obtained' => 0,
                        'is_correct' => false,
                        'auto_graded' => true,
                    ]);
                }
            }

            // Recalculate scores
            $attempt->grade();
            $attempt->refresh();

            // Ensure grade_status is fully_graded after manual completion
            if ($attempt->grade_status !== 'fully_graded') {
                $attempt->update([
                    'grade_status' => 'fully_graded',
                    'status' => 'graded',
                ]);
            }

            // Update grading info
            $attempt->update([
                'status' => 'graded',
                'grade_status' => 'fully_graded',
                'feedback' => $validated['feedback'] ?? $attempt->feedback,
                'graded_by' => auth()->id(),
                'graded_at' => now(),
            ]);

            // Update analytics
            $this->updateStudentAnalytics($attempt);

            DB::commit();

            return redirect()->route('grading.index')
                ->with('success', 'تم إكمال تصحيح المحاولة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error completing grading', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'حدث خطأ أثناء إكمال التصحيح: ' . $e->getMessage()]);
        }
    }

    /**
     * Regrade an attempt (recalculate auto-graded questions).
     */
    public function regradeAttempt($attemptId)
    {
        $attempt = QuizAttempt::with([
            'responses.question.questionType',
            'responses.question.options'
        ])->findOrFail($attemptId);

        DB::beginTransaction();
        try {
            $regradedCount = 0;
            $skippedCount = 0;

            // Regrade all auto-gradable responses (يشمل fill_blanks بالمنطق الحالي في QuizResponse::autoGrade)
            foreach ($attempt->responses as $response) {
                if ($response->question === null) {
                    $skippedCount++;

                    continue;
                }

                $questionType = $response->question->questionType->name ?? '';

                // Skip essay and short_answer (require manual grading)
                if (in_array($questionType, ['essay', 'short_answer'])) {
                    $skippedCount++;
                    continue;
                }

                // Check if response has an answer
                $hasAnswer = false;

                // Check response_data
                if (!empty($response->response_data)) {
                    if (is_array($response->response_data)) {
                        foreach ($response->response_data as $key => $value) {
                            if ($value !== null && $value !== '' && $value !== []) {
                                $hasAnswer = true;
                                break;
                            }
                        }
                    } else {
                        $hasAnswer = true;
                    }
                }

                // Check selected_option_ids
                if (!$hasAnswer && !empty($response->selected_option_ids)) {
                    if (is_array($response->selected_option_ids)) {
                        $hasAnswer = !empty(array_filter($response->selected_option_ids));
                    } else {
                        $hasAnswer = true;
                    }
                }

                // Check response_text
                if (!$hasAnswer && !empty($response->response_text)) {
                    $text = trim($response->response_text);
                    if ($text !== '' && $text !== 'null' && $text !== '[]') {
                        $hasAnswer = true;
                    }
                }

                if ($hasAnswer) {
                    try {
                        $response->autoGrade();
                        $response->refresh();
                        $regradedCount++;
                    } catch (\Exception $e) {
                        \Log::error('Error regrading response in controller', [
                            'response_id' => $response->id,
                            'question_id' => $response->question_id,
                            'question_type' => $questionType,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    $skippedCount++;
                }
            }

            // Recalculate attempt scores
            $attempt->grade();
            $attempt->refresh();

            // Update analytics
            $this->updateStudentAnalytics($attempt);

            DB::commit();

            return back()->with('success', "تم إعادة تصحيح {$regradedCount} إجابة بنجاح. تم تخطي {$skippedCount} إجابة.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error regrading attempt in controller', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'حدث خطأ أثناء إعادة التصحيح: ' . $e->getMessage()]);
        }
    }

    /**
     * Get grading statistics for a quiz.
     */
    public function getQuizStats($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        $stats = [
            'total_attempts' => $quiz->attempts()->count(),
            'pending_grading' => $quiz->attempts()
                ->where('status', 'submitted')
                ->where('grade_status', '!=', 'fully_graded')
                ->count(),
            'graded' => $quiz->attempts()
                ->where('status', 'graded')
                ->count(),
            'average_score' => $quiz->attempts()
                ->where('status', 'graded')
                ->avg('percentage_score'),
            'pass_rate' => $this->calculatePassRate($quiz),
            'grading_time' => $this->calculateAverageGradingTime($quiz),
        ];

        return response()->json($stats);
    }

    /**
     * Export grading report.
     */
    public function exportReport(Request $request)
    {
        $validated = $request->validate([
            'quiz_id' => 'nullable|exists:quizzes,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after:from_date',
        ]);

        $query = QuizAttempt::with(['quiz', 'student', 'grader'])
            ->where('status', 'graded');

        if (isset($validated['quiz_id'])) {
            $query->where('quiz_id', $validated['quiz_id']);
        }

        if (isset($validated['from_date'])) {
            $query->whereDate('graded_at', '>=', $validated['from_date']);
        }

        if (isset($validated['to_date'])) {
            $query->whereDate('graded_at', '<=', $validated['to_date']);
        }

        $attempts = $query->get();

        $reportData = $attempts->map(function ($attempt) {
            return [
                'الطالب' => $attempt->student->name,
                'الاختبار' => $attempt->quiz->title,
                'رقم المحاولة' => $attempt->attempt_number,
                'تاريخ التسليم' => $attempt->submitted_at->format('Y-m-d H:i'),
                'تاريخ التصحيح' => $attempt->graded_at ? $attempt->graded_at->format('Y-m-d H:i') : 'لم يتم التصحيح',
                'المصحح' => $attempt->grader ? $attempt->grader->name : '-',
                'الدرجة' => $attempt->total_score . ' / ' . $attempt->max_score,
                'النسبة المئوية' => round($attempt->percentage_score, 2) . '%',
                'النتيجة' => $attempt->passed ? 'ناجح' : 'راسب',
                'الوقت المستغرق' => $attempt->getTimeSpentHumanReadable(),
            ];
        });

        $filename = 'grading_report_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($reportData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Update student analytics after grading.
     */
    private function updateStudentAnalytics(QuizAttempt $attempt): void
    {
        $analytics = QuizAnalytics::firstOrNew([
            'student_id' => $attempt->student_id,
            'quiz_id' => $attempt->quiz_id,
            'course_id' => $attempt->quiz->course_id,
        ]);

        $analytics->recalculate();
    }

    /**
     * Calculate pass rate for a quiz.
     */
    private function calculatePassRate(Quiz $quiz): float
    {
        $totalGraded = $quiz->attempts()
            ->where('status', 'graded')
            ->count();

        if ($totalGraded === 0) {
            return 0;
        }

        $passed = $quiz->attempts()
            ->where('status', 'graded')
            ->where('passed', true)
            ->count();

        return ($passed / $totalGraded) * 100;
    }

    /**
     * Calculate average grading time.
     */
    private function calculateAverageGradingTime(Quiz $quiz): ?int
    {
        $attempts = $quiz->attempts()
            ->where('status', 'graded')
            ->whereNotNull('submitted_at')
            ->whereNotNull('graded_at')
            ->get();

        if ($attempts->isEmpty()) {
            return null;
        }

        $totalSeconds = 0;
        foreach ($attempts as $attempt) {
            $totalSeconds += $attempt->submitted_at->diffInSeconds($attempt->graded_at);
        }

        return (int)($totalSeconds / $attempts->count());
    }
}
