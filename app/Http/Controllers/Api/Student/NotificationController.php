<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\GamificationNotification;
use App\Services\Gamification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * إشعارات الطالب (Gamification) بصيغة JSON لـ Flutter — نفس مصدر لوحة الويب.
 */
class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min(max((int) $request->query('limit', 50), 1), 100);

        $query = $user->gamificationNotifications()->orderByDesc('created_at');

        if ($request->query('filter') === 'unread') {
            $query->where('is_read', false);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        $notifications = $query->limit($limit)->get()->map(fn (GamificationNotification $n) => $this->transform($n));

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $this->notificationService->getUnreadCount($user),
            ],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $this->notificationService->getUnreadCount($user),
            ],
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $notification = GamificationNotification::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود.',
            ], 404);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد الإشعار كمقروء.',
            'data' => [
                'unread_count' => $this->notificationService->getUnreadCount($user),
            ],
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد جميع الإشعارات كمقروءة.',
            'data' => [
                'unread_count' => 0,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(GamificationNotification $n): array
    {
        $metadata = $n->metadata ?? [];

        return [
            'id' => (string) $n->id,
            'type' => (string) $n->type,
            'title' => (string) $n->title,
            'body' => (string) $n->message,
            'message' => (string) $n->message,
            'is_read' => (bool) $n->is_read,
            'created_at' => $n->created_at?->toIso8601String(),
            'action_url' => $n->action_url,
            'related_id' => $n->related_id !== null ? (string) $n->related_id : null,
            'image_url' => isset($metadata['image_url']) ? (string) $metadata['image_url'] : null,
        ];
    }
}
