<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Resources\Mobile\V1\NotificationResource;
use App\Models\GamificationNotification;
use App\Models\User;
use App\Services\Gamification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * صندوق إشعارات الطالب في التطبيق — نفس صندوق الويب (gamification_notifications).
 */
class NotificationController extends BaseMobileController
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'filter' => ['nullable', 'in:unread'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $page = GamificationNotification::query()
            ->where('user_id', $user->id)
            ->when($request->input('filter') === 'unread', fn ($q) => $q->where('is_read', false))
            ->latest()
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 20));

        return $this->success(
            NotificationResource::collection($page->getCollection())->resolve($request),
            null,
            [...$this->paginationMeta($page), 'unread_count' => $this->notifications->getUnreadCount($user)],
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->success(['count' => $this->notifications->getUnreadCount($request->user())]);
    }

    public function markRead(Request $request, int $notification): JsonResponse
    {
        $user = $request->user();
        $model = $this->own($user, $notification);
        if (! $model) {
            return $this->error(__('mobile.notification_not_found'), 404);
        }

        if (! $model->is_read) {
            $this->notifications->markAsRead($model);
            $this->markHubCopyRead($user, $model);
        }

        return $this->success(['unread_count' => $this->notifications->getUnreadCount($user)]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->notifications->markAllAsRead($user);
        $user->unreadNotifications()->update(['read_at' => now()]);

        return $this->success(['unread_count' => 0], __('mobile.notifications_all_read'));
    }

    public function destroy(Request $request, int $notification): JsonResponse
    {
        $user = $request->user();
        $model = $this->own($user, $notification);
        if (! $model) {
            return $this->error(__('mobile.notification_not_found'), 404);
        }

        $this->notifications->delete($model);

        return $this->success(['unread_count' => $this->notifications->getUnreadCount($user)]);
    }

    private function own(User $user, int $id): ?GamificationNotification
    {
        return GamificationNotification::where('user_id', $user->id)->find($id);
    }

    /**
     * إشعارات مركز الإشعارات تُنسخ إلى الصندوق مع metadata.notification_id — نعلّم النسخة الأصلية أيضاً.
     */
    private function markHubCopyRead(User $user, GamificationNotification $model): void
    {
        $hubId = is_array($model->metadata) ? ($model->metadata['notification_id'] ?? null) : null;
        if (is_string($hubId) && $hubId !== '') {
            $user->notifications()->whereKey($hubId)->whereNull('read_at')->update(['read_at' => now()]);
        }
    }
}
