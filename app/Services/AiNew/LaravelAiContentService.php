<?php

namespace App\Services\AiNew;

use App\Ai\Agents\GeneralTextAgent;
use App\Models\LaravelAiModel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Responses\AgentResponse;
use Throwable;

class LaravelAiContentService
{
    public function __construct(
        private LaravelAiProviderManager $providerManager,
        private LaravelAiRequestLogger $logger,
        private LaravelAiPromptRunner $promptRunner,
    ) {}

    public function resolveDefaultModel(?string $capability = null): ?LaravelAiModel
    {
        $q = LaravelAiModel::query()->activeOrdered();

        if ($capability !== null) {
            $q->forCapability($capability);
        }

        return $q->first();
    }

    /**
     * @return array{text: string}
     */
    public function generateText(
        string $prompt,
        ?string $capability,
        ?Authenticatable $user = null,
        ?LaravelAiModel $model = null,
        int $timeout = 60,
    ): array {
        $model ??= $this->resolveDefaultModel($capability ?? 'content.general');

        if (! $model) {
            throw new \RuntimeException('No active Laravel AI model is configured.');
        }

        $started = hrtime(true);
        $operation = 'content.general';

        try {
            /** @var AgentResponse $response */
            $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt, $timeout) {
                $agent = new GeneralTextAgent;

                return $this->promptRunner->runPlain($model, $agent, $prompt, $timeout);
            });

            $latency = (int) ((hrtime(true) - $started) / 1_000_000);

            $this->logger->logSuccess(
                $model,
                $user,
                $operation,
                ['prompt' => $prompt],
                ['text' => mb_substr((string) $response->text, 0, 2000)],
                $latency,
            );

            return ['text' => (string) $response->text];
        } catch (Throwable $e) {
            $latency = (int) ((hrtime(true) - $started) / 1_000_000);
            Log::error('LaravelAiContentService failed', ['exception' => $e->getMessage()]);

            $this->logger->logFailure(
                $model,
                $user,
                $operation,
                ['prompt' => $prompt],
                $e->getMessage(),
                $latency,
            );

            throw $e;
        }
    }
}
