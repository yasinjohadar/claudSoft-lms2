<?php

namespace App\Http\Controllers\Admin\AI;

use App\Http\Controllers\Controller;
use App\Models\QuizResponse;
use App\Models\EssayGradingRubric;
use App\Services\AI\EssayGradingService;
use App\Jobs\AI\GradeEssayJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EssayGradingController extends Controller
{
    protected EssayGradingService $essayGradingService;

    public function __construct(EssayGradingService $essayGradingService)
    {
        $this->essayGradingService = $essayGradingService;
    }

    /**
     * Show essay grading interface
     */
    public function index(Request $request)
    {
        $query = QuizResponse::with(['question', 'user'])
            ->whereHas('question', function($q) {
                $q->whereHas('questionType', function($qt) {
                    $qt->where('name', 'essay');
                });
            });

        if ($request->filled('status')) {
            if ($request->status === 'ungraded') {
                $query->whereNull('score_obtained');
            } elseif ($request->status === 'ai_graded') {
                $query->where('ai_graded', true);
            } elseif ($request->status === 'manually_graded') {
                $query->where('ai_graded', false)->whereNotNull('score_obtained');
            }
        }

        $responses = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.ai.essay-grading', compact('responses'));
    }

    /**
     * Grade essay response
     */
    public function grade(Request $request, int $responseId)
    {
        $response = QuizResponse::with('question')->findOrFail($responseId);

        if (empty($response->response_text)) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد إجابة للتصحيح',
            ], 400);
        }

        try {
            if ($request->boolean('async', false)) {
                // Queue job
                GradeEssayJob::dispatch(
                    $responseId,
                    $response->question_id,
                    $response->response_text,
                    $request->input('provider_name')
                );

                return response()->json([
                    'success' => true,
                    'message' => 'تم بدء عملية التصحيح في الخلفية',
                ]);
            }

            // Grade synchronously
            $result = $this->essayGradingService->gradeEssay(
                $responseId,
                $response->question_id,
                $response->response_text,
                $request->input('provider_name')
            );

            return response()->json([
                'success' => true,
                'grading' => $result['grading'],
                'ai_request_id' => $result['ai_request_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل التصحيح: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Review and accept/reject AI grading
     */
    public function review(Request $request, int $responseId)
    {
        $validated = $request->validate([
            'action' => 'required|in:accept,reject',
            'manual_score' => 'nullable|numeric|min:0',
        ]);

        $response = QuizResponse::findOrFail($responseId);

        if ($validated['action'] === 'accept') {
            // Keep AI grading as is
            return response()->json([
                'success' => true,
                'message' => 'تم قبول التصحيح التلقائي',
            ]);
        } else {
            // Reject and allow manual grading
            $response->update([
                'ai_graded' => false,
                'score_obtained' => $validated['manual_score'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم رفض التصحيح التلقائي وفتح التصحيح اليدوي',
            ]);
        }
    }

    /**
     * Show rubric management
     */
    public function rubrics(Request $request)
    {
        $query = EssayGradingRubric::with(['question', 'creator']);

        if ($request->filled('question_id')) {
            $query->where('question_id', $request->question_id);
        }

        $rubrics = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.ai.essay-rubrics', compact('rubrics'));
    }
}
