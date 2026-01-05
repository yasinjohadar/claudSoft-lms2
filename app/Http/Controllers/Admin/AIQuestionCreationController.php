<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\AIModel;
use App\Models\ProgrammingLanguage;
use App\Models\QuestionType;
use App\Services\Ai\AIQuestionCreationService;
use App\Services\Ai\AIModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIQuestionCreationController extends Controller
{
    public function __construct(
        private AIQuestionCreationService $creationService,
        private AIModelService $modelService
    ) {}

    /**
     * عرض نموذج إنشاء أسئلة
     */
    public function create(Request $request)
    {
        $courses = Course::where('is_published', true)->orderBy('title')->get();
        $lessons = collect();
        $models = $this->modelService->getAvailableModels('question_generation');
        $questionTypes = QuestionType::active()->orderBy('display_name')->get();
        $programmingLanguages = ProgrammingLanguage::active()->orderBy('display_name')->get();
        $difficulties = [
            'easy' => 'سهل',
            'medium' => 'متوسط',
            'hard' => 'صعب',
            'mixed' => 'مختلط',
        ];

        if ($request->filled('course_id')) {
            $lessons = Lesson::whereHas('module.section', function($q) use ($request) {
                $q->where('course_id', $request->course_id);
            })->where('is_published', true)->get();
        }

        return view('admin.ai.question-creation.create', compact(
            'courses',
            'lessons',
            'models',
            'questionTypes',
            'programmingLanguages',
            'difficulties'
        ));
    }

    /**
     * إنشاء الأسئلة مباشرة في بنك الأسئلة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_type' => 'required|in:lesson_content,manual_text,topic',
            'lesson_id' => 'nullable|required_if:source_type,lesson_content|exists:lessons,id',
            'source_content' => 'required_if:source_type,manual_text,topic|string',
            'programming_language_id' => 'required|exists:programming_languages,id',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'exists:question_types,id',
            'number_of_questions' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:easy,medium,hard,mixed',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'course_id' => 'nullable|exists:courses,id',
        ], [
            'source_type.required' => 'نوع المصدر مطلوب',
            'source_content.required_if' => 'المحتوى المصدر مطلوب',
            'programming_language_id.required' => 'اللغة مطلوبة',
            'programming_language_id.exists' => 'اللغة المختارة غير موجودة',
            'question_types.required' => 'يجب اختيار نوع واحد على الأقل من أنواع الأسئلة',
            'question_types.min' => 'يجب اختيار نوع واحد على الأقل من أنواع الأسئلة',
            'number_of_questions.required' => 'عدد الأسئلة مطلوب',
        ]);

        try {
            $model = $validated['ai_model_id'] 
                ? AIModel::find($validated['ai_model_id'])
                : null;

            $programmingLanguage = ProgrammingLanguage::findOrFail($validated['programming_language_id']);
            $questionTypes = QuestionType::whereIn('id', $validated['question_types'])->get();

            if ($validated['source_type'] === 'lesson_content') {
                $lesson = Lesson::findOrFail($validated['lesson_id']);
                $questions = $this->creationService->createQuestionsFromLesson(
                    $lesson,
                    $programmingLanguage,
                    $questionTypes,
                    [
                        'user' => Auth::user(),
                        'model' => $model,
                        'number_of_questions' => $validated['number_of_questions'],
                        'difficulty_level' => $validated['difficulty_level'],
                        'course_id' => $validated['course_id'] ?? null,
                    ]
                );
            } elseif ($validated['source_type'] === 'topic') {
                $questions = $this->creationService->createQuestionsFromTopic(
                    $validated['source_content'],
                    $programmingLanguage,
                    $questionTypes,
                    [
                        'user' => Auth::user(),
                        'model' => $model,
                        'number_of_questions' => $validated['number_of_questions'],
                        'difficulty_level' => $validated['difficulty_level'],
                        'course_id' => $validated['course_id'] ?? null,
                    ]
                );
            } else {
                $questions = $this->creationService->createQuestionsFromText(
                    $validated['source_content'],
                    $programmingLanguage,
                    $questionTypes,
                    [
                        'user' => Auth::user(),
                        'model' => $model,
                        'number_of_questions' => $validated['number_of_questions'],
                        'difficulty_level' => $validated['difficulty_level'],
                        'course_id' => $validated['course_id'] ?? null,
                    ]
                );
            }

            return redirect()->route('question-bank.index')
                           ->with('success', 'تم إنشاء ' . $questions->count() . ' سؤال بنجاح في بنك الأسئلة.');
        } catch (\Exception $e) {
            Log::error('Error creating questions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء الأسئلة: ' . $e->getMessage())
                           ->withInput();
        }
    }
}

