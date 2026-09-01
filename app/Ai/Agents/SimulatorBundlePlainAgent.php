<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Writes one bundle file (HTML, CSS, or JS) as raw text for the staged
 * simulator pipeline.
 *
 * Deliberately not a HasStructuredOutput agent — wrapping code in a JSON field
 * forces the model to escape every newline, which is what previously collapsed
 * generated code onto a single line.
 */
#[MaxTokens(16384)]
#[Temperature(0.5)]
class SimulatorBundlePlainAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You write exactly one file (HTML, CSS, or JavaScript) for an interactive lesson simulator, following the rules in the user message. '
            .'Output the raw file content only: no JSON, no markdown fences, no commentary before or after. '
            .'Keep real line breaks and indentation — never put a whole file on one line.';
    }
}
