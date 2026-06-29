<?php

namespace App\Services\Quiz;

class QuizRandomSelectionResult
{
    /**
     * @param  array<int>  $questionIds
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public array $questionIds,
        public float $maxScore,
        public bool $recycled,
        public array $meta = [],
    ) {}

    public function selectionMeta(): array
    {
        return array_merge($this->meta, [
            'recycled' => $this->recycled,
            'selected_count' => count($this->questionIds),
        ]);
    }
}
