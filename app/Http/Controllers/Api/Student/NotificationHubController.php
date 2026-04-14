<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\NotificationDeviceToken;
use App\Models\NotificationUserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationHubController extends Controller
{
    public function inbox(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min(max((int) $request->query('limit', 20), 1), 100);
        $onlyUnread = (bool) $request->boolean('unread');

        $query = $user->notifications()->latest();
        if ($onlyUnread) {
            $query->whereNull('read_at');
        }

        $items = $query->limit($limit)->get()->map(function ($notification) {
            return [
                'id' => (string) $notification->id,
                'type' => (string) $notification->type,
                'title' => data_get($notification->data, 'title'),
                'body' => data_get($notification->data, 'body'),
                'event_key' => data_get($notification->data, 'event_key'),
                'channels' => data_get($notification->data, 'channels', []),
                'data' => data_get($notification->data, 'data', []),
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $items,
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => ['count' => $user->unreadNotifications()->count()],
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود.',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد الإشعار كمقروء.',
            'data' => ['unread_count' => $user->unreadNotifications()->count()],
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديد جميع الإشعارات كمقروءة.',
            'data' => ['unread_count' => 0],
        ]);
    }

    public function registerDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:1024'],
            'platform' => ['required', 'in:android,ios,web'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'meta' => ['nullable', 'array'],
        ]);

        $user = $request->user();

        $record = NotificationDeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $user->id,
                'platform' => $validated['platform'],
                'device_name' => $validated['device_name'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'meta' => $validated['meta'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الجهاز بنجاح.',
            'data' => ['device_token_id' => $record->id],
        ]);
    }

    public function unregisterDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:1024'],
        ]);

        $user = $request->user();
        NotificationDeviceToken::query()
            ->where('user_id', $user->id)
            ->where('token', $validated['token'])
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعطيل جهاز الإشعارات.',
        ]);
    }

    public function savePreference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_key' => ['required', 'string', 'max:255'],
            'database_enabled' => ['nullable', 'boolean'],
            'realtime_enabled' => ['nullable', 'boolean'],
            'fcm_enabled' => ['nullable', 'boolean'],
            'mail_enabled' => ['nullable', 'boolean'],
            'whatsapp_enabled' => ['nullable', 'boolean'],
            'meta' => ['nullable', 'array'],
        ]);

        $user = $request->user();

        $preference = NotificationUserPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'event_key' => $validated['event_key'],
            ],
            [
                'database_enabled' => $validated['database_enabled'] ?? true,
                'realtime_enabled' => $validated['realtime_enabled'] ?? true,
                'fcm_enabled' => $validated['fcm_enabled'] ?? true,
                'mail_enabled' => $validated['mail_enabled'] ?? false,
                'whatsapp_enabled' => $validated['whatsapp_enabled'] ?? false,
                'meta' => $validated['meta'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ تفضيلات الإشعارات.',
            'data' => [
                'id' => $preference->id,
                'event_key' => $preference->event_key,
                'database_enabled' => (bool) $preference->database_enabled,
                'realtime_enabled' => (bool) $preference->realtime_enabled,
                'fcm_enabled' => (bool) $preference->fcm_enabled,
                'mail_enabled' => (bool) $preference->mail_enabled,
                'whatsapp_enabled' => (bool) $preference->whatsapp_enabled,
                'meta' => $preference->meta ?? [],
                'updated_at' => $preference->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
