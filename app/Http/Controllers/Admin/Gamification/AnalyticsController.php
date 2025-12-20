<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Gamification\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * لوحة التحكم الرئيسية
     */
    public function dashboard()
    {
        $stats = $this->analyticsService->getDashboardStats();

        return view('admin.pages.gamification.analytics.dashboard', compact('stats'));
    }

    /**
     * إحصائيات النقاط
     */
    public function points()
    {
        $stats = $this->analyticsService->getDashboardStats()['points'];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * إحصائيات المستويات
     */
    public function levels()
    {
        $stats = $this->analyticsService->getDashboardStats()['levels'];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * إحصائيات الشارات
     */
    public function badges()
    {
        $stats = $this->analyticsService->getDashboardStats()['badges'];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * إحصائيات التفاعل
     */
    public function engagement()
    {
        $stats = $this->analyticsService->getDashboardStats()['engagement'];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * تقرير طالب
     */
    public function studentReport(User $user)
    {
        $report = $this->analyticsService->getStudentReport($user);
        $comparison = $this->analyticsService->compareToAverage($user);

        return response()->json([
            'success' => true,
            'user' => $user->only(['id', 'name', 'email', 'avatar']),
            'report' => $report,
            'comparison' => $comparison,
        ]);
    }

    /**
     * مسح الكاش
     */
    public function clearCache()
    {
        $this->analyticsService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'تم مسح الكاش بنجاح!',
        ]);
    }
}
