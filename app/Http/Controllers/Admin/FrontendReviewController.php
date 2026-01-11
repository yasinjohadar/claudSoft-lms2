<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrontendReview;
use Illuminate\Http\Request;

class FrontendReviewController extends Controller
{
    /**
     * Display a listing of platform reviews.
     */
    public function index(Request $request)
    {
        $query = FrontendReview::with('user')
            ->whereNull('frontend_course_id') // Platform reviews only
            ->latest();

        // Filter by status (active/inactive)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by featured
        if ($request->filled('featured')) {
            if ($request->featured === 'yes') {
                $query->where('is_featured', true);
            } elseif ($request->featured === 'no') {
                $query->where('is_featured', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_email', 'like', "%{$search}%")
                  ->orWhere('review_text', 'like', "%{$search}%")
                  ->orWhereHas('user', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $reviews = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => FrontendReview::whereNull('frontend_course_id')->count(),
            'active' => FrontendReview::whereNull('frontend_course_id')->where('is_active', true)->count(),
            'inactive' => FrontendReview::whereNull('frontend_course_id')->where('is_active', false)->count(),
            'featured' => FrontendReview::whereNull('frontend_course_id')->where('is_featured', true)->count(),
        ];

        return view('admin.pages.platform-reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Display the specified review.
     */
    public function show(FrontendReview $review)
    {
        $review->load('user');
        
        // Ensure it's a platform review
        if ($review->frontend_course_id !== null) {
            abort(404, 'تقييم غير موجود');
        }

        return view('admin.pages.platform-reviews.show', compact('review'));
    }

    /**
     * Approve a review.
     */
    public function approve(FrontendReview $review)
    {
        if ($review->frontend_course_id !== null) {
            abort(404, 'تقييم غير موجود');
        }

        $review->update([
            'is_active' => true,
        ]);

        return back()->with('success', 'تم اعتماد التقييم بنجاح');
    }

    /**
     * Reject a review.
     */
    public function reject(FrontendReview $review)
    {
        if ($review->frontend_course_id !== null) {
            abort(404, 'تقييم غير موجود');
        }

        $review->update([
            'is_active' => false,
        ]);

        return back()->with('success', 'تم رفض التقييم');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(FrontendReview $review)
    {
        if ($review->frontend_course_id !== null) {
            abort(404, 'تقييم غير موجود');
        }

        $review->update([
            'is_featured' => !$review->is_featured,
        ]);

        $message = $review->is_featured ? 'تم تمييز التقييم' : 'تم إلغاء تمييز التقييم';

        return back()->with('success', $message);
    }

    /**
     * Remove the specified review.
     */
    public function destroy(FrontendReview $review)
    {
        if ($review->frontend_course_id !== null) {
            abort(404, 'تقييم غير موجود');
        }

        $review->delete();

        return redirect()->route('admin.platform-reviews.index')
            ->with('success', 'تم حذف التقييم بنجاح');
    }
}

