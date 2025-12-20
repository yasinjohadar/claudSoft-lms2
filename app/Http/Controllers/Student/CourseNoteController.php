<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseNote;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseNote::where('user_id', auth()->id())
            ->with(['course', 'lesson'])
            ->latest();

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $courseNotes = $query->paginate(20);
        $courses = Course::whereHas('enrollments', function($q) {
            $q->where('student_id', auth()->id());
        })->get();

        return view('student.course-notes.index', compact('courseNotes', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'video_timestamp' => 'nullable|string',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_important' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_important'] = $request->has('is_important');

        CourseNote::create($validated);

        return redirect()->back()->with('success', 'تم إضافة الملاحظة بنجاح');
    }

    public function update(Request $request, CourseNote $courseNote)
    {
        if ($courseNote->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_timestamp' => 'nullable|string',
            'is_important' => 'boolean',
        ]);

        $validated['is_important'] = $request->has('is_important');

        $courseNote->update($validated);

        return redirect()->back()->with('success', 'تم تحديث الملاحظة بنجاح');
    }

    public function destroy(CourseNote $courseNote)
    {
        if ($courseNote->user_id !== auth()->id()) {
            abort(403);
        }

        $courseNote->delete();

        return response()->json(['success' => true]);
    }

    public function byCourse($courseId)
    {
        $notes = CourseNote::where('user_id', auth()->id())
            ->where('course_id', $courseId)
            ->with('lesson')
            ->latest()
            ->get();

        $course = Course::findOrFail($courseId);

        return view('student.course-notes.by-course', compact('notes', 'course'));
    }
}
