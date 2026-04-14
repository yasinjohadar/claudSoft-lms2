<?php

namespace App\Http\Controllers\Api\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\UserAchievement;
use App\Services\Gamification\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementApiController extends Controller
{
    public function __construct(private readonly AchievementService $achievementService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tier = $request->query('tier');
        $status = $request->query('status');

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $this->achievementService->getUserAchievementStats($user),
                'achievements' => $this->achievementService->getUserAchievements($user, $status, $tier)->values(),
                'recommended' => $this->achievementService->getRecommendedAchievements($user, 10)->values(),
            ],
        ]);
    }

    public function claim(Request $request, UserAchievement $userAchievement): JsonResponse
    {
        $user = $request->user();
        if ((int) $userAchievement->user_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإنجاز لا يخصك.',
            ], 403);
        }

        $success = $this->achievementService->claimReward($user, $userAchievement);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'تم المطالبة بالمكافأة بنجاح.' : 'لا يمكن المطالبة بالمكافأة حالياً.',
            'data' => [
                'achievement' => $userAchievement->fresh(),
                'stats' => $this->achievementService->getUserAchievementStats($user),
            ],
        ], $success ? 200 : 422);
    }
}
