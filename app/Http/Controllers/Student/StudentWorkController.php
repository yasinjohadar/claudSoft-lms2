<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentWork;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentWorkController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = StudentWork::where('student_id', $user->id)
            ->with(['course', 'approver'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $works = $query->paginate(12);

        $stats = [
            'total' => StudentWork::where('student_id', $user->id)->count(),
            'draft' => StudentWork::where('student_id', $user->id)->draft()->count(),
            'pending' => StudentWork::where('student_id', $user->id)->pending()->count(),
            'approved' => StudentWork::where('student_id', $user->id)->approved()->count(),
        ];

        $categories = StudentWork::getCategories();
        $statuses = StudentWork::getStatuses();

        return view('student.works.index', compact('works', 'stats', 'categories', 'statuses'));
    }

    public function create()
    {
        $courses = Course::whereHas('enrollments', function($q) {
            $q->where('student_id', auth()->id());
        })->get(['id', 'title']);

        $categories = StudentWork::getCategories();

        return view('student.works.create', compact('courses', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'title' => 'required|string|max:255',
            'category' => 'required|in:project,assignment,creative,research,other',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'image' => 'nullable|image|max:2048',
            'video_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'technologies' => 'nullable|string',
            'completion_date' => 'nullable|date',
            'status' => 'nullable|in:draft,pending',
        ]);

        $validated['student_id'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'draft';

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('student-works/images', 'public');
        }

        $work = StudentWork::create($validated);

        return redirect()->route('student.works.show', $work)->with('success', 'تم إضافة عملك بنجاح');
    }

    public function show(StudentWork $work)
    {
        $this->authorize('view', $work);

        $work->load(['course', 'approver']);
        $work->incrementViews();

        return view('student.works.show', compact('work'));
    }

    public function edit(StudentWork $work)
    {
        $this->authorize('update', $work);

        $courses = Course::whereHas('enrollments', function($q) {
            $q->where('student_id', auth()->id());
        })->get(['id', 'title']);

        $categories = StudentWork::getCategories();

        return view('student.works.edit', compact('work', 'courses', 'categories'));
    }

    public function update(Request $request, StudentWork $work)
    {
        $this->authorize('update', $work);

        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'title' => 'required|string|max:255',
            'category' => 'required|in:project,assignment,creative,research,other',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'image' => 'nullable|image|max:2048',
            'video_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'github_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'technologies' => 'nullable|string',
            'completion_date' => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            if ($work->image) {
                Storage::disk('public')->delete($work->image);
            }
            $validated['image'] = $request->file('image')->store('student-works/images', 'public');
        }

        $work->update($validated);

        return redirect()->route('student.works.show', $work)->with('success', 'تم تحديث عملك بنجاح');
    }

    public function destroy(StudentWork $work)
    {
        $this->authorize('delete', $work);

        if ($work->image) {
            Storage::disk('public')->delete($work->image);
        }

        $work->delete();

        return redirect()->route('student.works.index')->with('success', 'تم حذف العمل بنجاح');
    }

    public function submit(StudentWork $work)
    {
        $this->authorize('update', $work);

        if ($work->status !== 'draft') {
            return back()->with('error', 'لا يمكن تقديم هذا العمل');
        }

        $work->update(['status' => 'pending']);

        return back()->with('success', 'تم تقديم العمل للمراجعة بنجاح');
    }

    public function portfolio()
    {
        $user = auth()->user();

        $works = StudentWork::where('student_id', $user->id)
            ->approved()
            ->active()
            ->ordered()
            ->get();

        $featured = StudentWork::where('student_id', $user->id)
            ->featured()
            ->approved()
            ->active()
            ->take(3)
            ->get();

        return view('student.works.portfolio', compact('works', 'featured'));
    }
}
