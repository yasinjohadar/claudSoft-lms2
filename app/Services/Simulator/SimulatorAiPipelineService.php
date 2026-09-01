<?php

namespace App\Services\Simulator;

use App\Ai\Agents\SimulatorBundlePlainAgent;
use App\Ai\Agents\SimulatorPlanAgent;
use App\Exceptions\Ai\AiProviderException;
use App\Exceptions\Ai\GenerationCancelledException;
use App\Exceptions\Ai\ResumableIncompleteException;
use App\Models\AIModel;
use App\Models\LaravelAiModel;
use App\Models\SimulatorAiGeneration;
use App\Models\SimulatorAiPhase;
use App\Models\User;
use App\Services\Ai\AIModelService;
use App\Services\Ai\AIProviderFactory;
use App\Services\Ai\Concerns\ParsesAiJsonResponse;
use App\Services\AiNew\LaravelAiPromptRunner;
use App\Services\AiNew\LaravelAiProviderManager;
use App\Services\AiNew\LaravelAiRequestLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class SimulatorAiPipelineService
{
    use ParsesAiJsonResponse;

    private const PLAN_TIMEOUT = 90;

    private const PHASE_TIMEOUT = 180;

    public function __construct(
        private LaravelAiProviderManager $providerManager,
        private LaravelAiPromptRunner $promptRunner,
        private LaravelAiRequestLogger $logger,
        private SimulatorPromptService $promptService,
        private SimulatorStagedGenerator $stagedGenerator,
        private SimulatorBundleStorage $bundleStorage,
        private SimulatorGenerationService $generationService,
        private AIModelService $legacyModelService,
    ) {}

    public function run(SimulatorAiGeneration $generation): void
    {
        set_time_limit(0);

        $generation->refresh();
        if (in_array($generation->status, [
            SimulatorAiGeneration::STATUS_COMPLETED,
            SimulatorAiGeneration::STATUS_CANCELLED,
        ], true)) {
            return;
        }

        $generation->markRunning('starting', 'بدء المعالجة…', 2);

        try {
            $result = match ($generation->operation) {
                SimulatorAiGeneration::OPERATION_GENERATE => $this->runGenerate($generation),
                SimulatorAiGeneration::OPERATION_REFINE => $this->runRefine($generation),
                default => throw new \InvalidArgumentException('عملية غير معروفة: '.$generation->operation),
            };

            $this->persistToSimulator($generation, $result);
            $generation->markCompleted($result);
        } catch (ResumableIncompleteException $e) {
            Log::warning('Simulator AI paused with resumable progress', [
                'uuid' => $generation->uuid,
                'done' => $e->done,
                'planned' => $e->planned,
                'failed_phases' => $e->failedHeadings,
            ]);
            $generation->markPaused($e->getMessage());
        } catch (GenerationCancelledException $e) {
            Log::info('Simulator AI generation cancelled', ['uuid' => $generation->uuid]);
            $generation->markCancelled($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Simulator AI pipeline failed', [
                'uuid' => $generation->uuid,
                'operation' => $generation->operation,
                'message' => $e->getMessage(),
            ]);

            if ($generation->phases()->where('status', SimulatorAiPhase::STATUS_DONE)->exists()) {
                $generation->markPaused($this->friendlyError($e).' — الملفات المكتملة محفوظة، اضغط «متابعة التوليد».');

                return;
            }

            $generation->markFailed($this->friendlyError($e));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function runGenerate(SimulatorAiGeneration $generation): array
    {
        $payload = $generation->payload;
        $topicDescription = trim((string) ($payload['topic_description'] ?? ''));
        if ($topicDescription === '') {
            throw new \InvalidArgumentException('وصف الموضوع مطلوب.');
        }

        $engine = (string) ($payload['engine'] ?? 'legacy');
        $useLaravelAi = $engine === 'laravel_ai';
        $options = $this->optionsFromPayload($payload);
        $user = User::query()->find($generation->user_id);

        $laraModel = null;
        $legacyModel = null;
        if ($useLaravelAi) {
            $laraModel = $this->resolveLaravelModel($payload);
            $planTokens = (int) config('ai.simulator.plan_max_tokens', 3072);
            $phaseTokens = (int) config('ai.simulator.phase_max_tokens', 16384);
            if ((int) ($laraModel->max_tokens ?? 0) > 0) {
                $planTokens = min($planTokens, (int) $laraModel->max_tokens);
                $phaseTokens = min($phaseTokens, (int) $laraModel->max_tokens);
            }
        } else {
            $legacyModel = $this->resolveLegacyModel($payload);
            $cap = (int) $legacyModel->max_tokens > 0 ? (int) $legacyModel->max_tokens : 16000;
            $planTokens = min($cap, 4096);
            $phaseTokens = min($cap, 16000);
        }

        $planWriter = function (int $maxTokens) use ($useLaravelAi, $topicDescription, $options, $user, $laraModel, $legacyModel): array {
            return $useLaravelAi
                ? $this->generatePlan($topicDescription, $options, $user, $laraModel, $maxTokens)
                : $this->generatePlanLegacy($topicDescription, $options, $legacyModel, $maxTokens);
        };

        $phaseWriter = function (SimulatorPhaseAttempt $attempt) use (
            $useLaravelAi, $generation, $options, $user, $laraModel, $legacyModel
        ): string {
            $plan = $generation->partial_result['plan'] ?? [];
            $plan = is_array($plan) ? $plan : [];
            /** @var Collection<string, string> $done */
            $done = $generation->phases()
                ->where('status', SimulatorAiPhase::STATUS_DONE)
                ->pluck('content', 'phase');

            return $useLaravelAi
                ? $this->requestPhaseContent($attempt, $plan, $done, $options, $user, $laraModel)
                : $this->requestPhaseContentLegacy($attempt, $plan, $done, $options, $legacyModel);
        };

        return $this->stagedGenerator->generate($generation, $planTokens, $phaseTokens, $planWriter, $phaseWriter);
    }

    /**
     * Refine keeps the existing single-call bundle refine (already reasonably
     * scoped for a small edit), just resolved through the same generation row
     * so the review page's progress/status UI stays consistent.
     *
     * @return array<string, mixed>
     */
    private function runRefine(SimulatorAiGeneration $generation): array
    {
        $payload = $generation->payload;
        $bundle = [
            'html' => (string) ($payload['bundle_html'] ?? ''),
            'css' => (string) ($payload['bundle_css'] ?? ''),
            'js' => (string) ($payload['bundle_js'] ?? ''),
        ];
        $instructions = trim((string) ($payload['instructions'] ?? ''));
        if ($instructions === '') {
            throw new \InvalidArgumentException('تعليمات التعديل مطلوبة.');
        }

        $engine = (string) ($payload['engine'] ?? 'legacy');
        $options = [
            'engine' => $engine,
            'title' => $payload['title'] ?? '',
            'topic_key' => $payload['topic_key'] ?? 'custom.refine',
            'archetype' => $payload['archetype'] ?? 'playground',
        ];

        if ($engine === 'laravel_ai') {
            $options['laravel_model'] = $this->resolveLaravelModel($payload);
        } else {
            $options['legacy_model'] = $this->resolveLegacyModel($payload);
        }

        $generation->markProgress('refine', 'تطبيق التعديلات…', 40);

        $result = $this->generationService->refineHtmlBundle($bundle, $instructions, $options);

        $generation->markProgress('refine', 'اكتمل التعديل', 90);

        return [
            'bundle' => $result['bundle'],
            'title' => $result['title'],
            'description' => $payload['description'] ?? null,
            'archetype' => $result['meta']['archetype'] ?? $options['archetype'],
            'lang_code' => 'ar',
            'text_direction' => 'rtl',
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function generatePlan(
        string $topicDescription,
        array $options,
        ?User $user,
        LaravelAiModel $model,
        ?int $maxTokens = null,
    ): array {
        $prompt = $this->promptService->buildPlanPrompt($topicDescription, $options);
        $started = hrtime(true);

        /** @var StructuredAgentResponse $response */
        $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt, $maxTokens) {
            return $this->promptRunner->runStructured(
                $model,
                new SimulatorPlanAgent,
                $prompt,
                self::PLAN_TIMEOUT,
                null,
                $maxTokens,
            );
        });

        $structured = $response->toArray();
        $this->logger->logSuccess(
            $model,
            $user,
            'simulator.plan',
            ['topic' => $topicDescription],
            $structured,
            (int) ((hrtime(true) - $started) / 1_000_000)
        );

        return $structured;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function generatePlanLegacy(string $topicDescription, array $options, AIModel $model, int $maxTokens): array
    {
        $prompt = $this->promptService->buildPlanPrompt($topicDescription, $options)
            ."\n\nReturn ONLY a valid JSON object with keys: title, description, archetype, lang_code, text_direction, "
            .'key_elements (array of strings), interactions (array of strings), coverage_checklist (array of strings). '
            .'No markdown fences, no commentary.';

        $provider = AIProviderFactory::create($model);
        $cap = $maxTokens > 0 ? $maxTokens : ((int) $model->max_tokens > 0 ? (int) $model->max_tokens : 3072);

        $raw = trim((string) $provider->generateText($prompt, [
            'max_tokens' => min($cap, 16000),
            'temperature' => min($model->temperature ?? 0.6, 0.6),
        ]));

        if ($raw === '') {
            throw new \RuntimeException($provider->getLastError() ?? 'فشل توليد الخطة.');
        }

        $data = $this->parseJSONResponse($raw);
        if (trim((string) ($data['title'] ?? '')) === '') {
            throw new AiProviderException(
                'خطة المحاكاة عادت ناقصة أو غير صالحة.',
                AiProviderException::KIND_TOO_LARGE,
            );
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $plan
     * @param  Collection<string, string>  $doneContent
     * @param  array<string, mixed>  $options
     */
    private function requestPhaseContent(
        SimulatorPhaseAttempt $attempt,
        array $plan,
        Collection $doneContent,
        array $options,
        ?User $user,
        LaravelAiModel $model,
    ): string {
        $prompt = $this->buildPhasePrompt($attempt, $plan, $doneContent, $options);
        $agent = new SimulatorBundlePlainAgent;
        $started = hrtime(true);

        $response = $this->providerManager->runWithModel($model, function () use ($model, $agent, $prompt, $attempt) {
            return $this->promptRunner->runPlain($model, $agent, $prompt, self::PHASE_TIMEOUT, null, $attempt->maxTokens);
        });

        $text = trim((string) $response->text);
        $this->logger->logSuccess(
            $model,
            $user,
            'simulator.'.$attempt->phase.($attempt->compact ? '.retry' : ''),
            ['phase' => $attempt->phase],
            ['content_len' => mb_strlen($text)],
            (int) ((hrtime(true) - $started) / 1_000_000)
        );

        return $text;
    }

    /**
     * @param  array<string, mixed>  $plan
     * @param  Collection<string, string>  $doneContent
     * @param  array<string, mixed>  $options
     */
    private function requestPhaseContentLegacy(
        SimulatorPhaseAttempt $attempt,
        array $plan,
        Collection $doneContent,
        array $options,
        AIModel $model,
    ): string {
        $prompt = $this->buildPhasePrompt($attempt, $plan, $doneContent, $options);
        $provider = AIProviderFactory::create($model);

        $raw = $provider->generateText($prompt, [
            'max_tokens' => min($attempt->maxTokens, (int) $model->max_tokens > 0 ? (int) $model->max_tokens : $attempt->maxTokens),
            'temperature' => min($model->temperature ?? 0.5, 0.5),
        ]);

        if (trim((string) $raw) === '') {
            throw new \RuntimeException($provider->getLastError() ?? 'فشل توليد الملف.');
        }

        return trim((string) $raw);
    }

    /**
     * @param  array<string, mixed>  $plan
     * @param  Collection<string, string>  $doneContent
     * @param  array<string, mixed>  $options
     */
    private function buildPhasePrompt(
        SimulatorPhaseAttempt $attempt,
        array $plan,
        Collection $doneContent,
        array $options,
    ): string {
        return match ($attempt->phase) {
            'html' => $this->promptService->buildHtmlPhasePrompt($plan, $options, $attempt->validationFeedback),
            'css' => $this->promptService->buildCssPhasePrompt(
                $plan,
                (string) ($doneContent['html'] ?? ''),
                $options,
                $attempt->validationFeedback,
            ),
            'js' => $this->promptService->buildJsPhasePrompt(
                $plan,
                (string) ($doneContent['html'] ?? ''),
                (string) ($doneContent['css'] ?? ''),
                $options,
                $attempt->validationFeedback,
            ),
            default => throw new \InvalidArgumentException('مرحلة غير معروفة: '.$attempt->phase),
        };
    }

    private function persistToSimulator(SimulatorAiGeneration $generation, array $result): void
    {
        $simulator = $generation->simulator;
        if (! $simulator) {
            return;
        }

        $bundle = $result['bundle'];
        $meta = [
            'engine' => $generation->payload['engine'] ?? null,
            'render_mode' => 'html_bundle',
            'archetype' => $result['archetype'] ?? null,
            'lang_code' => $result['lang_code'] ?? 'ar',
            'text_direction' => $result['text_direction'] ?? 'rtl',
            'generation_uuid' => $generation->uuid,
            'generated_at' => now()->toIso8601String(),
        ];
        $path = $this->bundleStorage->save($simulator->slug, array_merge($bundle, ['meta' => $meta]));

        $simulator->update([
            'title' => $result['title'] ?? $simulator->title,
            'description' => $result['description'] ?? $simulator->description,
            'bundle_path' => $path,
            'simulator_archetype' => $result['archetype'] ?? $simulator->simulator_archetype,
            // Kept for any code still reading this column directly; the
            // simulator_ai_generations row is the authoritative status source.
            'ai_generation_meta' => array_merge($meta, ['status' => 'completed']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function optionsFromPayload(array $payload): array
    {
        return [
            'simulation_details' => $payload['simulation_details'] ?? '',
            'primary_language' => $payload['primary_language'] ?? 'php',
            'level' => $payload['level'] ?? 'beginner',
            'archetype' => $payload['archetype'] ?? 'auto',
            'output_language' => $payload['output_language'] ?? 'العربية',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveLaravelModel(array $payload): LaravelAiModel
    {
        if (! empty($payload['laravel_ai_model_id'])) {
            $model = LaravelAiModel::query()
                ->where('id', (int) $payload['laravel_ai_model_id'])
                ->where('is_active', true)
                ->first();
            if ($model) {
                return $model;
            }
        }

        $model = LaravelAiModel::query()->activeOrdered()->forCapability('simulator.generate')->first()
            ?? LaravelAiModel::query()->activeOrdered()->first();

        if (! $model) {
            throw new \RuntimeException('لا يوجد موديل Laravel AI نشط.');
        }

        return $model;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveLegacyModel(array $payload): AIModel
    {
        if (! empty($payload['ai_model_id'])) {
            $model = AIModel::query()->where('id', (int) $payload['ai_model_id'])->where('is_active', true)->first();
            if ($model) {
                return $model;
            }
        }

        $model = $this->legacyModelService->getAvailableModels('simulator_generation')->first()
            ?? $this->legacyModelService->getAvailableModels('all')->first();

        if (! $model) {
            throw new \RuntimeException('لا يوجد موديل AI متاح.');
        }

        return $model;
    }

    private function friendlyError(Throwable $e): string
    {
        if ($e instanceof AiProviderException) {
            return $e->getMessage();
        }

        return $e->getMessage() ?: 'حدث خطأ غير متوقع أثناء التوليد.';
    }
}
