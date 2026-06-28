<?php

namespace App\Jobs;

use App\Models\AIModel;
use App\Models\LessonSimulator;
use App\Models\LaravelAiModel;
use App\Services\Simulator\SimulatorBundleStorage;
use App\Services\Simulator\SimulatorGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateLessonSimulatorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    /**
     * @param  array<string, mixed>  $generationOptions
     */
    public function __construct(
        public LessonSimulator $simulator,
        public string $topicKey,
        public array $generationOptions,
        public string $engine,
        public int $modelId,
    ) {}

    public function handle(
        SimulatorGenerationService $generationService,
        SimulatorBundleStorage $bundleStorage,
    ): void {
        $simulator = $this->simulator->fresh();
        if (! $simulator) {
            return;
        }

        $simulator->update([
            'ai_generation_meta' => array_merge($simulator->ai_generation_meta ?? [], [
                'status' => 'processing',
                'engine' => $this->engine,
                'started_at' => now()->toIso8601String(),
            ]),
        ]);

        try {
            $generateOptions = array_merge($this->generationOptions, [
                'engine' => $this->engine,
                'generation_mode' => $simulator->render_mode ?? 'html_bundle',
            ]);

            if ($this->engine === 'legacy') {
                $legacyModel = AIModel::query()
                    ->where('id', $this->modelId)
                    ->where('is_active', true)
                    ->first();

                if (! $legacyModel) {
                    throw new \RuntimeException('موديل AI (النظام القديم) غير متاح.');
                }

                $generateOptions['legacy_model'] = $legacyModel;
            } else {
                $laraModel = LaravelAiModel::query()
                    ->where('id', $this->modelId)
                    ->where('is_active', true)
                    ->first();

                if (! $laraModel) {
                    throw new \RuntimeException('موديل Laravel AI غير متاح.');
                }

                $generateOptions['laravel_model'] = $laraModel;
            }

            $result = $generationService->generate($this->topicKey, $generateOptions);

            if (($simulator->render_mode ?? 'html_bundle') === 'html_bundle' && isset($result['bundle'])) {
                $bundlePath = $bundleStorage->save($simulator->slug, array_merge($result['bundle'], [
                    'meta' => $result['meta'],
                ]));

                $simulator->update([
                    'title' => $result['title'],
                    'description' => $this->generationOptions['topic_description'] ?? $simulator->description,
                    'bundle_path' => $bundlePath,
                    'simulator_archetype' => $result['meta']['archetype'] ?? null,
                    'spec_json' => ['meta' => [], 'sections' => []],
                    'ai_generation_meta' => array_merge($result['meta'], [
                        'status' => 'completed',
                        'completed_at' => now()->toIso8601String(),
                    ]),
                ]);
            } else {
                $spec = $result['spec'];
                $title = $spec['meta']['title'] ?? $simulator->title;

                $simulator->update([
                    'title' => $title,
                    'description' => $spec['sections'][0]['summary'] ?? $simulator->description,
                    'spec_json' => $spec,
                    'languages' => $spec['meta']['languages'] ?? $simulator->languages,
                    'ai_generation_meta' => array_merge($result['meta'], [
                        'status' => 'completed',
                        'completed_at' => now()->toIso8601String(),
                    ]),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('GenerateLessonSimulatorJob failed: '.$e->getMessage(), [
                'simulator_id' => $simulator->id,
                'engine' => $this->engine,
            ]);

            $simulator->update([
                'ai_generation_meta' => array_merge($simulator->ai_generation_meta ?? [], [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'failed_at' => now()->toIso8601String(),
                ]),
            ]);
        }
    }
}
