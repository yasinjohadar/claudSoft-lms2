<?php

namespace App\Services\AiNew;

use App\Models\LaravelAiModel;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
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

/**
 * Runs Laravel AI text/structured requests using per-model max_tokens and temperature from laravel_ai_models.
 * The #[MaxTokens] attribute on agents is only a fallback when max_tokens is missing or invalid.
 */
class LaravelAiPromptRunner
{
    public function runStructured(
        LaravelAiModel $model,
        Agent&HasStructuredOutput $agent,
        string $prompt,
        int $timeout = 120,
        ?int $preferMinTokens = null,
    ): StructuredAgentResponse {
        $schema = $agent->schema(new JsonSchemaTypeFactory);
        $textResponse = $this->invokeGateway($model, $agent, $prompt, $schema, $timeout, $preferMinTokens);

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
    ): AgentResponse {
        $textResponse = $this->invokeGateway($model, $agent, $prompt, null, $timeout, $preferMinTokens);
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
    ): TextResponse {
        $provider = Ai::textProviderFor($agent, $model->provider);
        $options = $this->buildOptions($model, $agent, $preferMinTokens);
        $tools = $agent instanceof HasTools ? $agent->tools() : [];

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
    }

    private function buildOptions(LaravelAiModel $model, Agent $agent, ?int $preferMinTokens = null): TextGenerationOptions
    {
        $base = TextGenerationOptions::forAgent($agent);
        $maxTokens = $this->effectiveMaxTokens($model, $base->maxTokens, $preferMinTokens);
        $temperature = $model->temperature !== null
            ? (float) $model->temperature
            : $base->temperature;

        return new TextGenerationOptions(
            maxSteps: $base->maxSteps,
            maxTokens: $maxTokens,
            temperature: $temperature,
            agent: $agent,
        );
    }

    private function effectiveMaxTokens(LaravelAiModel $model, ?int $agentDefault, ?int $preferMinTokens = null): int
    {
        $db = (int) ($model->max_tokens ?? 0);
        $fallback = $agentDefault ?? 4096;
        $raw = $db > 0 ? $db : $fallback;

        if ($preferMinTokens !== null && $preferMinTokens > 0) {
            $raw = max($raw, $preferMinTokens);
        }

        $ceiling = config('ai.application.completion_tokens_ceiling');
        if ($ceiling !== null && (int) $ceiling > 0) {
            $raw = min($raw, (int) $ceiling);
        }

        return max(1, $raw);
    }
}
