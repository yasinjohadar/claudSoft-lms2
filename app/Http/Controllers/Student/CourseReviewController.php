<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\Request;

class CourseReviewController extends Controller
{
    public function store(Request $request, Course $course)
    {
        // Check if student is enrolled in the course
        $enrollment = $course->enrollments()->where('student_id', auth()->id())->first();

        if (! $enrollment) {
            return back()->with('error', 'يجب أن تكون مسجلاً في الكورس لتتمكن من كتابة مراجعة');
        }

        // Check if student already reviewed this course
        $existingReview = CourseReview::where('course_id', $course->id)
            ->where('student_id', auth()->id())
            ->first();

        if ($existingReview) {
            return back()->with('error', 'لقد قمت بكتابة مراجعة لهذا الكورس مسبقاً');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'review' => 'required|string|min:10',
        ]);

        $validated['course_id'] = $course->id;
        $validated['student_id'] = auth()->id();
        $validated['status'] = 'pending'; // Will be reviewed by admin

        $review = CourseReview::create($validated);

        return back()->with('success', 'تم إرسال مراجعتك بنجاح! سيتم نشرها بعد موافقة الإدارة');
    }

    public function update(Request $request, CourseReview $review)
    {
        // Check if the review belongs to the current student
        if ($review->student_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بتعديل هذه المراجعة');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'review' => 'required|string|min:10',
        ]);

        $validated['status'] = 'pending'; // Reset to pending after edit

        $review->update($validated);

        return back()->with('success', 'تم تحديث مراجعتك بنجاح');
    }

    public function destroy(CourseReview $review)
    {
        // Check if the review belongs to the current student
        if ($review->student_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بحذف هذه المراجعة');
        }

        $review->delete();

        return back()->with('success', 'تم حذف مراجعتك بنجاح');
    }

    public function markHelpful(CourseReview $review)
    {
        $review->incrementHelpful();

        return response()->json([
            'success' => true,
            'helpful_count' => $review->helpful_count,
        ]);
    }
}
