<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use App\Models\QuizAnalytics;
use Illuminate\Http\Request;

class QuizReviewController extends Controller
{
    /**
     * Display list of student's quiz attempts.
     */
    public function index(Request $request)
    {
        $studentId = auth()->id();

        $query = QuizAttempt::with(['quiz.course'])
            ->where('student_id', $studentId)
            ->orderBy('started_at', 'desc');

        // Filter by quiz
        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by passed/failed
        if ($request->filled('result')) {
            if ($request->result === 'passed') {
                $query->where('passed', true);
            } elseif ($request->result === 'failed') {
                $query->where('passed', false);
            }
        }

        $attempts = $query->paginate(15);

        // Overall statistics
        $stats = [
            'total_attempts' => QuizAttempt::where('student_id', $studentId)->count(),
            'completed' => QuizAttempt::where('student_id', $studentId)
                ->where('is_completed', true)
                ->count(),
            'in_progress' => QuizAttempt::where('student_id', $studentId)
                ->where('status', 'in_progress')
                ->count(),
            'passed' => QuizAttempt::where('student_id', $studentId)
                ->where('passed', true)
                ->count(),
            'average_score' => QuizAttempt::where('student_id', $studentId)
                ->where('is_completed', true)
                ->avg('percentage_score'),
        ];

        return view('student.pages.quizzes.review-index', compact('attempts', 'stats'));
    }

    /**
     * Display detailed review of a specific attempt.
     */
    public function show($attemptId)
    {
        $attempt = QuizAttempt::with([
            'quiz.settings',
            'quiz.course',
            'responses.question.questionType',
            'responses.question.options',
            'grader'
        ])->findOrFail($attemptId);

        $studentId = auth()->id();

        // Verify ownership
        if ($attempt->student_id !== $studentId) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه المحاولة');
        }

        // Check if student can review this attempt based on quiz settings
        if (!$this->canReviewAttempt($attempt)) {
            return redirect()->route('student.quizzes.review.index')
                ->withErrors(['error' => 'لا يمكنك مراجعة هذه المحاولة في الوقت الحالي']);
        }

        // Organize responses by question order
        $orderedResponses = collect($attempt->questions_order)->map(function($questionId) use ($attempt) {
            return $attempt->responses()
                ->where('question_id', $questionId)
                ->with('question.options')
                ->first();
        })->filter();

        // Calculate statistics
        $stats = [
            'total_questions' => $orderedResponses->count(),
            'answered' => $orderedResponses->filter(function($response) {
                return $response->response_text || $response->response_data || $response->selected_option_ids;
            })->count(),
            'correct' => $orderedResponses->where('is_correct', true)->count(),
            'incorrect' => $orderedResponses->where('is_correct', false)->count(),
            'graded' => $orderedResponses->whereNotNull('score_obtained')->count(),
            'ungraded' => $orderedResponses->whereNull('score_obtained')->count(),
        ];

