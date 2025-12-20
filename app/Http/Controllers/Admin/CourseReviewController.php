<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\Request;

class CourseReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseReview::with(['course', 'student', 'approver'])->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Search in title and review text
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('review', 'like', "%{$request->search}%")
                  ->orWhereHas('student', function($sq) use ($request) {
                      $sq->where('name', 'like', "%{$request->search}%");
                  });
            });
        }

        $reviews = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => CourseReview::count(),
            'pending' => CourseReview::pending()->count(),
            'approved' => CourseReview::approved()->count(),
            'rejected' => CourseReview::rejected()->count(),
        ];

        $courses = Course::get(['id', 'title']);
        $statuses = CourseReview::getStatuses();

        return view('admin.course-reviews.index', compact('reviews', 'stats', 'courses', 'statuses'));
    }

    public function show(CourseReview $review)
    {
        $review->load(['course', 'student', 'approver']);

        return view('admin.course-reviews.show', compact('review'));
    }

    public function approve(CourseReview $review)
    {
        $review->approve(auth()->id());

        return back()->with('success', 'تم اعتماد المراجعة بنجاح');
    }

    public function reject(Request $request, CourseReview $review)
    {
        $request->validate([
            'admin_feedback' => 'nullable|string|max:1000',
        ]);

        $review->reject($request->admin_feedback);

        return back()->with('success', 'تم رفض المراجعة');
    }

    public function toggleFeatured(CourseReview $review)
    {
        $review->update(['is_featured' => !$review->is_featured]);

        $message = $review->is_featured
            ? 'تم إبراز المراجعة بنجاح'
            : 'تم إلغاء إبراز المراجعة';

        return back()->with('success', $message);
    }

    public function destroy(CourseReview $review)
    {
        $review->delete();

        return redirect()->route('admin.course-reviews.index')
            ->with('success', 'تم حذف المراجعة بنجاح');
    }
}
