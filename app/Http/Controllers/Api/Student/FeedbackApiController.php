<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\AIStudentFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);
        $paginator = AIStudentFeedback::where('student_id', $request->user()->id)
            ->with(['quizAttempt.quiz', 'aiModel'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'feedbacks' => collect($paginator->items())->map(fn ($f) => $this->serialize($f))->values(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, AIStudentFeedback $feedback): JsonResponse
    {
        if ((int) $feedback->student_id !== (int) $request->user()->id) {
            abort(403);
        }
        $feedback->load(['quizAttempt.quiz', 'aiModel']);

        return response()->json(['success' => true, 'data' => $this->serialize($feedback, true)]);
    }

    private function serialize(AIStudentFeedback $feedback, bool $detailed = false): array
    {
        $data = [
            'id' => $feedback->id,
            'title' => AIStudentFeedback::FEEDBACK_TYPES[$feedback->feedback_type] ?? 'ملاحظة AI',
            'quiz_title' => $feedback->quizAttempt?->quiz?->title,
            'feedback_type' => $feedback->feedback_type,
            'created_at' => $feedback->created_at?->toIso8601String(),
        ];
        if ($detailed) {
            $data['content'] = $feedback->feedback_text;
            $data['suggestions'] = $feedback->suggestions;
        }
        return $data;
    }
}