        return view('student.pages.quizzes.review-show', compact('attempt', 'orderedResponses', 'stats'));
    }

    /**
     * Display analytics and performance insights.
     */
    public function analytics(Request $request)
    {
        $studentId = auth()->id();

        // Get all analytics for the student
        $analytics = QuizAnalytics::where('student_id', $studentId)
            ->with(['quiz', 'course'])
            ->get();

        // Overall performance metrics
        $overallMetrics = [
            'total_quizzes' => $analytics->count(),
            'average_score' => $analytics->avg('average_percentage'),
            'best_score' => $analytics->max('best_percentage'),
            'total_time' => $analytics->sum('total_time_spent'),
            'average_improvement' => $analytics->avg('improvement_rate'),
        ];

        // Get all strengths and weaknesses
        $allStrengths = [];
        $allWeaknesses = [];

        foreach ($analytics as $analytic) {
            if (!empty($analytic->strengths)) {
                $allStrengths = array_merge($allStrengths, $analytic->strengths);
            }
            if (!empty($analytic->weaknesses)) {
                $allWeaknesses = array_merge($allWeaknesses, $analytic->weaknesses);
            }
        }

        // Aggregate strengths
        $strengthsByType = [];
        foreach ($allStrengths as $strength) {
            $type = $strength['type'] ?? 'unknown';
            if (!isset($strengthsByType[$type])) {
                $strengthsByType[$type] = [
                    'type' => $type,
                    'display_name' => $strength['display_name'] ?? $type,
                    'total_questions' => 0,
                    'total_score' => 0,
                    'max_score' => 0,
                ];
            }
            $strengthsByType[$type]['total_questions'] += $strength['total_questions'] ?? 0;
            $strengthsByType[$type]['total_score'] += $strength['total_score'] ?? 0;
            $strengthsByType[$type]['max_score'] += $strength['max_score'] ?? 0;
        }

        // Calculate percentages for strengths
        foreach ($strengthsByType as &$strength) {
            $strength['percentage'] = $strength['max_score'] > 0
                ? ($strength['total_score'] / $strength['max_score']) * 100
                : 0;
        }

        // Sort and get top 5 strengths
        $topStrengths = collect($strengthsByType)
            ->sortByDesc('percentage')
            ->take(5)
            ->values();

        // Aggregate weaknesses
        $weaknessesByType = [];
        foreach ($allWeaknesses as $weakness) {
            $type = $weakness['type'] ?? 'unknown';
            if (!isset($weaknessesByType[$type])) {
                $weaknessesByType[$type] = [
                    'type' => $type,
                    'display_name' => $weakness['display_name'] ?? $type,
                    'total_questions' => 0,
                    'total_score' => 0,
                    'max_score' => 0,
                ];
            }
            $weaknessesByType[$type]['total_questions'] += $weakness['total_questions'] ?? 0;
            $weaknessesByType[$type]['total_score'] += $weakness['total_score'] ?? 0;
            $weaknessesByType[$type]['max_score'] += $weakness['max_score'] ?? 0;
        }

        // Calculate percentages for weaknesses
        foreach ($weaknessesByType as &$weakness) {
            $weakness['percentage'] = $weakness['max_score'] > 0
                ? ($weakness['total_score'] / $weakness['max_score']) * 100
                : 0;
        }

        // Sort and get top 5 weaknesses
        $topWeaknesses = collect($weaknessesByType)
            ->sortBy('percentage')
            ->take(5)
            ->values();

        // Performance by course
        $performanceByCourse = $analytics->groupBy('course_id')->map(function($courseAnalytics) {
            return [
                'course' => $courseAnalytics->first()->course,
                'average_score' => $courseAnalytics->avg('average_percentage'),
                'quizzes_taken' => $courseAnalytics->count(),
                'total_time' => $courseAnalytics->sum('total_time_spent'),
            ];
        })->values();

        // Progress over time
        $progressOverTime = QuizAttempt::where('student_id', $studentId)
            ->where('is_completed', true)
            ->selectRaw('DATE(started_at) as date, AVG(percentage_score) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->limit(30)
            ->get();

        return view('student.pages.quizzes.analytics', compact(
            'analytics',
            'overallMetrics',
            'topStrengths',
            'topWeaknesses',
            'performanceByCourse',
            'progressOverTime'
        ));
    }

    /**
     * Display comparison of multiple attempts for same quiz.
     */
    public function compareAttempts(Request $request, $quizId)
    {
        $studentId = auth()->id();

        $attempts = QuizAttempt::where('student_id', $studentId)
            ->where('quiz_id', $quizId)
            ->where('is_completed', true)
            ->with(['quiz', 'responses'])
            ->orderBy('attempt_number', 'asc')
            ->get();

        if ($attempts->isEmpty()) {
            return redirect()->route('student.quizzes.review.index')
                ->withErrors(['error' => 'لا توجد محاولات مكتملة لهذا الاختبار']);
        }

        $quiz = $attempts->first()->quiz;

        // Prepare comparison data
        $comparisonData = $attempts->map(function($attempt) {
            return [
                'attempt' => $attempt,
                'stats' => [
                    'total_score' => $attempt->total_score,
                    'percentage' => $attempt->percentage_score,
                    'time_spent' => $attempt->time_spent,
                    'correct_answers' => $attempt->getCorrectCount(),
                    'passed' => $attempt->passed,
                ],
            ];
        });

        // Calculate improvement
        $improvement = [];
        if ($attempts->count() > 1) {
            $firstAttempt = $attempts->first();
            $lastAttempt = $attempts->last();

            $improvement = [
                'score_change' => $lastAttempt->percentage_score - $firstAttempt->percentage_score,
                'time_change' => $lastAttempt->time_spent - $firstAttempt->time_spent,
                'accuracy_change' => (
                    ($lastAttempt->getCorrectCount() / $lastAttempt->responses()->count()) * 100
                ) - (
                    ($firstAttempt->getCorrectCount() / $firstAttempt->responses()->count()) * 100
                ),
            ];
        }

        return view('student.pages.quizzes.compare', compact('quiz', 'comparisonData', 'improvement'));
    }

    /**
     * Get specific question review (AJAX).
     */
    public function getQuestionReview($attemptId, $questionId)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);
        $studentId = auth()->id();

        if ($attempt->student_id !== $studentId) {
            return response()->json(['success' => false], 403);
        }

        if (!$this->canReviewAttempt($attempt)) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك مراجعة هذه المحاولة في الوقت الحالي'
            ], 403);
        }

        $response = $attempt->responses()
            ->where('question_id', $questionId)
            ->with(['question.questionType', 'question.options'])
            ->first();

        if (!$response) {
            return response()->json(['success' => false], 404);
        }

        return response()->json([
            'success' => true,
            'response' => $response,
            'can_see_correct_answer' => $this->canSeeCorrectAnswer($attempt),
            'can_see_explanation' => $this->canSeeExplanation($attempt),
        ]);
    }

    /**
     * Download attempt report as PDF.
     */
    public function downloadReport($attemptId)
    {
        $attempt = QuizAttempt::with([
            'quiz',
            'quiz.course',
            'student',
            'responses.question.questionType'
        ])->findOrFail($attemptId);

        $studentId = auth()->id();

        if ($attempt->student_id !== $studentId) {
            abort(403);
        }

        // Prepare report data
        $reportData = [
            'student' => $attempt->student->name,
            'quiz' => $attempt->quiz->title,
            'course' => $attempt->quiz->course->title,
            'attempt_number' => $attempt->attempt_number,
            'started_at' => $attempt->started_at->format('Y-m-d H:i'),
            'submitted_at' => $attempt->submitted_at ? $attempt->submitted_at->format('Y-m-d H:i') : '-',
            'time_spent' => $attempt->getTimeSpentHumanReadable(),
            'total_score' => $attempt->total_score,
            'max_score' => $attempt->max_score,
            'percentage_score' => round($attempt->percentage_score, 2),
            'passed' => $attempt->passed ? 'نعم' : 'لا',
            'result' => $attempt->passed ? 'ناجح' : 'راسب',
            'questions' => $attempt->responses->map(function($response) {
                return [
                    'question_text' => $response->question->question_text,
                    'type' => $response->questionType->display_name,
                    'score_obtained' => $response->score_obtained ?? 'لم يتم التصحيح',
                    'max_score' => $response->max_score,
                    'is_correct' => $response->is_correct ? 'صحيح' : 'خطأ',
                ];
            }),
        ];

        $filename = 'quiz_report_' . $attemptId . '_' . date('Y-m-d') . '.json';

        return response()->json($reportData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Check if student can review an attempt.
     */
    private function canReviewAttempt(QuizAttempt $attempt): bool
    {
        $quiz = $attempt->quiz;

        // Can't review if still in progress
        if ($attempt->status === 'in_progress') {
            return false;
        }

        // Check allow_review setting
        if (!$quiz->allow_review) {
            return false;
        }

        return true;
    }

    /**
     * Check if student can see correct answers.
     */
    private function canSeeCorrectAnswer(QuizAttempt $attempt): bool
    {
        $quiz = $attempt->quiz;

        if (!$quiz->show_correct_answers) {
            return false;
        }

        $when = $quiz->show_correct_answers_after;

        switch ($when) {
            case 'immediately':
                return true;

            case 'after_due':
                if ($quiz->due_date) {
                    return now()->isAfter($quiz->due_date);
                }
                return true;

            case 'after_graded':
                return $attempt->status === 'graded';

            case 'never':
                return false;

            default:
                return false;
        }
    }

    /**
     * Check if student can see explanations.
     */
    private function canSeeExplanation(QuizAttempt $attempt): bool
    {
        // Same logic as correct answers for now
        return $this->canSeeCorrectAnswer($attempt);
    }

    /**
     * Get student's quiz history.
     */
    public function history($quizId)
    {
        $studentId = auth()->id();

        $attempts = QuizAttempt::where('student_id', $studentId)
            ->where('quiz_id', $quizId)
            ->with('quiz')
            ->orderBy('attempt_number', 'desc')
            ->get();

        if ($attempts->isEmpty()) {
            return redirect()->route('student.quizzes.index')
                ->withErrors(['error' => 'لا توجد محاولات لهذا الاختبار']);
        }

        $quiz = $attempts->first()->quiz;

        // Calculate statistics
        $stats = [
            'total_attempts' => $attempts->count(),
            'completed' => $attempts->where('is_completed', true)->count(),
            'best_score' => $attempts->where('is_completed', true)->max('percentage_score'),
            'average_score' => $attempts->where('is_completed', true)->avg('percentage_score'),
            'total_time' => $attempts->where('is_completed', true)->sum('time_spent'),
            'passed_count' => $attempts->where('passed', true)->count(),
        ];

        return view('student.pages.quizzes.history', compact('quiz', 'attempts', 'stats'));
    }
}
