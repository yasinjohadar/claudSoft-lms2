<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Writes one documentation section as raw HTML.
 *
 * Deliberately not a HasStructuredOutput agent: wrapping the section in a JSON
 * field forced the model to escape every newline, and it answered by writing code
 * samples on a single line. Plain text keeps the line breaks intact.
 */
#[MaxTokens(8192)]
#[Temperature(0.65)]
class DocumentationSectionPlainAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You write one documentation section as raw HTML, following the style guide in the user message. '
            .'Output HTML only: no JSON, no markdown fences, no commentary before or after. '
            .'Inside <pre><code> blocks keep real line breaks and indentation — never put a whole program on one line.';
    }
}
