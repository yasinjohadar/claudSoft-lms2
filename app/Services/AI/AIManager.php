<?php

namespace App\Services\AI;

use App\Models\AIProvider;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\GLMProvider;
use App\Services\AI\Providers\OpenRouterProvider;
use App\Services\AI\Providers\CustomProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIManager
{
    protected array $providers = [];
    protected ?AIProviderInterface $defaultProvider = null;

    /**
     * Get a provider instance
     *
     * @param string|null $providerName Provider name or null for default
     * @return AIProviderInterface
     * @throws \Exception
     */
    public function provider(?string $providerName = null): AIProviderInterface
    {
        // If no provider specified, use default
        if ($providerName === null) {
            return $this->getDefaultProvider();
        }

        // Check cache first
        $cacheKey = "ai_provider_{$providerName}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Get provider from database
        $providerModel = AIProvider::where('name', $providerName)
            ->where('is_active', true)
            ->first();

        if (!$providerModel) {
            throw new \Exception("Provider '{$providerName}' not found or inactive");
        }

        // Create provider instance
        $provider = $this->createProviderInstance($providerModel);
        
        // Cache for 1 hour
        Cache::put($cacheKey, $provider, 3600);

        return $provider;
    }

    /**
     * Get default provider
     *
     * @return AIProviderInterface
     * @throws \Exception
     */
    public function getDefaultProvider(): AIProviderInterface
    {
        if ($this->defaultProvider !== null) {
            return $this->defaultProvider;
        }

        $providerModel = AIProvider::where('is_default', true)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->first();

        if (!$providerModel) {
            // Try to get any active provider
            $providerModel = AIProvider::where('is_active', true)
                ->orderBy('priority', 'desc')
                ->first();
        }

        if (!$providerModel) {
            throw new \Exception('No active AI provider found');
        }

        $this->defaultProvider = $this->createProviderInstance($providerModel);
        return $this->defaultProvider;
    }

    /**
     * Create provider instance from model
     *
     * @param AIProvider $providerModel
     * @return AIProviderInterface
     * @throws \Exception
     */
    protected function createProviderInstance(AIProvider $providerModel): AIProviderInterface
    {
        $type = $providerModel->type;
        $config = array_merge($providerModel->config ?? [], [
            'api_key' => $providerModel->api_key,
            'api_url' => $providerModel->api_url,
            'model_name' => $providerModel->model_name,
        ]);

        return match ($type) {
            'openai' => new OpenAIProvider($config),
            'gemini' => new GeminiProvider($config),
            'glm' => new GLMProvider($config),
            'openrouter' => new OpenRouterProvider($config),
            'custom' => new CustomProvider($config),
            default => throw new \Exception("Unknown provider type: {$type}"),
        };
    }

    /**
     * Get all available providers
     *
     * @return array
     */
    public function getAvailableProviders(): array
    {
        return AIProvider::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get()
            ->map(function ($provider) {
                return [
                    'id' => $provider->id,
                    'name' => $provider->name,
                    'type' => $provider->type,
                    'is_default' => $provider->is_default,
                ];
            })
            ->toArray();
    }

    /**
     * Try multiple providers in order until one succeeds
     *
     * @param callable $callback Function that receives AIProviderInterface
     * @param array $providerNames Ordered list of provider names to try
     * @return mixed
     * @throws \Exception
     */
    public function tryProviders(callable $callback, array $providerNames = [])
    {
        if (empty($providerNames)) {
            $providerNames = AIProvider::where('is_active', true)
                ->orderBy('priority', 'desc')
                ->pluck('name')
                ->toArray();
        }

        $lastException = null;

        foreach ($providerNames as $providerName) {
            try {
                $provider = $this->provider($providerName);
                return $callback($provider);
            } catch (\Exception $e) {
                Log::warning("AI Provider '{$providerName}' failed", [
                    'error' => $e->getMessage(),
                ]);
                $lastException = $e;
                continue;
            }
        }

        throw new \Exception('All AI providers failed', 0, $lastException);
    }
}

