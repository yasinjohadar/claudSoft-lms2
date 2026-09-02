<?php

namespace App\Services\AiNew;

/**
 * One rung of the per-section retry ladder handed to the engine-specific writer.
 */
class BlogSectionAttempt
{
    /**
     * @param  list<string>  $priorHeadings  headings already written, for continuity
     * @param  list<string>  $laterHeadings  headings still to come, so this section does not cover them
     */
    public function __construct(
        public readonly string $heading,
        public readonly string $brief,
        public readonly array $priorHeadings,
        public readonly int $attempt,
        public readonly int $maxTokens,
        public readonly bool $compact,
        public readonly array $laterHeadings = [],
    ) {}
}
