<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\AIModel;
use App\Models\AIQuestionGeneration;
use App\Models\Course;
use App\Models\LaravelAiModel;
use App\Models\Lesson;
use App\Models\ProgrammingLanguage;
use App\Models\QuestionType;
use App\Models\Quiz;
use App\Services\Ai\AIModelService;
use App\Services\Ai\AIQuestionGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuestionBankAiGenerationController extends Controller
{
    use UsesLaravelAiSdkForWizards;

    public function __construct(
        private AIQuestionGenerationService $generationService,
        private AIModelService $modelService
    ) {}

    public function create(Request $request)
    {
        return $this->renderCreateForm($request);
    }

    public function createForQuiz(Quiz $quiz, Request $request)
    {
        return $this->renderCreateForm($request, $quiz);
    }

    public function store(Request $request)
    {
        return $this->processStore($request);
    }

    public function storeForQuiz(Quiz $quiz, Request $request)
    {
        return $this->processStore($request, $quiz);
    }

    public function review(AIQuestionGeneration $generation)
    {
        return $this->renderReview($generation);
    }

    public function reviewForQuiz(Quiz $quiz, AIQuestionGeneration $generation)
    {
        $this->ensureGenerationBelongsToQuiz($generation, $quiz);

        return $this->renderReview($generation, $quiz);
    }

    public function saveAll(AIQuestionGeneration $generation)
    {
        return $this->processSaveAll($generation);
    }

    public function saveAllForQuiz(Quiz $quiz, AIQuestionGeneration $generation)
    {
        $this->ensureGenerationBelongsToQuiz($generation, $quiz);

        return $this->processSaveAll($generation, $quiz);
    }

    public function saveSelected(Request $request, AIQuestionGeneration $generation)
    {
        return $this->processSaveSelected($request, $generation);
    }

    public function saveSelectedForQuiz(Request $request, Quiz $quiz, AIQuestionGeneration $generation)
    {
        $this->ensureGenerationBelongsToQuiz($generation, $quiz);

        return $this->processSaveSelected($request, $generation, $quiz);
    }

    public function saveOne(AIQuestionGeneration $generation, int $index)
    {
        return $this->processSaveOne($generation, $index);
    }

    public function saveOneForQuiz(Quiz $quiz, AIQuestionGeneration $generation, int $index)
    {
        $this->ensureGenerationBelongsToQuiz($generation, $quiz);

        return $this->processSaveOne($generation, $index, $quiz);
    }

    public function regenerate(AIQuestionGeneration $generation)
    {
        return $this->processRegenerate($generation);
    }

    public function regenerateForQuiz(Quiz $quiz, AIQuestionGeneration $generation)
    {
        $this->ensureGenerationBelongsToQuiz($generation, $quiz);

        return $this->processRegenerate($generation, $quiz);
    }

    private function renderCreateForm(Request $request, ?Quiz $quiz = null)
    {
        $courses = Course::where('is_published', true)->orderBy('title')->get();
        $lessons = collect();
        $models = $this->modelService->getAvailableModels('question_generation');
        $questionTypes = QuestionType::active()->orderBy('display_name')->get();
        $programmingLanguages = ProgrammingLanguage::active()->orderBy('sort_order')->get();
        $difficulties = [
            'easy' => 'سهل',
            'medium' => 'متوسط',
            'hard' => 'صعب',
            'mixed' => 'مختلط',
        ];

        $useLaravelAiEngine = $this->wizardUsesLaravelAiSdk('questions_engine');
        $laravelAiModels = LaravelAiModel::query()->activeOrdered()->get();
        $questionsEngineChoiceAvailable = $models->isNotEmpty() && $laravelAiModels->isNotEmpty();

        $prefillCourseId = $quiz?->course_id
            ?? $request->query('course_id', old('course_id'));
        $prefillLessonId = $quiz?->lesson_id
            ?? $request->query('lesson_id', old('lesson_id'));
        $prefillDifficulty = $request->query('difficulty', old('difficulty_level'));
        $prefillLanguageId = $request->query('language_id', old('programming_language_id'));

        if ($prefillCourseId) {
            $lessons = Lesson::whereHas('module.section', function ($q) use ($prefillCourseId) {
                $q->where('course_id', $prefillCourseId);
            })->where('is_published', true)->orderBy('title')->get();
        }

        return view('admin.pages.question-bank.ai-generate.create', compact(
            'courses',
            'lessons',
            'models',
            'questionTypes',
            'programmingLanguages',
            'difficulties',
            'useLaravelAiEngine',
            'laravelAiModels',
            'questionsEngineChoiceAvailable',
            'prefillCourseId',
            'prefillLessonId',
            'prefillDifficulty',
            'prefillLanguageId',
            'quiz',
        ));
    }

    private function processStore(Request $request, ?Quiz $quiz = null)
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
            'default_grade' => 'required|numeric|min:0.5|max:100',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'questions_engine' => 'nullable|in:laravel_ai,legacy',
        ], [
            'course_id.required' => 'الكورس مطلوب',
            'source_content.required_if' => 'المحتوى المصدر مطلوب',
            'programming_language_id.required' => 'اللغة مطلوبة',
            'question_types.required' => 'يجب اختيار نوع واحد على الأقل من أنواع الأسئلة',
            'lesson_name.required' => 'اسم الدرس مطلوب',
        ]);

        if ($quiz !== null) {
            if ((int) $validated['course_id'] !== (int) $quiz->course_id) {
                return redirect()->back()
                    ->with('error', 'يجب أن يطابق الكورس المحدد كورس الاختبار.')
                    ->withInput();
            }
        }

        try {
            $requestedEngine = $validated['questions_engine'] ?? null;
            if ($requestedEngine === 'laravel_ai' && ! LaravelAiModel::query()->where('is_active', true)->exists()) {
                return redirect()->back()
                    ->with('error', 'لا يوجد موديل Laravel AI نشط.')
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
                            ->with('error', 'موديل Laravel AI المحدد غير متاح.')
                            ->withInput();
                    }
                } else {
                    $laraModel = LaravelAiModel::query()->activeOrdered()->forCapability('questions.generate')->first()
                        ?? LaravelAiModel::query()->activeOrdered()->first();
                    if (! $laraModel) {
                        return redirect()->back()
                            ->with('error', 'لا يوجد موديل Laravel AI نشط.')
                            ->withInput();
                    }
                }
            } else {
                $legacyModel = ! empty($validated['ai_model_id'])
                    ? AIModel::find($validated['ai_model_id'])
                    : null;
            }

            $baseOptions = [
                'user' => Auth::user(),
                'course_id' => (int) $validated['course_id'],
                'programming_language_id' => (int) $validated['programming_language_id'],
                'question_type_ids' => $validated['question_types'],
                'number_of_questions' => $validated['number_of_questions'],
                'difficulty_level' => $validated['difficulty_level'],
                'default_grade' => $validated['default_grade'],
                'source_type' => $validated['source_type'],
            ];

            if ($quiz !== null) {
                $baseOptions['quiz_id'] = $quiz->id;
            }

            if ($useLaravel) {
                $baseOptions['laravel_model'] = $laraModel;
            } else {
                $baseOptions['model'] = $legacyModel;
            }

            if ($validated['source_type'] === 'lesson_content') {
                $lesson = Lesson::findOrFail($validated['lesson_id']);
                $generation = $this->generationService->createQuestionBankGeneration(array_merge($baseOptions, [
                    'lesson_id' => $lesson->id,
                    'lesson_name' => $lesson->title,
                    'source_content' => $lesson->description ?? $lesson->title,
                ]));
            } elseif ($validated['source_type'] === 'topic') {
                $generation = $this->generationService->createQuestionBankGeneration(array_merge($baseOptions, [
                    'lesson_name' => $validated['lesson_name'],
                    'source_content' => $validated['source_content'],
                ]));
            } else {
                $generation = $this->generationService->createQuestionBankGeneration(array_merge($baseOptions, [
                    'lesson_name' => $validated['lesson_name'],
                    'source_content' => $validated['source_content'],
                ]));
            }

            return redirect()
                ->to($this->reviewRoute($generation, $quiz))
                ->with('success', $this->completedFlashMessage($generation, quiz: $quiz));
        } catch (\Exception $e) {
            Log::error('Question bank AI generation failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء توليد الأسئلة: '.$e->getMessage())
                ->withInput();
        }
    }

    private function renderReview(AIQuestionGeneration $generation, ?Quiz $quiz = null)
    {
        $generation->refresh();
        $generation->load(['user', 'course', 'lesson', 'model', 'laravelAiModel', 'programmingLanguage', 'quiz']);

        if (! is_array($generation->generated_questions)) {
            $generation->generated_questions = json_decode($generation->generated_questions ?? '[]', true) ?? [];
        }

        if ($quiz === null && $generation->quiz_id) {
            $quiz = $generation->quiz;
        }

        return view('admin.pages.question-bank.ai-generate.review', compact('generation', 'quiz'));
    }

    private function processSaveAll(AIQuestionGeneration $generation, ?Quiz $quiz = null)
    {
        $quiz = $this->resolveQuizForGeneration($generation, $quiz);

        try {
            $questions = $this->generationService->saveGeneratedQuestions($generation);

            if ($questions->isEmpty()) {
                return redirect()->back()->with('info', $this->alreadySavedMessage($quiz));
            }

            return redirect()
                ->to($this->reviewRoute($generation, $quiz))
                ->with('success', $this->savedSuccessMessage($questions->count(), $quiz));
        } catch (\Exception $e) {
            Log::error('Error saving all AI questions: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء الحفظ: '.$e->getMessage());
        }
    }

    private function processSaveSelected(Request $request, AIQuestionGeneration $generation, ?Quiz $quiz = null)
    {
        $quiz = $this->resolveQuizForGeneration($generation, $quiz);

        $validated = $request->validate([
            'selected_questions' => 'required|array|min:1',
            'selected_questions.*' => 'integer|min:0',
        ]);

        try {
            $selectedIndices = array_map('intval', $validated['selected_questions']);
            $questions = $this->generationService->saveGeneratedQuestions($generation, $selectedIndices);

            if ($questions->isEmpty()) {
                return redirect()->back()->with('info', 'الأسئلة المحددة محفوظة مسبقاً أو غير متوفرة.');
            }

            return redirect()
                ->to($this->reviewRoute($generation, $quiz))
                ->with('success', $this->savedSuccessMessage($questions->count(), $quiz));
        } catch (\Exception $e) {
            Log::error('Error saving selected AI questions: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء الحفظ: '.$e->getMessage());
        }
    }

    private function processSaveOne(AIQuestionGeneration $generation, int $index, ?Quiz $quiz = null)
    {
        $quiz = $this->resolveQuizForGeneration($generation, $quiz);

        try {
            if ($generation->isIndexSaved($index)) {
                return redirect()->back()->with('info', $this->alreadySavedMessage($quiz));
            }

            $question = $this->generationService->saveSingleQuestion($generation, $index);

            if (! $question) {
                return redirect()->back()->with('error', 'تعذر حفظ السؤال المحدد.');
            }

            return redirect()
                ->to($this->reviewRoute($generation, $quiz))
                ->with('success', $quiz
                    ? 'تم حفظ السؤال في بنك الأسئلة وربطه بالاختبار بنجاح.'
                    : 'تم حفظ السؤال في بنك الأسئلة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error saving single AI question: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء الحفظ: '.$e->getMessage());
        }
    }

    private function processRegenerate(AIQuestionGeneration $generation, ?Quiz $quiz = null)
    {
        set_time_limit(180);

        try {
            $generation->update([
                'status' => 'pending',
                'generated_questions' => null,
                'saved_indices' => [],
                'saved_question_ids' => [],
                'error_message' => null,
            ]);

            $this->generationService->processGeneration($generation->fresh());

            return redirect()
                ->to($this->reviewRoute($generation, $quiz))
                ->with('success', $this->completedFlashMessage($generation->fresh(), 'تم إعادة التوليد.', $quiz));
        } catch (\Exception $e) {
            Log::error('Error regenerating question bank AI batch: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء إعادة التوليد: '.$e->getMessage());
        }
    }

    private function ensureGenerationBelongsToQuiz(AIQuestionGeneration $generation, Quiz $quiz): void
    {
        if ((int) $generation->quiz_id !== (int) $quiz->id) {
            throw new NotFoundHttpException();
        }
    }

    private function reviewRoute(AIQuestionGeneration $generation, ?Quiz $quiz = null): string
    {
        if ($quiz !== null) {
            return route('quizzes.ai-generate.review', [$quiz, $generation]);
        }

        if ($generation->quiz_id) {
            return route('quizzes.ai-generate.review', [$generation->quiz_id, $generation]);
        }

        return route('question-bank.ai-generate.review', $generation);
    }

    private function savedSuccessMessage(int $count, ?Quiz $quiz = null): string
    {
        if ($quiz) {
            return 'تم حفظ '.$count.' سؤال في بنك الأسئلة وربطها بالاختبار «'.$quiz->title.'».';
        }

        return 'تم حفظ '.$count.' سؤال في بنك الأسئلة.';
    }

    private function resolveQuizForGeneration(AIQuestionGeneration $generation, ?Quiz $quiz = null): ?Quiz
    {
        if ($quiz !== null) {
            return $quiz;
        }

        if ($generation->quiz_id) {
            return Quiz::find($generation->quiz_id);
        }

        return null;
    }

    private function alreadySavedMessage(?Quiz $quiz = null): string
    {
        if ($quiz) {
            return 'جميع الأسئلة محفوظة مسبقاً في بنك الأسئلة ومربوطة بالاختبار.';
        }

        return 'جميع الأسئلة محفوظة مسبقاً في بنك الأسئلة.';
    }

    private function completedFlashMessage(AIQuestionGeneration $generation, string $lead = 'تم إكمال التوليد.', ?Quiz $quiz = null): string
    {
        $count = is_array($generation->generated_questions) ? count($generation->generated_questions) : 0;

        $parts = [
            $lead,
            "تم توليد {$count} سؤالاً جاهزاً للمراجعة.",
        ];

        if ($quiz) {
            $parts[] = 'راجع الأسئلة ثم احفظها — ستُضاف تلقائياً للاختبار وبنك الأسئلة.';
        } else {
            $parts[] = 'راجع الأسئلة ثم احفظ الكل أو احفظ كل سؤال على حدة.';
        }

        if ($generation->status === 'completed' && filled($generation->error_message) && str_contains((string) $generation->error_message, 'سؤال')) {
            $parts[] = 'تنبيه: '.$generation->error_message;
        }

        return implode(' ', $parts);
    }
}
