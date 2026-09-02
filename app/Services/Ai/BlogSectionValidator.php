<?php

namespace App\Services\Ai;

/**
 * Decides whether a repaired blog section is good enough to keep.
 *
 * Forked from DocumentationSectionValidator: same shape, but checks for a plain
 * `<h2` heading instead of the docs-only `section-title` class, and uses lower
 * text-length floors since blog sections are shorter prose than doc pages.
 */
class BlogSectionValidator
{
    public function __construct(
        private BlogHtmlRepairer $repairer = new BlogHtmlRepairer,
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

        if (! str_contains($html, '<h2')) {
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

    /** Minimum visible text per section. */
    public function minTextLength(string $contentLength, bool $compact): int
    {
        if ($compact) {
            return 140;
        }

        return match ($contentLength) {
            'short' => 180,
            'long' => 380,
            default => 260,
        };
    }
}
