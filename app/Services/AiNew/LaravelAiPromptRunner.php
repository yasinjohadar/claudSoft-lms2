<?php

namespace App\Services\AiNew;

use App\Models\LaravelAiModel;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Ai;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Gateway\TextGenerationOptions;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Laravel\Ai\Responses\StructuredTextResponse;
use Laravel\Ai\Responses\TextResponse;
use Prism\Prism\Exceptions\PrismRequestTooLargeException;
use Throwable;

/**
 * Runs Laravel AI text/structured requests using per-model max_tokens and temperature from laravel_ai_models.
 * The #[MaxTokens] attribute on agents is only a fallback when max_tokens is missing or invalid.
 *
 * When the provider rejects the request as too large / over token limits, retries with a stepped-down
 * max_tokens so generation can continue using the model's configured limit as the starting point.
 */
class LaravelAiPromptRunner
{
    /** @var list<int> */
    private const TOKEN_STEP_DOWN = [65536, 32768, 16384, 12288, 8192, 6144, 4096, 3072, 2048];

    public function runStructured(
        LaravelAiModel $model,
        Agent&HasStructuredOutput $agent,
        string $prompt,
        int $timeout = 120,
        ?int $preferMinTokens = null,
        ?int $maxTokensCap = null,
    ): StructuredAgentResponse {
        $schema = $agent->schema(new JsonSchemaTypeFactory);
        $textResponse = $this->invokeGateway($model, $agent, $prompt, $schema, $timeout, $preferMinTokens, $maxTokensCap);

        if (! $textResponse instanceof StructuredTextResponse) {
            throw new \RuntimeException('Structured agent did not return structured output.');
        }

        $invocationId = (string) Str::uuid7();

        return (new StructuredAgentResponse(
            $invocationId,
            $textResponse->structured,
            $textResponse->text,
            $textResponse->usage,
            $textResponse->meta,
        ))
            ->withToolCallsAndResults($textResponse->toolCalls, $textResponse->toolResults)
            ->withSteps($textResponse->steps);
    }

    public function runPlain(
        LaravelAiModel $model,
        Agent $agent,
        string $prompt,
        int $timeout = 60,
        ?int $preferMinTokens = null,
        ?int $maxTokensCap = null,
    ): AgentResponse {
        $textResponse = $this->invokeGateway($model, $agent, $prompt, null, $timeout, $preferMinTokens, $maxTokensCap);
        $invocationId = (string) Str::uuid7();

        return (new AgentResponse(
            $invocationId,
            $textResponse->text,
            $textResponse->usage,
            $textResponse->meta,
        ))
            ->withMessages($textResponse->messages)
            ->withToolCallsAndResults($textResponse->toolCalls, $textResponse->toolResults)
            ->withSteps($textResponse->steps);
    }

    /**
     * @param  array<string, mixed>|null  $schema
     */
    private function invokeGateway(
        LaravelAiModel $model,
        Agent $agent,
        string $prompt,
        ?array $schema,
        int $timeout,
        ?int $preferMinTokens = null,
        ?int $maxTokensCap = null,
    ): TextResponse {
        $provider = Ai::textProviderFor($agent, $model->provider);
        $tools = $agent instanceof HasTools ? $agent->tools() : [];
        $base = TextGenerationOptions::forAgent($agent);
        $preferred = $this->effectiveMaxTokens($model, $base->maxTokens, $preferMinTokens, $maxTokensCap);
        $attempts = $this->maxTokenAttempts($preferred);

        $last = null;
        foreach ($attempts as $index => $maxTokens) {
            $options = new TextGenerationOptions(
                maxSteps: $base->maxSteps,
                maxTokens: $maxTokens,
                temperature: $model->temperature !== null
                    ? (float) $model->temperature
                    : $base->temperature,
                agent: $agent,
            );

            try {
                return $provider->textGateway()->generateText(
                    $provider,
                    $model->model,
                    (string) $agent->instructions(),
                    [new UserMessage($prompt, [])],
                    $tools,
                    $schema,
                    $options,
                    $timeout,
                );
            } catch (Throwable $e) {
                $last = $e;
                if (! $this->isRetryableTokenOrSizeError($e)) {
                    throw $e;
                }

                $next = $attempts[$index + 1] ?? null;
                Log::warning('Laravel AI request hit provider token/size limit — retrying with lower max_tokens', [
                    'model_id' => $model->id,
                    'provider' => $model->provider,
                    'model' => $model->model,
                    'attempted_max_tokens' => $maxTokens,
                    'next_max_tokens' => $next,
                    'error' => $e->getMessage(),
                ]);

                if ($next === null) {
                    break;
                }
            }
        }

        throw $last ?? new \RuntimeException('AI request failed after max_tokens retries.');
    }

    /**
     * Model DB max_tokens is authoritative. preferMinTokens only fills gaps when DB is unset/invalid,
     * and never raises above the configured model limit. maxTokensCap is a hard per-call budget
     * (staged documentation sections) and always wins when lower.
     */
    public function effectiveMaxTokens(
        LaravelAiModel $model,
        ?int $agentDefault,
        ?int $preferMinTokens = null,
        ?int $maxTokensCap = null,
    ): int {
        $db = (int) ($model->max_tokens ?? 0);
        $fallback = $agentDefault ?? 4096;

        if ($db > 0) {
            $raw = $db;
        } else {
            $raw = $fallback;
            if ($preferMinTokens !== null && $preferMinTokens > 0) {
                $raw = max($raw, $preferMinTokens);
            }
        }

        if ($maxTokensCap !== null && $maxTokensCap > 0) {
            $raw = min($raw, $maxTokensCap);
        }

        $ceiling = config('ai.application.completion_tokens_ceiling');
        if ($ceiling !== null && (int) $ceiling > 0) {
            $raw = min($raw, (int) $ceiling);
        }

        return max(1, $raw);
    }

    /**
     * @return list<int>
     */
    public function maxTokenAttempts(int $preferred): array
    {
        $preferred = max(1, $preferred);
        $attempts = [$preferred];

        foreach (self::TOKEN_STEP_DOWN as $step) {
            if ($step < $preferred) {
                $attempts[] = $step;
            }
        }

        return array_values(array_unique($attempts));
    }

    public function isRetryableTokenOrSizeError(Throwable $e): bool
    {
        if ($e instanceof PrismRequestTooLargeException) {
            return true;
        }

        $msg = strtolower($e->getMessage());

        return str_contains($msg, 'too large')
            || str_contains($msg, 'max token')
            || str_contains($msg, 'max_tokens')
            || str_contains($msg, 'context length')
            || str_contains($msg, 'context_length')
            || str_contains($msg, 'token limit')
            || str_contains($msg, 'maximum context')
            || str_contains($msg, 'prompt is too long');
    }
}
