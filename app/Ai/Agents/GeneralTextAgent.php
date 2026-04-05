<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(8192)]
class GeneralTextAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are a concise assistant. Answer clearly and briefly unless asked otherwise.';
    }
}
