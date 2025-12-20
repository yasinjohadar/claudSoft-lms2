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
use Illuminate\Support\Facades\DB;

class QuizAnalyticsController extends Controller
{
    /**
     * Display analytics dashboard.
     */
    public function index(Request $request)
    {
        // Filter options
        $courses = Course::where('is_published', true)->get();
        $quizzes = Quiz::where('is_published', true)->orderBy('title')->get(['id', 'title']);

        // Overall statistics - combine both Quiz and QuestionModule
        $totalQuizzes = Quiz::where('is_published', true)->count();
        $totalQuestionModules = QuestionModule::count();

        $totalQuizAttempts = QuizAttempt::count();
        $totalModuleAttempts = QuestionModuleAttempt::count();

        $completedQuizAttempts = QuizAttempt::where('is_completed', true)->count();
        $completedModuleAttempts = QuestionModuleAttempt::where('status', 'completed')->count();

        $avgQuizScore = QuizAttempt::where('is_completed', true)
            ->whereNotNull('percentage_score')
            ->avg('percentage_score') ?? 0;

        $avgModuleScore = QuestionModuleAttempt::where('status', 'completed')
            ->whereNotNull('percentage')
            ->avg('percentage') ?? 0;

        $totalAttempts = $totalQuizAttempts + $totalModuleAttempts;
        $completedAttempts = $completedQuizAttempts + $completedModuleAttempts;

        // Calculate weighted average score
        $averageScore = 0;
        if ($completedAttempts > 0) {
            $averageScore = (($avgQuizScore * $completedQuizAttempts) + ($avgModuleScore * $completedModuleAttempts)) / $completedAttempts;
        }

        $stats = [
            'total_quizzes' => $totalQuizzes + $totalQuestionModules,
            'total_attempts' => $totalAttempts,
            'completed_attempts' => $completedAttempts,
            'average_score' => $averageScore,
            'total_students' => User::whereHas('enrollments')->count(),
        ];

        // Recent activity - combine both systems
        $recentQuizAttempts = QuizAttempt::with(['quiz', 'student'])
            ->orderBy('started_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($attempt) {
                return [
                    'student' => $attempt->student,
                    'title' => $attempt->quiz->title ?? 'N/A',
                    'type' => 'quiz',
                    'is_completed' => $attempt->is_completed,
                    'score' => $attempt->percentage_score,
                    'started_at' => $attempt->started_at,
                ];
            });

