<?php

namespace App\Services\AiNew;

use App\Models\LaravelAiModel;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Laravel\Ai\Enums\Lab;

/**
 * Applies per-row API credentials to config('ai.providers.*') for the duration of a callback,
 * then restores the previous values in a finally block. Under Laravel Octane, avoid leaving
 * mutated provider config across requests — always use this manager (or run in an isolated worker).
 */
class LaravelAiProviderManager
{
    public function configKeyForModel(LaravelAiModel $model): string
    {
        $key = $model->provider;

        if (! is_string($key) || $key === '') {
            throw new InvalidArgumentException('Invalid provider on Laravel AI model.');
        }

        if (! array_key_exists($key, config('ai.providers', []))) {
            throw new InvalidArgumentException("Unknown AI provider config key [{$key}].");
        }

        return $key;
    }

    public function labForModel(LaravelAiModel $model): Lab
    {
        return Lab::from($model->provider);
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function runWithModel(LaravelAiModel $model, callable $callback): mixed
    {
        $configKey = $this->configKeyForModel($model);
        $apiKey = $model->getDecryptedApiKey();

        if ($apiKey === null || $apiKey === '') {
            throw new InvalidArgumentException('API key is missing for this model.');
        }

        $previous = Config::get("ai.providers.{$configKey}");

        $merged = is_array($previous) ? $previous : [];
        $merged['key'] = $apiKey;

        if (! empty($model->base_url)) {
            $merged['url'] = $model->base_url;
        }

        Config::set("ai.providers.{$configKey}", $merged);

        try {
            return $callback();
        } finally {
            Config::set("ai.providers.{$configKey}", $previous);
        }
    }
}
