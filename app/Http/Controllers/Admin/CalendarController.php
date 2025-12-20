<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupReminder;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar.index');
    }

    public function getEvents(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $reminders = GroupReminder::where('is_active', true)
            ->when($start, function($query) use ($start) {
                $query->where(function($q) use ($start) {
                    $q->whereNotNull('remind_at')
                      ->where('remind_at', '>=', $start)
                      ->orWhereNotNull('sent_at')
                      ->where('sent_at', '>=', $start);
                });
            })
            ->when($end, function($query) use ($end) {
                $query->where(function($q) use ($end) {
                    $q->whereNotNull('remind_at')
                      ->where('remind_at', '<=', $end)
                      ->orWhereNotNull('sent_at')
                      ->where('sent_at', '<=', $end);
                });
            })
            ->with(['creator', 'target'])
            ->get();

        $events = [];
        $types = GroupReminder::getReminderTypes();
        $priorities = GroupReminder::getPriorities();

        foreach ($reminders as $reminder) {
            $typeInfo = $types[$reminder->reminder_type] ?? $types['announcement'];
            $priorityInfo = $priorities[$reminder->priority] ?? $priorities['medium'];

            $eventDate = $reminder->remind_at ?? $reminder->sent_at;

            if ($eventDate) {
                $events[] = [
                    'id' => $reminder->id,
                    'title' => $typeInfo['icon'] . ' ' . $reminder->title,
                    'start' => $eventDate->toIso8601String(),
                    'backgroundColor' => $this->getColorCode($typeInfo['color']),
                    'borderColor' => $this->getColorCode($priorityInfo['color']),
                    'extendedProps' => [
                        'type' => $typeInfo['name'],
                        'priority' => $priorityInfo['name'],
                        'message' => \Str::limit($reminder->message, 100),
                        'recipients' => $reminder->recipients_count,
                        'status' => $reminder->is_sent ? 'مرسلة' : 'قيد الانتظار',
                        'creator' => $reminder->creator->name,
                        'target' => $reminder->target->title ?? $reminder->target->name ?? 'N/A',
                    ],
                    'url' => route('admin.reminders.show', $reminder),
                ];
            }
        }

        return response()->json($events);
    }

    private function getColorCode($colorName)
    {
        $colors = [
            'primary' => '#3b82f6',
            'success' => '#10b981',
            'danger' => '#ef4444',
            'warning' => '#f59e0b',
            'info' => '#06b6d4',
            'secondary' => '#6b7280',
        ];

        return $colors[$colorName] ?? $colors['primary'];
    }
}
