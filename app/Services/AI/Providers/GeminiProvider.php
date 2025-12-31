<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIProviderInterface
{
    protected array $config;
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiUrl = $config['api_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/models';
        $this->model = $config['model_name'] ?? 'gemini-pro';
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->model;
        $url = "{$this->apiUrl}/{$model}:generateContent?key={$this->apiKey}";

        // Convert messages format for Gemini
        $contents = [];
        foreach ($messages as $message) {
            $contents[] = [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message['content']]],
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 2000,
            ],
        ];

        try {
            $response = Http::timeout(120)->post($url, $payload);

            if (!$response->successful()) {
                throw new \Exception('Gemini API error: ' . $response->body());
            }

            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $usageMetadata = $data['usageMetadata'] ?? [];

            return [
                'content' => $content,
                'tokens_used' => [
                    'input' => $usageMetadata['promptTokenCount'] ?? 0,
                    'output' => $usageMetadata['candidatesTokenCount'] ?? 0,
                    'total' => $usageMetadata['totalTokenCount'] ?? 0,
                ],
                'model_used' => $model,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini API request failed', [
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
        return 'Google Gemini';
    }

    public function getType(): string
    {
        return 'gemini';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
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
        return [];
    }

    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        // Gemini Pro pricing (as of 2024)
        $inputPricePer1k = 0.0005;  // $0.0005 per 1K input tokens
        $outputPricePer1k = 0.0015; // $0.0015 per 1K output tokens

        $inputCost = ($inputTokens / 1000) * $inputPricePer1k;
        $outputCost = ($outputTokens / 1000) * $outputPricePer1k;

        return $inputCost + $outputCost;
    }
}

