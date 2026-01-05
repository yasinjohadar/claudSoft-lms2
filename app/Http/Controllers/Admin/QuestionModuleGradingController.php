<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionModuleAttempt;
use App\Models\QuestionModuleResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionModuleGradingController extends Controller
{
    /**
     * Display a listing of attempts that need manual grading.
     */
    public function index()
    {
        // Get all completed attempts
        $attempts = QuestionModuleAttempt::with([
            'questionModule',
            'student',
            'responses.question.questionType'
        ])
        ->where('status', 'completed')
        ->orderBy('completed_at', 'desc')
        ->paginate(20);

        // Count responses needing grading for each attempt
        foreach ($attempts as $attempt) {
            $attempt->pending_grading_count = $attempt->responses()
                ->where(function($query) {
                    $query->whereNull('is_correct')
                          ->orWhereNull('score_obtained');
                })
                ->count();
        }

        return view('admin.pages.question-module-grading.index', compact('attempts'));
    }

    /**
     * Show grading interface for a specific attempt.
     */
    public function show($attemptId)
    {
        $attempt = QuestionModuleAttempt::with([
            'questionModule',
            'student',
            'responses.question.questionType',
            'responses.question.options'
        ])->findOrFail($attemptId);

        // Check if attempt is completed
        if (!$attempt->isCompleted()) {
            return redirect()->route('admin.question-module-grading.index')
                ->with('error', 'لا يمكن تصحيح محاولة لم يتم تسليمها بعد');
        }

        // Get responses that need manual grading (is_correct is null or score_obtained is null)
        $responsesNeedingGrading = $attempt->responses()
            ->where(function($query) {
                $query->whereNull('is_correct')
                      ->orWhereNull('score_obtained');
            })
            ->with(['question.questionType', 'question.options'])
            ->orderBy('id')
            ->get();

        // Get all responses for display
        $allResponses = $attempt->responses()
            ->with(['question.questionType', 'question.options'])
            ->orderBy('id')
            ->get();

        return view('admin.pages.question-module-grading.show', compact('attempt', 'responsesNeedingGrading', 'allResponses'));
    }

    /**
     * Grade a specific response.
     */
    public function gradeResponse(Request $request, $responseId)
    {
        $validated = $request->validate([
            'is_correct' => 'nullable|boolean',
            'score_obtained' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:1000',
        ]);

        try {
            $response = QuestionModuleResponse::with(['attempt', 'question'])->findOrFail($responseId);

            // Ensure score doesn't exceed max_score
            $scoreObtained = min($validated['score_obtained'], $response->max_score);

            $response->update([
                'is_correct' => $validated['is_correct'] ?? ($scoreObtained > 0),
                'score_obtained' => $scoreObtained,
                'feedback' => $validated['feedback'] ?? null,
            ]);

            // Recalculate attempt scores
            $attempt = $response->attempt;
            $attempt->calculateScores();

            return response()->json([
                'success' => true,
                'message' => 'تم تصحيح الإجابة بنجاح',
                'response' => $response->fresh(['question.questionType']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error grading question module response', [
                'response_id' => $responseId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التصحيح: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk grade multiple responses.
     */
    public function gradeBulk(Request $request)
    {
        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*.response_id' => 'required|exists:question_module_responses,id',
            'responses.*.is_correct' => 'nullable|boolean',
            'responses.*.score_obtained' => 'required|numeric|min:0',
            'responses.*.feedback' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $attemptIds = [];

            foreach ($validated['responses'] as $responseData) {
                $response = QuestionModuleResponse::findOrFail($responseData['response_id']);
                
                $scoreObtained = min($responseData['score_obtained'], $response->max_score);

                $response->update([
                    'is_correct' => $responseData['is_correct'] ?? ($scoreObtained > 0),
                    'score_obtained' => $scoreObtained,
                    'feedback' => $responseData['feedback'] ?? null,
                ]);

                $attemptIds[] = $response->attempt_id;
            }

            // Recalculate scores for all affected attempts
            $uniqueAttemptIds = array_unique($attemptIds);
            foreach ($uniqueAttemptIds as $attemptId) {
                $attempt = QuestionModuleAttempt::find($attemptId);
                if ($attempt) {
                    $attempt->calculateScores();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تصحيح ' . count($validated['responses']) . ' إجابة بنجاح',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk grading question module responses', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التصحيح: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete grading for an attempt.
     */
    public function completeGrading($attemptId)
    {
        try {
            $attempt = QuestionModuleAttempt::findOrFail($attemptId);

            // Check if all responses are graded
            $ungradedCount = $attempt->responses()
                ->whereNull('is_correct')
                ->orWhereNull('score_obtained')
                ->count();

            if ($ungradedCount > 0) {
                return redirect()->back()
                    ->with('error', 'يجب تصحيح جميع الإجابات أولاً');
            }

            // Recalculate scores one final time
            $attempt->calculateScores();

            return redirect()->route('admin.question-module-grading.index')
                ->with('success', 'تم إكمال تصحيح المحاولة بنجاح');
        } catch (\Exception $e) {
            Log::error('Error completing grading for question module attempt', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}

