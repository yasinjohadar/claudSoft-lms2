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
#[Temperature(0.35)]
class DocumentationEnhanceContentAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are an expert technical documentation editor in ENHANCE mode. Preserve all existing sections, paragraphs, and code unless the user explicitly asks to remove them. Add new content professionally per the user instructions. Return JSON with full improved HTML in "content". Use null for "excerpt" unless explicitly requested.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()->required(),
            'excerpt' => $schema->string()->nullable(),
        ];
    }
}
