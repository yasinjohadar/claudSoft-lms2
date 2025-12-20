<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\UserChallenge;
use App\Services\Gamification\ChallengeService;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    protected ChallengeService $challengeService;

    public function __construct(ChallengeService $challengeService)
    {
        $this->challengeService = $challengeService;
    }

    /**
     * عرض جميع التحديات المتاحة
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->input('type');

        $challenges = $this->challengeService->getAvailableChallenges($user, $type);
        $activeChallenges = $this->challengeService->getActiveChallenges($user, $type);

        // تجميع حسب النوع
        $groupedChallenges = $challenges->groupBy('type');

        // إحصائيات
        $stats = $this->challengeService->getUserChallengeStats($user);

        return view('student.pages.gamification.challenges', compact(
            'challenges',
            'activeChallenges',
            'groupedChallenges',
            'stats',
            'type'
        ));
    }

    /**
     * عرض التحديات النشطة للطالب
     */
    public function active(Request $request)
    {
        $user = $request->user();
        $type = $request->input('type');

        $activeChallenges = $this->challengeService->getActiveChallenges($user, $type);

        // حساب الوقت المتبقي لكل تحدي
        $activeChallenges->each(function($userChallenge) {
            if ($userChallenge->expires_at) {
                $userChallenge->time_remaining = now()->diffInSeconds($userChallenge->expires_at, false);
                $userChallenge->time_remaining_human = now()->diffForHumans($userChallenge->expires_at);
            }
        });

        return response()->json([
            'success' => true,
            'active_challenges' => $activeChallenges,
            'count' => $activeChallenges->count(),
        ]);
    }

    /**
     * عرض تفاصيل تحدي معين
     */
    public function show(Request $request, Challenge $challenge)
    {
        $user = $request->user();

        $challenge->load('badge');

        // الحصول على تقدم المستخدم في هذا التحدي
        $userChallenge = UserChallenge::where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->latest()
            ->first();

        $timeRemaining = null;
        if ($userChallenge && $userChallenge->expires_at) {
            $timeRemaining = [
                'seconds' => now()->diffInSeconds($userChallenge->expires_at, false),
                'human' => now()->diffForHumans($userChallenge->expires_at),
                'expires_at' => $userChallenge->expires_at,
            ];
        }

        // إحصائيات التحدي
        $stats = [
            'total_completions' => UserChallenge::where('challenge_id', $challenge->id)
                ->where('status', 'completed')
                ->count(),
            'current_active' => UserChallenge::where('challenge_id', $challenge->id)
                ->where('status', 'active')
                ->count(),
            'average_completion_time' => null, // يمكن حسابه لاحقاً
        ];

        return response()->json([
            'success' => true,
            'challenge' => $challenge,
            'user_challenge' => $userChallenge,
            'time_remaining' => $timeRemaining,
            'stats' => $stats,
        ]);
    }

    /**
     * قبول تحدي (للتحديات غير التلقائية)
     */
    public function accept(Request $request, Challenge $challenge)
    {
        $user = $request->user();

        if ($challenge->auto_assign) {
            return response()->json([
                'success' => false,
                'message' => 'هذا التحدي يتم تعيينه تلقائياً.',
            ], 400);
        }

        $userChallenge = $this->challengeService->acceptChallenge($user, $challenge);

        if (!$userChallenge) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن قبول التحدي. قد تكون قد وصلت للحد الأقصى من التحديات النشطة.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم قبول التحدي بنجاح! حظاً موفقاً!',
            'user_challenge' => $userChallenge,
        ]);
    }

    /**
     * إلغاء تحدي نشط
     */
    public function cancel(Request $request, UserChallenge $userChallenge)
    {
        $user = $request->user();

        // التحقق من ملكية التحدي
        if ($userChallenge->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإلغاء هذا التحدي.',
            ], 403);
        }

        $cancelled = $this->challengeService->cancelChallenge($userChallenge);

        if (!$cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إلغاء التحدي. التحدي غير نشط.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء التحدي بنجاح.',
        ]);
    }

    /**
     * عرض إحصائيات التحديات للطالب
     */
    public function myStats(Request $request)
    {
        $user = $request->user();

        $stats = $this->challengeService->getUserChallengeStats($user);

        // التحديات المكتملة مؤخراً
        $recentCompletions = UserChallenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('challenge:id,name,icon,type,difficulty,reward_points,reward_xp,reward_gems')
            ->latest('completed_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'recent_completions' => $recentCompletions,
        ]);
    }

    /**
     * عرض التحديات اليومية
     */
    public function daily(Request $request)
    {
        $user = $request->user();

        // التأكد من تعيين التحديات اليومية
        $this->challengeService->assignDailyChallenges($user);

        $dailyChallenges = $this->challengeService->getActiveChallenges($user, 'daily');

        // حساب الوقت المتبقي
        $dailyChallenges->each(function($userChallenge) {
            if ($userChallenge->expires_at) {
                $userChallenge->time_remaining_seconds = now()->diffInSeconds($userChallenge->expires_at, false);
                $userChallenge->time_remaining_human = now()->diffForHumans($userChallenge->expires_at);
            }
        });

        // إحصائيات يومية
        $dailyStats = [
            'completed_today' => UserChallenge::where('user_id', $user->id)
                ->whereHas('challenge', function($q) {
                    $q->where('type', 'daily');
                })
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'active_today' => $dailyChallenges->count(),
        ];

        return response()->json([
            'success' => true,
            'daily_challenges' => $dailyChallenges,
            'stats' => $dailyStats,
        ]);
    }

    /**
     * عرض التحديات الأسبوعية
     */
    public function weekly(Request $request)
    {
        $user = $request->user();

        $weeklyChallenges = $this->challengeService->getAvailableChallenges($user, 'weekly');

        // التحديات النشطة
        $activeWeekly = $this->challengeService->getActiveChallenges($user, 'weekly');

        $weeklyStats = [
            'completed_this_week' => UserChallenge::where('user_id', $user->id)
                ->whereHas('challenge', function($q) {
                    $q->where('type', 'weekly');
                })
                ->where('status', 'completed')
                ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'active' => $activeWeekly->count(),
        ];

        return response()->json([
            'success' => true,
            'weekly_challenges' => $weeklyChallenges,
            'active_challenges' => $activeWeekly,
            'stats' => $weeklyStats,
        ]);
    }

    /**
     * عرض التحديات الشهرية
     */
    public function monthly(Request $request)
    {
        $user = $request->user();

        $monthlyChallenges = $this->challengeService->getAvailableChallenges($user, 'monthly');

        // التحديات النشطة
        $activeMonthly = $this->challengeService->getActiveChallenges($user, 'monthly');

        $monthlyStats = [
            'completed_this_month' => UserChallenge::where('user_id', $user->id)
                ->whereHas('challenge', function($q) {
                    $q->where('type', 'monthly');
                })
                ->where('status', 'completed')
                ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'active' => $activeMonthly->count(),
        ];

        return response()->json([
            'success' => true,
            'monthly_challenges' => $monthlyChallenges,
            'active_challenges' => $activeMonthly,
            'stats' => $monthlyStats,
        ]);
    }

    /**
     * عرض التحديات الخاصة
     */
    public function special(Request $request)
    {
        $user = $request->user();

        $specialChallenges = $this->challengeService->getAvailableChallenges($user, 'special');

        // التحديات النشطة
        $activeSpecial = $this->challengeService->getActiveChallenges($user, 'special');

        $specialStats = [
            'completed' => UserChallenge::where('user_id', $user->id)
                ->whereHas('challenge', function($q) {
                    $q->where('type', 'special');
                })
                ->where('status', 'completed')
                ->count(),
            'active' => $activeSpecial->count(),
        ];

        return response()->json([
            'success' => true,
            'special_challenges' => $specialChallenges,
            'active_challenges' => $activeSpecial,
            'stats' => $specialStats,
        ]);
    }

    /**
     * عرض التحديات الموصى بها
     */
    public function recommended(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 3);

        $recommended = $this->challengeService->getRecommendedChallenges($user, $limit);

        return response()->json([
            'success' => true,
            'recommended_challenges' => $recommended,
        ]);
    }

    /**
     * عرض تاريخ التحديات المكتملة
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $query = UserChallenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('challenge:id,name,icon,type,difficulty,reward_points,reward_xp,reward_gems');

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->whereHas('challenge', function($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        // فلترة حسب الفترة الزمنية
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('completed_at', today());
                    break;
                case 'week':
                    $query->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
            }
        }

        $history = $query->orderByDesc('completed_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * عرض تقدم التحدي
     */
    public function progress(Request $request, UserChallenge $userChallenge)
    {
        $user = $request->user();

        // التحقق من ملكية التحدي
        if ($userChallenge->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بعرض هذا التحدي.',
            ], 403);
        }

        $userChallenge->load('challenge');

        $timeRemaining = null;
        if ($userChallenge->expires_at && $userChallenge->status === 'active') {
            $timeRemaining = [
                'seconds' => now()->diffInSeconds($userChallenge->expires_at, false),
                'human' => now()->diffForHumans($userChallenge->expires_at),
                'percentage' => $userChallenge->expires_at > now()
                    ? round((now()->diffInSeconds($userChallenge->started_at) / $userChallenge->started_at->diffInSeconds($userChallenge->expires_at)) * 100, 2)
                    : 100,
            ];
        }

        return response()->json([
            'success' => true,
            'user_challenge' => $userChallenge,
            'time_remaining' => $timeRemaining,
        ]);
    }
}
