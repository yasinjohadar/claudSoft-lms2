<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\CampEnrollment;
use App\Models\TrainingCamp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingCampApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;
        $camps = TrainingCamp::with('category')
            ->active()
            ->visibleToStudent($studentId)
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('start_date')
            ->get()
            ->map(function ($camp) use ($studentId) {
                $enrollment = CampEnrollment::where('camp_id', $camp->id)
                    ->where('student_id', $studentId)
                    ->first();

                return [
                    'id' => $camp->id,
                    'name' => $camp->name,
                    'description' => $camp->description,
                    'instructor_name' => $camp->instructor_name,
                    'start_date' => $camp->start_date?->toDateString(),
                    'end_date' => $camp->end_date?->toDateString(),
                    'price' => (float) $camp->price,
                    'category' => $camp->category?->name,
                    'enrollment_status' => $enrollment?->status,
                    'is_enrolled' => (bool) $enrollment,
                ];
            });

        return response()->json(['success' => true, 'data' => ['camps' => $camps]]);
    }

    public function show(Request $request, TrainingCamp $camp): JsonResponse
    {
        $camp->load(['category', 'targets']);
        $studentId = $request->user()->id;
        $enrollment = CampEnrollment::where('camp_id', $camp->id)
            ->where('student_id', $studentId)
            ->first();

        if (! $enrollment && ! $camp->isVisibleToStudent($studentId)) {
            return response()->json([
                'success' => false,
                'message' => 'هذا المعسكر غير متاح لمجموعتك',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'camp' => [
                    'id' => $camp->id,
                    'name' => $camp->name,
                    'description' => $camp->description,
                    'instructor_name' => $camp->instructor_name,
                    'start_date' => $camp->start_date?->toDateString(),
                    'end_date' => $camp->end_date?->toDateString(),
                    'price' => (float) $camp->price,
                    'location' => $camp->location,
                    'category' => $camp->category?->name,
                ],
                'enrollment' => $enrollment ? [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status,
                ] : null,
            ],
        ]);
    }
}
