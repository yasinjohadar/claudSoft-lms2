<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    protected array $config;
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiUrl = $config['api_url'] ?? 'https://api.openai.com/v1/chat/completions';
        $this->model = $config['model_name'] ?? 'gpt-4';
    }

    public function chat(array $messages, array $options = []): array
    {
        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 2000,
        ];

        if (isset($options['stream'])) {
            $payload['stream'] = $options['stream'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($this->apiUrl, $payload);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API error: ' . $response->body());
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
            Log::error('OpenAI API request failed', [
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
        return 'OpenAI';
    }

    public function getType(): string
    {
        return 'openai';
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
        // OpenAI doesn't provide usage stats via API in the same way
        // This would need to be tracked separately
        return [];
    }

    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        // Pricing for GPT-4 (as of 2024)
        // These should be configurable
        $inputPricePer1k = 0.03;  // $0.03 per 1K input tokens
        $outputPricePer1k = 0.06; // $0.06 per 1K output tokens

        $inputCost = ($inputTokens / 1000) * $inputPricePer1k;
        $outputCost = ($outputTokens / 1000) * $outputPricePer1k;

        return $inputCost + $outputCost;
    }
}

