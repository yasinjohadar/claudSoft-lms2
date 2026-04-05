<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\LaravelAiModel;

trait UsesLaravelAiSdkForWizards
{
    /**
     * @param  'blog_engine'|'docs_engine'|'questions_engine'|'reports_engine'  $featureEngineKey
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
}
