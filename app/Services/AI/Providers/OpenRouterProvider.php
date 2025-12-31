<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterProvider implements AIProviderInterface
{
    protected array $config;
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiUrl = $config['api_url'] ?? 'https://openrouter.ai/api/v1/chat/completions';
        $this->model = $config['model_name'] ?? 'openai/gpt-4';
    }

    public function chat(array $messages, array $options = []): array
    {
        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 2000,
        ];

        // OpenRouter specific headers
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => $this->config['http_referer'] ?? url('/'),
            'X-Title' => $this->config['app_name'] ?? 'Claudsoft Academy',
        ];

        try {
            $response = Http::withHeaders($headers)->timeout(120)->post($this->apiUrl, $payload);

            if (!$response->successful()) {
                throw new \Exception('OpenRouter API error: ' . $response->body());
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $usage = $data['usage'] ?? [];

            return [
                'content' => $content,
                'tokens_used' => [
                    'input' => $usage['prompt_tokens'] ?? 0,
                    'output' => $usage['completion_tokens'] ?? 0,
                    'total' => $usage['total_tokens'] ?? 0,
                ],
                'model_used' => $data['model'] ?? $this->model,
            ];
        } catch (\Exception $e) {
            Log::error('OpenRouter API request failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            throw $e;
        }
    }

    public function generateText(string $prompt, array $options = []): array
    {
        return $this->chat([
            ['role' => 'user', 'content' => $prompt]
        ], $options);
    }

    public function getName(): string
    {
        return 'OpenRouter';
    }

    public function getType(): string
    {
        return 'openrouter';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiUrl);
    }

    public function testConnection(): array
    {
        try {
            $result = $this->generateText('Test', ['max_tokens' => 5]);
            return [
                'success' => true,
                'message' => 'Connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getUsageStats(): array
    {
        // OpenRouter provides usage stats via separate endpoint
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get('https://openrouter.ai/api/v1/auth/key');

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch OpenRouter usage stats', ['error' => $e->getMessage()]);
        }

        return [];
    }

    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        // OpenRouter pricing varies by model
        // This should be configured per model or fetched from API
        // Default approximation
        $inputPricePer1k = 0.01;
        $outputPricePer1k = 0.03;

        $inputCost = ($inputTokens / 1000) * $inputPricePer1k;
        $outputCost = ($outputTokens / 1000) * $outputPricePer1k;

        return $inputCost + $outputCost;
    }
}

