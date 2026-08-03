<?php

namespace App\Services\AiNew;

/**
 * One rung of the per-section retry ladder handed to the engine-specific writer.
 *
 * @param  list<string>  $priorHeadings
 */
class DocumentationSectionAttempt
{
    /**
     * @param  list<string>  $priorHeadings
     */
    public function __construct(
        public readonly string $heading,
        public readonly string $brief,
        public readonly array $priorHeadings,
        public readonly int $attempt,
        public readonly int $maxTokens,
        public readonly bool $compact,
        public readonly bool $plain,
    ) {}
}
