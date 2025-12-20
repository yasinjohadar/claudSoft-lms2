<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Group;
use App\Services\Gamification\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationManagementController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * عرض صفحة إرسال الإشعارات
     */
    public function index()
    {
        return view('admin.notifications.index');
    }

    /**
     * عرض سجل الإشعارات المرسلة
     */
    public function history(Request $request)
    {
        $query = \App\Models\GamificationNotification::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Filter by date
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        }

        $notifications = $query->paginate(50);

        return view('admin.notifications.history', compact('notifications'));
    }

    /**
     * إرسال إشعار لطالب واحد
     */
    public function sendToStudent(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'action_url' => 'nullable|string|max:500',
        ]);

        try {
            $student = User::findOrFail($validated['student_id']);

            $this->notificationService->send(
                user: $student,
                type: $validated['type'],
                title: $validated['title'],
                message: $validated['message'],
                icon: $validated['icon'] ?? '📢',
                actionUrl: $validated['action_url'] ?? null,
                metadata: [
                    'sent_by' => auth()->user()->name,
                    'sent_by_id' => auth()->id(),
                    'sent_at' => now()->toDateTimeString(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الإشعار بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification to student', [
                'error' => $e->getMessage(),
                'student_id' => $validated['student_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الإشعار',
            ], 500);
        }
    }

    /**
     * إرسال إشعار لطلاب كورس معين
     */
    public function sendToCourse(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'action_url' => 'nullable|string|max:500',
        ]);

        try {
            $course = Course::findOrFail($validated['course_id']);

            // Get all enrolled students
            $students = $course->students; // Assuming relationship exists

            $sentCount = 0;
            foreach ($students as $student) {
                $this->notificationService->send(
                    user: $student,
                    type: $validated['type'],
                    title: $validated['title'],
                    message: $validated['message'],
                    icon: $validated['icon'] ?? '📢',
                    actionUrl: $validated['action_url'] ?? null,
                    relatedType: Course::class,
                    relatedId: $course->id,
                    metadata: [
                        'course_name' => $course->title,
                        'sent_by' => auth()->user()->name,
                        'sent_by_id' => auth()->id(),
                        'sent_at' => now()->toDateTimeString(),
                    ]
                );
                $sentCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "تم إرسال الإشعار لـ {$sentCount} طالب",
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification to course students', [
                'error' => $e->getMessage(),
                'course_id' => $validated['course_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الإشعارات',
            ], 500);
        }
    }

    /**
     * إرسال إشعار لطلاب مجموعة معينة
     */
    public function sendToGroup(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'action_url' => 'nullable|string|max:500',
        ]);

        try {
            $group = Group::findOrFail($validated['group_id']);

            // Get all students in the group
            $students = $group->students; // Assuming relationship exists

            $sentCount = 0;
            foreach ($students as $student) {
                $this->notificationService->send(
                    user: $student,
                    type: $validated['type'],
                    title: $validated['title'],
                    message: $validated['message'],
                    icon: $validated['icon'] ?? '📢',
                    actionUrl: $validated['action_url'] ?? null,
                    relatedType: Group::class,
                    relatedId: $group->id,
                    metadata: [
                        'group_name' => $group->name,
                        'sent_by' => auth()->user()->name,
                        'sent_by_id' => auth()->id(),
                        'sent_at' => now()->toDateTimeString(),
                    ]
                );
                $sentCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "تم إرسال الإشعار لـ {$sentCount} طالب في المجموعة",
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification to group students', [
                'error' => $e->getMessage(),
                'group_id' => $validated['group_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الإشعارات',
            ], 500);
        }
    }

    /**
     * إرسال إشعار broadcast لجميع الطلاب
     */
    public function sendBroadcast(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'action_url' => 'nullable|string|max:500',
        ]);

        try {
            // Get all students
            $students = User::role('student')->get();

            $sentCount = 0;
            foreach ($students as $student) {
                $this->notificationService->send(
                    user: $student,
                    type: $validated['type'],
                    title: $validated['title'],
                    message: $validated['message'],
                    icon: $validated['icon'] ?? '📢',
                    actionUrl: $validated['action_url'] ?? null,
                    metadata: [
                        'broadcast' => true,
                        'sent_by' => auth()->user()->name,
                        'sent_by_id' => auth()->id(),
                        'sent_at' => now()->toDateTimeString(),
                    ]
                );
                $sentCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "تم إرسال الإشعار لجميع الطلاب ({$sentCount} طالب)",
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to broadcast notification', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الإشعارات',
            ], 500);
        }
    }

    /**
     * Get students list for autocomplete
     */
    public function getStudents(Request $request)
    {
        $search = $request->get('search', '');

        $students = User::role('student')
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($students);
    }

    /**
     * Get courses list for autocomplete
     */
    public function getCourses(Request $request)
    {
        $search = $request->get('search', '');

        $courses = Course::where('title', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'title']);

        return response()->json($courses);
    }

    /**
     * Get groups list for autocomplete
     */
    public function getGroups(Request $request)
    {
        $search = $request->get('search', '');

        $groups = Group::where('name', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($groups);
    }

    /**
     * Get notification statistics
     */
    public function statistics()
    {
        $stats = [
            'total_sent' => \App\Models\GamificationNotification::count(),
            'total_read' => \App\Models\GamificationNotification::where('is_read', true)->count(),
            'total_unread' => \App\Models\GamificationNotification::where('is_read', false)->count(),
            'sent_today' => \App\Models\GamificationNotification::whereDate('created_at', today())->count(),
            'sent_this_week' => \App\Models\GamificationNotification::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'sent_this_month' => \App\Models\GamificationNotification::whereMonth('created_at', now()->month)->count(),
        ];

        // Get notifications by type
        $byType = \App\Models\GamificationNotification::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();

        return view('admin.notifications.statistics', compact('stats', 'byType'));
    }
}
