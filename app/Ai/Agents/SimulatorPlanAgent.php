<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(3072)]
#[Temperature(0.55)]
class SimulatorPlanAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You plan interactive lesson simulators. Return JSON matching the schema exactly. '
            .'Cover the topic comprehensively and pick the correct output language code and text direction.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'description' => $schema->string()->required(),
            'archetype' => $schema->string()->required(),
            'lang_code' => $schema->string()->required(),
            'text_direction' => $schema->string()->required(),
            'key_elements' => $schema->array()->items($schema->string())->required(),
            'interactions' => $schema->array()->items($schema->string())->required(),
            'coverage_checklist' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
