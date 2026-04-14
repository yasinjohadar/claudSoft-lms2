<?php

namespace App\Http\Controllers\Api\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Services\Gamification\ChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeApiController extends Controller
{
    public function __construct(private readonly ChallengeService $challengeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->query('type');

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $this->challengeService->getAvailableChallenges($user, $type)->values(),
                'active' => $this->challengeService->getActiveChallenges($user, $type)->values(),
                'stats' => $this->challengeService->getUserChallengeStats($user),
                'recommended' => $this->challengeService->getRecommendedChallenges($user, 6)->values(),
            ],
        ]);
    }

    public function accept(Request $request, Challenge $challenge): JsonResponse
    {
        $user = $request->user();

        if ($challenge->auto_assign) {
            return response()->json([
                'success' => false,
                'message' => 'هذا التحدي يتم تعيينه تلقائياً.',
            ], 422);
        }

        $userChallenge = $this->challengeService->acceptChallenge($user, $challenge);

        if (! $userChallenge) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن قبول التحدي حالياً.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم قبول التحدي بنجاح.',
            'data' => ['user_challenge' => $userChallenge],
        ]);
    }
}
