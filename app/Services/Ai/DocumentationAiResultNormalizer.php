<?php

namespace App\Services\Ai;

use RuntimeException;

/**
 * Unwraps AI documentation payloads that leak JSON into HTML fields,
 * and validates wizard-shaped results before they reach the admin form.
 */
class DocumentationAiResultNormalizer
{
    /**
     * @param  array<string, mixed>|string  $input
     * @return array{
     *     title?: string,
     *     slug?: string,
     *     excerpt?: string,
     *     content?: string,
     *     html?: string,
     *     meta_title?: string,
     *     meta_description?: string
     * }
     */
    public function unwrapPayload(array|string $input): array
    {
        if (is_string($input)) {
            return $this->unwrapString($input);
        }

        $out = [];
        foreach (['title', 'slug', 'excerpt', 'content', 'html', 'meta_title', 'meta_description'] as $key) {
            if (! array_key_exists($key, $input)) {
                continue;
            }
            $value = $input[$key];
            if (! is_string($value) && ! is_numeric($value)) {
                continue;
            }
            $out[$key] = trim((string) $value);
        }

        // Nested JSON accidentally placed in content/html/excerpt
        foreach (['content', 'html', 'excerpt'] as $nestedKey) {
            if (empty($out[$nestedKey]) || ! $this->looksLikeJsonBlob($out[$nestedKey])) {
                continue;
            }
            $nested = $this->unwrapString($out[$nestedKey]);
            if ($nested === []) {
                continue;
            }
            foreach (['title', 'slug', 'excerpt', 'content', 'html', 'meta_title', 'meta_description'] as $key) {
                if (empty($nested[$key])) {
                    continue;
                }
                $current = $out[$key] ?? '';
                $shouldReplace = $current === ''
                    || $key === $nestedKey
                    || $this->looksLikeJsonBlob($current)
                    || ($key === 'title' && $this->looksLikeInstructionPrompt($current))
                    || ($key === 'meta_title' && $this->looksLikeInstructionPrompt($current));
                if ($shouldReplace) {
                    $out[$key] = $nested[$key];
                }
            }
            if (! empty($nested['content']) && $nestedKey === 'content') {
                $out['content'] = $nested['content'];
            }
            if (! empty($nested['html']) && $nestedKey === 'html') {
                $out['html'] = $nested['html'];
            }
        }

        if (! empty($out['content'])) {
            $out['content'] = $this->normalizeHtmlString($out['content']);
        }
        if (! empty($out['html'])) {
            $out['html'] = $this->normalizeHtmlString($out['html']);
        }
        if (! empty($out['excerpt']) && $this->looksLikeJsonBlob($out['excerpt'])) {
            unset($out['excerpt']);
        }

        return $out;
    }

    /**
     * Extract HTML from a section agent response that may be a full wizard JSON blob.
     */
    public function extractSectionHtml(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        if ($this->looksLikeJsonBlob($raw)) {
            $unwrapped = $this->unwrapString($raw);
            $html = trim((string) ($unwrapped['html'] ?? $unwrapped['content'] ?? ''));
            $html = $this->normalizeHtmlString($html);
            if ($html !== '' && $this->isPlausibleHtml($html)) {
                return $html;
            }

            return '';
        }

        $html = $this->normalizeHtmlString($raw);

        return $this->isPlausibleHtml($html) ? $html : '';
    }

    public function looksLikeJsonBlob(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }

        // Strip markdown fences
        if (preg_match('/^```(?:json)?\s*/i', $value)) {
            $value = preg_replace('/^```(?:json)?\s*/i', '', $value) ?? $value;
            $value = preg_replace('/\s*```$/', '', $value) ?? $value;
            $value = trim($value);
        }

        if (($value[0] ?? '') !== '{') {
            return false;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $this->arrayLooksLikeWizardPayload($decoded);
        }

