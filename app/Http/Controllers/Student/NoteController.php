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
            'is_important' => 'nullable|in:0,1,true,false',
        ]);

        $validated['user_id'] = auth()->id();
        // Handle checkbox: if present and has value, set to true, otherwise false
        $validated['is_important'] = $request->filled('is_important') && ($request->is_important == '1' || $request->is_important === true || $request->is_important === 'true');

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
            'is_important' => 'nullable|in:0,1,true,false',
        ]);

        // Handle checkbox: if present and has value, set to true, otherwise false
        $validated['is_important'] = $request->filled('is_important') && ($request->is_important == '1' || $request->is_important === true || $request->is_important === 'true');

        $note->update($validated);

        return redirect()->back()->with('success', 'تم تحديث الملاحظة بنجاح');
    }

    public function destroy(Note $note)
    {
        try {
            // Check if note belongs to the authenticated user
            if ($note->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بحذف هذه الملاحظة'
                ], 403);
            }

            $noteTitle = $note->title;
            $note->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الملاحظة "' . $noteTitle . '" بنجاح'
            ]);
        } catch (\Exception $e) {
            \Log::error('Note deletion error: ' . $e->getMessage(), [
                'note_id' => $note->id ?? null,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الملاحظة: ' . $e->getMessage()
            ], 500);
        }
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
