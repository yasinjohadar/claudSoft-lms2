<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AIModel;
use App\Models\LaravelAiModel;

trait UsesLaravelAiSdkForWizards
{
    /**
     * @param  'blog_engine'|'docs_engine'|'questions_engine'|'reports_engine'|'simulators_engine'  $featureEngineKey
     */
    protected function wizardUsesLaravelAiSdk(string $featureEngineKey): bool
    {
        if (config('ai.application.engine') === 'laravel_ai') {
            return true;
        }

        $engine = config("ai.application.{$featureEngineKey}");
        if ($engine === 'laravel_ai') {
            return true;
        }
        if ($engine === 'legacy') {
            return false;
        }

        return LaravelAiModel::query()->where('is_active', true)->exists();
    }

    /**
     * تجاوز محرك المعالج لكل طلب: laravel_ai أو legacy؛ وإلا السلوك الافتراضي من الإعدادات.
     *
     * @param  'blog_engine'|'docs_engine'|'questions_engine'|'reports_engine'|'simulators_engine'  $featureEngineKey
     */
    protected function resolveWizardAiEngine(?string $requestedEngine, string $featureEngineKey): bool
    {
        if ($requestedEngine === 'laravel_ai') {
            return true;
        }
        if ($requestedEngine === 'legacy') {
            return false;
        }

        return $this->wizardUsesLaravelAiSdk($featureEngineKey);
    }

    /**
     * Resolve documentation AI stack for refine/enhance/generate requests.
     * Honors explicit docs_engine, then explicit model ids, then available stacks.
     */
    protected function resolveDocumentationAiEngine(
        ?string $requestedEngine,
        ?int $laravelAiModelId = null,
        ?int $legacyAiModelId = null,
    ): bool {
        if ($requestedEngine === 'laravel_ai') {
            return true;
        }
        if ($requestedEngine === 'legacy') {
            return false;
        }

        if ($laravelAiModelId && LaravelAiModel::query()->whereKey($laravelAiModelId)->where('is_active', true)->exists()) {
            return true;
        }

        if ($legacyAiModelId && AIModel::query()->whereKey($legacyAiModelId)->exists()) {
            return false;
        }

        $hasLaravel = LaravelAiModel::query()->where('is_active', true)->exists();
        $hasLegacy = AIModel::query()->where('is_active', true)->exists();

        if ($hasLaravel && ! $hasLegacy) {
            return true;
        }
        if ($hasLegacy && ! $hasLaravel) {
            return false;
        }

        return $this->wizardUsesLaravelAiSdk('docs_engine');
    }
}
