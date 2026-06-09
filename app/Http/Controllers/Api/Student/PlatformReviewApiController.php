<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\FrontendReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformReviewApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $review = FrontendReview::where('user_id', $request->user()->id)
            ->whereNull('frontend_course_id')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $review ? [
                'id' => $review->id,
                'rating' => (int) $review->rating,
                'review_text' => $review->review_text,
                'student_position' => $review->student_position,
                'is_published' => (bool) $review->is_published,
                'created_at' => $review->created_at?->toIso8601String(),
            ] : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $existing = FrontendReview::where('user_id', $request->user()->id)
            ->whereNull('frontend_course_id')
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'لديك تقييم موجود'], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:10|max:1000',
            'student_position' => 'nullable|string|max:255',
        ]);

        $review = FrontendReview::create([
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'],
            'student_position' => $validated['student_position'] ?? null,
            'is_published' => false,
        ]);

        return response()->json(['success' => true, 'data' => ['id' => $review->id]]);
    }

    public function update(Request $request, FrontendReview $review): JsonResponse
    {
        if ((int) $review->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:10|max:1000',
            'student_position' => 'nullable|string|max:255',
        ]);

        $review->update($validated);

        return response()->json(['success' => true, 'message' => 'تم تحديث التقييم']);
    }
}