        // Truncated / messy JSON still starts like a wizard payload
        return (bool) preg_match('/"\s*(title|slug|excerpt|content|html)\s*"\s*:/u', $value);
    }

    public function isPlausibleHtml(string $value): bool
    {
        $value = trim($value);
        if ($value === '' || $this->looksLikeJsonBlob($value)) {
            return false;
        }

        return (bool) preg_match(
            '/<(section|h[1-6]|p|div|ul|ol|table|pre|article|header|main)\b/i',
            $value
        );
    }

    public function looksLikeInstructionPrompt(string $title, ?string $topic = null): bool
    {
        $title = trim($title);
        if ($title === '') {
            return false;
        }

        if ($topic !== null && trim($topic) !== '' && mb_strtolower($title) === mb_strtolower(trim($topic))) {
            // Long topics that are full instructions should not be titles
            if (mb_strlen($title) >= 80) {
                return true;
            }
        }

        if (mb_strlen($title) >= 120) {
            return true;
        }

        return (bool) preg_match(
            '/^(قم\s+ب|أنشئ|اكتب|إنشاء|Create\s+|Write\s+|Generate\s+|Please\s+)/ui',
            $title
        );
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array{
     *     title: string,
     *     slug?: string,
     *     excerpt: string,
     *     content: string,
     *     meta_title?: string,
     *     meta_description?: string
     * }
     */
    public function assertWizardShape(array $result, ?string $topic = null): array
    {
        $normalized = $this->unwrapPayload($result);

        $content = trim((string) ($normalized['content'] ?? $normalized['html'] ?? ''));
        if ($content === '' || ! $this->isPlausibleHtml($content)) {
            throw new RuntimeException(
                'استجابة التوليد لا تحتوي HTML صالحاً للصفحة. أعد المحاولة — لا تُحفظ حقول JSON خام.'
            );
        }

        $title = trim((string) ($normalized['title'] ?? ''));
        if ($title === '' || $this->looksLikeInstructionPrompt($title, $topic) || $this->looksLikeJsonBlob($title)) {
            throw new RuntimeException(
                'عنوان الصفحة غير صالح (يشبه الطلب أو JSON). أعد التوليد.'
            );
        }

        $excerpt = trim((string) ($normalized['excerpt'] ?? ''));
        if ($excerpt === '' || $this->looksLikeJsonBlob($excerpt)) {
            $excerpt = $this->excerptFromHtml($content);
        }

        $out = [
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
        ];

        if (! empty($normalized['slug'])) {
            $out['slug'] = trim((string) $normalized['slug']);
        }

        $metaTitle = trim((string) ($normalized['meta_title'] ?? ''));
        if ($metaTitle === '' || $this->looksLikeInstructionPrompt($metaTitle, $topic) || $this->looksLikeJsonBlob($metaTitle)) {
            $metaTitle = mb_substr($title, 0, 60);
        }
        $metaDescription = trim((string) ($normalized['meta_description'] ?? ''));
        if ($metaDescription === '' || $this->looksLikeJsonBlob($metaDescription)) {
            $metaDescription = mb_substr($excerpt, 0, 160);
        }
        $out['meta_title'] = $metaTitle;
        $out['meta_description'] = $metaDescription;

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private function unwrapString(string $input): array
    {
        $value = trim($input);
        if ($value === '') {
            return [];
        }

        if (preg_match('/^```(?:json)?\s*/i', $value)) {
            $value = preg_replace('/^```(?:json)?\s*/i', '', $value) ?? $value;
            $value = preg_replace('/\s*```$/s', '', $value) ?? $value;
            $value = trim($value);
        }

        // Quoted JSON string
        if (($value[0] ?? '') === '"' && str_ends_with($value, '"')) {
            $decodedString = json_decode($value, true);
            if (is_string($decodedString) && trim($decodedString) !== '') {
                $value = trim($decodedString);
            }
        }

        if (($value[0] ?? '') !== '{') {
            if ($this->isPlausibleHtml($value)) {
                return ['content' => $this->normalizeHtmlString($value)];
            }

            return [];
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            // Try first { ... last }
            $start = strpos($value, '{');
            $end = strrpos($value, '}');
            if ($start === false || $end === false || $end <= $start) {
                return [];
            }
            $decoded = json_decode(substr($value, $start, $end - $start + 1), true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return [];
            }
        }

        return $this->unwrapPayload($decoded);
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    private function arrayLooksLikeWizardPayload(array $decoded): bool
    {
        foreach (['title', 'slug', 'excerpt', 'content', 'html'] as $key) {
            if (array_key_exists($key, $decoded)) {
                return true;
            }
        }

        return false;
    }

    public function normalizeHtmlString(string $content): string
    {
        $value = trim($content);
        if ($value === '') {
            return '';
        }

        if (($value[0] ?? '') === '"' && str_ends_with($value, '"')) {
            $decodedString = json_decode($value, true);
            if (is_string($decodedString) && trim($decodedString) !== '') {
                $value = trim($decodedString);
            }
        }

        $value = str_replace(['\\/', '\\"'], ['/', '"'], $value);
        $value = preg_replace('/\\\\r\\\\n|\\\\n|\\\\r/', "\n", $value) ?? $value;
        $value = preg_replace('/\r\n|\r/', "\n", $value) ?? $value;

        return trim($value);
    }

    public function excerptFromHtml(string $content): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return mb_substr(trim($text), 0, 200);
    }
}
