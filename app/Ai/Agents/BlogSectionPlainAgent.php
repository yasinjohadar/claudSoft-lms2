<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Writes one blog article section as raw HTML.
 *
 * Deliberately not a HasStructuredOutput agent: wrapping the section in a JSON
 * field forced the model to escape every newline, and it answered by writing code
 * samples on a single line. Plain text keeps the line breaks intact.
 */
#[MaxTokens(4096)]
#[Temperature(0.7)]
class BlogSectionPlainAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You write one section of a blog article as raw HTML, following the style guide in the user message. '
            .'Use only h2, h3, p, ul, ol, li, blockquote, strong, em, and pre/code where a code sample truly helps. '
            .'Output HTML only: no JSON, no markdown fences, no commentary before or after. '
            .'Inside <pre><code> blocks keep real line breaks and indentation — never put a whole program on one line.';
    }
}
