<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnalytics;
use App\Models\QuizResponse;
use App\Models\QuestionModule;
use App\Models\QuestionModuleAttempt;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class QuizAnalyticsController extends Controller
{
    /**
     * Display analytics dashboard.
     */
    public function index(Request $request)
    {
        $courses = Course::where('is_published', true)->orderBy('title')->get();
        $quizzes = Quiz::where('is_published', true)->orderBy('title')->get(['id', 'title', 'course_id']);

        $quizAttemptsQuery = $this->filteredQuizAttempts($request);
        $moduleAttemptsQuery = $this->filteredModuleAttempts($request);

        $totalQuizAttempts = (clone $quizAttemptsQuery)->count();
        $totalModuleAttempts = (clone $moduleAttemptsQuery)->count();

        $completedQuizAttempts = (clone $quizAttemptsQuery)->where('is_completed', true)->count();
        $completedModuleAttempts = (clone $moduleAttemptsQuery)->where('status', 'completed')->count();

        $passedQuizAttempts = (clone $quizAttemptsQuery)->where('is_completed', true)->where('passed', true)->count();
        $passedModuleAttempts = (clone $moduleAttemptsQuery)->where('status', 'completed')->where('is_passed', true)->count();

        $avgQuizScore = (clone $quizAttemptsQuery)->where('is_completed', true)
            ->whereNotNull('percentage_score')
            ->avg('percentage_score') ?? 0;

        $avgModuleScore = (clone $moduleAttemptsQuery)->where('status', 'completed')
            ->whereNotNull('percentage')
            ->avg('percentage') ?? 0;

        $avgQuizTime = (clone $quizAttemptsQuery)->where('is_completed', true)
            ->whereNotNull('time_spent')
            ->avg('time_spent') ?? 0;

        $avgModuleTime = (clone $moduleAttemptsQuery)->where('status', 'completed')
            ->whereNotNull('time_spent')
            ->avg('time_spent') ?? 0;

        $totalAttempts = $totalQuizAttempts + $totalModuleAttempts;
        $completedAttempts = $completedQuizAttempts + $completedModuleAttempts;
        $passedAttempts = $passedQuizAttempts + $passedModuleAttempts;

        $averageScore = 0;
        if ($completedAttempts > 0) {
            $averageScore = (($avgQuizScore * $completedQuizAttempts) + ($avgModuleScore * $completedModuleAttempts)) / $completedAttempts;
        }

        $averageTime = 0;
        if ($completedAttempts > 0) {
            $averageTime = (($avgQuizTime * $completedQuizAttempts) + ($avgModuleTime * $completedModuleAttempts)) / $completedAttempts;
        }

        $activeStudentIds = (clone $quizAttemptsQuery)->distinct()->pluck('student_id')
            ->merge((clone $moduleAttemptsQuery)->distinct()->pluck('student_id'))
            ->unique()
            ->filter();

        $courseFilter = $request->filled('course_id');
        $totalAssessments = Quiz::where('is_published', true)
            ->when($courseFilter, fn ($q) => $q->where('course_id', $request->course_id))
            ->count();

        if (!$courseFilter) {
            $totalAssessments += QuestionModule::count();
        }

        $stats = [
            'total_quizzes' => $totalAssessments,
            'total_attempts' => $totalAttempts,
            'completed_attempts' => $completedAttempts,
            'average_score' => $averageScore,
            'pass_rate' => $completedAttempts > 0 ? ($passedAttempts / $completedAttempts) * 100 : 0,
            'completion_rate' => $totalAttempts > 0 ? ($completedAttempts / $totalAttempts) * 100 : 0,
            'average_time' => $averageTime,
            'active_students' => $activeStudentIds->count(),
            'total_students' => User::whereHas('enrollments')->count(),
            'in_progress' => (clone $quizAttemptsQuery)->where('status', 'in_progress')->count()
                + (clone $moduleAttemptsQuery)->where('status', 'in_progress')->count(),
        ];

        $recentAttempts = $this->getRecentAttempts($request, 12);
        $topStudents = $this->getTopStudents($request, 10);
        $atRiskStudents = $this->getAtRiskStudents($request, 8);
        $difficultQuizzes = $this->getDifficultQuizzes($request, 8);
        $bestQuizzes = $this->getBestQuizzes($request, 8);
        $scoreDistribution = $this->getOverallScoreDistribution($request);
        $attemptTrends = $this->getOverallAttemptTrends($request);
        $coursePerformance = $this->getPerformanceByCourse($request, 8);

        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        return view('admin.pages.analytics.index', compact(
            'courses',
            'quizzes',
            'stats',
            'recentAttempts',
            'topStudents',
            'atRiskStudents',
            'difficultQuizzes',
            'bestQuizzes',
            'scoreDistribution',
            'attemptTrends',
            'coursePerformance',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show detailed quiz analytics.
     */
    public function quiz($quizId)
    {
        $quiz = Quiz::with([
            'course',
            'quizQuestions.question.questionType',
            'quizQuestions.question.options',
        ])->findOrFail($quizId);

        $attemptsQuery = fn () => $quiz->attempts()->realAttempts();

        // Quiz statistics
        $stats = [
            'total_attempts' => $attemptsQuery()->count(),
            'completed_attempts' => $attemptsQuery()->where('is_completed', true)->count(),
            'in_progress' => $attemptsQuery()->where('status', 'in_progress')->count(),
            'average_score' => $attemptsQuery()
                ->where('is_completed', true)
                ->avg('percentage_score'),
            'highest_score' => $attemptsQuery()
                ->where('is_completed', true)
                ->max('percentage_score'),
            'lowest_score' => $attemptsQuery()
                ->where('is_completed', true)
                ->min('percentage_score'),
            'pass_rate' => $this->calculatePassRate($quiz),
            'average_time' => $attemptsQuery()
                ->where('is_completed', true)
                ->avg('time_spent'),
        ];

        // Score distribution
        $scoreDistribution = $this->getScoreDistribution($quiz);

        // Question analysis
        $questionAnalysis = $this->getQuestionAnalysis($quiz);

        // Student performance
        $studentPerformance = $this->getStudentPerformance($quiz);

        // Attempt trends over time
        $attemptTrends = $this->getAttemptTrends($quiz);

        return view('admin.pages.analytics.quiz', compact(
            'quiz',
            'stats',
            'scoreDistribution',
            'questionAnalysis',
            'studentPerformance',
            'attemptTrends'
        ));
    }

    /**
     * Show detailed student analytics.
     */
    public function student($studentId)
    {
        $student = User::with(['enrollments.course'])->findOrFail($studentId);

        // Student overall statistics
        $stats = [
            'total_attempts' => QuizAttempt::where('student_id', $studentId)->count(),
            'completed_attempts' => QuizAttempt::where('student_id', $studentId)
                ->where('is_completed', true)
                ->count(),
            'average_score' => QuizAttempt::where('student_id', $studentId)
                ->where('is_completed', true)
                ->avg('percentage_score'),
            'best_score' => QuizAttempt::where('student_id', $studentId)
                ->where('is_completed', true)
                ->max('percentage_score'),
            'total_time' => QuizAttempt::where('student_id', $studentId)
                ->where('is_completed', true)
                ->sum('time_spent'),
            'pass_rate' => $this->calculateStudentPassRate($studentId),
        ];

        // Get all analytics records for this student
        $analytics = QuizAnalytics::where('student_id', $studentId)
            ->with(['quiz', 'course'])
            ->get();

        // Performance by course
        $performanceByCourse = $this->getStudentPerformanceByCourse($studentId);

        // Performance by quiz type
        $performanceByType = $this->getStudentPerformanceByType($studentId);

        // Strengths and weaknesses
        $strengths = $this->getStudentStrengths($studentId);
        $weaknesses = $this->getStudentWeaknesses($studentId);

        // Progress over time
        $progressOverTime = $this->getStudentProgressOverTime($studentId);

        return view('admin.pages.analytics.student', compact(
            'student',
            'stats',
            'analytics',
            'performanceByCourse',
            'performanceByType',
            'strengths',
            'weaknesses',
            'progressOverTime'
        ));
    }

    /**
     * Show course analytics.
     */
    public function course($courseId)
    {
        $course = Course::with(['quizzes' => function($q) {
            $q->where('is_published', true);
        }])->findOrFail($courseId);

        // Course statistics
        $stats = [
            'total_quizzes' => $course->quizzes()->count(),
            'total_attempts' => QuizAttempt::whereHas('quiz', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })->count(),
            'completed_attempts' => QuizAttempt::whereHas('quiz', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })->where('is_completed', true)->count(),
            'average_score' => QuizAttempt::whereHas('quiz', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })->where('is_completed', true)->avg('percentage_score'),
            'enrolled_students' => $course->enrollments()->count(),
        ];

        // Quiz performance comparison
        $quizComparison = $this->getCourseQuizComparison($course);

        // Student engagement
        $studentEngagement = $this->getCourseStudentEngagement($course);

        return view('admin.pages.analytics.course', compact(
            'course',
            'stats',
            'quizComparison',
            'studentEngagement'
        ));
    }

    /**
     * Compare multiple quizzes.
     */
    public function compare(Request $request)
    {
        $validated = $request->validate([
            'quiz_ids' => 'required|array|min:2|max:5',
            'quiz_ids.*' => 'exists:quizzes,id',
        ]);

        $quizzes = Quiz::whereIn('id', $validated['quiz_ids'])
            ->with('course')
            ->get();

        $comparison = [];

        foreach ($quizzes as $quiz) {
            $comparison[] = [
                'quiz' => $quiz,
                'stats' => [
                    'total_attempts' => $quiz->attempts()->count(),
                    'average_score' => $quiz->attempts()->where('is_completed', true)->avg('percentage_score'),
                    'pass_rate' => $this->calculatePassRate($quiz),
                    'average_time' => $quiz->attempts()->where('is_completed', true)->avg('time_spent'),
                ],
            ];
        }

        return view('admin.pages.analytics.compare', compact('comparison'));
    }

    /**
     * Export analytics report.
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:quiz,student,course,overall',
            'id' => 'nullable|integer',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after:from_date',
        ]);

        $reportData = [];

        switch ($validated['type']) {
            case 'quiz':
                $reportData = $this->exportQuizReport($validated['id'], $validated['from_date'] ?? null, $validated['to_date'] ?? null);
                break;
            case 'student':
                $reportData = $this->exportStudentReport($validated['id']);
                break;
            case 'course':
                $reportData = $this->exportCourseReport($validated['id']);
                break;
            case 'overall':
                $reportData = $this->exportOverallReport($validated['from_date'] ?? null, $validated['to_date'] ?? null);
                break;
        }

        $filename = 'analytics_' . $validated['type'] . '_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($reportData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Resolve date range from request filters.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function resolveDateRange(Request $request): array
    {
        if ($request->filled('from_date') && $request->filled('to_date')) {
            return [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay(),
            ];
        }

        $period = $request->input('period', '30');
        if ($period === 'all') {
            return [null, null];
        }

        $days = max(1, (int) $period);

        return [now()->subDays($days)->startOfDay(), now()->endOfDay()];
    }

    private function filteredQuizAttempts(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $query = QuizAttempt::query();

        if ($request->filled('course_id')) {
            $query->whereHas('quiz', fn ($q) => $q->where('course_id', $request->course_id));
        }

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        if ($from) {
            $query->where('started_at', '>=', $from);
        }

        if ($to) {
            $query->where('started_at', '<=', $to);
        }

        return $query;
    }

    private function filteredModuleAttempts(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $query = QuestionModuleAttempt::query();

        if ($request->filled('course_id')) {
            $query->whereHas('questionModule.courseModules', fn ($q) => $q->where('course_id', $request->course_id));
        }

        if ($from) {
            $query->where('started_at', '>=', $from);
        }

        if ($to) {
            $query->where('started_at', '<=', $to);
        }

        return $query;
    }

    private function getRecentAttempts(Request $request, int $limit = 10)
    {
        $recentQuizAttempts = $this->filteredQuizAttempts($request)
            ->with(['quiz', 'student'])
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($attempt) {
                return [
                    'student' => $attempt->student,
                    'student_id' => $attempt->student_id,
                    'title' => $attempt->quiz->title ?? 'N/A',
                    'type' => 'quiz',
                    'type_id' => $attempt->quiz_id,
                    'is_completed' => $attempt->is_completed,
                    'passed' => $attempt->passed,
                    'score' => $attempt->percentage_score,
                    'started_at' => $attempt->started_at,
                    'time_spent' => $attempt->time_spent,
                ];
            });

        $recentModuleAttempts = $this->filteredModuleAttempts($request)
            ->with(['questionModule', 'student'])
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($attempt) {
                return [
                    'student' => $attempt->student,
                    'student_id' => $attempt->student_id,
                    'title' => $attempt->questionModule->title ?? 'N/A',
                    'type' => 'module',
                    'type_id' => $attempt->question_module_id,
                    'is_completed' => $attempt->status === 'completed',
                    'passed' => $attempt->is_passed,
                    'score' => $attempt->percentage,
                    'started_at' => $attempt->started_at,
                    'time_spent' => $attempt->time_spent,
                ];
            });

        return $recentQuizAttempts->concat($recentModuleAttempts)
            ->sortByDesc('started_at')
            ->take($limit)
            ->values();
    }

    private function mergeStudentAttemptStats($quizStats, $moduleStats): array
    {
        $merged = [];

        foreach ($quizStats as $stat) {
            $merged[$stat->student_id] = [
                'weighted_sum' => ($stat->avg_score ?? 0) * ($stat->attempts_count ?? 0),
                'attempts_count' => (int) ($stat->attempts_count ?? 0),
            ];
        }

        foreach ($moduleStats as $stat) {
            if (!isset($merged[$stat->student_id])) {
                $merged[$stat->student_id] = ['weighted_sum' => 0, 'attempts_count' => 0];
            }

            $merged[$stat->student_id]['weighted_sum'] += ($stat->avg_score ?? 0) * ($stat->attempts_count ?? 0);
            $merged[$stat->student_id]['attempts_count'] += (int) ($stat->attempts_count ?? 0);
        }

        return $merged;
    }

    private function mapStudentStats(array $merged, string $sort = 'desc', ?int $limit = null, ?callable $filter = null)
    {
        if (empty($merged)) {
            return collect();
        }

        $students = User::whereIn('id', array_keys($merged))->get()->keyBy('id');

        $collection = collect($merged)->map(function ($data, $studentId) use ($students) {
            $attemptsCount = $data['attempts_count'];

            return (object) [
                'id' => (int) $studentId,
                'name' => $students[$studentId]->name ?? 'غير محدد',
                'average_score' => $attemptsCount > 0 ? $data['weighted_sum'] / $attemptsCount : 0,
                'attempts_count' => $attemptsCount,
            ];
        });

        if ($filter) {
            $collection = $collection->filter($filter);
        }

        $collection = $sort === 'asc'
            ? $collection->sortBy('average_score')
            : $collection->sortByDesc('average_score');

        if ($limit !== null) {
            $collection = $collection->take($limit);
        }

        return $collection->values();
    }

    private function getStudentAttemptStats(Request $request)
    {
        $quizStats = $this->filteredQuizAttempts($request)
            ->where('is_completed', true)
            ->whereNotNull('percentage_score')
            ->select('student_id', DB::raw('AVG(percentage_score) as avg_score'), DB::raw('COUNT(*) as attempts_count'))
            ->groupBy('student_id')
            ->get();

        $moduleStats = $this->filteredModuleAttempts($request)
            ->where('status', 'completed')
            ->whereNotNull('percentage')
            ->select('student_id', DB::raw('AVG(percentage) as avg_score'), DB::raw('COUNT(*) as attempts_count'))
            ->groupBy('student_id')
            ->get();

        return $this->mergeStudentAttemptStats($quizStats, $moduleStats);
    }

    /**
     * Get top performing students.
     */
    private function getTopStudents(Request $request, int $limit = 10)
    {
        return $this->mapStudentStats(
            $this->getStudentAttemptStats($request),
            'desc',
            $limit,
            fn ($stat) => $stat->attempts_count > 0
        );
    }

    private function getAtRiskStudents(Request $request, int $limit = 8)
    {
        return $this->mapStudentStats(
            $this->getStudentAttemptStats($request),
            'asc',
            $limit,
            fn ($stat) => $stat->attempts_count >= 2 && $stat->average_score < 60
        );
    }

    /**
     * Get most difficult assessments.
     */
    private function getDifficultQuizzes(Request $request, int $limit = 5)
    {
        return $this->getRankedAssessments($request, 'asc', $limit);
    }

    private function getBestQuizzes(Request $request, int $limit = 5)
    {
        return $this->getRankedAssessments($request, 'desc', $limit);
    }

    private function getRankedAssessments(Request $request, string $direction, int $limit)
    {
        [$from, $to] = $this->resolveDateRange($request);
        $courseId = $request->input('course_id');

        $quizQuery = Quiz::query()
            ->select('quizzes.*')
            ->join('quiz_attempts', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->where('quiz_attempts.is_completed', true)
            ->whereNotNull('quiz_attempts.percentage_score');

        if ($courseId) {
            $quizQuery->where('quizzes.course_id', $courseId);
        }

        if ($from) {
            $quizQuery->where('quiz_attempts.started_at', '>=', $from);
        }

        if ($to) {
            $quizQuery->where('quiz_attempts.started_at', '<=', $to);
        }

        if ($request->filled('quiz_id')) {
            $quizQuery->where('quizzes.id', $request->quiz_id);
        }

        $quizzes = $quizQuery
            ->with('course')
            ->groupBy('quizzes.id')
            ->havingRaw('COUNT(quiz_attempts.id) >= 1')
            ->orderByRaw('AVG(quiz_attempts.percentage_score) ' . ($direction === 'asc' ? 'ASC' : 'DESC'))
            ->limit($limit)
            ->get()
            ->map(function ($quiz) {
                $avgScore = $quiz->attempts()->where('is_completed', true)->avg('percentage_score') ?? 0;
                $attemptsCount = $quiz->attempts()->where('is_completed', true)->count();

                return (object) [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'type' => 'quiz',
                    'average_score' => $avgScore,
                    'attempts_count' => $attemptsCount,
                    'course' => $quiz->course?->title,
                ];
            });

        $moduleQuery = QuestionModule::query()
            ->select('question_modules.*')
            ->join('question_module_attempts', 'question_modules.id', '=', 'question_module_attempts.question_module_id')
            ->where('question_module_attempts.status', 'completed')
            ->whereNotNull('question_module_attempts.percentage');

        if ($courseId) {
            $moduleQuery->whereHas('courseModules', fn ($q) => $q->where('course_id', $courseId));
        }

        if ($from) {
            $moduleQuery->where('question_module_attempts.started_at', '>=', $from);
        }

        if ($to) {
            $moduleQuery->where('question_module_attempts.started_at', '<=', $to);
        }

        $modules = $moduleQuery
            ->groupBy('question_modules.id')
            ->havingRaw('COUNT(question_module_attempts.id) >= 1')
            ->orderByRaw('AVG(question_module_attempts.percentage) ' . ($direction === 'asc' ? 'ASC' : 'DESC'))
            ->limit($limit)
            ->get()
            ->map(function ($module) {
                $avgScore = QuestionModuleAttempt::where('question_module_id', $module->id)
                    ->where('status', 'completed')
                    ->avg('percentage') ?? 0;
                $attemptsCount = QuestionModuleAttempt::where('question_module_id', $module->id)
                    ->where('status', 'completed')
                    ->count();

                return (object) [
                    'id' => $module->id,
                    'title' => $module->title,
                    'type' => 'module',
                    'average_score' => $avgScore,
                    'attempts_count' => $attemptsCount,
                    'course' => null,
                ];
            });

        $sorted = $quizzes->concat($modules)->sortBy('average_score', SORT_REGULAR, $direction === 'desc');

        return $sorted->take($limit)->values();
    }

    private function getOverallScoreDistribution(Request $request): array
    {
        $ranges = [
            '0-20' => [0, 20],
            '21-40' => [21, 40],
            '41-60' => [41, 60],
            '61-80' => [61, 80],
            '81-100' => [81, 100],
        ];

        $distribution = array_fill_keys(array_keys($ranges), 0);

        $quizScores = $this->filteredQuizAttempts($request)
            ->where('is_completed', true)
            ->whereNotNull('percentage_score')
            ->pluck('percentage_score');

        $moduleScores = $this->filteredModuleAttempts($request)
            ->where('status', 'completed')
            ->whereNotNull('percentage')
            ->pluck('percentage');

        foreach ($quizScores->concat($moduleScores) as $score) {
            foreach ($ranges as $label => [$min, $max]) {
                if ($score >= $min && $score <= $max) {
                    $distribution[$label]++;
                    break;
                }
            }
        }

        return $distribution;
    }

    private function getOverallAttemptTrends(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $quizTrends = $this->filteredQuizAttempts($request)
            ->where('is_completed', true)
            ->selectRaw('DATE(started_at) as date, COUNT(*) as count, AVG(percentage_score) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $moduleTrends = $this->filteredModuleAttempts($request)
            ->where('status', 'completed')
            ->selectRaw('DATE(started_at) as date, COUNT(*) as count, AVG(percentage) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dates = $quizTrends->keys()->merge($moduleTrends->keys())->unique()->sort()->values();

        if ($dates->isEmpty() && $from && $to) {
            $cursor = $from->copy();
            while ($cursor->lte($to)) {
                $dates->push($cursor->toDateString());
                $cursor->addDay();
            }
        }

        return $dates->map(function ($date) use ($quizTrends, $moduleTrends) {
            $quiz = $quizTrends->get($date);
            $module = $moduleTrends->get($date);
            $quizCount = (int) ($quiz->count ?? 0);
            $moduleCount = (int) ($module->count ?? 0);
            $totalCount = $quizCount + $moduleCount;

            $weightedScore = 0;
            if ($totalCount > 0) {
                $weightedScore = (
                    (($quiz->avg_score ?? 0) * $quizCount) +
                    (($module->avg_score ?? 0) * $moduleCount)
                ) / $totalCount;
            }

            return (object) [
                'date' => $date,
                'count' => $totalCount,
                'avg_score' => $weightedScore,
            ];
        })->values();
    }

    private function getPerformanceByCourse(Request $request, int $limit = 8)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $query = QuizAttempt::query()
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->join('courses', 'quizzes.course_id', '=', 'courses.id')
            ->where('quiz_attempts.is_completed', true)
            ->whereNotNull('quiz_attempts.percentage_score');

        if ($from) {
            $query->where('quiz_attempts.started_at', '>=', $from);
        }

        if ($to) {
            $query->where('quiz_attempts.started_at', '<=', $to);
        }

        if ($request->filled('course_id')) {
            $query->where('courses.id', $request->course_id);
        }

        return $query
            ->select(
                'courses.id',
                'courses.title',
                DB::raw('COUNT(quiz_attempts.id) as attempts_count'),
                DB::raw('AVG(quiz_attempts.percentage_score) as average_score'),
                DB::raw('SUM(CASE WHEN quiz_attempts.passed = 1 THEN 1 ELSE 0 END) as passed_count')
            )
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('attempts_count')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $attempts = (int) $row->attempts_count;

                return (object) [
                    'id' => $row->id,
                    'title' => $row->title,
                    'attempts_count' => $attempts,
                    'average_score' => (float) $row->average_score,
                    'pass_rate' => $attempts > 0 ? ((int) $row->passed_count / $attempts) * 100 : 0,
                ];
            });
    }

    /**
     * Calculate pass rate.
     */
    private function calculatePassRate(Quiz $quiz): float
    {
        $completed = $quiz->attempts()->realAttempts()->where('is_completed', true)->count();

        if ($completed === 0) {
            return 0;
        }

        $passed = $quiz->attempts()
            ->realAttempts()
            ->where('is_completed', true)
            ->where('passed', true)
            ->count();

        return ($passed / $completed) * 100;
    }

    /**
     * Calculate student pass rate.
     */
    private function calculateStudentPassRate(int $studentId): float
    {
        $completed = QuizAttempt::where('student_id', $studentId)
            ->where('is_completed', true)
            ->count();

        if ($completed === 0) {
            return 0;
        }

        $passed = QuizAttempt::where('student_id', $studentId)
            ->where('is_completed', true)
            ->where('passed', true)
            ->count();

        return ($passed / $completed) * 100;
    }

    /**
     * Get score distribution for a quiz.
     */
    private function getScoreDistribution(Quiz $quiz)
    {
        $ranges = [
            '0-20' => [0, 20],
            '21-40' => [21, 40],
            '41-60' => [41, 60],
            '61-80' => [61, 80],
            '81-100' => [81, 100],
        ];

        $distribution = [];

        foreach ($ranges as $label => $range) {
            $count = $quiz->attempts()
                ->realAttempts()
                ->where('is_completed', true)
                ->whereBetween('percentage_score', $range)
                ->count();

            $distribution[$label] = $count;
        }

        return $distribution;
    }

    /**
     * Get question analysis for a quiz.
     */
    private function getQuestionAnalysis(Quiz $quiz)
    {
        return $quiz->quizQuestions->map(function ($quizQuestion) use ($quiz) {
            $responses = QuizResponse::where('question_id', $quizQuestion->question_id)
                ->whereHas('attempt', function ($q) use ($quiz) {
                    $q->where('quiz_id', $quiz->id)
                        ->realAttempts()
                        ->where('is_completed', true);
                })
                ->get();

            $question = $quizQuestion->question;
            $totalResponses = $responses->count();
            $correctResponses = $responses->where('is_correct', true)->count();
            $successRate = $totalResponses > 0 ? ($correctResponses / $totalResponses) * 100 : 0;

            return [
                'question' => $question,
                'total_responses' => $totalResponses,
                'correct_responses' => $correctResponses,
                'incorrect_responses' => $totalResponses - $correctResponses,
                'success_rate' => $successRate,
                'difficulty' => $successRate >= 70 ? 'easy' : ($successRate >= 50 ? 'medium' : 'hard'),
                'average_score' => $responses->avg('score_obtained'),
                'average_time' => $responses->avg('time_spent'),
                'option_distribution' => $this->getOptionDistribution($question, $responses),
            ];
        });
    }

    /**
     * Distribution of selected options for a question.
     */
    private function getOptionDistribution($question, $responses): array
    {
        if (!$question || !$question->relationLoaded('options')) {
            $question?->load('options');
        }

        $options = $question?->options ?? collect();
        if ($options->isEmpty()) {
            return [];
        }

        $mcTypes = ['multiple_choice_single', 'multiple_choice_multiple', 'true_false'];
        if (!in_array($question->questionType->name ?? '', $mcTypes, true)) {
            return [];
        }

        $counts = $options->pluck('id')->mapWithKeys(fn ($id) => [$id => 0])->all();

        foreach ($responses as $response) {
            $ids = $response->selected_option_ids ?? [];

            if (empty($ids) && is_array($response->response_data)) {
                $answer = $response->response_data['answer'] ?? null;
                if (is_array($answer)) {
                    $ids = $answer;
                } elseif (is_numeric($answer)) {
                    $ids = [(int) $answer];
                }
            }

            if (!is_array($ids)) {
                $ids = [$ids];
            }

            foreach ($ids as $id) {
                $id = (int) $id;
                if (array_key_exists($id, $counts)) {
                    $counts[$id]++;
                }
            }
        }

        $total = max(1, $responses->count());

        return $options->map(function ($option) use ($counts, $total) {
            $count = $counts[$option->id] ?? 0;

            return [
                'id' => $option->id,
                'text' => strip_tags($option->option_text ?? ''),
                'is_correct' => (bool) $option->is_correct,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1),
            ];
        })->values()->all();
    }

    /**
     * Get student performance for a quiz.
     */
    private function getStudentPerformance(Quiz $quiz)
    {
        return $quiz->attempts()
            ->realAttempts()
            ->with('student')
            ->where('is_completed', true)
            ->get()
            ->groupBy('student_id')
            ->map(function($attempts, $studentId) {
                return [
                    'student' => $attempts->first()->student,
                    'attempts_count' => $attempts->count(),
                    'best_score' => $attempts->max('percentage_score'),
                    'average_score' => $attempts->avg('percentage_score'),
                    'improvement' => $this->calculateImprovement($attempts),
                ];
            })
            ->sortByDesc('best_score')
            ->values();
    }

    /**
     * Calculate improvement between first and last attempt.
     */
    private function calculateImprovement($attempts)
    {
        if ($attempts->count() < 2) {
            return 0;
        }

        $first = $attempts->sortBy('attempt_number')->first();
        $last = $attempts->sortByDesc('attempt_number')->first();

        return $last->percentage_score - $first->percentage_score;
    }

    /**
     * Get attempt trends over time.
     */
    private function getAttemptTrends(Quiz $quiz)
    {
        return $quiz->attempts()
            ->realAttempts()
            ->where('is_completed', true)
            ->selectRaw('DATE(started_at) as date, COUNT(*) as count, AVG(percentage_score) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get student performance by course.
     */
    private function getStudentPerformanceByCourse(int $studentId)
    {
        return Course::whereHas('quizzes.attempts', function($q) use ($studentId) {
                $q->where('student_id', $studentId)
                  ->where('is_completed', true);
            })
            ->get()
            ->map(function($course) use ($studentId) {
                $attempts = QuizAttempt::whereHas('quiz', function($q) use ($course) {
                        $q->where('course_id', $course->id);
                    })
                    ->where('student_id', $studentId)
                    ->where('is_completed', true)
                    ->get();

                return [
                    'course' => $course,
                    'average_score' => $attempts->avg('percentage_score'),
                    'total_attempts' => $attempts->count(),
                ];
            });
    }

    /**
     * Get student performance by quiz type.
     */
    private function getStudentPerformanceByType(int $studentId)
    {
        return QuizAttempt::where('student_id', $studentId)
            ->where('is_completed', true)
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->select('quizzes.quiz_type', DB::raw('AVG(quiz_attempts.percentage_score) as avg_score'), DB::raw('COUNT(*) as count'))
            ->groupBy('quizzes.quiz_type')
            ->get();
    }

    /**
     * Get student strengths.
     */
    private function getStudentStrengths(int $studentId)
    {
        $analytics = QuizAnalytics::where('student_id', $studentId)->get();

        $allStrengths = [];
        foreach ($analytics as $analytic) {
            if (!empty($analytic->strengths)) {
                $allStrengths = array_merge($allStrengths, $analytic->strengths);
            }
        }

        return collect($allStrengths)->sortByDesc('percentage')->take(5)->values();
    }

    /**
     * Get student weaknesses.
     */
    private function getStudentWeaknesses(int $studentId)
    {
        $analytics = QuizAnalytics::where('student_id', $studentId)->get();

        $allWeaknesses = [];
        foreach ($analytics as $analytic) {
            if (!empty($analytic->weaknesses)) {
                $allWeaknesses = array_merge($allWeaknesses, $analytic->weaknesses);
            }
        }

        return collect($allWeaknesses)->sortBy('percentage')->take(5)->values();
    }

    /**
     * Get student progress over time.
     */
    private function getStudentProgressOverTime(int $studentId)
    {
        return QuizAttempt::where('student_id', $studentId)
            ->where('is_completed', true)
            ->selectRaw('DATE(started_at) as date, AVG(percentage_score) as avg_score')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get course quiz comparison.
     */
    private function getCourseQuizComparison(Course $course)
    {
        return $course->quizzes->map(function($quiz) {
            return [
                'quiz' => $quiz,
                'average_score' => $quiz->attempts()->where('is_completed', true)->avg('percentage_score'),
                'pass_rate' => $this->calculatePassRate($quiz),
                'total_attempts' => $quiz->attempts()->count(),
            ];
        });
    }

    /**
     * Get course student engagement.
     */
    private function getCourseStudentEngagement(Course $course)
    {
        $totalStudents = $course->enrollments()->count();
        $activeStudents = User::whereHas('quizAttempts', function($q) use ($course) {
                $q->whereHas('quiz', function($qq) use ($course) {
                    $qq->where('course_id', $course->id);
                });
            })
            ->count();

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'engagement_rate' => $totalStudents > 0 ? ($activeStudents / $totalStudents) * 100 : 0,
        ];
    }

    /**
     * Export quiz report.
     */
    private function exportQuizReport($quizId, $fromDate = null, $toDate = null)
    {
        $quiz = Quiz::with('course')->findOrFail($quizId);

        return [
            'quiz' => $quiz->title,
            'course' => $quiz->course->title,
            'statistics' => [
                'total_attempts' => $quiz->attempts()->count(),
                'completed' => $quiz->attempts()->where('is_completed', true)->count(),
                'average_score' => $quiz->attempts()->where('is_completed', true)->avg('percentage_score'),
                'pass_rate' => $this->calculatePassRate($quiz),
            ],
        ];
    }

    /**
     * Export student report.
     */
    private function exportStudentReport($studentId)
    {
        $student = User::findOrFail($studentId);

        return [
            'student' => $student->name,
            'email' => $student->email,
            'statistics' => [
                'total_attempts' => QuizAttempt::where('student_id', $studentId)->count(),
                'average_score' => QuizAttempt::where('student_id', $studentId)->where('is_completed', true)->avg('percentage_score'),
                'pass_rate' => $this->calculateStudentPassRate($studentId),
            ],
        ];
    }

    /**
     * Export course report.
     */
    private function exportCourseReport($courseId)
    {
        $course = Course::findOrFail($courseId);

        return [
            'course' => $course->title,
            'statistics' => [
                'total_quizzes' => $course->quizzes()->count(),
                'enrolled_students' => $course->enrollments()->count(),
            ],
        ];
    }

    /**
     * Export overall report.
     */
    private function exportOverallReport($fromDate = null, $toDate = null)
    {
        return [
            'total_quizzes' => Quiz::count(),
            'total_attempts' => QuizAttempt::count(),
            'average_score' => QuizAttempt::where('is_completed', true)->avg('percentage_score'),
        ];
    }
}
