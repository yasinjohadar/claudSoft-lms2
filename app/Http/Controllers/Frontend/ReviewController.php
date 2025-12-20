<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FrontendReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display general reviews (not specific to a course)
     */
    public function index()
    {
        $reviews = FrontendReview::with('user')
                        ->whereNull('frontend_course_id') // General reviews only
                        ->where('is_active', true)
                        ->orderBy('is_featured', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate(12);

        return view('frontend.pages.reviews', compact('reviews'));
    }

    /**
     * Show the form for creating a new review
     */
    public function create()
    {
        // Check if user already submitted a general review
        $existingReview = FrontendReview::where('user_id', Auth::id())
                                       ->whereNull('frontend_course_id')
                                       ->first();

        if ($existingReview) {
            return redirect()->route('frontend.reviews.index')
                           ->with('error', 'لقد قمت بإضافة تقييم مسبقاً. يمكنك تعديله من لوحة التحكم الخاصة بك.');
        }

        return view('frontend.pages.add-review');
    }

    /**
     * Store a new general review
     */
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:10|max:1000',
            'suggestion' => 'nullable|string|max:500',
        ]);

        // Check if user already submitted a general review
        $existingReview = FrontendReview::where('user_id', Auth::id())
                                       ->whereNull('frontend_course_id')
                                       ->first();

        if ($existingReview) {
            return back()->with('error', 'لقد قمت بإضافة تقييم مسبقاً. يمكنك تعديله من لوحة التحكم الخاصة بك.');
        }

        FrontendReview::create([
            'user_id' => Auth::id(),
            'frontend_course_id' => null,
            'student_name' => Auth::user()->name,
            'student_email' => Auth::user()->email,
            'student_position' => $request->student_position ?? null,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'suggestion' => $request->suggestion,
            'is_active' => false, // Pending admin approval
            'is_featured' => false,
        ]);

        return redirect()->route('frontend.reviews.index')
                       ->with('success', 'شكراً لك! تم إرسال تقييمك بنجاح وسيتم مراجعته من قبل الإدارة قبل نشره على المنصة.');
    }
}
