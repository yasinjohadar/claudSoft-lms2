<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Gamification\Badge;
use App\Models\User;
use App\Services\Gamification\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BadgeController extends Controller
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * عرض قائمة الشارات
     */
    public function index(Request $request)
    {
        $query = Badge::query();

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلترة حسب الندرة
        if ($request->filled('rarity')) {
            $query->where('rarity', $request->rarity);
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

        $badges = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'total' => Badge::count(),
            'active' => Badge::where('is_active', true)->count(),
            'by_rarity' => Badge::selectRaw('rarity, COUNT(*) as count')
                ->groupBy('rarity')
                ->pluck('count', 'rarity')
                ->toArray(),
        ];

        return view('admin.pages.gamification.badges.index', compact('badges', 'stats'));
    }

    /**
     * عرض صفحة إنشاء شارة
     */
    public function create()
    {
        return view('admin.pages.gamification.badges.create');
    }

    /**
     * حفظ شارة جديدة
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:badges,slug',
            'description' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
            'type' => 'required|in:achievement,progress,performance,engagement,special,event,social',
            'category' => 'nullable|string|max:50',
            'rarity' => 'required|in:common,rare,epic,legendary,mythic',
            'criteria' => 'nullable|array',
            'points_value' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'is_hidden' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $slug = $request->slug ?? Str::slug($request->name);

            Badge::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'icon' => $request->icon ?? '🏆',
                'type' => $request->type,
                'category' => $request->category,
                'rarity' => $request->rarity,
                'criteria' => $request->criteria,
                'points_value' => $request->points_value ?? 0,
                'is_active' => $request->has('is_active'),
                'is_visible' => $request->has('is_visible'),
                'is_hidden' => $request->has('is_hidden'),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            return redirect()
                ->route('admin.gamification.badges.index')
                ->with('success', 'تم إنشاء الشارة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض تفاصيل شارة
     */
    public function show(Badge $badge)
    {
        $badge->load('userBadges.user');

        $stats = [
            'total_earned' => $badge->userBadges()->count(),
            'earned_today' => $badge->userBadges()->whereDate('awarded_at', today())->count(),
            'earned_this_week' => $badge->userBadges()->where('awarded_at', '>=', now()->startOfWeek())->count(),
            'earned_this_month' => $badge->userBadges()->where('awarded_at', '>=', now()->startOfMonth())->count(),
        ];

        $recentEarners = $badge->userBadges()
            ->with('user')
            ->latest('awarded_at')
            ->take(20)
            ->get();

        return view('admin.pages.gamification.badges.show', compact('badge', 'stats', 'recentEarners'));
    }

    /**
     * عرض صفحة تعديل شارة
     */
    public function edit(Badge $badge)
    {
        return view('admin.pages.gamification.badges.edit', compact('badge'));
    }

    /**
     * تحديث شارة
     */
    public function update(Request $request, Badge $badge)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:badges,slug,' . $badge->id,
            'description' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
            'type' => 'required|in:achievement,progress,performance,engagement,special,event,social',
            'category' => 'nullable|string|max:50',
            'rarity' => 'required|in:common,rare,epic,legendary,mythic',
            'criteria' => 'nullable|array',
            'points_value' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'is_hidden' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $badge->update([
                'name' => $request->name,
                'slug' => $request->slug ?? $badge->slug,
                'description' => $request->description,
                'icon' => $request->icon,
                'type' => $request->type,
                'category' => $request->category,
                'rarity' => $request->rarity,
                'criteria' => $request->criteria,
                'points_value' => $request->points_value ?? 0,
                'is_active' => $request->has('is_active'),
                'is_visible' => $request->has('is_visible'),
                'is_hidden' => $request->has('is_hidden'),
                'sort_order' => $request->sort_order ?? $badge->sort_order,
            ]);

            return redirect()
                ->route('admin.gamification.badges.index')
                ->with('success', 'تم تحديث الشارة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف شارة
     */
    public function destroy(Badge $badge)
    {
        try {
            $badge->delete();

            return redirect()
                ->route('admin.gamification.badges.index')
                ->with('success', 'تم حذف الشارة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * منح شارة يدوياً لمستخدم
     */
    public function awardToUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'badge_id' => 'required|exists:badges,id',
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator);
        }

        try {
            $badge = Badge::findOrFail($request->badge_id);
            $user = User::findOrFail($request->user_id);

            $userBadge = $this->badgeService->awardBadge(
                $user,
                $badge,
                null,
                null,
                ['reason' => $request->reason, 'manually_awarded' => true, 'awarded_by' => auth()->id()]
            );

            if ($userBadge) {
                return redirect()
                    ->back()
                    ->with('success', 'تم منح الشارة بنجاح');
            } else {
                return redirect()
                    ->back()
                    ->with('info', 'المستخدم يمتلك هذه الشارة بالفعل');
            }
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * تفعيل/تعطيل شارة
     */
    public function toggleActive(Badge $badge)
    {
        try {
            $badge->update(['is_active' => !$badge->is_active]);

            return redirect()
                ->back()
                ->with('success', 'تم تحديث حالة الشارة');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * إحصائيات الشارات
     */
    public function statistics()
    {
        $totalBadges = Badge::count();
        $activeBadges = Badge::where('is_active', true)->count();

        $byRarity = Badge::selectRaw('rarity, COUNT(*) as count')
            ->groupBy('rarity')
            ->orderByRaw("FIELD(rarity, 'mythic', 'legendary', 'epic', 'rare', 'common')")
            ->get();

        $byType = Badge::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        $mostEarned = Badge::withCount('userBadges')
            ->orderByDesc('user_badges_count')
            ->limit(10)
            ->get();

        $leastEarned = Badge::withCount('userBadges')
            ->having('user_badges_count', '>', 0)
            ->orderBy('user_badges_count')
            ->limit(10)
            ->get();

        $neverEarned = Badge::withCount('userBadges')
            ->having('user_badges_count', '=', 0)
            ->get();

        return view('admin.pages.gamification.badges.statistics', compact(
            'totalBadges',
            'activeBadges',
            'byRarity',
            'byType',
            'mostEarned',
            'leastEarned',
            'neverEarned'
        ));
    }
}
