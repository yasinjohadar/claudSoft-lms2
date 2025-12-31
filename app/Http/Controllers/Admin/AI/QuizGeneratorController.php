<?php

namespace App\Http\Controllers\Admin\AI;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\QuestionType;
use App\Services\AI\QuizGenerationService;
use App\Jobs\AI\GenerateQuizJob;
use Illuminate\Http\Request;

class QuizGeneratorController extends Controller
{
    protected QuizGenerationService $quizGenerator;

    public function __construct(QuizGenerationService $quizGenerator)
    {
        $this->quizGenerator = $quizGenerator;
    }

    /**
     * Show quiz generator form
     */
    public function index()
    {
        $courses = Course::where('is_published', true)->get();
        $questionTypes = QuestionType::active()->get();
        $aiProviders = \App\Models\AIProvider::active()->get();

        return view('admin.ai.quiz-generator', compact('courses', 'questionTypes', 'aiProviders'));
    }

    /**
     * Generate quiz
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'total_questions' => 'required|integer|min:5|max:100',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'exists:question_types,name',
            'difficulty' => 'required|in:easy,medium,hard',
            'time_limit' => 'nullable|integer|min:5|max:300',
            'provider_name' => 'nullable|string',
            'save_immediately' => 'boolean',
        ]);

        try {
            $specifications = [
                'total_questions' => $validated['total_questions'],
                'question_types' => $validated['question_types'],
                'difficulty' => $validated['difficulty'],
                'time_limit' => $validated['time_limit'] ?? 60,
            ];

            if ($request->boolean('async', false)) {
                // Queue job
                GenerateQuizJob::dispatch(
                    $validated['course_id'],
                    $specifications,
                    $validated['provider_name'] ?? null,
                    auth()->id()
                );

                return response()->json([
                    'success' => true,
                    'message' => 'تم بدء عملية إنشاء الاختبار في الخلفية',
                ]);
            }

            // Generate synchronously
            $result = $this->quizGenerator->generateCompleteQuiz(
                $validated['course_id'],
                $specifications,
                $validated['provider_name'] ?? null
            );

            // Save if requested
            if ($request->boolean('save_immediately')) {
                $quiz = $this->quizGenerator->saveQuiz(
                    $result['quiz'],
                    $validated['course_id'],
                    null,
                    $result['ai_request_id'] ?? null
                );

                return response()->json([
                    'success' => true,
                    'quiz' => $result['quiz'],
                    'quiz_id' => $quiz->id,
                    'message' => 'تم إنشاء وحفظ الاختبار بنجاح',
                ]);
            }

            return response()->json([
                'success' => true,
                'quiz' => $result['quiz'],
                'ai_request_id' => $result['ai_request_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء الاختبار: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Balance quiz difficulty
     */
    public function balance(Request $request, int $quizId)
    {
        try {
            $result = $this->quizGenerator->balanceQuiz(
                $quizId,
                $request->input('provider_name')
            );

            return response()->json([
                'success' => true,
                'analysis' => $result['analysis'],
                'suggestions' => $result['suggestions'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحليل الاختبار: ' . $e->getMessage(),
            ], 500);
        }
    }
}
