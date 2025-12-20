<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\GamificationNotification;
use App\Services\Gamification\NotificationService;
use App\Services\Gamification\AnalyticsService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;
    protected AnalyticsService $analyticsService;

    public function __construct(
        NotificationService $notificationService,
        AnalyticsService $analyticsService
    ) {
        $this->notificationService = $notificationService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * عرض صفحة الإشعارات
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Build query
        $query = $user->gamificationNotifications()->orderBy('created_at', 'desc');

        // Filter by read/unread
        if ($request->has('filter') && $request->filter === 'unread') {
            $query->where('is_read', false);
        }

        // Filter by type
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Paginate
        $notifications = $query->paginate(20);

        return view('student.gamification.notifications.index', compact('notifications'));
    }

    /**
     * API للحصول على آخر الإشعارات (للـ dropdown)
     */
    public function api(Request $request)
    {
        $user = $request->user();
        $limit = $request->get('limit', 10); // Default 10, allow custom limit

        $notifications = $user->gamificationNotifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon_html' => $notification->icon_html,
                    'action_url' => $notification->action_url,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    /**
     * عدد الإشعارات غير المقروءة
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markAsRead(Request $request, GamificationNotification $notification)
    {
        $user = $request->user();

        // التحقق من الملكية
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح.',
            ], 403);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد الإشعار كمقروء.',
        ]);
    }

    /**
     * تحديد جميع الإشعارات كمقروءة
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'message' => "تم تحديد {$count} إشعار كمقروء.",
        ]);
    }

    /**
     * حذف إشعار
     */
    public function destroy(Request $request, GamificationNotification $notification)
    {
        $user = $request->user();

        // التحقق من الملكية
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح.',
            ], 403);
        }

        $this->notificationService->delete($notification);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار.',
        ]);
    }

    /**
     * تقريري الشخصي
     */
    public function myReport(Request $request)
    {
        $user = $request->user();

        $report = $this->analyticsService->getStudentReport($user);
        $comparison = $this->analyticsService->compareToAverage($user);

        return response()->json([
            'success' => true,
            'report' => $report,
            'comparison' => $comparison,
        ]);
    }

    /**
     * تحديث تفضيلات الإشعارات
     */
    public function updatePreferences(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'preferences' => 'required|array',
        ]);

        $user->update([
            'notification_preferences' => $validated['preferences'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث تفضيلات الإشعارات.',
            'preferences' => $user->notification_preferences,
        ]);
    }

    /**
     * الحصول على تفضيلات الإشعارات
     */
    public function getPreferences(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'preferences' => $user->notification_preferences ?? [],
        ]);
    }
}
