<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseNote;
use App\Models\GroupReminder;
use App\Models\Note;
use App\Models\StudentWork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentNotesApiController extends Controller
{
    public function notes(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 20), 1), 50);
        $query = Note::where('user_id', $request->user()->id)->active()->latest();
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'notes' => collect($paginator->items())->map(fn ($n) => $this->serializeNote($n))->values(),
                'categories' => Note::getCategories(),
                'pagination' => ['total' => $paginator->total(), 'current_page' => $paginator->currentPage()],
            ],
        ]);
    }

    public function storeNote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
            'color' => 'nullable|string',
            'reminder_at' => 'nullable|date',
            'is_important' => 'nullable|boolean',
        ]);
        $validated['user_id'] = $request->user()->id;
        $note = Note::create($validated);

        return response()->json(['success' => true, 'data' => $this->serializeNote($note)]);
    }

    public function updateNote(Request $request, Note $note): JsonResponse
    {
        abort_if($note->user_id !== $request->user()->id, 403);
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'category' => 'sometimes|string',
            'color' => 'nullable|string',
            'reminder_at' => 'nullable|date',
            'is_important' => 'nullable|boolean',
        ]);
        $note->update($validated);

        return response()->json(['success' => true, 'data' => $this->serializeNote($note)]);
    }

    public function deleteNote(Request $request, Note $note): JsonResponse
    {
        abort_if($note->user_id !== $request->user()->id, 403);
        $note->delete();

        return response()->json(['success' => true, 'message' => 'تم الحذف']);
    }

    public function courseNotes(Request $request): JsonResponse
    {
        $notes = CourseNote::where('student_id', $request->user()->id)
            ->with('course')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'notes' => collect($notes->items())->map(fn ($n) => [
                    'id' => $n->id,
                    'course_id' => $n->course_id,
                    'course_title' => $n->course?->title,
                    'content' => $n->content,
                    'created_at' => $n->created_at?->toIso8601String(),
                ])->values(),
            ],
        ]);
    }

    public function reminders(Request $request): JsonResponse
    {
        $reminders = GroupReminder::where('is_sent', true)
            ->latest('sent_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'reminders' => collect($reminders->items())->map(fn ($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'message' => $r->message,
                    'sent_at' => $r->sent_at?->toIso8601String(),
                ])->values(),
            ],
        ]);
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $start = $request->input('start');
        $end = $request->input('end');
        $userId = $request->user()->id;

        $notes = Note::where('user_id', $userId)
            ->whereNotNull('reminder_at')
            ->where('is_archived', false)
            ->when($start, fn ($q) => $q->where('reminder_at', '>=', $start))
            ->when($end, fn ($q) => $q->where('reminder_at', '<=', $end))
            ->get();

        $events = $notes->map(fn ($note) => [
            'id' => 'note-' . $note->id,
            'title' => $note->title,
            'start' => $note->reminder_at->toIso8601String(),
            'type' => 'note',
            'color' => $note->color,
        ])->values();

        return response()->json(['success' => true, 'data' => ['events' => $events]]);
    }

    public function works(Request $request): JsonResponse
    {
        $works = StudentWork::where('student_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'works' => collect($works->items())->map(fn ($w) => [
                    'id' => $w->id,
                    'title' => $w->title,
                    'description' => $w->description,
                    'status' => $w->status,
                    'due_date' => $w->due_date?->toDateString(),
                ])->values(),
            ],
        ]);
    }

    private function serializeNote(Note $note): array
    {
        return [
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'category' => $note->category,
            'color' => $note->color,
            'is_important' => (bool) $note->is_important,
            'is_pinned' => (bool) $note->is_pinned,
            'reminder_at' => $note->reminder_at?->toIso8601String(),
            'created_at' => $note->created_at?->toIso8601String(),
        ];
    }
}
