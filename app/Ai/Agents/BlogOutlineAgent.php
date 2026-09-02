<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(2048)]
#[Temperature(0.6)]
class BlogOutlineAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You plan blog article outlines. Return JSON matching the schema exactly. '
            .'Sections must read as natural article subheadings (not encyclopedia chapters) '
            .'and must not overlap in content.';
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
