<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class LaravelAiModel extends Model
{
    protected $table = 'laravel_ai_models';

    protected $fillable = [
        'name',
        'provider',
        'model',
        'api_key',
        'base_url',
        'is_active',
        'priority',
        'capabilities',
        'max_tokens',
        'temperature',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'capabilities' => 'array',
        'max_tokens' => 'integer',
        'temperature' => 'float',
    ];

    protected ?string $rawApiKey = null;

    /**
     * Provider keys must match Laravel AI SDK enum Lab string values
     * (literals avoid loading laravel/ai when listing models in admin).
     */
    public static function providerLabels(): array
    {
        return [
            'anthropic' => 'Anthropic',
            'azure' => 'Azure OpenAI',
            'cohere' => 'Cohere',
            'deepseek' => 'DeepSeek',
            'eleven' => 'ElevenLabs',
            'gemini' => 'Google Gemini',
            'groq' => 'Groq',
            'jina' => 'Jina',
            'mistral' => 'Mistral',
            'ollama' => 'Ollama',
            'openai' => 'OpenAI',
            'openrouter' => 'OpenRouter',
            'voyageai' => 'Voyage AI',
            'xai' => 'xAI',
        ];
    }

    public static function allowedProviders(): array
    {
        return array_keys(self::providerLabels());
    }

    public static function capabilityLabels(): array
    {
        return [
            'blog.generate' => 'توليد مقالات (Laravel AI)',
            'docs.refine' => 'توثيق / أقسام (Laravel AI)',
            'questions.generate' => 'توليد أسئلة (Laravel AI)',
            'simulator.generate' => 'توليد محاكيات الدروس (Laravel AI)',
            'reports.student_progress' => 'تقارير تقدم الطلاب (Laravel AI)',
            'content.general' => 'نص عام (Laravel AI)',
        ];
    }

    public function setApiKeyAttribute($value): void
    {
        if ($value !== null && $value !== '') {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        }
    }

    public function setRawApiKeyForTesting(string $apiKey): void
    {
        $this->rawApiKey = $apiKey;
    }

    public function getDecryptedApiKey(): ?string
    {
        if ($this->rawApiKey !== null) {
            return $this->rawApiKey;
        }

        $apiKey = $this->attributes['api_key'] ?? null;

        if (empty($apiKey)) {
            return null;
        }

        try {
            return Crypt::decryptString($apiKey);
        } catch (\Exception $e) {
            return $apiKey;
        }
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LaravelAiLog::class, 'laravel_ai_model_id');
    }

    public function scopeActiveOrdered(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderByDesc('priority')->orderByDesc('id');
    }

    public function scopeForCapability(Builder $query, string $capability): Builder
    {
        return $query->where(function (Builder $q) use ($capability) {
            $q->whereNull('capabilities')
                ->orWhereJsonContains('capabilities', $capability);
        });
    }

    public function scopeFilterActive(Builder $query, ?bool $active): Builder
    {
        if ($active === null) {
            return $query;
        }

        return $query->where('is_active', $active);
    }
}
