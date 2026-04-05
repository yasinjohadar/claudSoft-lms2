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
class DocumentationRefineContentAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are an expert technical documentation editor. Return JSON matching the schema: improved full HTML in "content". Use null for "excerpt" unless the user message explicitly requires a plain-text excerpt.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()->required(),
            'excerpt' => $schema->string()->nullable(),
        ];
    }
}
