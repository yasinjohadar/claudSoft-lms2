<?php

namespace App\Services\Simulator;

use App\Ai\Agents\SimulatorGenerationPlainAgent;
use App\Models\AIModel;
use App\Models\LessonSimulator;
use App\Models\LaravelAiModel;
use App\Models\LaravelAiLog;
use App\Services\Ai\AIProviderFactory;
use App\Services\AiNew\LaravelAiPromptRunner;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SimulatorGenerationService
{
    public function __construct(
        private SimulatorPromptService $promptService,
        private SimulatorSpecValidator $validator,
        private LaravelAiPromptRunner $promptRunner,
        private SimulatorSpecJsonParser $jsonParser,
        private SimulatorBundleParser $bundleParser,
        private SimulatorBundleValidator $bundleValidator,
        private SimulatorBundleSanitizer $bundleSanitizer,
        private SimulatorBundleStorage $bundleStorage,
        private SimulatorArchetypeResolver $archetypeResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     * @return array{bundle?: array{html: string, css: string, js: string}, spec?: array, meta: array, title: string}
     */
    public function generate(string $topicKey, array $options = []): array
    {
        $mode = $options['generation_mode'] ?? config('simulator.default_render_mode', 'html_bundle');

        if ($mode === 'json_spec') {
            return $this->generateJsonSpec($topicKey, $options);
        }

        return $this->generateHtmlBundle($topicKey, $options);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{bundle: array{html: string, css: string, js: string}, meta: array, title: string}
     */
    public function generateHtmlBundle(string $topicKey, array $options = []): array
    {
        set_time_limit(300);

        $engine = $options['engine'] ?? 'legacy';
        $archetype = $this->archetypeResolver->resolve(
            $options['primary_language'] ?? 'php',
            $options['archetype'] ?? 'auto',
        );

        $promptOptions = array_merge($options, ['archetype' => $archetype]);
        $prompt = $this->promptService->buildBundleGenerationPrompt($topicKey, $promptOptions);

        if ($engine === 'legacy') {
            return $this->runLegacyBundleGeneration($prompt, $topicKey, $options, $archetype);
        }

        return $this->runLaravelBundleGeneration($prompt, $topicKey, $options, $archetype);
    }

    /**
     * @param  array{html: string, css: string, js: string}  $bundle
     * @param  array<string, mixed>  $options
     * @return array{bundle: array{html: string, css: string, js: string}, meta: array, title: string}
     */
    public function refineHtmlBundle(array $bundle, string $instructions, array $options = []): array
    {
        set_time_limit(300);

        $prompt = $this->promptService->buildBundleRefinePrompt($bundle, $instructions, $options);
        $topicKey = $options['topic_key'] ?? 'custom.refine';
        $archetype = $options['archetype'] ?? 'playground';
        $engine = $options['engine'] ?? 'laravel_ai';

        if ($engine === 'legacy') {
            $result = $this->runLegacyBundleGeneration($prompt, $topicKey, $options, $archetype);
        } else {
            $result = $this->runLaravelBundleGeneration($prompt, $topicKey, $options, $archetype);
        }

        $result['meta']['operation'] = 'refine';

        return $result;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{spec: array, meta: array, title: string}
     */
    public function generateJsonSpec(string $topicKey, array $options = []): array
    {
        set_time_limit(300);

        $engine = $options['engine'] ?? 'laravel_ai';
        if ($engine === 'legacy') {
            $result = $this->generateWithLegacyJsonModel($topicKey, $options);
        } else {
            $result = $this->generateWithLaravelJsonModel($topicKey, $options);
        }

        return array_merge($result, [
            'title' => $result['spec']['meta']['title'] ?? $options['topic_description'] ?? 'محاكاة',
        ]);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{bundle: array{html: string, css: string, js: string}, meta: array, title: string}
     */
    private function runLegacyBundleGeneration(string $prompt, string $topicKey, array $options, string $archetype): array
    {
        $model = $options['legacy_model'] ?? null;
        if (! $model instanceof AIModel) {
            throw new \RuntimeException('لم يُحدَّد موديل AI من النظام القديم.');
        }

        $apiKey = $model->getDecryptedApiKey();
        if (empty($apiKey)) {
            throw new \RuntimeException('API Key غير موجود للموديل المحدد.');
        }

        $provider = AIProviderFactory::create($model);
        $maxTokens = $model->max_tokens > 0 ? $model->max_tokens : 12000;

        $fetch = function (string $p) use ($provider, $maxTokens, $model) {
            return $provider->generateText($p, [
                'max_tokens' => min($maxTokens, 16000),
                'temperature' => min($model->temperature ?? 0.7, 0.35),
            ]);
        };

        $raw = trim($fetch($prompt) ?? '');
        if ($raw === '') {
            throw new \RuntimeException($provider->getLastError() ?? 'فشل التوليد — لم يتم الحصول على رد من API.');
        }

        $bundle = $this->parseBundleWithRepair($raw, $fetch);

        return $this->finalizeBundle($bundle, $topicKey, $options, $archetype, 'legacy', [
            'ai_model_id' => $model->id,
            'model' => $model->model_key,
            'provider' => $model->provider,
        ], $prompt);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{bundle: array{html: string, css: string, js: string}, meta: array, title: string}
     */
    private function runLaravelBundleGeneration(string $prompt, string $topicKey, array $options, string $archetype): array
    {
        $laraModel = $options['laravel_model'] ?? null;
        if (! $laraModel instanceof LaravelAiModel) {
            $laraModel = LaravelAiModel::query()
                ->activeOrdered()
                ->forCapability('simulator.generate')
                ->first()
                ?? LaravelAiModel::query()->activeOrdered()->first();

            if (! $laraModel) {
                throw new \RuntimeException('لا يوجد موديل Laravel AI نشط.');
            }
        }

        $agent = new SimulatorGenerationPlainAgent;
        $startedAt = microtime(true);

        $fetch = function (string $p) use ($laraModel, $agent) {
            $response = $this->promptRunner->runPlain($laraModel, $agent, $p, 240);

            return trim($response->text ?? '');
        };

        try {
            $raw = $fetch($prompt);
            if ($raw === '') {
                throw new \RuntimeException('فشل التوليد — رد فارغ من Laravel AI.');
            }

            $bundle = $this->parseBundleWithRepair($raw, $fetch);

            $result = $this->finalizeBundle($bundle, $topicKey, $options, $archetype, 'laravel_ai', [
                'laravel_ai_model_id' => $laraModel->id,
                'model' => $laraModel->model,
                'provider' => $laraModel->provider,
            ], $prompt);

            $this->logLaravelGeneration($laraModel, $prompt, $raw, true, microtime(true) - $startedAt);

            return $result;
        } catch (\Throwable $e) {
            $this->logLaravelGeneration($laraModel, $prompt, $e->getMessage(), false, microtime(true) - $startedAt);
            throw $e;
        }
    }

    /**
     * @param  callable(string): ?string  $fetch
     * @return array{html: string, css: string, js: string}
     */
    private function parseBundleWithRepair(string $raw, callable $fetch): array
    {
        try {
            $bundle = $this->bundleSanitizer->sanitize($this->bundleParser->parse($raw));
        } catch (\RuntimeException $e) {
            Log::info('Simulator bundle parse failed, repair pass');
            $repaired = $fetch($this->bundleParser->buildRepairPrompt($raw));
            if (! $repaired || trim($repaired) === '') {
                throw $e;
            }
            $bundle = $this->bundleSanitizer->sanitize($this->bundleParser->parse(trim($repaired)));
        }

        $validation = $this->bundleValidator->validate($bundle);
        if (! $validation['valid']) {
            Log::info('Simulator bundle validation failed, repair pass', ['errors' => $validation['errors']]);
            $repaired = $fetch($this->bundleParser->buildValidationRepairPrompt($bundle, $validation['errors']));
            if (! $repaired || trim($repaired) === '') {
                throw new \RuntimeException('ملفات المحاكاة غير صالحة: '.implode(' ', $validation['errors']));
            }
            $bundle = $this->bundleSanitizer->sanitize($this->bundleParser->parse(trim($repaired)));
            $validation = $this->bundleValidator->validate($bundle);
            if (! $validation['valid']) {
                throw new \RuntimeException('ملفات المحاكاة غير صالحة: '.implode(' ', $validation['errors']));
            }
        }

        return $bundle;
    }

    /**
     * @param  array{html: string, css: string, js: string}  $bundle
     * @param  array<string, mixed>  $options
     * @param  array<string, mixed>  $modelMeta
     * @return array{bundle: array{html: string, css: string, js: string}, meta: array, title: string}
     */
    private function finalizeBundle(
        array $bundle,
        string $topicKey,
        array $options,
        string $archetype,
        string $engine,
        array $modelMeta,
        string $prompt,
    ): array {
        $title = $this->extractTitleFromHtml($bundle['html'])
            ?? Str::limit($options['topic_description'] ?? 'محاكاة', 255);

        $meta = array_merge($modelMeta, [
            'engine' => $engine,
            'render_mode' => 'html_bundle',
            'archetype' => $archetype,
            'topic_key' => $topicKey,
            'prompt_hash' => hash('sha256', $prompt),
            'generated_at' => now()->toIso8601String(),
        ]);

        return [
            'bundle' => $bundle,
            'meta' => $meta,
            'title' => $title,
        ];
    }

    private function extractTitleFromHtml(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            return trim(strip_tags($m[1])) ?: null;
        }
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
            return trim(strip_tags($m[1])) ?: null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{spec: array, meta: array}
     */
    private function generateWithLaravelJsonModel(string $topicKey, array $options): array
    {
        $laraModel = $options['laravel_model'] ?? null;
        if (! $laraModel instanceof LaravelAiModel) {
            $laraModel = LaravelAiModel::query()->activeOrdered()->first();
            if (! $laraModel) {
                throw new \RuntimeException('لا يوجد موديل Laravel AI نشط.');
            }
        }

        $prompt = $this->promptService->buildGenerationPrompt($topicKey, $options);
        $agent = new SimulatorGenerationPlainAgent;
        $startedAt = microtime(true);

        try {
            $response = $this->promptRunner->runPlain($laraModel, $agent, $prompt, 180);
            $raw = trim($response->text ?? '');
            $spec = $this->parseSpecJsonWithRepair($raw, function (string $repairPrompt) use ($laraModel, $agent) {
                $response = $this->promptRunner->runPlain($laraModel, $agent, $repairPrompt, 180);

                return trim($response->text ?? '');
            });

            $validation = $this->validator->validate($spec);
            if (! $validation['valid']) {
                throw new \RuntimeException('JSON غير صالح: '.implode(' ', $validation['errors']));
            }

            $aiMeta = [
                'engine' => 'laravel_ai',
                'render_mode' => 'json_spec',
                'model' => $laraModel->model,
                'provider' => $laraModel->provider,
                'laravel_ai_model_id' => $laraModel->id,
                'prompt_hash' => hash('sha256', $prompt),
                'generated_at' => now()->toIso8601String(),
            ];

            $this->logLaravelGeneration($laraModel, $prompt, $raw, true, microtime(true) - $startedAt);

            return ['spec' => $spec, 'meta' => $aiMeta];
        } catch (\Throwable $e) {
            $this->logLaravelGeneration($laraModel, $prompt, $e->getMessage(), false, microtime(true) - $startedAt);
            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{spec: array, meta: array}
     */
    private function generateWithLegacyJsonModel(string $topicKey, array $options): array
    {
        $model = $options['legacy_model'] ?? null;
        if (! $model instanceof AIModel) {
            throw new \RuntimeException('لم يُحدَّد موديل AI.');
        }

        $prompt = $this->promptService->buildGenerationPrompt($topicKey, $options);
        $provider = AIProviderFactory::create($model);
        $maxTokens = $model->max_tokens > 0 ? $model->max_tokens : 8000;

        $response = $provider->generateText($prompt, [
            'max_tokens' => min($maxTokens, 16000),
            'temperature' => min($model->temperature ?? 0.7, 0.4),
        ]);

        if (! $response || trim($response) === '') {
            throw new \RuntimeException($provider->getLastError() ?? 'فشل التوليد.');
        }

        $spec = $this->parseSpecJsonWithRepair(trim($response), function (string $repairPrompt) use ($provider, $maxTokens, $model) {
            return $provider->generateText($repairPrompt, [
                'max_tokens' => min($maxTokens, 16000),
                'temperature' => 0.2,
            ]);
        });

        $validation = $this->validator->validate($spec);
        if (! $validation['valid']) {
            throw new \RuntimeException('JSON غير صالح: '.implode(' ', $validation['errors']));
        }

        return [
            'spec' => $spec,
            'meta' => [
                'engine' => 'legacy',
                'render_mode' => 'json_spec',
                'model' => $model->model_key,
                'provider' => $model->provider,
                'ai_model_id' => $model->id,
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    public function createDraftFromSpec(
        array $spec,
        string $topicKey,
        array $aiMeta = [],
        ?int $createdBy = null,
    ): LessonSimulator {
        $title = $spec['meta']['title'] ?? SimulatorTopicRegistry::label($topicKey);
        $languages = $spec['meta']['languages'] ?? [];

        return LessonSimulator::create([
            'title' => $title,
            'slug' => LessonSimulator::uniqueSlug($title),
            'description' => $spec['sections'][0]['summary'] ?? null,
            'topic_key' => $topicKey,
            'spec_json' => $spec,
            'render_mode' => 'json_spec',
            'spec_version' => config('simulator.spec_version', '1.0'),
            'status' => 'draft',
            'languages' => $languages,
            'ai_generation_meta' => $aiMeta ?: null,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function parseSpecJson(string $raw): array
    {
        return $this->jsonParser->parse($raw);
    }

    /**
     * @param  callable(string): ?string  $repairFetcher
     * @return array<string, mixed>
     */
    private function parseSpecJsonWithRepair(string $raw, ?callable $repairFetcher = null): array
    {
        try {
            return $this->jsonParser->parse($raw);
        } catch (\RuntimeException $firstError) {
            if (! $repairFetcher) {
                throw $firstError;
            }

            $repairPrompt = $this->promptService->buildJsonRepairPrompt($raw);
            $repaired = $repairFetcher($repairPrompt);
            if (! $repaired || trim($repaired) === '') {
                throw $firstError;
            }

            return $this->jsonParser->parse(trim($repaired));
        }
    }

    private function logLaravelGeneration(
        LaravelAiModel $model,
        string $prompt,
        string $response,
        bool $success,
        float $duration,
    ): void {
        try {
            LaravelAiLog::create([
                'laravel_ai_model_id' => $model->id,
                'user_id' => auth()->id(),
                'operation' => 'simulator.generate',
                'request_payload' => ['prompt' => Str::limit($prompt, 32000)],
                'response_payload' => ['response' => Str::limit($response, 32000)],
                'status' => $success ? 'success' : 'failed',
                'error_message' => $success ? null : Str::limit($response, 1000),
                'latency_ms' => (int) round($duration * 1000),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log simulator generation: '.$e->getMessage());
        }
    }
}
