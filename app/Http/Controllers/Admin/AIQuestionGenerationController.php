<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\AIModel;
use App\Models\AIQuestionGeneration;
use App\Models\Course;
use App\Models\LaravelAiModel;
use App\Models\Lesson;
use App\Services\Ai\AIModelService;
use App\Services\Ai\AIQuestionGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIQuestionGenerationController extends Controller
{
    use UsesLaravelAiSdkForWizards;

    public function __construct(
        private AIQuestionGenerationService $generationService,
        private AIModelService $modelService
    ) {}

    /**
     * قائمة طلبات التوليد
     */
    public function index()
    {
        $generations = AIQuestionGeneration::with(['user', 'lesson', 'model', 'laravelAiModel'])
            ->latest()
            ->paginate(20);

        return view('admin.ai.question-generations.index', compact('generations'));
    }

    /**
     * عرض نموذج توليد أسئلة
     */
    public function create(Request $request)
    {
        $courses = Course::where('is_published', true)->orderBy('title')->get();
        $lessons = collect();
        $models = $this->modelService->getAvailableModels('question_generation');
        $questionTypes = AIQuestionGeneration::QUESTION_TYPES;
        $difficulties = AIQuestionGeneration::DIFFICULTIES;

        $useLaravelAiEngine = $this->wizardUsesLaravelAiSdk('questions_engine');
        $laravelAiModels = $useLaravelAiEngine
            ? LaravelAiModel::query()->activeOrdered()->get()
            : collect();

        if ($request->filled('course_id')) {
            $lessons = Lesson::whereHas('module.section', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            })->where('is_published', true)->get();
        }

        return view('admin.ai.question-generations.create', compact(
            'courses',
            'lessons',
            'models',
            'questionTypes',
            'difficulties',
            'useLaravelAiEngine',
            'laravelAiModels',
        ));
    }

    /**
     * إنشاء طلب توليد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_type' => 'required|in:lesson_content,manual_text,topic',
            'lesson_id' => 'nullable|required_if:source_type,lesson_content|exists:lessons,id',
            'source_content' => 'required_if:source_type,manual_text,topic|string',
            'question_type' => 'required|in:'.implode(',', array_keys(AIQuestionGeneration::QUESTION_TYPES)),
            'number_of_questions' => 'required|integer|min:1|max:50',
            'difficulty_level' => 'required|in:'.implode(',', array_keys(AIQuestionGeneration::DIFFICULTIES)),
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
        ], [
            'source_type.required' => 'نوع المصدر مطلوب',
            'source_content.required_if' => 'المحتوى المصدر مطلوب',
            'question_type.required' => 'نوع السؤال مطلوب',
            'number_of_questions.required' => 'عدد الأسئلة مطلوب',
        ]);

        try {
            $useLaravel = $this->wizardUsesLaravelAiSdk('questions_engine');
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

            $genOptions = [
                'user' => Auth::user(),
                'model' => $legacyModel,
                'laravel_model' => $laraModel,
                'question_type' => $validated['question_type'],
                'number_of_questions' => $validated['number_of_questions'],
                'difficulty_level' => $validated['difficulty_level'],
            ];

            if ($validated['source_type'] === 'lesson_content') {
                $lesson = Lesson::findOrFail($validated['lesson_id']);
                $generation = $this->generationService->generateFromLesson($lesson, $genOptions);
            } elseif ($validated['source_type'] === 'topic') {
                $generation = $this->generationService->generateFromTopic($validated['source_content'], $genOptions);
            } else {
                $generation = $this->generationService->generateFromText($validated['source_content'], $genOptions);
            }

            $generation->refresh();

            return redirect()->route('admin.ai.question-generations.show', $generation)
                ->with('success', $this->questionGenerationCompletedFlashMessage($generation, 'تم إكمال التوليد.'));
        } catch (\Exception $e) {
            Log::error('Error creating question generation: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إنشاء طلب التوليد: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض الأسئلة المولدة
     */
    public function show(AIQuestionGeneration $generation)
    {
        // تحديث البيانات من قاعدة البيانات
        $generation->refresh();

        // تحميل العلاقات
        $generation->load(['user', 'course', 'lesson', 'model', 'laravelAiModel']);

        // التأكد من أن generated_questions هو array
        if ($generation->generated_questions && ! is_array($generation->generated_questions)) {
            $generation->generated_questions = json_decode($generation->generated_questions, true) ?? [];
        }

        return view('admin.ai.question-generations.show', compact('generation'));
    }

    /**
     * معالجة الطلب (Queue)
     */
    public function process(AIQuestionGeneration $generation)
    {
        // زيادة وقت التنفيذ إلى 3 دقائق للطلبات الطويلة
        set_time_limit(180);

        try {
            $questions = $this->generationService->processGeneration($generation);
            $generation->refresh();

            return redirect()->back()
                ->with('success', $this->questionGenerationCompletedFlashMessage($generation, 'تم إكمال المعالجة.'));
        } catch (\Exception $e) {
            Log::error('Error processing generation: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء المعالجة: '.$e->getMessage());
        }
    }

    /**
     * حفظ الأسئلة المولدة
     */
    public function save(AIQuestionGeneration $generation)
    {
        try {
            $questions = $this->generationService->saveGeneratedQuestions($generation);

            return redirect()->route('question-bank.index')
                ->with('success', 'تم حفظ '.$questions->count().' سؤال في بنك الأسئلة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error saving generated questions: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الأسئلة: '.$e->getMessage());
        }
    }

    /**
     * حفظ الأسئلة المحددة فقط
     */
    public function saveSelected(Request $request, AIQuestionGeneration $generation)
    {
        $validated = $request->validate([
            'selected_questions' => 'required|array|min:1',
            'selected_questions.*' => 'integer|min:0',
        ]);

        try {
            $selectedIndices = array_map('intval', $validated['selected_questions']);
            $questions = $this->generationService->saveGeneratedQuestions($generation, $selectedIndices);

            return redirect()->route('question-bank.index')
                ->with('success', 'تم حفظ '.$questions->count().' سؤال في بنك الأسئلة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error saving selected questions: '.$e->getMessage(), [
                'generation_id' => $generation->id,
                'selected_indices' => $validated['selected_questions'] ?? [],
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الأسئلة: '.$e->getMessage());
        }
    }

    /**
     * إعادة توليد
     */
    /**
     * رسالة نجاح بعد التوليد: العدد + تذكير أن بنك الأسئلة يحتاج حفظاً يدوياً من هذه الصفحة.
     */
    private function questionGenerationCompletedFlashMessage(AIQuestionGeneration $generation, string $leadSentence): string
    {
        $items = $generation->generated_questions;
        $count = is_array($items) ? count($items) : 0;

        $parts = [
            $leadSentence,
            "تم توليد {$count} سؤالاً جاهزاً للمراجعة.",
            'لم تُضف الأسئلة بعد إلى بنك الأسئلة؛ بعد المراجعة استخدم «حفظ الكل» أو «حفظ المحدد» لإضافتها إلى البنك.',
        ];

        if ($generation->status === 'completed' && filled($generation->error_message) && str_contains((string) $generation->error_message, 'سؤال')) {
            $parts[] = 'تنبيه: '.$generation->error_message;
        }

        return implode(' ', $parts);
    }

    public function regenerate(AIQuestionGeneration $generation)
    {
        // زيادة وقت التنفيذ إلى 3 دقائق للطلبات الطويلة
        set_time_limit(180);

        try {
            $generation->update(['status' => 'pending']);
            $this->generationService->processGeneration($generation);
            $generation->refresh();

            return redirect()->back()
                ->with('success', $this->questionGenerationCompletedFlashMessage($generation, 'تم إعادة التوليد.'));
        } catch (\Exception $e) {
            Log::error('Error regenerating questions: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إعادة التوليد: '.$e->getMessage());
        }
    }
}
