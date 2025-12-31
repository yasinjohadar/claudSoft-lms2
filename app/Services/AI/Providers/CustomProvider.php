<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomProvider implements AIProviderInterface
{
    protected array $config;
    protected string $apiKey;
    protected string $apiUrl;
    protected string $model;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->apiUrl = $config['api_url'] ?? '';
        $this->model = $config['model_name'] ?? 'default';
    }

    public function chat(array $messages, array $options = []): array
    {
        // Custom provider - adapt based on API format
        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 2000,
        ];

        // Allow custom payload format
        if (isset($this->config['payload_format'])) {
            $payload = $this->formatPayload($messages, $options);
        }

        $headers = [
            'Content-Type' => 'application/json',
        ];

        // Add API key based on auth type
        $authType = $this->config['auth_type'] ?? 'bearer';
        if ($authType === 'bearer' && !empty($this->apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        } elseif ($authType === 'header' && !empty($this->apiKey)) {
            $headerName = $this->config['api_key_header'] ?? 'X-API-Key';
            $headers[$headerName] = $this->apiKey;
        } elseif ($authType === 'query' && !empty($this->apiKey)) {
            // API key in query string
            $separator = strpos($this->apiUrl, '?') !== false ? '&' : '?';
            $this->apiUrl .= $separator . 'api_key=' . $this->apiKey;
        }

        try {
            $response = Http::withHeaders($headers)->timeout(120)->post($this->apiUrl, $payload);

            if (!$response->successful()) {
                throw new \Exception('Custom API error: ' . $response->body());
            }

            $data = $response->json();
            
            // Parse response based on custom format
            $content = $this->extractContent($data);
            $tokens = $this->extractTokens($data);

            return [
                'content' => $content,
                'tokens_used' => $tokens,
                'model_used' => $this->model,
            ];
        } catch (\Exception $e) {
            Log::error('Custom AI API request failed', [
                'error' => $e->getMessage(),
                'url' => $this->apiUrl,
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
        return $this->config['name'] ?? 'Custom Provider';
    }

    public function getType(): string
    {
        return 'custom';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiUrl);
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
        // Custom pricing from config
        $inputPricePer1k = $this->config['input_price_per_1k'] ?? 0.01;
        $outputPricePer1k = $this->config['output_price_per_1k'] ?? 0.03;

        $inputCost = ($inputTokens / 1000) * $inputPricePer1k;
        $outputCost = ($outputTokens / 1000) * $outputPricePer1k;

        return $inputCost + $outputCost;
    }

    /**
     * Format payload based on custom format
     */
    protected function formatPayload(array $messages, array $options): array
    {
        $format = $this->config['payload_format'] ?? 'openai';
        
        // Add custom formatting logic here
        return [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 2000,
        ];
    }

    /**
     * Extract content from response based on custom format
     */
    protected function extractContent(array $data): string
    {
        $contentPath = $this->config['response_content_path'] ?? 'choices.0.message.content';
        
        $keys = explode('.', $contentPath);
        $value = $data;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return '';
            }
            $value = $value[$key];
        }
        
        return is_string($value) ? $value : '';
    }

    /**
     * Extract tokens from response
     */
    protected function extractTokens(array $data): array
    {
        $usagePath = $this->config['response_usage_path'] ?? 'usage';
        $inputPath = $this->config['response_input_tokens_path'] ?? 'prompt_tokens';
        $outputPath = $this->config['response_output_tokens_path'] ?? 'completion_tokens';
        
        $usage = $data;
        if ($usagePath !== '') {
            $keys = explode('.', $usagePath);
            foreach ($keys as $key) {
                if (isset($usage[$key])) {
                    $usage = $usage[$key];
                }
            }
        }
        
        return [
            'input' => $usage[$inputPath] ?? 0,
            'output' => $usage[$outputPath] ?? 0,
            'total' => ($usage[$inputPath] ?? 0) + ($usage[$outputPath] ?? 0),
        ];
    }
}

