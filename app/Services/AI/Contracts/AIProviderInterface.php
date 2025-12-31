<?php

namespace App\Services\AI\Contracts;

interface AIProviderInterface
{
    /**
     * Send a chat completion request
     *
     * @param array $messages Array of messages with 'role' and 'content'
     * @param array $options Additional options (temperature, max_tokens, etc.)
     * @return array Response with 'content', 'tokens_used', 'model_used'
     */
    public function chat(array $messages, array $options = []): array;

    /**
     * Generate text from a prompt
     *
     * @param string $prompt The prompt text
     * @param array $options Additional options
     * @return array Response with 'content', 'tokens_used', 'model_used'
     */
    public function generateText(string $prompt, array $options = []): array;

    /**
     * Get the provider name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the provider type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Check if provider is available/configured
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Test the connection to the provider
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(): array;

    /**
     * Get usage statistics
     *
     * @return array
     */
    public function getUsageStats(): array;

    /**
     * Calculate cost for tokens
     *
     * @param int $inputTokens
     * @param int $outputTokens
     * @return float
     */
    public function calculateCost(int $inputTokens, int $outputTokens): float;
}

