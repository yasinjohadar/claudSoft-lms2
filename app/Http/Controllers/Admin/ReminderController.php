<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupReminder;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReminderController extends Controller
{
    /**
     * عرض جميع التذكيرات
     */
    public function index(Request $request)
    {
        $query = GroupReminder::with(['creator', 'target'])
            ->latest();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('reminder_type', $request->type);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'sent') {
                $query->where('is_sent', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_sent', false);
            }
        }

        $reminders = $query->paginate(20);

        $reminderTypes = GroupReminder::getReminderTypes();
        $priorities = GroupReminder::getPriorities();

        return view('admin.reminders.index', compact('reminders', 'reminderTypes', 'priorities'));
    }

    /**
     * عرض نموذج إنشاء تذكير جديد
     */
    public function create()
    {
        $courses = Course::all();
        $reminderTypes = GroupReminder::getReminderTypes();
        $priorities = GroupReminder::getPriorities();

        return view('admin.reminders.create', compact('courses', 'reminderTypes', 'priorities'));
    }

    /**
     * حفظ تذكير جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_type' => 'required|in:course,group,training_camp',
            'target_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'reminder_type' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'remind_at' => 'nullable|date',
            'send_email' => 'boolean',
            'send_notification' => 'boolean',
        ]);

        $validated['creator_id'] = auth()->id();
        $validated['send_email'] = $request->has('send_email');
        $validated['send_notification'] = $request->has('send_notification');

        // تحديد نوع الهدف
        if ($validated['target_type'] === 'course') {
            $validated['target_type'] = 'App\Models\Course';
        }

        $reminder = GroupReminder::create($validated);

        // حساب عدد المستلمين
        $recipients = $reminder->getRecipients();
        $reminder->update(['recipients_count' => $recipients->count()]);

        // إذا كان الإرسال فوري (بدون جدولة)
        if (!$request->filled('remind_at')) {
            return $this->sendReminder($reminder);
        }

        return redirect()
            ->route('admin.reminders.index')
            ->with('success', 'تم إنشاء التذكير بنجاح وسيتم إرساله في الموعد المحدد');
    }

    /**
     * عرض تفاصيل تذكير
     */
    public function show(GroupReminder $reminder)
    {
        $reminder->load(['creator', 'target']);
        $recipients = $reminder->getRecipients();

        return view('admin.reminders.show', compact('reminder', 'recipients'));
    }

    /**
     * عرض نموذج تعديل تذكير
     */
    public function edit(GroupReminder $reminder)
    {
        if ($reminder->is_sent) {
            return redirect()
                ->route('admin.reminders.index')
                ->with('error', 'لا يمكن تعديل تذكير تم إرساله بالفعل');
        }

        $courses = Course::all();
        $reminderTypes = GroupReminder::getReminderTypes();
        $priorities = GroupReminder::getPriorities();

        return view('admin.reminders.edit', compact('reminder', 'courses', 'reminderTypes', 'priorities'));
    }

    /**
     * تحديث تذكير
     */
    public function update(Request $request, GroupReminder $reminder)
    {
        if ($reminder->is_sent) {
            return redirect()
                ->route('admin.reminders.index')
                ->with('error', 'لا يمكن تعديل تذكير تم إرساله بالفعل');
        }

        $validated = $request->validate([
            'target_type' => 'required|in:course,group,training_camp',
            'target_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'reminder_type' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'remind_at' => 'nullable|date',
            'send_email' => 'boolean',
            'send_notification' => 'boolean',
        ]);

        $validated['send_email'] = $request->has('send_email');
        $validated['send_notification'] = $request->has('send_notification');

        if ($validated['target_type'] === 'course') {
            $validated['target_type'] = 'App\Models\Course';
        }

        $reminder->update($validated);

        // إعادة حساب المستلمين
        $recipients = $reminder->getRecipients();
        $reminder->update(['recipients_count' => $recipients->count()]);

        return redirect()
            ->route('admin.reminders.index')
            ->with('success', 'تم تحديث التذكير بنجاح');
    }

    /**
     * حذف تذكير
     */
    public function destroy(GroupReminder $reminder)
    {
        $reminder->delete();

        return redirect()
            ->route('admin.reminders.index')
            ->with('success', 'تم حذف التذكير بنجاح');
    }

    /**
     * إرسال تذكير
     */
    public function send(GroupReminder $reminder)
    {
        if ($reminder->is_sent) {
            return redirect()
                ->back()
                ->with('error', 'تم إرسال هذا التذكير مسبقاً');
        }

        return $this->sendReminder($reminder);
    }

    /**
     * إرسال التذكير للمستلمين
     */
    protected function sendReminder(GroupReminder $reminder)
    {
        try {
            DB::beginTransaction();

            $recipients = $reminder->getRecipients();

            if ($recipients->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'لا يوجد مستلمين لهذا التذكير');
            }

            // إرسال إشعار لكل طالب
            foreach ($recipients as $recipient) {
                // إرسال إشعار داخلي
                if ($reminder->send_notification) {
                    // سيتم دمجه مع NotificationService لاحقاً
                }

                // إرسال بريد إلكتروني
                if ($reminder->send_email) {
                    // سيتم دمجه مع Email System لاحقاً
                }
            }

            // تحديث حالة التذكير
            $reminder->update([
                'is_sent' => true,
                'sent_at' => now(),
                'recipients_count' => $recipients->count(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.reminders.index')
                ->with('success', "تم إرسال التذكير بنجاح إلى {$recipients->count()} طالب");

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إرسال التذكير: ' . $e->getMessage());
        }
    }

    /**
     * إحصائيات التذكيرات
     */
    public function statistics()
    {
        $totalReminders = GroupReminder::count();
        $sentReminders = GroupReminder::where('is_sent', true)->count();
        $pendingReminders = GroupReminder::where('is_sent', false)->count();
        $totalRecipients = GroupReminder::sum('recipients_count');

        // By Type
        $byTypeData = GroupReminder::select('reminder_type', DB::raw('count(*) as count'))
            ->groupBy('reminder_type')
            ->get();

        $types = GroupReminder::getReminderTypes();
        $byType = [];
        foreach ($byTypeData as $item) {
            $byType[] = [
                'name' => $types[$item->reminder_type]['name'] ?? $item->reminder_type,
                'count' => $item->count
            ];
        }

        // By Priority
        $byPriorityData = GroupReminder::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get();

        $priorities = GroupReminder::getPriorities();
        $byPriority = [];
        foreach ($byPriorityData as $item) {
            $byPriority[] = [
                'name' => $priorities[$item->priority]['name'] ?? $item->priority,
                'count' => $item->count
            ];
        }

        $recentReminders = GroupReminder::where('is_sent', true)
            ->latest('sent_at')
            ->take(10)
            ->get();

        return view('admin.reminders.statistics', compact(
            'totalReminders',
            'sentReminders',
            'pendingReminders',
            'totalRecipients',
            'byType',
            'byPriority',
            'recentReminders'
        ));
    }
}
