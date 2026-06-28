<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateLessonSimulatorJob;
use App\Models\AIModel;
use App\Models\Course;
use App\Models\LaravelAiModel;
use App\Models\LessonSimulator;
use App\Services\Ai\AIModelService;
use App\Services\Simulator\SimulatorBundleStorage;
use App\Services\Simulator\SimulatorGenerationService;
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
        private SimulatorGenerationService $generationService,
        private SimulatorBundleStorage $bundleStorage,
    ) {}

    public function create(): View
    {
        return view('admin.lesson-simulators.ai-create', array_merge(SimulatorAiWizard::viewData(), [
            'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
            'statuses' => LessonSimulator::STATUSES,
            'bundle' => ['html' => '', 'css' => '', 'js' => ''],
        ]));
    }

    public function generateSync(Request $request): JsonResponse
    {
        $validated = $this->validateGenerationRequest($request);

        try {
            [$topicKey, $options, $engine, $modelId] = $this->resolveGenerationContext($validated);

            $result = $this->generationService->generateHtmlBundle($topicKey, $options);

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $result['title'],
                    'description' => $validated['topic_description'],
                    'topic_key' => $topicKey,
                    'html' => $result['bundle']['html'],
                    'css' => $result['bundle']['css'],
                    'js' => $result['bundle']['js'],
                    'meta' => $result['meta'],
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

    public function refineBundle(Request $request): JsonResponse
    {
        $validated = $this->validateRefineRequest($request);

        $bundle = [
            'html' => $validated['bundle_html'],
            'css' => $validated['bundle_css'] ?? '',
            'js' => $validated['bundle_js'] ?? '',
        ];

        try {
            [, $options] = $this->resolveEngineContext($validated);

            $options['title'] = $validated['title'] ?? '';
            $options['topic_key'] = 'custom.refine';

            $result = $this->generationService->refineHtmlBundle(
                $bundle,
                $validated['instructions'],
                $options,
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $result['title'],
                    'html' => $result['bundle']['html'],
                    'css' => $result['bundle']['css'],
                    'js' => $result['bundle']['js'],
                    'meta' => $result['meta'],
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

    public function storeAsync(Request $request): RedirectResponse
    {
        $validated = $this->validateGenerationRequest($request);

        try {
            [$topicKey, $options, $engine, $modelId] = $this->resolveGenerationContext($validated);

            $title = Str::limit(trim($validated['topic_description']), 255);

            $simulator = LessonSimulator::create([
                'title' => $title,
                'slug' => LessonSimulator::uniqueSlug($title),
                'description' => $validated['topic_description'],
                'topic_key' => $topicKey,
                'render_mode' => 'html_bundle',
                'spec_json' => ['meta' => [], 'sections' => []],
                'spec_version' => config('simulator.spec_version', '1.0'),
                'status' => 'draft',
                'languages' => array_values(array_unique(array_merge(
                    [$validated['primary_language']],
                    $options['languages'] ?? []
                ))),
                'ai_generation_meta' => [
                    'status' => 'pending',
                    'engine' => $engine,
                    'topic_description' => $validated['topic_description'],
                    'simulation_details' => $validated['simulation_details'] ?? null,
                    'archetype' => $options['archetype'] ?? null,
                    'queued_at' => now()->toIso8601String(),
                ],
                'created_by' => Auth::id(),
            ]);

            GenerateLessonSimulatorJob::dispatch(
                $simulator,
                $topicKey,
                $options,
                $engine,
                $modelId,
            );

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

        $meta = $lessonSimulator->ai_generation_meta ?? [];
        $status = $meta['status'] ?? null;

        $bundle = ['html' => '', 'css' => '', 'js' => ''];
        if ($status === 'completed' && $lessonSimulator->hasPlayableContent()) {
            $bundle = $this->bundleStorage->load($lessonSimulator->slug) ?? $bundle;
        }

        return view('admin.lesson-simulators.ai-review', array_merge(
            SimulatorAiWizard::viewData(),
            [
                'simulator' => $lessonSimulator->load('courses'),
                'generationStatus' => $status,
                'generationMeta' => $meta,
                'bundle' => $bundle,
                'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
                'statuses' => LessonSimulator::STATUSES,
            ]
        ));
    }

    public function status(LessonSimulator $lessonSimulator): JsonResponse
    {
        $meta = $lessonSimulator->ai_generation_meta ?? [];
        $status = $meta['status'] ?? 'unknown';

        $payload = [
            'success' => true,
            'status' => $status,
            'meta' => $meta,
            'has_content' => $lessonSimulator->hasPlayableContent(),
            'review_url' => route('admin.lesson-simulators.ai.review', $lessonSimulator),
            'edit_url' => route('admin.lesson-simulators.edit', $lessonSimulator),
        ];

        if ($status === 'completed' && $lessonSimulator->hasPlayableContent()) {
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
            [$topicKey, $options, $engine, $modelId] = $this->resolveGenerationContext(
                $validated,
                $lessonSimulator->topic_key,
            );

            $mode = $request->input('mode', 'async');

            if ($mode === 'sync') {
                $result = $this->generationService->generateHtmlBundle($topicKey, $options);

                $path = $this->bundleStorage->save($lessonSimulator->slug, array_merge($result['bundle'], [
                    'meta' => $result['meta'],
                ]));

                $lessonSimulator->update([
                    'title' => $result['title'],
                    'description' => $topicDescription,
                    'topic_key' => $topicKey,
                    'bundle_path' => $path,
                    'simulator_archetype' => $result['meta']['archetype'] ?? null,
                    'ai_generation_meta' => array_merge($result['meta'], [
                        'status' => 'completed',
                        'completed_at' => now()->toIso8601String(),
                    ]),
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'title' => $result['title'],
                            'html' => $result['bundle']['html'],
                            'css' => $result['bundle']['css'],
                            'js' => $result['bundle']['js'],
                        ],
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                }

                return redirect()
                    ->route('admin.lesson-simulators.ai.review', $lessonSimulator)
                    ->with('success', 'تم إعادة التوليد بنجاح.');
            }

            $lessonSimulator->update([
                'topic_key' => $topicKey,
                'description' => $topicDescription,
                'ai_generation_meta' => array_merge($lessonSimulator->ai_generation_meta ?? [], [
                    'status' => 'pending',
                    'engine' => $engine,
                    'topic_description' => $topicDescription,
                    'simulation_details' => $validated['simulation_details'] ?? null,
                    'queued_at' => now()->toIso8601String(),
                    'error' => null,
                ]),
            ]);

            GenerateLessonSimulatorJob::dispatch(
                $lessonSimulator,
                $topicKey,
                $options,
                $engine,
                $modelId,
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => 'pending',
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
     * @param  array<string, mixed>  $validated
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function resolveEngineContext(array $validated): array
    {
        $requestedEngine = $validated['simulators_engine'] ?? null;
        if ($requestedEngine === 'laravel_ai' && ! LaravelAiModel::query()->where('is_active', true)->exists()) {
            throw new \RuntimeException('لا يوجد موديل Laravel AI نشط.');
        }

        $useLaravel = $this->resolveWizardAiEngine($requestedEngine, 'simulators_engine');
        $engine = $useLaravel ? 'laravel_ai' : 'legacy';

        $options = [
            'generation_mode' => 'html_bundle',
            'engine' => $engine,
            'archetype' => 'playground',
        ];

        if ($useLaravel) {
            $laraModel = null;
            if (! empty($validated['laravel_ai_model_id'])) {
                $laraModel = LaravelAiModel::query()
                    ->where('id', $validated['laravel_ai_model_id'])
                    ->where('is_active', true)
                    ->first();
                if (! $laraModel) {
                    throw new \RuntimeException('موديل Laravel AI المحدد غير متاح.');
                }
            } else {
                $laraModel = LaravelAiModel::query()
                    ->activeOrdered()
                    ->forCapability('simulator.generate')
                    ->first()
                    ?? LaravelAiModel::query()->activeOrdered()->first();
                if (! $laraModel) {
                    throw new \RuntimeException('لا يوجد موديل Laravel AI نشط.');
                }
            }
            $options['laravel_model'] = $laraModel;
        } else {
            $legacyModel = ! empty($validated['ai_model_id'])
                ? AIModel::query()->where('id', $validated['ai_model_id'])->where('is_active', true)->first()
                : app(AIModelService::class)->getAvailableModels('simulator_generation')->first()
                    ?? app(AIModelService::class)->getAvailableModels('all')->first();

            if (! $legacyModel) {
                throw new \RuntimeException('لا يوجد موديل AI (النظام القديم) متاح.');
            }
            $options['legacy_model'] = $legacyModel;
        }

        return [$engine, $options];
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
            'simulators_engine' => 'nullable|in:laravel_ai,legacy',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{0: string, 1: array<string, mixed>, 2: string, 3: int}
     */
    private function resolveGenerationContext(array $validated, ?string $fallbackTopicKey = null): array
    {
        $topicKey = trim($validated['topic_key'] ?? '');
        if ($topicKey === '' || SimulatorTopicRegistry::isCustomKey($topicKey)) {
            $topicKey = SimulatorTopicRegistry::customKeyFromDescription($validated['topic_description']);
        }
        if ($topicKey === 'custom.' && $fallbackTopicKey) {
            $topicKey = $fallbackTopicKey;
        }

        $requestedEngine = $validated['simulators_engine'] ?? null;
        if ($requestedEngine === 'laravel_ai' && ! LaravelAiModel::query()->where('is_active', true)->exists()) {
            throw new \RuntimeException('لا يوجد موديل Laravel AI نشط.');
        }

        $useLaravel = $this->resolveWizardAiEngine($requestedEngine, 'simulators_engine');
        $engine = $useLaravel ? 'laravel_ai' : 'legacy';
        $modelId = 0;

        $options = [
            'topic_description' => $validated['topic_description'],
            'simulation_details' => $validated['simulation_details'] ?? '',
            'primary_language' => $validated['primary_language'],
            'level' => $validated['level'],
            'archetype' => $validated['archetype'] ?? 'auto',
            'generation_mode' => 'html_bundle',
            'engine' => $engine,
        ];

        if ($useLaravel) {
            $laraModel = null;
            if (! empty($validated['laravel_ai_model_id'])) {
                $laraModel = LaravelAiModel::query()
                    ->where('id', $validated['laravel_ai_model_id'])
                    ->where('is_active', true)
                    ->first();
                if (! $laraModel) {
                    throw new \RuntimeException('موديل Laravel AI المحدد غير متاح.');
                }
            } else {
                $laraModel = LaravelAiModel::query()
                    ->activeOrdered()
                    ->forCapability('simulator.generate')
                    ->first()
                    ?? LaravelAiModel::query()->activeOrdered()->first();
                if (! $laraModel) {
                    throw new \RuntimeException('لا يوجد موديل Laravel AI نشط.');
                }
            }
            $options['laravel_model'] = $laraModel;
            $modelId = $laraModel->id;
        } else {
            $legacyModel = ! empty($validated['ai_model_id'])
                ? AIModel::query()->where('id', $validated['ai_model_id'])->where('is_active', true)->first()
                : app(AIModelService::class)->getAvailableModels('simulator_generation')->first()
                    ?? app(AIModelService::class)->getAvailableModels('all')->first();

            if (! $legacyModel) {
                throw new \RuntimeException('لا يوجد موديل AI (النظام القديم) متاح.');
            }
            $options['legacy_model'] = $legacyModel;
            $modelId = $legacyModel->id;
        }

        return [$topicKey, $options, $engine, $modelId];
    }
}
