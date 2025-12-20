<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\SocialActivity;
use App\Services\Gamification\SocialActivityService;
use Illuminate\Http\Request;

class SocialActivityController extends Controller
{
    protected SocialActivityService $socialActivityService;

    public function __construct(SocialActivityService $socialActivityService)
    {
        $this->socialActivityService = $socialActivityService;
    }

    /**
     * عرض قائمة الأنشطة الاجتماعية
     */
    public function index(Request $request)
    {
        $query = SocialActivity::with('user:id,name,email,avatar');

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلترة حسب المستخدم
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // فلترة حسب الفترة
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
            }
        }

        $activities = $query->latest('created_at')
            ->paginate(50);

        // إحصائيات
        $stats = [
            'total_activities' => SocialActivity::count(),
            'activities_today' => SocialActivity::whereDate('created_at', today())->count(),
            'total_likes' => SocialActivity::sum('likes_count'),
            'total_comments' => SocialActivity::sum('comments_count'),
            'by_type' => SocialActivity::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
        ];

        return response()->json([
            'success' => true,
            'activities' => $activities,
            'stats' => $stats,
        ]);
    }

    /**
     * عرض تفاصيل نشاط
     */
    public function show(SocialActivity $socialActivity)
    {
        $socialActivity->load([
            'user:id,name,email,avatar',
            'likes.user:id,name,avatar',
            'comments.user:id,name,avatar'
        ]);

        return response()->json([
            'success' => true,
            'activity' => $socialActivity,
        ]);
    }

    /**
     * حذف نشاط (مراقبة محتوى)
     */
    public function destroy(SocialActivity $socialActivity)
    {
        $socialActivity->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف النشاط بنجاح!',
        ]);
    }

    /**
     * إحصائيات الأنشطة الاجتماعية
     */
    public function statistics()
    {
        $totalActivities = SocialActivity::count();
        $totalLikes = SocialActivity::sum('likes_count');
        $totalComments = SocialActivity::sum('comments_count');

        $activitiesByType = SocialActivity::selectRaw('type, COUNT(*) as count, SUM(likes_count) as likes, SUM(comments_count) as comments')
            ->groupBy('type')
            ->get();

        $mostActiveUsers = SocialActivity::selectRaw('user_id, COUNT(*) as activity_count')
            ->groupBy('user_id')
            ->with('user:id,name,email')
            ->orderByDesc('activity_count')
            ->limit(10)
            ->get();

        $mostLikedActivities = SocialActivity::orderByDesc('likes_count')
            ->with('user:id,name,email')
            ->limit(10)
            ->get();

        $dailyActivities = SocialActivity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_activities' => $totalActivities,
                'total_likes' => $totalLikes,
                'total_comments' => $totalComments,
                'activities_by_type' => $activitiesByType,
                'most_active_users' => $mostActiveUsers,
                'most_liked_activities' => $mostLikedActivities,
                'daily_activities' => $dailyActivities,
            ],
        ]);
    }
}
