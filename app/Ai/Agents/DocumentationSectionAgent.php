<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(8192)]
#[Temperature(0.65)]
class DocumentationSectionAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You write one documentation section as HTML. Return JSON matching the schema. Put only that section HTML in "html" using the style guide from the user message. Do not repeat other sections.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'html' => $schema->string()->required(),
        ];
    }
}
