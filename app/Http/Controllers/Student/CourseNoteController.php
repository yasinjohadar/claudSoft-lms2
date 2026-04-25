<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseNote;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
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
        $course = Course::findOrFail($courseId);

        $isEnrolled = $course->enrollments()
            ->where('student_id', auth()->id())
            ->exists();

        if (! $isEnrolled) {
            abort(403);
        }

        $notes = CourseNote::where('user_id', auth()->id())
            ->where('course_id', $courseId)
            ->with('lesson')
            ->latest()
            ->get();

        return view('student.course-notes.by-course', compact('notes', 'course'));
    }

    public function lessons($courseId)
    {
        $course = Course::findOrFail($courseId);

        $isEnrolled = $course->enrollments()
            ->where('student_id', auth()->id())
            ->exists();

        if (! $isEnrolled) {
            return response()->json(['message' => 'غير مصرح لك بعرض دروس هذا الكورس'], 403);
        }

        // جلب IDs الدروس بشكل مرن لأن بعض البيانات القديمة لا تعتمد module_type فقط.
        $lessonIds = CourseModule::where('course_id', $courseId)
            ->whereNotNull('modulable_id')
            ->where(function ($query) {
                $query->where('module_type', 'lesson')
                    ->orWhere('modulable_type', Lesson::class)
                    ->orWhere('modulable_type', 'LIKE', '%Lesson');
            })
            ->orderBy('sort_order')
            ->pluck('modulable_id')
            ->filter()
            ->unique()
            ->values();

        $lessons = Lesson::whereIn('id', $lessonIds)
            ->select('id', 'title')
            ->orderBy('title', 'asc')
            ->get();

        return response()->json($lessons);
    }
}
