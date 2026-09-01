<?php

namespace App\Services\Simulator;

readonly class SimulatorPhaseAttempt
{
    public function __construct(
        public string $phase,
        public string $label,
        public int $attempt,
        public int $maxTokens,
        public bool $compact,
        public ?string $validationFeedback = null,
    ) {}
}
