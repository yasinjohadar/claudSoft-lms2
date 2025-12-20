<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Services\Gamification\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LeaderboardController extends Controller
{
    protected LeaderboardService $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * عرض قائمة اللوحات
     */
    public function index()
    {
        $leaderboards = Leaderboard::orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->withCount('entries')
            ->get();

        $stats = [
            'total' => Leaderboard::count(),
            'active' => Leaderboard::where('is_active', true)->count(),
            'total_entries' => \DB::table('leaderboard_entries')->count(),
        ];

        return view('admin.pages.gamification.leaderboards.index', compact('leaderboards', 'stats'));
    }

    /**
     * عرض صفحة إنشاء لوحة
     */
    public function create()
    {
        return view('admin.pages.gamification.leaderboards.create');
    }

    /**
     * حفظ لوحة جديدة
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:leaderboards,slug',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:global,course,weekly,monthly,speed,accuracy,streak,social',
            'icon' => 'nullable|string|max:20',
            'period' => 'required|in:all_time,daily,weekly,monthly,yearly,season',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_entries' => 'nullable|integer|min:10|max:500',
            'is_active' => 'sometimes|boolean',
            'is_visible' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'name.required' => 'اسم اللوحة مطلوب',
            'type.required' => 'نوع اللوحة مطلوب',
            'type.in' => 'نوع اللوحة المحدد غير صحيح',
            'period.required' => 'الفترة مطلوبة',
            'period.in' => 'الفترة المحددة غير صحيحة',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $slug = $request->slug ?? Str::slug($request->name);

            Leaderboard::create([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'type' => $request->type,
                'icon' => $request->icon ?? '🏆',
                'period' => $request->period,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'max_entries' => $request->max_entries ?? 100,
                'is_active' => $request->has('is_active'),
                'is_visible' => $request->has('is_visible') ?? true,
                'sort_order' => $request->sort_order ?? 0,
            ]);

            return redirect()
                ->route('admin.gamification.leaderboards.index')
                ->with('success', 'تم إنشاء لوحة المتصدرين بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض تفاصيل لوحة
     */
    public function show(Leaderboard $leaderboard)
    {
        $entries = $this->leaderboardService->getLeaderboard($leaderboard, 100);
        $stats = $this->leaderboardService->getLeaderboardStats($leaderboard);

        return view('admin.pages.gamification.leaderboards.show', compact('leaderboard', 'entries', 'stats'));
    }

    /**
     * عرض صفحة تعديل لوحة
     */
    public function edit(Leaderboard $leaderboard)
    {
        return view('admin.pages.gamification.leaderboards.edit', compact('leaderboard'));
    }

    /**
     * تحديث لوحة
     */
    public function update(Request $request, Leaderboard $leaderboard)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:leaderboards,slug,' . $leaderboard->id,
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:global,course,weekly,monthly,speed,accuracy,streak,social',
            'icon' => 'nullable|string|max:20',
            'period' => 'required|in:all_time,daily,weekly,monthly,yearly,season',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_entries' => 'nullable|integer|min:10|max:500',
            'is_active' => 'sometimes|boolean',
            'is_visible' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'name.required' => 'اسم اللوحة مطلوب',
            'type.required' => 'نوع اللوحة مطلوب',
            'type.in' => 'نوع اللوحة المحدد غير صحيح',
            'period.required' => 'الفترة مطلوبة',
            'period.in' => 'الفترة المحددة غير صحيحة',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $leaderboard->update([
                'name' => $request->name,
                'slug' => $request->slug ?? $leaderboard->slug,
                'description' => $request->description,
                'type' => $request->type,
                'icon' => $request->icon,
                'period' => $request->period,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'max_entries' => $request->max_entries ?? 100,
                'is_active' => $request->has('is_active'),
                'is_visible' => $request->has('is_visible') ?? $leaderboard->is_visible,
                'sort_order' => $request->sort_order ?? $leaderboard->sort_order,
            ]);

            return redirect()
                ->route('admin.gamification.leaderboards.index')
                ->with('success', 'تم تحديث لوحة المتصدرين بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف لوحة
     */
    public function destroy(Leaderboard $leaderboard)
    {
        try {
            $leaderboard->delete();

            return redirect()
                ->route('admin.gamification.leaderboards.index')
                ->with('success', 'تم حذف لوحة المتصدرين بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * تحديث لوحة يدوياً
     */
    public function updateLeaderboard(Leaderboard $leaderboard)
    {
        try {
            $success = $this->leaderboardService->updateLeaderboard($leaderboard);

            if ($success) {
                return redirect()
                    ->back()
                    ->with('success', 'تم تحديث اللوحة بنجاح');
            } else {
                return redirect()
                    ->back()
                    ->with('error', 'فشل في تحديث اللوحة');
            }
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * تحديث جميع اللوحات
     */
    public function updateAll()
    {
        try {
            $updated = $this->leaderboardService->updateAllLeaderboards();

            return redirect()
                ->back()
                ->with('success', 'تم تحديث ' . count($updated) . ' لوحة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * منح مكافآت اللوحة
     */
    public function awardRewards(Leaderboard $leaderboard)
    {
        try {
            $awarded = $this->leaderboardService->awardLeaderboardRewards($leaderboard);

            return redirect()
                ->back()
                ->with('success', "تم منح المكافآت لـ {$awarded} مستخدم");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * تفعيل/تعطيل لوحة
     */
    public function toggleActive(Leaderboard $leaderboard)
    {
        try {
            $leaderboard->update(['is_active' => !$leaderboard->is_active]);

            return redirect()
                ->back()
                ->with('success', 'تم تحديث حالة اللوحة');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }
}
