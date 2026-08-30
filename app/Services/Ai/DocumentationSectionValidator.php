<?php

namespace App\Services\Ai;

/**
 * Decides whether a repaired section is good enough to keep.
 *
 * The repairer fixes what it can prove; this catches what is left — a code block
 * still collapsed onto one line, or a section so short the page stops being the
 * comprehensive reference the author asked for. Rejecting here costs one more
 * model call; accepting stores a page nobody wants to read.
 */
class DocumentationSectionValidator
{
    public function __construct(
        private DocumentationHtmlRepairer $repairer = new DocumentationHtmlRepairer,
    ) {}

    /**
     * @return string|null a short reason to reject, or null when the section is fine
     */
    public function rejectionReason(string $html, string $contentLength, bool $compact): ?string
    {
        $html = trim($html);
        if ($html === '') {
            return 'empty';
        }

        if (! str_contains($html, 'section-title')) {
            return 'missing_heading';
        }

        if ($this->repairer->hasUnsplitCodeBlock($html)) {
            return 'one_line_code';
        }

        $textLength = mb_strlen(trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? ''));
        if ($textLength < $this->minTextLength($contentLength, $compact)) {
            return 'too_short';
        }

        return null;
    }

    /**
     * Minimum visible text per section.
     *
     * Long pages were coming back with ~350-character sections because the retry
     * ladder kept falling to its compact rungs; the floor is what stops that from
     * passing silently.
     */
    public function minTextLength(string $contentLength, bool $compact): int
    {
        if ($compact) {
            return 320;
        }

        return match ($contentLength) {
            'short' => 420,
            'long' => 900,
            default => 650,
        };
    }
}
