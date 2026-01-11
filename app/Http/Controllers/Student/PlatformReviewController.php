<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FrontendReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlatformReviewController extends Controller
{
    /**
     * Display the student's platform review.
     */
    public function index()
    {
        $review = FrontendReview::where('user_id', Auth::id())
            ->whereNull('frontend_course_id')
            ->first();

        return view('student.pages.platform-review.index', compact('review'));
    }

    /**
     * Show the form for creating a new review.
     */
    public function create()
    {
        // Check if user already has a review
        $existingReview = FrontendReview::where('user_id', Auth::id())
            ->whereNull('frontend_course_id')
            ->first();

        if ($existingReview) {
            return redirect()->route('student.platform-review.index')
                ->with('info', 'لديك تقييم موجود. يمكنك تعديله من هذه الصفحة.');
        }

        return view('student.pages.platform-review.create');
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request)
    {
        // Check if user already has a review
        $existingReview = FrontendReview::where('user_id', Auth::id())
            ->whereNull('frontend_course_id')
            ->first();

        if ($existingReview) {
            return redirect()->route('student.platform-review.index')
                ->with('error', 'لديك تقييم موجود بالفعل. يمكنك تعديله من هذه الصفحة.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:10|max:1000',
            'student_position' => 'nullable|string|max:255',
            'suggestion' => 'nullable|string|max:500',
        ]);

        FrontendReview::create([
            'user_id' => Auth::id(),
            'frontend_course_id' => null,
            'student_name' => Auth::user()->name,
            'student_email' => Auth::user()->email,
            'student_position' => $validated['student_position'] ?? null,
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'],
            'suggestion' => $validated['suggestion'] ?? null,
            'is_active' => false, // Pending admin approval
            'is_featured' => false,
        ]);

        return redirect()->route('student.platform-review.index')
            ->with('success', 'شكراً لك! تم إرسال تقييمك بنجاح وسيتم مراجعته من قبل الإدارة قبل نشره على المنصة.');
    }

    /**
     * Show the form for editing the specified review.
     */
    public function edit(FrontendReview $review)
    {
        // Verify ownership
        if ($review->user_id !== Auth::id() || $review->frontend_course_id !== null) {
            abort(403, 'غير مصرح لك بالوصول إلى هذا التقييم');
        }

        return view('student.pages.platform-review.edit', compact('review'));
    }

    /**
     * Update the specified review.
     */
    public function update(Request $request, FrontendReview $review)
    {
        // Verify ownership
        if ($review->user_id !== Auth::id() || $review->frontend_course_id !== null) {
            abort(403, 'غير مصرح لك بتعديل هذا التقييم');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:10|max:1000',
            'student_position' => 'nullable|string|max:255',
            'suggestion' => 'nullable|string|max:500',
        ]);

        $review->update([
            'student_name' => Auth::user()->name,
            'student_email' => Auth::user()->email,
            'student_position' => $validated['student_position'] ?? null,
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'],
            'suggestion' => $validated['suggestion'] ?? null,
            'is_active' => false, // Reset to pending after update
        ]);

        return redirect()->route('student.platform-review.index')
            ->with('success', 'تم تحديث تقييمك بنجاح وسيتم مراجعته من قبل الإدارة مرة أخرى.');
    }
}

