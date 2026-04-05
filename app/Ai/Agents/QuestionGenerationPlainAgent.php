<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Plain-text JSON array output for question bank generation (parsed by AIQuestionGenerationService).
 */
#[MaxTokens(16384)]
#[Temperature(0.7)]
class QuestionGenerationPlainAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You output only a single valid JSON array (no markdown fences, no commentary). Each object must match the fields described in the user message.';
    }
}
