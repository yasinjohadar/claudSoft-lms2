<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(256)]
class PingAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are a connectivity check. Reply with exactly: OK';
    }
}
