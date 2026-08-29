<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\GroupNotification;
use App\Services\Notifications\NotificationHubService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupNotificationController extends Controller
{
    public function __construct(
        protected NotificationHubService $notificationHub
    ) {}

    public function index(CourseGroup $group)
    {
        $notifications = GroupNotification::where('group_id', $group->id)
            ->latest()
            ->paginate(15);

        $notifications->getCollection()->transform(function (GroupNotification $notification) {
            $notification->setAttribute('read_count', $notification->readCount());

            return $notification;
        });

        return view('admin.pages.groups.notifications', [
            'group' => $group,
            'notifications' => $notifications,
        ]);
    }

    public function store(Request $request, CourseGroup $group)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => ['required', Rule::in(['success', 'info', 'warning', 'error'])],
            'action_url' => 'nullable|url|max:500',
        ]);

        $students = $group->students()->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد أعضاء في هذه المجموعة لإرسال الإشعار إليهم.',
            ], 422);
        }

        $notification = GroupNotification::create([
            'group_id' => $group->id,
            'sent_by' => auth()->id(),
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'action_url' => $validated['action_url'] ?? null,
            'recipients_count' => $students->count(),
        ]);

        foreach ($students as $student) {
            $this->notificationHub->sendToUser($student, 'admin.custom', [
                'title' => $validated['title'],
                'body' => $validated['message'],
                'type' => $validated['type'],
                'action_url' => $validated['action_url'] ?? null,
                'student_name' => $student->name,
                'group_name' => $group->name,
                'group_id' => $group->id,
                'group_notification_id' => $notification->id,
            ]);
        }

        $notification->setAttribute('read_count', 0);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الإشعار إلى '.$students->count().' طالب.',
            'notification' => $notification,
        ]);
    }

    public function show(CourseGroup $group, GroupNotification $notification)
    {
        abort_unless((int) $notification->group_id === (int) $group->id, 404);

        $recipients = $notification->recipientsQuery()
            ->with('user:id,name,name_ar')
            ->orderByDesc('is_read')
            ->orderBy('read_at')
            ->get()
            ->map(function ($recipient) {
                return [
                    'student_name' => $recipient->user?->name_ar ?: $recipient->user?->name ?: '—',
                    'is_read' => (bool) $recipient->is_read,
                    'read_at' => $recipient->read_at?->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'recipients' => $recipients,
        ]);
    }

    public function destroy(CourseGroup $group, GroupNotification $notification)
    {
        abort_unless((int) $notification->group_id === (int) $group->id, 404);

        $notification->recipientsQuery()->delete();
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار من صندوق كل المستلمين.',
        ]);
    }
}
