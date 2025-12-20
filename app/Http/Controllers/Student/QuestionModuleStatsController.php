<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuestionModuleAttempt;
use App\Models\QuestionModule;
use Illuminate\Support\Facades\DB;

class QuestionModuleStatsController extends Controller
{
    /**
     * Display comprehensive statistics for student's question modules.
     */
    public function index()
    {
        $student = auth()->user();

        // Get all attempts for this student
        $attempts = QuestionModuleAttempt::with(['questionModule'])
            ->where('student_id', $student->id)
            ->orderBy('completed_at', 'desc')
            ->get();

        // Overall Statistics
        $totalAttempts = $attempts->where('status', 'completed')->count();
        $passedAttempts = $attempts->where('is_passed', true)->count();
        $failedAttempts = $attempts->where('is_passed', false)->count();
        $averageScore = $attempts->where('status', 'completed')->avg('percentage') ?? 0;

        // Get unique question modules attempted
        $uniqueModules = $attempts->pluck('question_module_id')->unique()->count();

        // Calculate total time spent (in hours)
        $totalTimeSpent = $attempts->where('status', 'completed')->sum('time_spent');
        $totalHours = round($totalTimeSpent / 3600, 1);

        // Recent attempts (last 10)
        $recentAttempts = $attempts->where('status', 'completed')->take(10);

        // Performance over time (last 30 days)
        $performanceData = $attempts->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(30))
            ->groupBy(function($item) {
                return $item->completed_at->format('Y-m-d');
            })
            ->map(function($group) {
                return [
                    'date' => $group->first()->completed_at->format('Y-m-d'),
                    'average' => round($group->avg('percentage'), 2),
                    'count' => $group->count(),
                ];
            })->values();

        // Question types performance
        $questionTypeStats = DB::table('question_module_responses as qmr')
            ->join('question_module_attempts as qma', 'qmr.attempt_id', '=', 'qma.id')
            ->join('question_bank as qb', 'qmr.question_id', '=', 'qb.id')
            ->join('question_types as qt', 'qb.question_type_id', '=', 'qt.id')
            ->where('qma.student_id', $student->id)
            ->where('qma.status', 'completed')
            ->whereNotNull('qmr.is_correct')
            ->select(
                'qt.display_name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN qmr.is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                DB::raw('ROUND((SUM(CASE WHEN qmr.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as percentage')
            )
            ->groupBy('qt.display_name')
            ->get();

        // Best and worst performances
        $bestAttempt = $attempts->where('status', 'completed')->sortByDesc('percentage')->first();
        $worstAttempt = $attempts->where('status', 'completed')->sortBy('percentage')->first();

        // Grade distribution
        $gradeDistribution = [
            'A' => $attempts->where('percentage', '>=', 90)->count(),
            'B' => $attempts->whereBetween('percentage', [80, 89.99])->count(),
            'C' => $attempts->whereBetween('percentage', [70, 79.99])->count(),
            'D' => $attempts->whereBetween('percentage', [60, 69.99])->count(),
            'F' => $attempts->where('percentage', '<', 60)->count(),
        ];

        // Available question modules (not attempted yet or can retry)
        $availableModules = QuestionModule::whereHas('courseModules.course.enrollments', function($query) use ($student) {
            $query->where('student_id', $student->id);
        })
        ->where('is_published', true)
        ->get()
        ->filter(function($module) use ($student) {
            return $module->canStudentAttempt($student->id);
        });

        return view('student.question-modules.stats', compact(
            'totalAttempts',
            'passedAttempts',
            'failedAttempts',
            'averageScore',
            'uniqueModules',
            'totalHours',
            'totalTimeSpent',
            'recentAttempts',
            'performanceData',
            'questionTypeStats',
            'bestAttempt',
            'worstAttempt',
            'gradeDistribution',
            'availableModules'
        ));
    }

    /**
     * Get statistics data for AJAX (for dashboard widget).
     */
    public function getDashboardStats()
    {
        $student = auth()->user();

        $attempts = QuestionModuleAttempt::where('student_id', $student->id)
            ->where('status', 'completed')
            ->get();

        $stats = [
            'total_attempts' => $attempts->count(),
            'passed_attempts' => $attempts->where('is_passed', true)->count(),
            'failed_attempts' => $attempts->where('is_passed', false)->count(),
            'average_score' => round($attempts->avg('percentage') ?? 0, 1),
            'total_modules' => $attempts->pluck('question_module_id')->unique()->count(),
            'last_attempt' => $attempts->sortByDesc('completed_at')->first(),
        ];

        return response()->json($stats);
    }

    /**
     * Show detailed stats for a specific question module.
     */
    public function showModuleStats($questionModuleId)
    {
        $student = auth()->user();
        $questionModule = QuestionModule::with(['questions', 'courseModules.course'])
            ->findOrFail($questionModuleId);

        // Get all attempts for this module
        $attempts = QuestionModuleAttempt::with(['responses.question.questionType'])
            ->where('question_module_id', $questionModuleId)
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderBy('attempt_number', 'asc')
            ->get();

        if ($attempts->isEmpty()) {
            return redirect()->back()->with('info', 'لم تقم بأي محاولات لهذا الاختبار بعد');
        }

        // Calculate statistics
        $averageScore = $attempts->avg('percentage');
        $bestScore = $attempts->max('percentage');
        $worstScore = $attempts->min('percentage');
        $totalTimeSpent = $attempts->sum('time_spent');

        // Progress chart data
        $progressData = $attempts->map(function($attempt) {
            return [
                'attempt_number' => $attempt->attempt_number,
                'percentage' => $attempt->percentage,
                'is_passed' => $attempt->is_passed,
            ];
        });

        // Per-question statistics
        $questionStats = [];
        foreach ($questionModule->questions as $question) {
            $responses = $attempts->flatMap->responses->where('question_id', $question->id);
            $correctCount = $responses->where('is_correct', true)->count();
            $totalResponses = $responses->whereNotNull('is_correct')->count();

            $questionStats[] = [
                'question' => $question,
                'total_attempts' => $totalResponses,
                'correct_count' => $correctCount,
                'accuracy' => $totalResponses > 0 ? round(($correctCount / $totalResponses) * 100, 1) : 0,
            ];
        }

        return view('student.question-modules.module-stats', compact(
            'questionModule',
            'attempts',
            'averageScore',
            'bestScore',
            'worstScore',
            'totalTimeSpent',
            'progressData',
            'questionStats'
        ));
    }
}
