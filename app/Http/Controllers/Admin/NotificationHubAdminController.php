<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationDeliveryLog;
use App\Models\NotificationTemplate;
use App\Models\SystemSetting;
use App\Services\Notifications\NotificationHubService;
use App\Services\Notifications\StudentSegmentResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationHubAdminController extends Controller
{
    public function settings(): JsonResponse
    {
        $keys = [
            'channel_database_enabled',
            'channel_realtime_enabled',
            'channel_fcm_enabled',
            'channel_mail_enabled',
            'channel_whatsapp_enabled',
            'events_enabled_default',
        ];

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = SystemSetting::get($key, 'notification_hub', null);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_database_enabled' => ['nullable', 'boolean'],
            'channel_realtime_enabled' => ['nullable', 'boolean'],
            'channel_fcm_enabled' => ['nullable', 'boolean'],
            'channel_mail_enabled' => ['nullable', 'boolean'],
            'channel_whatsapp_enabled' => ['nullable', 'boolean'],
            'events_enabled_default' => ['nullable', 'boolean'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, (bool) $value, 'boolean', 'notification_hub');
        }

        return response()->json(['success' => true, 'message' => 'تم تحديث إعدادات الإشعارات.']);
    }

    public function updateEventToggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_key' => ['required', 'string', 'max:255'],
            'enabled' => ['required', 'boolean'],
        ]);

        SystemSetting::set(
            'event_'.$validated['event_key'].'_enabled',
            (bool) $validated['enabled'],
            'boolean',
            'notification_hub'
        );

        return response()->json(['success' => true, 'message' => 'تم تحديث حالة الحدث.']);
    }

    public function templates(Request $request): JsonResponse
    {
        $query = NotificationTemplate::query()->latest();

        if ($request->filled('event_key')) {
            $query->where('event_key', $request->string('event_key'));
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->string('channel'));
        }
        if ($request->filled('locale')) {
            $query->where('locale', $request->string('locale'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(25),
        ]);
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_key' => ['required', 'string', 'max:255'],
            'channel' => ['required', Rule::in(['database', 'realtime', 'fcm', 'mail', 'whatsapp'])],
            'locale' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:255'],
            'title_template' => ['nullable', 'string', 'max:255'],
            'body_template' => ['required', 'string'],
            'meta' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $template = NotificationTemplate::updateOrCreate(
            [
                'event_key' => $validated['event_key'],
                'channel' => $validated['channel'],
                'locale' => $validated['locale'],
            ],
            [
                'name' => $validated['name'],
                'title_template' => $validated['title_template'] ?? null,
                'body_template' => $validated['body_template'],
                'meta' => $validated['meta'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ القالب.',
            'data' => $template,
        ]);
    }

    public function updateTemplate(Request $request, int $id): JsonResponse
    {
        $template = NotificationTemplate::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'title_template' => ['nullable', 'string', 'max:255'],
            'body_template' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث القالب.',
            'data' => $template->fresh(),
        ]);
    }

    public function deleteTemplate(int $id): JsonResponse
    {
        NotificationTemplate::query()->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف القالب.',
        ]);
    }

    public function sendSegmented(Request $request, StudentSegmentResolverService $resolver, NotificationHubService $hub): JsonResponse
    {
        $validated = $request->validate([
            'event_key' => ['required', 'string', 'max:255'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:users,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'group_id' => ['nullable', 'integer', 'exists:course_groups,id'],
            'enrollment_status' => ['nullable', Rule::in(['active', 'completed', 'pending', 'suspended'])],
            'data' => ['nullable', 'array'],
            'channels' => ['nullable', 'array'],
            'channels.*' => [Rule::in(['database', 'realtime', 'fcm', 'mail', 'whatsapp'])],
        ]);

        $users = $resolver->resolve($validated);
        $sent = $hub->sendToUsers(
            $users,
            $validated['event_key'],
            $validated['data'] ?? [],
            $validated['channels'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => "تم إرسال الإشعار إلى {$sent} طالب.",
            'data' => [
                'target_count' => $users->count(),
                'sent_count' => $sent,
            ],
        ]);
    }

    public function logs(Request $request): JsonResponse
    {
        $query = NotificationDeliveryLog::query()->latest();

        if ($request->filled('event_key')) {
            $query->where('event_key', $request->string('event_key'));
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->string('channel'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(50),
        ]);
    }
}
