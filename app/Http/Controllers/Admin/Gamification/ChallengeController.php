<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Gamification\Challenge;
use App\Models\UserChallenge;
use App\Models\User;
use App\Services\Gamification\ChallengeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChallengeController extends Controller
{
    protected ChallengeService $challengeService;

    public function __construct(ChallengeService $challengeService)
    {
        $this->challengeService = $challengeService;
    }

    /**
     * عرض قائمة التحديات
     */
    public function index(Request $request)
    {
        $query = Challenge::query();

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلترة حسب الصعوبة
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // فلترة حسب الحالة
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // البحث
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $challenges = $query->orderBy('type')
            ->orderBy('sort_order')
            ->paginate(20);

        // إحصائيات
        $stats = [
            'total' => Challenge::count(),
            'active' => Challenge::where('is_active', true)->count(),
            'by_type' => Challenge::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'by_difficulty' => Challenge::selectRaw('difficulty, COUNT(*) as count')
                ->groupBy('difficulty')
                ->pluck('count', 'difficulty'),
        ];

        return view('admin.pages.gamification.challenges.index', compact('challenges', 'stats'));
    }

    /**
     * عرض نموذج إنشاء تحدي جديد
     */
    public function create()
    {
        return view('admin.pages.gamification.challenges.create');
    }

    /**
     * حفظ تحدي جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:daily,weekly,monthly,special',
            'difficulty' => 'required|in:easy,medium,hard',
            'icon' => 'nullable|string|max:10',
            'target_type' => 'required|string|max:50',
            'target_value' => 'required|integer|min:1',
            'reward_points' => 'nullable|integer|min:0',
            'reward_xp' => 'nullable|integer|min:0',
            'reward_gems' => 'nullable|integer|min:0',
            'badge_id' => 'nullable|exists:gamification_badges,id',
            'is_active' => 'boolean',
            'auto_assign' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // توليد slug تلقائي
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);
        $validated['is_active'] = $request->has('is_active');
        $validated['auto_assign'] = $request->has('auto_assign');

        $challenge = Challenge::create($validated);

        return redirect()
            ->route('admin.gamification.challenges.index')
            ->with('success', 'تم إنشاء التحدي بنجاح');
    }

    /**
     * عرض تفاصيل تحدي
     */
    public function show(Challenge $challenge)
    {
        $challenge->load('badge');

        // إحصائيات التحدي
        $stats = [
            'total_assigned' => UserChallenge::where('challenge_id', $challenge->id)->count(),
            'active' => UserChallenge::where('challenge_id', $challenge->id)
                ->where('status', 'active')
                ->count(),
            'completed' => UserChallenge::where('challenge_id', $challenge->id)
                ->where('status', 'completed')
                ->count(),
            'expired' => UserChallenge::where('challenge_id', $challenge->id)
                ->where('status', 'expired')
                ->count(),
            'completion_rate' => 0,
            'average_progress' => UserChallenge::where('challenge_id', $challenge->id)
                ->avg('progress_percentage'),
        ];

        if ($stats['total_assigned'] > 0) {
            $stats['completion_rate'] = round(($stats['completed'] / $stats['total_assigned']) * 100, 2);
        }

        // آخر المستخدمين الذين أكملوا التحدي
        $recentCompletions = UserChallenge::where('challenge_id', $challenge->id)
            ->where('status', 'completed')
            ->with('user:id,name,email,avatar')
            ->latest('completed_at')
            ->limit(10)
            ->get();

        return view('admin.pages.gamification.challenges.show', compact('challenge', 'stats', 'recentCompletions'));
    }

    /**
     * عرض نموذج تعديل تحدي
     */
    public function edit(Challenge $challenge)
    {
        return view('admin.pages.gamification.challenges.edit', compact('challenge'));
    }

    /**
     * تحديث تحدي
     */
    public function update(Request $request, Challenge $challenge)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:daily,weekly,monthly,special',
            'difficulty' => 'required|in:easy,medium,hard',
            'icon' => 'nullable|string|max:10',
            'target_type' => 'required|string|max:50',
            'target_value' => 'required|integer|min:1',
            'reward_points' => 'nullable|integer|min:0',
            'reward_xp' => 'nullable|integer|min:0',
            'reward_gems' => 'nullable|integer|min:0',
            'badge_id' => 'nullable|exists:gamification_badges,id',
            'is_active' => 'boolean',
            'auto_assign' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['auto_assign'] = $request->has('auto_assign');

        $challenge->update($validated);

        return redirect()
            ->route('admin.gamification.challenges.index')
            ->with('success', 'تم تحديث التحدي بنجاح');
    }

    /**
     * حذف تحدي
     */
    public function destroy(Challenge $challenge)
    {
        // التحقق من عدم وجود مستخدمين نشطين في التحدي
        $activeCount = UserChallenge::where('challenge_id', $challenge->id)
            ->where('status', 'active')
            ->count();

        if ($activeCount > 0) {
            return redirect()
                ->back()
                ->with('error', "لا يمكن حذف التحدي. يوجد {$activeCount} مستخدم نشط في هذا التحدي.");
        }

        $challenge->delete();

        return redirect()
            ->route('admin.gamification.challenges.index')
            ->with('success', 'تم حذف التحدي بنجاح');
    }

    /**
     * تفعيل/تعطيل تحدي
     */
    public function toggleActive(Challenge $challenge)
    {
        $challenge->update([
            'is_active' => !$challenge->is_active,
        ]);

        $status = $challenge->is_active ? 'تفعيل' : 'تعطيل';

        return redirect()
            ->back()
            ->with('success', "تم {$status} التحدي بنجاح");
    }

    /**
     * تعيين تحدي لمستخدم يدوياً
     */
    public function assignToUser(Request $request)
    {
        $validated = $request->validate([
            'challenge_id' => 'required|exists:challenges,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $challenge = Challenge::findOrFail($validated['challenge_id']);
        $user = User::findOrFail($validated['user_id']);

        $userChallenge = $this->challengeService->assignChallenge($user, $challenge);

        if (!$userChallenge) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تعيين التحدي. قد يكون المستخدم قد وصل للحد الأقصى من التحديات النشطة.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تعيين التحدي للمستخدم بنجاح!',
            'user_challenge' => $userChallenge,
        ]);
    }

    /**
     * تعيين تحدي لمجموعة من المستخدمين
     */
    public function assignToMultipleUsers(Request $request)
    {
        $validated = $request->validate([
            'challenge_id' => 'required|exists:challenges,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $challenge = Challenge::findOrFail($validated['challenge_id']);
        $users = User::whereIn('id', $validated['user_ids'])->get();

        $assigned = 0;
        $failed = 0;

        foreach ($users as $user) {
            $userChallenge = $this->challengeService->assignChallenge($user, $challenge);
            if ($userChallenge) {
                $assigned++;
            } else {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "تم تعيين التحدي لـ {$assigned} مستخدم. فشل {$failed}.",
            'assigned' => $assigned,
            'failed' => $failed,
        ]);
    }

    /**
     * إحصائيات عامة للتحديات
     */
    public function statistics()
    {
        $totalChallenges = Challenge::count();
        $activeChallenges = Challenge::where('is_active', true)->count();

        $totalAssignments = UserChallenge::count();
        $activeAssignments = UserChallenge::where('status', 'active')->count();
        $completedAssignments = UserChallenge::where('status', 'completed')->count();
        $expiredAssignments = UserChallenge::where('status', 'expired')->count();

        $overallCompletionRate = $totalAssignments > 0
            ? round(($completedAssignments / $totalAssignments) * 100, 2)
            : 0;

        $byType = Challenge::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        $byDifficulty = Challenge::selectRaw('difficulty, COUNT(*) as count')
            ->groupBy('difficulty')
            ->pluck('count', 'difficulty');

        $completionsByType = UserChallenge::join('challenges', 'user_challenges.challenge_id', '=', 'challenges.id')
            ->where('user_challenges.status', 'completed')
            ->selectRaw('challenges.type, COUNT(*) as count')
            ->groupBy('challenges.type')
            ->pluck('count', 'type');

        // أكثر التحديات اكتمالاً
        $topCompleted = Challenge::withCount(['userChallenges as completions' => function($q) {
                $q->where('status', 'completed');
            }])
            ->orderByDesc('completions')
            ->limit(10)
            ->get();

        // أكثر التحديات صعوبة (أقل نسبة إكمال)
        $hardestChallenges = Challenge::withCount(['userChallenges as total', 'userChallenges as completed' => function($q) {
                $q->where('status', 'completed');
            }])
            ->having('total', '>', 10)
            ->get()
            ->map(function($challenge) {
                $challenge->completion_rate = $challenge->total > 0
                    ? round(($challenge->completed / $challenge->total) * 100, 2)
                    : 0;
                return $challenge;
            })
            ->sortBy('completion_rate')
            ->take(10)
            ->values();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_challenges' => $totalChallenges,
                'active_challenges' => $activeChallenges,
                'total_assignments' => $totalAssignments,
                'active_assignments' => $activeAssignments,
                'completed_assignments' => $completedAssignments,
                'expired_assignments' => $expiredAssignments,
                'overall_completion_rate' => $overallCompletionRate,
                'by_type' => $byType,
                'by_difficulty' => $byDifficulty,
                'completions_by_type' => $completionsByType,
                'top_completed' => $topCompleted,
                'hardest_challenges' => $hardestChallenges,
            ],
        ]);
    }

    /**
     * المستخدمون المشاركون في تحدي
     */
    public function participants(Challenge $challenge, Request $request)
    {
        $query = UserChallenge::where('challenge_id', $challenge->id)
            ->with('user:id,name,email,avatar');

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $participants = $query->orderByDesc('progress_percentage')
            ->orderByDesc('current_progress')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'participants' => $participants,
        ]);
    }

    /**
     * تحديث تقدم مستخدم يدوياً
     */
    public function updateUserProgress(Request $request, UserChallenge $userChallenge)
    {
        $validated = $request->validate([
            'increment' => 'required|integer|min:1',
        ]);

        $updated = $this->challengeService->updateProgress(
            $userChallenge->user,
            $userChallenge->challenge,
            $validated['increment']
        );

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحديث التقدم.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التقدم بنجاح!',
            'user_challenge' => $updated,
        ]);
    }

    /**
     * إلغاء تحدي لمستخدم
     */
    public function cancelUserChallenge(UserChallenge $userChallenge)
    {
        $cancelled = $this->challengeService->cancelChallenge($userChallenge);

        if (!$cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إلغاء التحدي. التحدي غير نشط.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء التحدي بنجاح!',
        ]);
    }
}
