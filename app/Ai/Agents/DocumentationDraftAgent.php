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
#[Temperature(0.5)]
class DocumentationDraftAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You write technical documentation. Return structured sections with headings and HTML body per section, plus a short summary.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema->string()->required(),
            'sections' => $schema->array()
                ->min(1)
                ->items($schema->object([
                    'heading' => $schema->string()->required(),
                    'body_html' => $schema->string()->required(),
                ]))
                ->required(),
        ];
    }
}
