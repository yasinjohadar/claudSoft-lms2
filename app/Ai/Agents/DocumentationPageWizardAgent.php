<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Structured output aligned with the legacy documentation AI wizard (title, slug, excerpt, HTML content).
 */
#[MaxTokens(8192)]
#[Temperature(0.65)]
class DocumentationPageWizardAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You write technical documentation pages. Output must match the JSON schema exactly. Put full page HTML only in "content" as described in the user message.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'slug' => $schema->string()->required(),
            'excerpt' => $schema->string()->required(),
            'content' => $schema->string()->required(),
        ];
    }
}
