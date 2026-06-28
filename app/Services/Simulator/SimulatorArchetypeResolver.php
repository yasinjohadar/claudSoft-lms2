<?php

namespace App\Services\Simulator;

class SimulatorArchetypeResolver
{
    /**
     * @param  'playground'|'stepper'|'auto'|null  $requested
     */
    public function resolve(string $primaryLanguage, ?string $requested = 'auto'): string
    {
        if (in_array($requested, ['playground', 'stepper'], true)) {
            return $requested;
        }

        return in_array($primaryLanguage, ['html', 'css'], true) ? 'playground' : 'stepper';
    }
}
