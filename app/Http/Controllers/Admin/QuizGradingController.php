<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use App\Models\QuizAnalytics;
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

        // Get responses that need manual grading
        $responsesNeedingGrading = $attempt->responses()
            ->whereNull('score_obtained')
            ->orWhere('auto_graded', false)
            ->with('question.questionType')
            ->get();

        return view('admin.pages.grading.show', compact('attempt', 'responsesNeedingGrading'));
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
            'is_correct' => 'nullable|boolean',
        ]);

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

        // Check if all responses are graded
        $ungradedCount = $attempt->responses()
            ->whereNull('score_obtained')
            ->count();

        if ($ungradedCount > 0) {
            return back()->withErrors(['error' => "يوجد {$ungradedCount} إجابة لم يتم تصحيحها بعد"]);
        }

        DB::beginTransaction();
        try {
            // Recalculate scores
            $attempt->grade();

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
            return back()->withErrors(['error' => 'حدث خطأ أثناء إكمال التصحيح: ' . $e->getMessage()]);
        }
    }

    /**
     * Regrade an attempt (recalculate auto-graded questions).
     */
    public function regradeAttempt($attemptId)
    {
        $attempt = QuizAttempt::with('responses')->findOrFail($attemptId);

        DB::beginTransaction();
        try {
            // Regrade all auto-gradable responses
            foreach ($attempt->responses as $response) {
                $questionType = $response->questionType->name ?? '';

                // Skip essay and calculated questions (manual grading)
                if (in_array($questionType, ['essay', 'calculated'])) {
                    continue;
                }

                // Regrade
                $response->autoGrade();
            }

            // Recalculate attempt scores
            $attempt->grade();

            // Update analytics
            $this->updateStudentAnalytics($attempt);

            DB::commit();

            return back()->with('success', 'تم إعادة تصحيح المحاولة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
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
