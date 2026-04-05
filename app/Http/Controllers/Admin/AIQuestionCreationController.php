<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\AIModel;
use App\Models\Course;
use App\Models\LaravelAiModel;
use App\Models\Lesson;
use App\Models\ProgrammingLanguage;
use App\Models\QuestionType;
use App\Models\Quiz;
use App\Services\Ai\AIModelService;
use App\Services\Ai\AIQuestionCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AIQuestionCreationController extends Controller
{
    use UsesLaravelAiSdkForWizards;

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

        $useLaravelAiEngine = $this->wizardUsesLaravelAiSdk('questions_engine');
        $laravelAiModels = LaravelAiModel::query()->activeOrdered()->get();
        $questionsEngineChoiceAvailable = $models->isNotEmpty() && $laravelAiModels->isNotEmpty();

        $quiz = null;
        if ($request->filled('quiz_id')) {
            $quiz = Quiz::findOrFail($request->query('quiz_id'));
        }

        $courseIdForLessons = $request->query('course_id') ?: old('course_id');
        if ($courseIdForLessons) {
            $lessons = Lesson::whereHas('module.section', function ($q) use ($courseIdForLessons) {
                $q->where('course_id', $courseIdForLessons);
            })->where('is_published', true)->orderBy('title')->get();
        }

        return view('admin.ai.question-creation.create', compact(
            'courses',
            'lessons',
            'models',
            'questionTypes',
            'programmingLanguages',
            'difficulties',
            'quiz',
            'useLaravelAiEngine',
            'laravelAiModels',
            'questionsEngineChoiceAvailable',
        ));
    }

    /**
     * إنشاء الأسئلة مباشرة في بنك الأسئلة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_type' => 'required|in:lesson_content,manual_text,topic',
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => [
                'nullable',
                'required_if:source_type,lesson_content',
                Rule::exists('lessons', 'id')->where(function ($query) use ($request) {
                    $query->where('is_published', true)
                        ->whereHas('module.section', fn ($q) => $q->where('course_id', (int) $request->input('course_id')));
                }),
            ],
            'lesson_name' => [
                Rule::requiredIf(fn () => in_array($request->input('source_type'), ['manual_text', 'topic'], true)),
                'nullable',
                'string',
                'max:255',
            ],
            'source_content' => 'required_if:source_type,manual_text,topic|string',
            'programming_language_id' => 'required|exists:programming_languages,id',
            'question_types' => 'required|array|min:1',
            'question_types.*' => 'exists:question_types,id',
            'number_of_questions' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:easy,medium,hard,mixed',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'questions_engine' => 'nullable|in:laravel_ai,legacy',
            'quiz_id' => 'nullable|exists:quizzes,id',
        ], [
            'source_type.required' => 'نوع المصدر مطلوب',
            'course_id.required' => 'الكورس مطلوب لمطابقة بنك الأسئلة',
            'source_content.required_if' => 'المحتوى المصدر مطلوب',
            'programming_language_id.required' => 'اللغة مطلوبة',
            'programming_language_id.exists' => 'اللغة المختارة غير موجودة',
            'question_types.required' => 'يجب اختيار نوع واحد على الأقل من أنواع الأسئلة',
            'question_types.min' => 'يجب اختيار نوع واحد على الأقل من أنواع الأسئلة',
            'number_of_questions.required' => 'عدد الأسئلة مطلوب',
            'lesson_name.required' => 'اسم الدرس مطلوب لمطابقة بنك الأسئلة',
        ]);

        try {
            $requestedEngine = $validated['questions_engine'] ?? null;
            if ($requestedEngine === 'laravel_ai' && ! LaravelAiModel::query()->where('is_active', true)->exists()) {
                return redirect()->back()
                    ->with('error', 'لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «موديلات Laravel AI SDK» أو اختر المحرك القديم.')
                    ->withInput();
            }

            $useLaravel = $this->resolveWizardAiEngine($requestedEngine, 'questions_engine');
            $legacyModel = null;
            $laraModel = null;

            if ($useLaravel) {
                if (! empty($validated['laravel_ai_model_id'])) {
                    $laraModel = LaravelAiModel::query()
                        ->where('id', $validated['laravel_ai_model_id'])
                        ->where('is_active', true)
                        ->first();
                    if (! $laraModel) {
                        return redirect()->back()
                            ->with('error', 'موديل Laravel AI المحدد غير متاح أو غير نشط.')
                            ->withInput();
                    }
                } else {
                    $laraModel = LaravelAiModel::query()->activeOrdered()->forCapability('questions.generate')->first()
                        ?? LaravelAiModel::query()->activeOrdered()->first();
                    if (! $laraModel) {
                        return redirect()->back()
                            ->with('error', 'لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «موديلات Laravel AI SDK».')
                            ->withInput();
                    }
                }
            } else {
                $legacyModel = $validated['ai_model_id']
                    ? AIModel::find($validated['ai_model_id'])
                    : null;
            }

            $programmingLanguage = ProgrammingLanguage::findOrFail($validated['programming_language_id']);
            $questionTypes = QuestionType::whereIn('id', $validated['question_types'])->get();

            $baseOptions = [
                'user' => Auth::user(),
                'number_of_questions' => $validated['number_of_questions'],
                'difficulty_level' => $validated['difficulty_level'],
                'course_id' => (int) $validated['course_id'],
            ];
            if ($useLaravel) {
                $baseOptions['laravel_model'] = $laraModel;
            } else {
                $baseOptions['model'] = $legacyModel;
            }

            if ($validated['source_type'] === 'lesson_content') {
                $lesson = Lesson::findOrFail($validated['lesson_id']);
                $options = array_merge($baseOptions, [
                    'lesson_name' => $lesson->title,
                ]);
                $questions = $this->creationService->createQuestionsFromLesson(
                    $lesson,
                    $programmingLanguage,
                    $questionTypes,
                    $options
                );
            } elseif ($validated['source_type'] === 'topic') {
                $options = array_merge($baseOptions, [
                    'lesson_name' => $validated['lesson_name'],
                ]);
                $questions = $this->creationService->createQuestionsFromTopic(
                    $validated['source_content'],
                    $programmingLanguage,
                    $questionTypes,
                    $options
                );
            } else {
                $options = array_merge($baseOptions, [
                    'lesson_name' => $validated['lesson_name'],
                ]);
                $questions = $this->creationService->createQuestionsFromText(
                    $validated['source_content'],
                    $programmingLanguage,
                    $questionTypes,
                    array_merge($options, ['source_type' => 'manual_text'])
                );
            }

            // ربط الأسئلة بالاختبار إذا كان quiz_id موجوداً
            if ($request->filled('quiz_id')) {
                $quiz = Quiz::findOrFail($validated['quiz_id']);

                DB::beginTransaction();
                try {
                    $maxOrder = DB::table('quiz_questions')
                        ->where('quiz_id', $quiz->id)
                        ->max('question_order') ?? 0;

                    $addedCount = 0;
                    foreach ($questions as $question) {
                        $exists = DB::table('quiz_questions')
                            ->where('quiz_id', $quiz->id)
                            ->where('question_id', $question->id)
                            ->exists();

                        if (! $exists) {
                            $maxOrder++;
                            $quiz->questions()->attach($question->id, [
                                'question_order' => $maxOrder,
                                'question_grade' => $question->default_grade,
                                'is_required' => false,
                            ]);
                            $addedCount++;
                        }
                    }

                    $maxScore = $quiz->calculateMaxScore();
                    $quiz->update(['max_score' => $maxScore]);

                    DB::commit();

                    $message = $addedCount > 0
                        ? 'تم إنشاء '.$questions->count().' سؤال بنجاح وربط '.$addedCount.' سؤال بالاختبار "'.$quiz->title.'".'
                        : 'تم إنشاء '.$questions->count().' سؤال بنجاح. جميع الأسئلة موجودة مسبقاً في الاختبار.';

                    return redirect()->route('quizzes.manage-questions', $quiz->id)
                        ->with('success', $message);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error linking questions to quiz: '.$e->getMessage());

                    return redirect()->route('quizzes.manage-questions', $quiz->id)
                        ->with('error', 'تم إنشاء الأسئلة بنجاح ولكن حدث خطأ أثناء ربطها بالاختبار: '.$e->getMessage());
                }
            }

            return redirect()->route('question-bank.index')
                ->with('success', 'تم إنشاء '.$questions->count().' سؤال بنجاح في بنك الأسئلة.');
        } catch (\Exception $e) {
            Log::error('Error creating questions: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إنشاء الأسئلة: '.$e->getMessage())
                ->withInput();
        }
    }
}
