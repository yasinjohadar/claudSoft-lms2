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
use App\Services\Ai\AIModelService;
use App\Services\Ai\AIQuestionGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class QuestionBankAiGenerationController extends Controller
{
    use UsesLaravelAiSdkForWizards;

    public function __construct(
        private AIQuestionGenerationService $generationService,
        private AIModelService $modelService
    ) {}

    public function create(Request $request)
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

        $prefillCourseId = $request->query('course_id', old('course_id'));
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
            'prefillDifficulty',
            'prefillLanguageId',
        ));
    }

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
                ->route('question-bank.ai-generate.review', $generation)
                ->with('success', $this->completedFlashMessage($generation));
        } catch (\Exception $e) {
            Log::error('Question bank AI generation failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء توليد الأسئلة: '.$e->getMessage())
                ->withInput();
        }
    }

    public function review(AIQuestionGeneration $generation)
    {
        $generation->refresh();
        $generation->load(['user', 'course', 'lesson', 'model', 'laravelAiModel', 'programmingLanguage']);

        if (! is_array($generation->generated_questions)) {
            $generation->generated_questions = json_decode($generation->generated_questions ?? '[]', true) ?? [];
        }

        return view('admin.pages.question-bank.ai-generate.review', compact('generation'));
    }

    public function saveAll(AIQuestionGeneration $generation)
    {
        try {
            $questions = $this->generationService->saveGeneratedQuestions($generation);

            if ($questions->isEmpty()) {
                return redirect()->back()->with('info', 'جميع الأسئلة محفوظة مسبقاً في بنك الأسئلة.');
            }

            return redirect()
                ->route('question-bank.ai-generate.review', $generation)
                ->with('success', 'تم حفظ '.$questions->count().' سؤال في بنك الأسئلة.');
        } catch (\Exception $e) {
            Log::error('Error saving all AI questions: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء الحفظ: '.$e->getMessage());
        }
    }

    public function saveSelected(Request $request, AIQuestionGeneration $generation)
    {
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
                ->route('question-bank.ai-generate.review', $generation)
                ->with('success', 'تم حفظ '.$questions->count().' سؤال في بنك الأسئلة.');
        } catch (\Exception $e) {
            Log::error('Error saving selected AI questions: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء الحفظ: '.$e->getMessage());
        }
    }

    public function saveOne(AIQuestionGeneration $generation, int $index)
    {
        try {
            if ($generation->isIndexSaved($index)) {
                return redirect()->back()->with('info', 'هذا السؤال محفوظ مسبقاً في بنك الأسئلة.');
            }

            $question = $this->generationService->saveSingleQuestion($generation, $index);

            if (! $question) {
                return redirect()->back()->with('error', 'تعذر حفظ السؤال المحدد.');
            }

            return redirect()
                ->route('question-bank.ai-generate.review', $generation)
                ->with('success', 'تم حفظ السؤال في بنك الأسئلة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error saving single AI question: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء الحفظ: '.$e->getMessage());
        }
    }

    public function regenerate(AIQuestionGeneration $generation)
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
                ->route('question-bank.ai-generate.review', $generation)
                ->with('success', $this->completedFlashMessage($generation->fresh(), 'تم إعادة التوليد.'));
        } catch (\Exception $e) {
            Log::error('Error regenerating question bank AI batch: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء إعادة التوليد: '.$e->getMessage());
        }
    }

    private function completedFlashMessage(AIQuestionGeneration $generation, string $lead = 'تم إكمال التوليد.'): string
    {
        $count = is_array($generation->generated_questions) ? count($generation->generated_questions) : 0;

        $parts = [
            $lead,
            "تم توليد {$count} سؤالاً جاهزاً للمراجعة.",
            'راجع الأسئلة ثم احفظ الكل أو احفظ كل سؤال على حدة.',
        ];

        if ($generation->status === 'completed' && filled($generation->error_message) && str_contains((string) $generation->error_message, 'سؤال')) {
            $parts[] = 'تنبيه: '.$generation->error_message;
        }

        return implode(' ', $parts);
    }
}
