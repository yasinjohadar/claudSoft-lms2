<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\AIModel;
use App\Models\Course;
use App\Models\LaravelAiModel;
use App\Models\LessonSimulator;
use App\Models\SimulatorAiGeneration;
use App\Services\Ai\AIModelService;
use App\Services\Simulator\SimulatorAiJobStarter;
use App\Services\Simulator\SimulatorAiPipelineService;
use App\Services\Simulator\SimulatorBundleStorage;
use App\Services\Simulator\SimulatorCategoryTree;
use App\Services\Simulator\SimulatorTopicRegistry;
use App\Support\SimulatorAiWizard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LessonSimulatorAiController extends Controller
{
    use UsesLaravelAiSdkForWizards;

    public function __construct(
        private SimulatorAiJobStarter $jobStarter,
        private SimulatorAiPipelineService $pipeline,
        private SimulatorBundleStorage $bundleStorage,
    ) {}

    public function create(): View
    {
        return view('admin.lesson-simulators.ai-create', array_merge(SimulatorAiWizard::viewData(), [
            'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
            'statuses' => LessonSimulator::STATUSES,
            'categoryOptions' => SimulatorCategoryTree::optionsForSelect(activeOnly: true),
            'bundle' => ['html' => '', 'css' => '', 'js' => ''],
        ]));
    }

    /** "توليد الآن" — runs the staged pipeline inline within the request. */
    public function generateSync(Request $request): JsonResponse
    {
        $validated = $this->validateGenerationRequest($request);

        try {
            $payload = $this->generationPayload($validated);

            $generation = SimulatorAiGeneration::query()->create([
                'user_id' => Auth::id(),
                'operation' => SimulatorAiGeneration::OPERATION_GENERATE,
                'status' => SimulatorAiGeneration::STATUS_QUEUED,
                'progress' => 0,
                'stage' => 'queued',
                'stage_label' => 'في الطابور…',
                'payload' => $payload,
            ]);

            $this->pipeline->run($generation);
            $generation->refresh();

            if ($generation->status !== SimulatorAiGeneration::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => $generation->error_message ?: 'تعذّر إكمال التوليد.',
                ], 422);
            }

            $result = $generation->result;

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $result['title'] ?? '',
                    'description' => $result['description'] ?? $validated['topic_description'],
                    'topic_key' => $payload['topic_key'],
                    'html' => $result['bundle']['html'] ?? '',
                    'css' => $result['bundle']['css'] ?? '',
                    'js' => $result['bundle']['js'] ?? '',
                    'meta' => [
                        'archetype' => $result['archetype'] ?? null,
                        'lang_code' => $result['lang_code'] ?? null,
                        'text_direction' => $result['text_direction'] ?? null,
                    ],
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            Log::error('Simulator AI sync generate failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /** "تطبيق التعديلات" — runs the refine operation inline within the request. */
    public function refineBundle(Request $request): JsonResponse
    {
        $validated = $this->validateRefineRequest($request);

        try {
            $engine = $this->resolveEngine($validated['simulators_engine'] ?? null);
            $this->assertActiveModel($engine, $validated);

            $payload = [
                'bundle_html' => $validated['bundle_html'],
                'bundle_css' => $validated['bundle_css'] ?? '',
                'bundle_js' => $validated['bundle_js'] ?? '',
                'instructions' => $validated['instructions'],
                'title' => $validated['title'] ?? '',
                'engine' => $engine,
                'ai_model_id' => $validated['ai_model_id'] ?? null,
                'laravel_ai_model_id' => $validated['laravel_ai_model_id'] ?? null,
            ];

            $generation = SimulatorAiGeneration::query()->create([
                'user_id' => Auth::id(),
                'operation' => SimulatorAiGeneration::OPERATION_REFINE,
                'status' => SimulatorAiGeneration::STATUS_QUEUED,
                'progress' => 0,
                'stage' => 'queued',
                'stage_label' => 'في الطابور…',
                'payload' => $payload,
            ]);

            $this->pipeline->run($generation);
            $generation->refresh();

            if ($generation->status !== SimulatorAiGeneration::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => $generation->error_message ?: 'تعذّر تطبيق التعديلات.',
                ], 422);
            }

            $result = $generation->result;

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $result['title'] ?? '',
                    'html' => $result['bundle']['html'] ?? '',
                    'css' => $result['bundle']['css'] ?? '',
                    'js' => $result['bundle']['js'] ?? '',
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            Log::error('Simulator AI refine failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /** "توليد في الخلفية" — creates the draft simulator and queues the staged pipeline. */
    public function storeAsync(Request $request): RedirectResponse
    {
        $validated = $this->validateGenerationRequest($request);

        try {
            $payload = $this->generationPayload($validated);
            $title = Str::limit(trim($validated['topic_description']), 255);

            $simulator = LessonSimulator::create([
                'title' => $title,
                'slug' => LessonSimulator::uniqueSlug($title),
                'description' => $validated['topic_description'],
                'topic_key' => $payload['topic_key'],
                'simulator_category_id' => $validated['simulator_category_id'] ?? null,
                'render_mode' => 'html_bundle',
                'spec_json' => ['meta' => [], 'sections' => []],
                'spec_version' => config('simulator.spec_version', '1.0'),
                'status' => 'draft',
                'languages' => [$validated['primary_language']],
                'ai_generation_meta' => ['status' => 'pending'],
                'created_by' => Auth::id(),
            ]);

            $this->jobStarter->start(Auth::user(), SimulatorAiGeneration::OPERATION_GENERATE, $payload, $simulator->id);

            return redirect()
                ->route('admin.lesson-simulators.ai.review', $simulator)
                ->with('success', 'تم إرسال طلب التوليد — انتظر اكتمال المعالجة في الخلفية.');
        } catch (\Throwable $e) {
            Log::error('Simulator AI async store failed: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'تعذّر بدء التوليد: '.$e->getMessage());
        }
    }

    public function review(LessonSimulator $lessonSimulator): View|RedirectResponse
    {
        if (! $lessonSimulator->isHtmlBundle()) {
            return redirect()
                ->route('admin.lesson-simulators.edit', $lessonSimulator)
                ->with('warning', 'هذه المحاكاة ليست من نوع HTML bundle.');
        }

        $generation = $this->latestGeneration($lessonSimulator);
        $status = $generation?->status;

        $bundle = ['html' => '', 'css' => '', 'js' => ''];
        if ($status === SimulatorAiGeneration::STATUS_COMPLETED && $lessonSimulator->hasPlayableContent()) {
            $bundle = $this->bundleStorage->load($lessonSimulator->slug) ?? $bundle;
        }

        return view('admin.lesson-simulators.ai-review', array_merge(
            SimulatorAiWizard::viewData(),
            [
                'simulator' => $lessonSimulator->load('courses'),
                'generationStatus' => $status,
                'generationPayload' => $generation?->toStatusPayload(),
                'bundle' => $bundle,
                'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
                'statuses' => LessonSimulator::STATUSES,
                'categoryOptions' => SimulatorCategoryTree::optionsForSelect(activeOnly: true),
            ]
        ));
    }

    public function status(LessonSimulator $lessonSimulator): JsonResponse
    {
        $generation = $this->latestGeneration($lessonSimulator);

        if (! $generation) {
            return response()->json([
                'success' => true,
                'status' => 'unknown',
                'has_content' => $lessonSimulator->hasPlayableContent(),
                'review_url' => route('admin.lesson-simulators.ai.review', $lessonSimulator),
                'edit_url' => route('admin.lesson-simulators.edit', $lessonSimulator),
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        if ($generation->isStale()) {
            $generation->markPaused('توقّفت عملية التوليد دون استجابة من الخادم. الملفات المكتملة محفوظة — اضغط «متابعة التوليد».');
        }

        $payload = array_merge($generation->toStatusPayload(), [
            'has_content' => $lessonSimulator->hasPlayableContent(),
            'review_url' => route('admin.lesson-simulators.ai.review', $lessonSimulator),
            'edit_url' => route('admin.lesson-simulators.edit', $lessonSimulator),
        ]);

        if ($generation->status === SimulatorAiGeneration::STATUS_COMPLETED && $lessonSimulator->hasPlayableContent()) {
            $bundle = $this->bundleStorage->load($lessonSimulator->slug);
            if ($bundle) {
                $payload['bundle'] = [
                    'html' => $bundle['html'],
                    'css' => $bundle['css'],
                    'js' => $bundle['js'],
                ];
                $payload['title'] = $lessonSimulator->title;
            }
        }

        return response()->json($payload, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function regenerate(Request $request, LessonSimulator $lessonSimulator): JsonResponse|RedirectResponse
    {
        $validated = $this->validateGenerationRequest($request, allowPartialTopic: true);

        $topicDescription = trim($validated['topic_description']
            ?? $lessonSimulator->description
            ?? $lessonSimulator->title);

        if ($topicDescription === '') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'وصف الموضوع مطلوب.'], 422);
            }

            return back()->with('error', 'وصف الموضوع مطلوب.');
        }

        $validated['topic_description'] = $topicDescription;

        try {
            $payload = $this->generationPayload($validated, $lessonSimulator->topic_key);
            $mode = $request->input('mode', 'async');

            if ($mode === 'sync') {
                $generation = SimulatorAiGeneration::query()->create([
                    'user_id' => Auth::id(),
                    'lesson_simulator_id' => $lessonSimulator->id,
                    'operation' => SimulatorAiGeneration::OPERATION_GENERATE,
                    'status' => SimulatorAiGeneration::STATUS_QUEUED,
                    'progress' => 0,
                    'stage' => 'queued',
                    'stage_label' => 'في الطابور…',
                    'payload' => $payload,
                ]);

                $this->pipeline->run($generation);
                $generation->refresh();

                if ($generation->status !== SimulatorAiGeneration::STATUS_COMPLETED) {
                    $message = $generation->error_message ?: 'تعذّر إكمال التوليد.';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 422);
                    }

                    return back()->with('error', $message);
                }

                $result = $generation->result;

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'title' => $result['title'] ?? '',
                            'html' => $result['bundle']['html'] ?? '',
                            'css' => $result['bundle']['css'] ?? '',
                            'js' => $result['bundle']['js'] ?? '',
                        ],
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                }

                return redirect()
                    ->route('admin.lesson-simulators.ai.review', $lessonSimulator)
                    ->with('success', 'تم إعادة التوليد بنجاح.');
            }

            $lessonSimulator->update([
                'topic_key' => $payload['topic_key'],
                'description' => $topicDescription,
            ]);

            $this->jobStarter->start(Auth::user(), SimulatorAiGeneration::OPERATION_GENERATE, $payload, $lessonSimulator->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => 'queued',
                    'review_url' => route('admin.lesson-simulators.ai.review', $lessonSimulator),
                ]);
            }

            return redirect()
                ->route('admin.lesson-simulators.ai.review', $lessonSimulator)
                ->with('success', 'تم إرسال طلب إعادة التوليد.');
        } catch (\Throwable $e) {
            Log::error('Simulator AI regenerate failed: '.$e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', 'تعذّر إعادة التوليد: '.$e->getMessage());
        }
    }

    /**
     * Continue a paused/failed staged generation: only the phases that are not
     * done yet are regenerated, the plan and finished phases are reused.
     */
    public function resume(LessonSimulator $lessonSimulator): JsonResponse
    {
        $generation = $this->latestGeneration($lessonSimulator);

        if (! $generation || ! $generation->isResumable()) {
            return response()->json([
                'success' => false,
                'message' => $generation?->status === SimulatorAiGeneration::STATUS_COMPLETED
                    ? 'اكتمل التوليد بالفعل.'
                    : 'لا يوجد تقدم محفوظ لمتابعته. ابدأ توليداً جديداً.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $generation->update([
            'status' => SimulatorAiGeneration::STATUS_QUEUED,
            'stage' => 'resuming',
            'stage_label' => 'استئناف التوليد…',
            'error_message' => null,
            'finished_at' => null,
            'heartbeat_at' => now(),
        ]);

        $this->jobStarter->dispatch($generation);

        return response()->json([
            'success' => true,
            'job' => $generation->fresh()->toStatusPayload(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Stop a running/queued generation. Honored at the next safe checkpoint
     * (before the next attempt) — it cannot interrupt an AI call already in
     * flight, but it stops the next one from starting, so a stuck run ends
     * within one attempt instead of running indefinitely.
     */
    public function cancel(LessonSimulator $lessonSimulator): JsonResponse
    {
        $generation = $this->latestGeneration($lessonSimulator);

        if (! $generation || ! in_array($generation->status, [
            SimulatorAiGeneration::STATUS_QUEUED,
            SimulatorAiGeneration::STATUS_RUNNING,
        ], true)) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد توليد قيد التشغيل لإيقافه.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $generation->markCancelled('تم إيقاف التوليد بطلب من المستخدم.');

        return response()->json([
            'success' => true,
            'job' => $generation->fresh()->toStatusPayload(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function latestGeneration(LessonSimulator $lessonSimulator): ?SimulatorAiGeneration
    {
        return SimulatorAiGeneration::query()
            ->where('lesson_simulator_id', $lessonSimulator->id)
            ->latest('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRefineRequest(Request $request): array
    {
        return $request->validate([
            'instructions' => 'required|string|max:3000',
            'bundle_html' => 'required|string',
            'bundle_css' => 'nullable|string',
            'bundle_js' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'simulators_engine' => 'nullable|in:laravel_ai,legacy',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateGenerationRequest(Request $request, bool $allowPartialTopic = false): array
    {
        $topicRule = $allowPartialTopic ? 'nullable|string|max:500' : 'required|string|max:500';

        return $request->validate([
            'topic_description' => $topicRule,
            'topic_key' => 'nullable|string|max:255',
            'primary_language' => 'required|string|max:50',
            'level' => 'required|in:beginner,intermediate,advanced',
            'archetype' => 'nullable|in:playground,stepper,auto',
            'simulation_details' => 'nullable|string|max:2000',
            'output_language' => 'nullable|string|max:100',
            'simulator_category_id' => 'nullable|exists:simulator_categories,id',
            'simulators_engine' => 'nullable|in:laravel_ai,legacy',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function generationPayload(array $validated, ?string $fallbackTopicKey = null): array
    {
        $topicKey = trim($validated['topic_key'] ?? '');
        if ($topicKey === '' || SimulatorTopicRegistry::isCustomKey($topicKey)) {
            $topicKey = SimulatorTopicRegistry::customKeyFromDescription($validated['topic_description']);
        }
        if ($topicKey === 'custom.' && $fallbackTopicKey) {
            $topicKey = $fallbackTopicKey;
        }

        $engine = $this->resolveEngine($validated['simulators_engine'] ?? null);
        $this->assertActiveModel($engine, $validated);

        return [
            'topic_key' => $topicKey,
            'topic_description' => $validated['topic_description'],
            'simulation_details' => $validated['simulation_details'] ?? '',
            'primary_language' => $validated['primary_language'],
            'level' => $validated['level'],
            'archetype' => $validated['archetype'] ?? 'auto',
            'output_language' => $validated['output_language'] ?? 'العربية',
            'engine' => $engine,
            'ai_model_id' => $validated['ai_model_id'] ?? null,
            'laravel_ai_model_id' => $validated['laravel_ai_model_id'] ?? null,
        ];
    }

    private function resolveEngine(?string $requestedEngine): string
    {
        return $this->resolveWizardAiEngine($requestedEngine, 'simulators_engine') ? 'laravel_ai' : 'legacy';
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function assertActiveModel(string $engine, array $validated): void
    {
        if ($engine === 'laravel_ai') {
            $exists = ! empty($validated['laravel_ai_model_id'])
                ? LaravelAiModel::query()->where('id', $validated['laravel_ai_model_id'])->where('is_active', true)->exists()
                : LaravelAiModel::query()->where('is_active', true)->exists();

            if (! $exists) {
                throw new \RuntimeException('لا يوجد موديل Laravel AI نشط. أضف موديلاً أو اختر المحرك القديم.');
            }

            return;
        }

        $exists = ! empty($validated['ai_model_id'])
            ? AIModel::query()->where('id', $validated['ai_model_id'])->where('is_active', true)->exists()
            : app(AIModelService::class)->getAvailableModels('simulator_generation')->isNotEmpty()
                || app(AIModelService::class)->getAvailableModels('all')->isNotEmpty();

        if (! $exists) {
            throw new \RuntimeException('لا يوجد موديل AI (النظام القديم) متاح.');
        }
    }
}
