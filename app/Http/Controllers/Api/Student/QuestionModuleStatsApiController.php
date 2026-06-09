<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\QuestionModule;
use App\Models\QuestionModuleAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionModuleStatsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();

        $attempts = QuestionModuleAttempt::with(['questionModule'])
            ->where('student_id', $student->id)
            ->orderByDesc('completed_at')
            ->get();

        $completed = $attempts->where('status', 'completed');
        $passed = $completed->where('is_passed', true);
        $failed = $completed->where('is_passed', false);

        $performanceData = $completed
            ->filter(fn ($a) => $a->completed_at && $a->completed_at >= now()->subDays(30))
            ->groupBy(fn ($a) => $a->completed_at->format('Y-m-d'))
            ->map(fn ($group) => [
                'date' => $group->first()->completed_at->format('Y-m-d'),
                'average' => round($group->avg('percentage'), 2),
                'count' => $group->count(),
            ])
            ->values();

        $gradeDistribution = [
            'A' => $completed->where('percentage', '>=', 90)->count(),
            'B' => $completed->whereBetween('percentage', [80, 89.99])->count(),
            'C' => $completed->whereBetween('percentage', [70, 79.99])->count(),
            'D' => $completed->whereBetween('percentage', [60, 69.99])->count(),
            'F' => $completed->where('percentage', '<', 60)->count(),
        ];

        $recentAttempts = $completed->take(10)->map(fn ($a) => [
            'id' => $a->id,
            'question_module_id' => $a->question_module_id,
            'title' => $a->questionModule?->title,
            'percentage' => round((float) $a->percentage, 1),
            'is_passed' => (bool) $a->is_passed,
            'completed_at' => optional($a->completed_at)->toIso8601String(),
        ])->values();

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

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_attempts' => $completed->count(),
                    'passed_attempts' => $passed->count(),
                    'failed_attempts' => $failed->count(),
                    'average_score' => round((float) ($completed->avg('percentage') ?? 0), 1),
                    'unique_modules' => $completed->pluck('question_module_id')->unique()->count(),
                    'total_hours' => round($completed->sum('time_spent') / 3600, 1),
                ],
                'grade_distribution' => $gradeDistribution,
                'performance_data' => $performanceData,
                'question_type_stats' => $questionTypeStats,
                'recent_attempts' => $recentAttempts,
            ],
        ]);
    }

    public function moduleStats(Request $request, int $questionModuleId): JsonResponse
    {
        $student = $request->user();
        $questionModule = QuestionModule::with(['questions'])->findOrFail($questionModuleId);

        $attempts = QuestionModuleAttempt::with(['responses.question.questionType'])
            ->where('question_module_id', $questionModuleId)
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderBy('attempt_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'module' => [
                    'id' => $questionModule->id,
                    'title' => $questionModule->title,
                ],
                'attempts' => $attempts->map(fn ($a) => [
                    'id' => $a->id,
                    'attempt_number' => $a->attempt_number,
                    'percentage' => round((float) $a->percentage, 1),
                    'is_passed' => (bool) $a->is_passed,
                    'completed_at' => optional($a->completed_at)->toIso8601String(),
                ])->values(),
                'average_score' => round((float) ($attempts->avg('percentage') ?? 0), 1),
                'best_score' => round((float) ($attempts->max('percentage') ?? 0), 1),
                'worst_score' => round((float) ($attempts->min('percentage') ?? 0), 1),
            ],
        ]);
    }
}
