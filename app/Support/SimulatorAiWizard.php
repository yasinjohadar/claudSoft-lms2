<?php

namespace App\Support;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Models\LaravelAiModel;
use App\Services\Ai\AIModelService;
use App\Services\Simulator\SimulatorTopicRegistry;

class SimulatorAiWizard
{
    use UsesLaravelAiSdkForWizards;

    /**
     * @return array<string, mixed>
     */
    public static function viewData(): array
    {
        $instance = new self;
        $modelService = app(AIModelService::class);

        $legacyModels = $modelService->getAvailableModels('simulator_generation');
        if ($legacyModels->isEmpty()) {
            $legacyModels = $modelService->getAvailableModels('all');
        }

        $laravelAiModels = LaravelAiModel::query()->activeOrdered()->get();

        return [
            'topics' => SimulatorTopicRegistry::groupedForSelect(),
            'primaryLanguages' => config('simulator.primary_languages', []),
            'levels' => config('simulator.levels', []),
            'archetypes' => config('simulator.archetypes', []),
            'legacyModels' => $legacyModels,
            'laravelAiModels' => $laravelAiModels,
            'useLaravelAiEngine' => $instance->wizardUsesLaravelAiSdk('simulators_engine'),
            'simulatorsEngineChoiceAvailable' => $legacyModels->isNotEmpty() && $laravelAiModels->isNotEmpty(),
        ];
    }
}
