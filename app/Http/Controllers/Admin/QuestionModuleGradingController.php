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

        // Get responses that need manual grading - only short_answer and essay types
        // First get all responses of these types, then filter in PHP to check student_answer properly
        $responsesNeedingGrading = $attempt->responses()
            ->whereHas('question.questionType', function($q) {
                $q->whereIn('name', ['short_answer', 'essay']);
            })
            ->where(function($query) {
                $query->whereNull('is_correct')
                      ->orWhereNull('score_obtained');
            })
            ->whereNotNull('student_answer') // Basic check - student_answer column is not null
            ->with(['question.questionType', 'question.options'])
            ->orderBy('id')
            ->get()
            ->filter(function($response) {
                // Filter in PHP to properly check array/string values
                $studentAnswer = $response->student_answer;
                if ($studentAnswer === null) {
                    return false;
                }
                if (is_array($studentAnswer)) {
                    return !empty($studentAnswer);
                }
                $answerStr = trim((string)$studentAnswer);
                return $answerStr !== '' && $answerStr !== 'null' && $answerStr !== '[]';
            })
            ->values(); // Re-index the collection

        // Get all responses for display (including auto-graded ones)
        $allResponses = $attempt->responses()
            ->with(['question.questionType', 'question.options'])
            ->orderBy('id')
            ->get();
        
        // Log for debugging
        Log::info('Question Module Grading - Show', [
            'attempt_id' => $attemptId,
            'total_responses' => $allResponses->count(),
            'responses_needing_grading' => $responsesNeedingGrading->count(),
            'responses_data' => $allResponses->map(function($r) {
                return [
                    'id' => $r->id,
                    'question_id' => $r->question_id,
                    'question_type' => $r->question->questionType->name ?? 'unknown',
                    'has_student_answer' => !empty($r->student_answer),
                    'student_answer_type' => gettype($r->student_answer),
                    'student_answer_value' => is_array($r->student_answer) ? json_encode($r->student_answer) : $r->student_answer,
                    'is_correct' => $r->is_correct,
                    'score_obtained' => $r->score_obtained,
                ];
            })->toArray(),
        ]);

        return view('admin.pages.question-module-grading.show', compact('attempt', 'responsesNeedingGrading', 'allResponses'));
    }

    /**
     * Grade a specific response.
     */
    public function gradeResponse(Request $request, $responseId)
    {
        $validated = $request->validate([
            'is_correct' => 'nullable',
            'score_obtained' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:1000',
        ]);
        
        // Handle is_correct - convert string '1'/'0'/'true'/'false' to boolean
        $isCorrect = null;
        if ($request->has('is_correct') && $request->input('is_correct') !== null && $request->input('is_correct') !== '') {
            $isCorrectValue = $request->input('is_correct');
            if ($isCorrectValue === '1' || $isCorrectValue === 1 || $isCorrectValue === true || $isCorrectValue === 'true') {
                $isCorrect = true;
            } elseif ($isCorrectValue === '0' || $isCorrectValue === 0 || $isCorrectValue === false || $isCorrectValue === 'false') {
                $isCorrect = false;
            }
        }

        try {
            $response = QuestionModuleResponse::with(['attempt', 'question'])->findOrFail($responseId);

            // Ensure score doesn't exceed max_score
            $scoreObtained = min($validated['score_obtained'], $response->max_score);

            // Determine is_correct: if explicitly set, use it; otherwise, true only if score equals max_score
            $finalIsCorrect = $isCorrect;
            if ($finalIsCorrect === null) {
                // Auto-determine: correct only if score equals max_score
                $finalIsCorrect = ($scoreObtained >= $response->max_score);
            }
            
            $response->update([
                'is_correct' => $finalIsCorrect,
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
                ->where(function($query) {
                    $query->whereNull('is_correct')
                          ->orWhereNull('score_obtained');
                })
                ->count();

            if ($ungradedCount > 0) {
                return redirect()->back()
                    ->with('error', 'يجب تصحيح جميع الإجابات أولاً. لا يزال هناك ' . $ungradedCount . ' سؤال يحتاج تصحيح.');
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

