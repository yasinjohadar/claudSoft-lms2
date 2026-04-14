<?php

namespace App\Http\Controllers\Api\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Services\Gamification\StreakService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StreakApiController extends Controller
{
    public function __construct(private readonly StreakService $streakService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'streak_info' => $this->streakService->getStreakInfo($user),
                'monthly_stats' => $this->streakService->getMonthlyStreakStats($user),
                'streak_rewards' => config('gamification.points.streak_milestones', []),
            ],
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $user = $request->user();
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $activityDays = DB::table('daily_streaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');

        $calendar = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $key = $currentDate->format('Y-m-d');
            $row = $activityDays[$key] ?? null;
            $calendar[] = [
                'date' => $key,
                'is_active' => $row !== null,
                'activities_count' => (int) ($row->activities_count ?? 0),
                'points_earned' => (int) ($row->points_earned ?? 0),
                'xp_earned' => (int) ($row->xp_earned ?? 0),
            ];
            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'month' => $month,
                'calendar' => $calendar,
            ],
        ]);
    }
}
