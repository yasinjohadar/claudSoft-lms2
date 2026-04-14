<?php

namespace App\Http\Controllers\Api\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Services\Gamification\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointsApiController extends Controller
{
    public function __construct(private readonly PointsService $pointsService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $totalPoints = $this->pointsService->getTotalPoints($user);
        $availablePoints = $this->pointsService->getAvailablePoints($user);

        return response()->json([
            'success' => true,
            'data' => [
                'total_points' => $totalPoints,
                'available_points' => $availablePoints,
                'spent_points' => max(0, $totalPoints - $availablePoints),
                'streak_multiplier' => $this->pointsService->getStreakMultiplier($user),
            ],
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min(max((int) $request->query('limit', 30), 1), 200);

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $this->pointsService->getPointsHistory($user, $limit)->values(),
            ],
        ]);
    }
}
