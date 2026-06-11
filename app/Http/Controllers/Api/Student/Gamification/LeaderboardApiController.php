<?php

namespace App\Http\Controllers\Api\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Services\Gamification\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardApiController extends Controller
{
    public function __construct(private readonly LeaderboardService $leaderboardService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $leaderboards = Leaderboard::query()
            ->where('is_active', true)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Leaderboard $leaderboard) use ($user) {
                return [
                    'id' => (int) $leaderboard->id,
                    'name' => (string) $leaderboard->name,
                    'description' => $leaderboard->description,
                    'type' => $leaderboard->type,
                    'scope' => $leaderboard->period,
                    'user_rank' => $this->leaderboardService->getUserRank($user, $leaderboard),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => ['leaderboards' => $leaderboards],
        ]);
    }

    public function show(Request $request, Leaderboard $leaderboard): JsonResponse
    {
        if (! $leaderboard->is_active || ! $leaderboard->is_visible) {
            return response()->json([
                'success' => false,
                'message' => 'لوحة الترتيب غير متاحة.',
            ], 404);
        }

        $user = $request->user();
        $limit = min(max((int) $request->query('limit', 50), 1), 100);

        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboard,
                'entries' => $this->leaderboardService->getLeaderboard($leaderboard, $limit),
                'user_rank' => $this->leaderboardService->getUserRank($user, $leaderboard),
                'surrounding_users' => $this->leaderboardService->getSurroundingUsers($user, $leaderboard, 3),
                'stats' => $this->leaderboardService->getLeaderboardStats($leaderboard),
            ],
        ]);
    }
}
