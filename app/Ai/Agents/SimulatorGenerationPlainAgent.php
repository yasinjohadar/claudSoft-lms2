<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Plain JSON output for lesson simulator spec generation.
 */
#[MaxTokens(16384)]
#[Temperature(0.5)]
class SimulatorGenerationPlainAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You output only a single valid JSON object (no markdown fences, no commentary) representing an interactive lesson simulator spec.';
    }
}
