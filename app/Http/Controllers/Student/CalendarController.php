<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\GroupReminder;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        return view('student.calendar.index');
    }

    public function getEvents(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');
        $user = auth()->user();

        $events = [];

        // Get personal notes with reminders
        $notes = Note::where('user_id', $user->id)
            ->whereNotNull('reminder_at')
            ->where('is_archived', false)
            ->when($start, fn($q) => $q->where('reminder_at', '>=', $start))
            ->when($end, fn($q) => $q->where('reminder_at', '<=', $end))
            ->get();

        $categories = Note::getCategories();

        foreach ($notes as $note) {
            $categoryInfo = $categories[$note->category] ?? $categories['personal'];

            $events[] = [
                'id' => 'note-' . $note->id,
                'title' => $categoryInfo['icon'] . ' ' . $note->title,
                'start' => $note->reminder_at->toIso8601String(),
                'backgroundColor' => $note->color,
                'borderColor' => $note->color,
                'extendedProps' => [
                    'type' => 'ملاحظة شخصية',
                    'category' => $categoryInfo['name'],
                    'content' => \Str::limit($note->content, 100),
                    'isPinned' => $note->is_pinned,
                    'isFavorite' => $note->is_favorite,
                ],
                'url' => route('student.notes.index') . '#note-' . $note->id,
            ];
        }

        // Get group reminders for student's courses
        $reminders = GroupReminder::where('is_active', true)
            ->where('is_sent', true)
            ->whereHasMorph('target', ['App\Models\Course'], function($query) use ($user) {
                // Reminders المرتبطة بالكورسات التي مسجَّل بها الطالب
                $query->whereHas('enrollments', function($q) use ($user) {
                    $q->where('student_id', $user->id);
                });
            })
            ->when($start, fn($q) => $q->where(function($query) use ($start) {
                $query->whereNotNull('remind_at')->where('remind_at', '>=', $start)
                      ->orWhereNotNull('sent_at')->where('sent_at', '>=', $start);
            }))
            ->when($end, fn($q) => $q->where(function($query) use ($end) {
                $query->whereNotNull('remind_at')->where('remind_at', '<=', $end)
                      ->orWhereNotNull('sent_at')->where('sent_at', '<=', $end);
            }))
            ->with(['creator', 'target'])
            ->get();

        $types = GroupReminder::getReminderTypes();
        $priorities = GroupReminder::getPriorities();

        foreach ($reminders as $reminder) {
            $typeInfo = $types[$reminder->reminder_type] ?? $types['announcement'];
            $priorityInfo = $priorities[$reminder->priority] ?? $priorities['medium'];

            $eventDate = $reminder->remind_at ?? $reminder->sent_at;

            if ($eventDate) {
                $events[] = [
                    'id' => 'reminder-' . $reminder->id,
                    'title' => $typeInfo['icon'] . ' ' . $reminder->title,
                    'start' => $eventDate->toIso8601String(),
                    'backgroundColor' => $this->getColorCode($typeInfo['color']),
                    'borderColor' => $this->getColorCode($priorityInfo['color']),
                    'extendedProps' => [
                        'type' => 'تذكير جماعي',
                        'reminderType' => $typeInfo['name'],
                        'priority' => $priorityInfo['name'],
                        'message' => \Str::limit($reminder->message, 100),
                        'creator' => $reminder->creator->name,
                        'target' => $reminder->target->title ?? $reminder->target->name ?? 'N/A',
                    ],
                    'url' => route('student.reminders.show', $reminder),
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
