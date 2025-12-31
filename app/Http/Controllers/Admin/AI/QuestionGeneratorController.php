<?php

namespace App\Http\Controllers\Admin\AI;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuestionType;
use App\Services\AI\QuestionGenerationService;
use App\Jobs\AI\GenerateQuestionsJob;
use Illuminate\Http\Request;

class QuestionGeneratorController extends Controller
{
    protected QuestionGenerationService $questionGenerator;

    public function __construct(QuestionGenerationService $questionGenerator)
    {
        $this->questionGenerator = $questionGenerator;
    }

    /**
     * Show question generator form
     */
    public function index()
    {
        $courses = Course::where('is_published', true)->get();
        $questionTypes = QuestionType::active()->get();
        $aiProviders = \App\Models\AIProvider::active()->get();

        return view('admin.ai.question-generator', compact('courses', 'questionTypes', 'aiProviders'));
    }

    /**
     * Generate questions
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'count' => 'required|integer|min:1|max:50',
            'difficulty' => 'required|in:easy,medium,hard',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'exists:question_types,name',
            'provider_name' => 'nullable|string',
            'save_immediately' => 'boolean',
        ]);

        try {
            if ($request->boolean('async', false)) {
                // Queue job
                GenerateQuestionsJob::dispatch(
                    $validated['course_id'] ?? null,
                    $validated['lesson_id'] ?? null,
                    $validated['count'],
                    $validated['difficulty'],
                    $validated['question_types'],
                    $validated['provider_name'] ?? null,
                    auth()->id()
                );

                return response()->json([
                    'success' => true,
                    'message' => 'تم بدء عملية إنشاء الأسئلة في الخلفية',
                ]);
            }

            // Generate synchronously
            $result = $this->questionGenerator->generateQuestions(
                $validated['course_id'] ?? null,
                $validated['lesson_id'] ?? null,
                $validated['count'],
                $validated['difficulty'],
                $validated['question_types'],
                $validated['provider_name'] ?? null
            );

            // Save if requested
            if ($request->boolean('save_immediately')) {
                $questionIds = $this->questionGenerator->saveQuestions(
                    $result['questions'],
                    $validated['course_id'] ?? null,
                    $result['ai_request_id'] ?? null
                );

                return response()->json([
                    'success' => true,
                    'questions' => $result['questions'],
                    'saved_question_ids' => $questionIds,
                    'message' => 'تم إنشاء وحفظ الأسئلة بنجاح',
                ]);
            }

            return response()->json([
                'success' => true,
                'questions' => $result['questions'],
                'ai_request_id' => $result['ai_request_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء الأسئلة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enhance existing question
     */
    public function enhance(Request $request, int $questionId)
    {
        try {
            $result = $this->questionGenerator->enhanceQuestion(
                $questionId,
                $request->input('provider_name')
            );

            return response()->json([
                'success' => true,
                'enhanced_question' => $result['enhanced_question'],
                'suggestions' => $result['suggestions'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تحسين السؤال: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lessons for a course (AJAX)
     */
    public function getLessons(Request $request, int $courseId)
    {
        $lessons = Lesson::where('course_id', $courseId)
            ->orderBy('order')
            ->get(['id', 'title']);

        return response()->json($lessons);
    }
}
