<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(4096)]
#[Temperature(0.55)]
class DocumentationOutlineAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You plan technical documentation pages. Return JSON matching the schema exactly. Sections must cover the topic comprehensively without overlapping.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'slug' => $schema->string()->required(),
            'excerpt' => $schema->string()->required(),
            'sections' => $schema->array()
                ->min(2)
                ->items($schema->object([
                    'heading' => $schema->string()->required(),
                    'brief' => $schema->string()->required(),
                ]))
                ->required(),
        ];
    }
}
