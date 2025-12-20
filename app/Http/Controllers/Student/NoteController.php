<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::where('user_id', auth()->id())
            ->active()
            ->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $notes = $query->paginate(20);
        $pinnedNotes = Note::where('user_id', auth()->id())
            ->pinned()
            ->active()
            ->latest()
            ->get();

        $categories = Note::getCategories();

        return view('student.notes.index', compact('notes', 'pinnedNotes', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
            'color' => 'nullable|string',
            'reminder_at' => 'nullable|date',
            'is_important' => 'nullable|boolean',
        ]);

        $validated['user_id'] = auth()->id();

        Note::create($validated);

        return redirect()->back()->with('success', 'تم إضافة الملاحظة بنجاح');
    }

    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
            'color' => 'nullable|string',
            'reminder_at' => 'nullable|date',
        ]);

        $note->update($validated);

        return redirect()->back()->with('success', 'تم تحديث الملاحظة بنجاح');
    }

    public function destroy(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->delete();

        return response()->json(['success' => true]);
    }

    public function togglePin(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->update(['is_pinned' => !$note->is_pinned]);

        return response()->json(['success' => true, 'is_pinned' => $note->is_pinned]);
    }

    public function toggleFavorite(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->update(['is_favorite' => !$note->is_favorite]);

        return response()->json(['success' => true, 'is_favorite' => $note->is_favorite]);
    }

    public function archive(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->update(['is_archived' => !$note->is_archived]);

        return response()->json(['success' => true, 'is_archived' => $note->is_archived]);
    }

    public function archived()
    {
        $archivedNotes = Note::where('user_id', auth()->id())
            ->archived()
            ->latest()
            ->paginate(20);

        return view('student.notes.archived', compact('archivedNotes'));
    }
}