        $recentModuleAttempts = QuestionModuleAttempt::with(['questionModule', 'student'])
            ->orderBy('started_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($attempt) {
                return [
                    'student' => $attempt->student,
                    'title' => $attempt->questionModule->title ?? 'N/A',
                    'type' => 'module',
                    'is_completed' => $attempt->status === 'completed',
                    'score' => $attempt->percentage,
                    'started_at' => $attempt->started_at,
                ];
            });

        $recentAttempts = $recentQuizAttempts->concat($recentModuleAttempts)
            ->sortByDesc('started_at')
            ->take(10);

        // Top performing students
        $topStudents = $this->getTopStudents(10);

        // Worst performing quizzes
        $difficultQuizzes = $this->getDifficultQuizzes(5);

        return view('admin.pages.analytics.index', compact(
            'courses',
            'quizzes',
            'stats',
            'recentAttempts',
            'topStudents',
            'difficultQuizzes'
        ));
    }

    /**
     * Show detailed quiz analytics.
     */
    public function quiz($quizId)
    {
        $quiz = Quiz::with(['course', 'quizQuestions.question'])->findOrFail($quizId);

        // Quiz statistics
        $stats = [
            'total_attempts' => $quiz->attempts()->count(),
            'completed_attempts' => $quiz->attempts()->where('is_completed', true)->count(),
            'in_progress' => $quiz->attempts()->where('status', 'in_progress')->count(),
            'average_score' => $quiz->attempts()
                ->where('is_completed', true)
                ->avg('percentage_score'),
            'highest_score' => $quiz->attempts()
                ->where('is_completed', true)
                ->max('percentage_score'),
            'lowest_score' => $quiz->attempts()
                ->where('is_completed', true)
                ->min('percentage_score'),
            'pass_rate' => $this->calculatePassRate($quiz),
            'average_time' => $quiz->attempts()
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
     * Get top performing students.
     */
    private function getTopStudents(int $limit = 10)
    {
        // Get all students who have completed attempts in either system
        $students = User::whereHas('courseEnrollments')->get();

        $studentStats = $students->map(function($student) {
            // Quiz attempts
            $quizAvg = QuizAttempt::where('student_id', $student->id)
                ->where('is_completed', true)
                ->avg('percentage_score') ?? 0;
            $quizCount = QuizAttempt::where('student_id', $student->id)
                ->where('is_completed', true)
                ->count();

            // Question module attempts
            $moduleAvg = QuestionModuleAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->avg('percentage') ?? 0;
            $moduleCount = QuestionModuleAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->count();

            $totalAttempts = $quizCount + $moduleCount;

            // Calculate weighted average
            $averageScore = 0;
            if ($totalAttempts > 0) {
                $averageScore = (($quizAvg * $quizCount) + ($moduleAvg * $moduleCount)) / $totalAttempts;
            }

            return (object)[
                'id' => $student->id,
                'name' => $student->name,
                'average_score' => $averageScore,
                'attempts_count' => $totalAttempts,
            ];
        })
        ->filter(function($stat) {
            return $stat->attempts_count > 0;
        })
        ->sortByDesc('average_score')
        ->take($limit);

        return $studentStats;
    }

    /**
     * Get most difficult quizzes.
     */
    private function getDifficultQuizzes(int $limit = 5)
    {
        // Get difficult quizzes
        $quizzes = Quiz::select('quizzes.*')
            ->join('quiz_attempts', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->where('quiz_attempts.is_completed', true)
            ->groupBy('quizzes.id')
            ->havingRaw('COUNT(quiz_attempts.id) >= 1')
            ->orderByRaw('AVG(quiz_attempts.percentage_score) ASC')
            ->limit($limit)
            ->get()
            ->map(function($quiz) {
                $avgScore = $quiz->attempts()->where('is_completed', true)->avg('percentage_score') ?? 0;
                $attemptsCount = $quiz->attempts()->where('is_completed', true)->count();
                return (object)[
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'average_score' => $avgScore,
                    'attempts_count' => $attemptsCount,
                ];
            });

        // Get difficult question modules
        $modules = QuestionModule::select('question_modules.*')
            ->join('question_module_attempts', 'question_modules.id', '=', 'question_module_attempts.question_module_id')
            ->where('question_module_attempts.status', 'completed')
            ->groupBy('question_modules.id')
            ->havingRaw('COUNT(question_module_attempts.id) >= 1')
            ->orderByRaw('AVG(question_module_attempts.percentage) ASC')
            ->limit($limit)
            ->get()
            ->map(function($module) {
                $avgScore = QuestionModuleAttempt::where('question_module_id', $module->id)
                    ->where('status', 'completed')
                    ->avg('percentage') ?? 0;
                $attemptsCount = QuestionModuleAttempt::where('question_module_id', $module->id)
                    ->where('status', 'completed')
                    ->count();
                return (object)[
                    'id' => $module->id,
                    'title' => $module->title,
                    'average_score' => $avgScore,
                    'attempts_count' => $attemptsCount,
                ];
            });

        // Combine and sort by average score
        return $quizzes->concat($modules)
            ->sortBy('average_score')
            ->take($limit);
    }

    /**
     * Calculate pass rate.
     */
    private function calculatePassRate(Quiz $quiz): float
    {
        $completed = $quiz->attempts()->where('is_completed', true)->count();

        if ($completed === 0) {
            return 0;
        }

        $passed = $quiz->attempts()
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
        return $quiz->quizQuestions->map(function($quizQuestion) {
            $responses = QuizResponse::where('question_id', $quizQuestion->question_id)
                ->whereHas('attempt', function($q) use ($quizQuestion) {
                    $q->where('quiz_id', $quizQuestion->quiz_id)
                      ->where('is_completed', true);
                })
                ->get();

            $totalResponses = $responses->count();
            $correctResponses = $responses->where('is_correct', true)->count();

            return [
                'question' => $quizQuestion->question,
                'total_responses' => $totalResponses,
                'correct_responses' => $correctResponses,
                'success_rate' => $totalResponses > 0 ? ($correctResponses / $totalResponses) * 100 : 0,
                'average_score' => $responses->avg('score_obtained'),
                'average_time' => $responses->avg('time_spent'),
            ];
        });
    }

    /**
     * Get student performance for a quiz.
     */
    private function getStudentPerformance(Quiz $quiz)
    {
        return $quiz->attempts()
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
