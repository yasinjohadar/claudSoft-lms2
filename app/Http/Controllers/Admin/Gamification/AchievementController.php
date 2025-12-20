<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Gamification\Achievement;
use App\Models\Gamification\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AchievementController extends Controller
{
    /**
     * عرض قائمة الإنجازات
     */
    public function index(Request $request)
    {
        $query = Achievement::with('badge');

        // فلترة حسب المرتبة
        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        $achievements = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'total' => Achievement::count(),
            'active' => Achievement::where('is_active', true)->count(),
            'by_tier' => Achievement::selectRaw('tier, COUNT(*) as count')
                ->groupBy('tier')
                ->orderByRaw("FIELD(tier, 'diamond', 'platinum', 'gold', 'silver', 'bronze')")
                ->pluck('count', 'tier')
                ->toArray(),
        ];

        return view('admin.pages.gamification.achievements.index', compact('achievements', 'stats'));
    }

    /**
     * عرض صفحة إنشاء إنجاز
     */
    public function create()
    {
        $badges = Badge::where('is_active', true)->orderBy('name')->get();
        return view('admin.pages.gamification.achievements.create', compact('badges'));
    }

    /**
     * حفظ إنجاز جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:achievements,slug',
            'description' => 'required|string|max:500',
            'tier' => 'required|in:bronze,silver,gold,platinum,diamond',
            'badge_id' => 'nullable|exists:badges,id',
            'type' => 'required|string|max:50',
            'target_value' => 'required|integer|min:1',
            'criteria' => 'nullable|array',
            'reward_points' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
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

            Achievement::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'tier' => $request->tier,
                'badge_id' => $request->badge_id,
                'type' => $request->type,
                'target_value' => $request->target_value,
                'criteria' => $request->criteria,
                'reward_points' => $request->reward_points ?? 0,
                'is_active' => $request->has('is_active'),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            return redirect()
                ->route('admin.pages.gamification.achievements.index')
                ->with('success', 'تم إنشاء الإنجاز بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض تفاصيل إنجاز
     */
    public function show(Achievement $achievement)
    {
        $achievement->load('badge', 'userAchievements.user');

        $stats = [
            'total_started' => $achievement->userAchievements()->count(),
            'in_progress' => $achievement->userAchievements()->where('status', 'in_progress')->count(),
            'completed' => $achievement->userAchievements()->where('status', 'completed')->count(),
            'completion_rate' => 0,
        ];

        if ($stats['total_started'] > 0) {
            $stats['completion_rate'] = round(($stats['completed'] / $stats['total_started']) * 100, 2);
        }

        $recentCompletions = $achievement->userAchievements()
            ->where('status', 'completed')
            ->with('user')
            ->latest('completed_at')
            ->take(20)
            ->get();

        return view('admin.pages.gamification.achievements.show', compact('achievement', 'stats', 'recentCompletions'));
    }

    /**
     * عرض صفحة تعديل إنجاز
     */
    public function edit(Achievement $achievement)
    {
        $badges = Badge::where('is_active', true)->orderBy('name')->get();
        return view('admin.pages.gamification.achievements.edit', compact('achievement', 'badges'));
    }

    /**
     * تحديث إنجاز
     */
    public function update(Request $request, Achievement $achievement)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:achievements,slug,' . $achievement->id,
            'description' => 'required|string|max:500',
            'tier' => 'required|in:bronze,silver,gold,platinum,diamond',
            'badge_id' => 'nullable|exists:badges,id',
            'type' => 'required|string|max:50',
            'target_value' => 'required|integer|min:1',
            'criteria' => 'nullable|array',
            'reward_points' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $achievement->update([
                'name' => $request->name,
                'slug' => $request->slug ?? $achievement->slug,
                'description' => $request->description,
                'tier' => $request->tier,
                'badge_id' => $request->badge_id,
                'type' => $request->type,
                'target_value' => $request->target_value,
                'criteria' => $request->criteria,
                'reward_points' => $request->reward_points ?? 0,
                'is_active' => $request->has('is_active'),
                'sort_order' => $request->sort_order ?? $achievement->sort_order,
            ]);

            return redirect()
                ->route('admin.pages.gamification.achievements.index')
                ->with('success', 'تم تحديث الإنجاز بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف إنجاز
     */
    public function destroy(Achievement $achievement)
    {
        try {
            $achievement->delete();

            return redirect()
                ->route('admin.pages.gamification.achievements.index')
                ->with('success', 'تم حذف الإنجاز بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * تفعيل/تعطيل إنجاز
     */
    public function toggleActive(Achievement $achievement)
    {
        try {
            $achievement->update(['is_active' => !$achievement->is_active]);

            return redirect()
                ->back()
                ->with('success', 'تم تحديث حالة الإنجاز');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * إحصائيات الإنجازات
     */
    public function statistics()
    {
        $totalAchievements = Achievement::count();
        $activeAchievements = Achievement::where('is_active', true)->count();

        $byTier = Achievement::selectRaw('tier, COUNT(*) as count')
            ->groupBy('tier')
            ->orderByRaw("FIELD(tier, 'diamond', 'platinum', 'gold', 'silver', 'bronze')")
            ->get();

        $mostCompleted = Achievement::withCount(['userAchievements' => function($q) {
                $q->where('status', 'completed');
            }])
            ->orderByDesc('user_achievements_count')
            ->limit(10)
            ->get();

        $leastCompleted = Achievement::withCount(['userAchievements' => function($q) {
                $q->where('status', 'completed');
            }])
            ->having('user_achievements_count', '>', 0)
            ->orderBy('user_achievements_count')
            ->limit(10)
            ->get();

        return view('admin.pages.gamification.achievements.statistics', compact(
            'totalAchievements',
            'activeAchievements',
            'byTier',
            'mostCompleted',
            'leastCompleted'
        ));
    }
}
