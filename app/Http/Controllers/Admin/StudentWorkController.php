<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentWork;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentWorkController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentWork::with(['student', 'course', 'approver'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $works = $query->paginate(20);

        $stats = [
            'total' => StudentWork::count(),
            'pending' => StudentWork::pending()->count(),
            'approved' => StudentWork::approved()->count(),
            'rejected' => StudentWork::where('status', 'rejected')->count(),
        ];

        $students = User::role('student')->get(['id', 'name']);
        $categories = StudentWork::getCategories();
        $statuses = StudentWork::getStatuses();

        return view('admin.student-works.index', compact('works', 'stats', 'students', 'categories', 'statuses'));
    }

    public function create()
    {
        $students = User::role('student')->get(['id', 'name']);
        $courses = Course::get(['id', 'title']);
        $categories = StudentWork::getCategories();

        return view('admin.student-works.create', compact('students', 'courses', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'title' => 'required|string|max:255',
            'category' => 'required|in:project,assignment,creative,research,other',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'image' => 'nullable|image|max:2048',
            'technologies' => 'nullable|string',
            'completion_date' => 'nullable|date',
            'rating' => 'nullable|numeric|min:0|max:10',
            'admin_feedback' => 'nullable|string',
            'status' => 'required|in:draft,pending,approved,rejected',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('student-works/images', 'public');
        }

        if ($validated['status'] === 'approved') {
            $validated['approved_by'] = auth()->id();
            $validated['approved_at'] = now();
        }

        $work = StudentWork::create($validated);

        return redirect()->route('admin.student-works.show', $work)->with('success', 'تم إضافة العمل بنجاح');
    }

    public function show(StudentWork $studentWork)
    {
        $studentWork->load(['student', 'course', 'approver']);
        $studentWork->incrementViews();

        return view('admin.student-works.show', compact('studentWork'));
    }

    public function edit(StudentWork $studentWork)
    {
        $students = User::role('student')->get(['id', 'name']);
        $courses = Course::get(['id', 'title']);
        $categories = StudentWork::getCategories();

        return view('admin.student-works.edit', compact('studentWork', 'students', 'courses', 'categories'));
    }

    public function update(Request $request, StudentWork $studentWork)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'title' => 'required|string|max:255',
            'category' => 'required|in:project,assignment,creative,research,other',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'image' => 'nullable|image|max:2048',
            'technologies' => 'nullable|string',
            'completion_date' => 'nullable|date',
            'rating' => 'nullable|numeric|min:0|max:10',
            'admin_feedback' => 'nullable|string',
            'status' => 'required|in:draft,pending,approved,rejected',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($studentWork->image) {
                Storage::disk('public')->delete($studentWork->image);
            }
            $validated['image'] = $request->file('image')->store('student-works/images', 'public');
        }

        if ($validated['status'] === 'approved' && $studentWork->status !== 'approved') {
            $validated['approved_by'] = auth()->id();
            $validated['approved_at'] = now();
        }

        $studentWork->update($validated);

        return redirect()->route('admin.student-works.show', $studentWork)->with('success', 'تم تحديث العمل بنجاح');
    }

    public function destroy(StudentWork $studentWork)
    {
        if ($studentWork->image) {
            Storage::disk('public')->delete($studentWork->image);
        }

        $studentWork->delete();

        return redirect()->route('admin.student-works.index')->with('success', 'تم حذف العمل بنجاح');
    }

    public function approve(StudentWork $studentWork)
    {
        $studentWork->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'تم اعتماد العمل بنجاح');
    }

    public function reject(Request $request, StudentWork $studentWork)
    {
        $request->validate(['admin_feedback' => 'required|string']);

        $studentWork->update([
            'status' => 'rejected',
            'admin_feedback' => $request->admin_feedback,
        ]);

        return back()->with('success', 'تم رفض العمل');
    }

    public function toggleFeatured(StudentWork $studentWork)
    {
        $studentWork->update(['is_featured' => !$studentWork->is_featured]);
        return back()->with('success', 'تم تحديث حالة الإبراز');
    }

    public function toggleActive(StudentWork $studentWork)
    {
        $studentWork->update(['is_active' => !$studentWork->is_active]);
        return back()->with('success', 'تم تحديث حالة التفعيل');
    }
}
