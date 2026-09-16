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
            .'and must not overlap in content. '
            .'Also suggest 3 to 6 short topical tags for the article (1-3 words each, '
            .'written in the same language as the article, no hashtags, no duplicates).';
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
            // Optional on purpose: the single-article wizard predates tags and must
            // keep working against a model that omits them.
            'tags' => $schema->array()->items($schema->string()),
        ];
    }
}
