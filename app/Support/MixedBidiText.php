<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * عرض نص يخلط العربية (RTL) مع تاغات HTML أو مقاطع LTR دون تشويه ترتيب الأحرف (BiDi).
 * يدعم التاغات الخام وكذلك التاغات داخل backticks (`<tag>`) ويعرضها كشرائح مرئية آمنة.
 */
final class MixedBidiText
{
    /**
     * تاغ HTML بسيط (أسماء ASCII) كما في أسئلة DOM/البرمجة.
     */
    private const TAG_PATTERN = '/^<\/?[A-Za-z][A-Za-z0-9-]*(?:\s[^<>]*)?\s*\/?>$/u';

    private const SPLIT_PATTERN = '/(<\/?[A-Za-z][A-Za-z0-9-]*(?:\s[^<>]*)?\s*\/?>)/u';

    private const BACKTICK_PATTERN = '/`([^`]+)`/u';

    public static function toHtml(?string $text): HtmlString
    {
        if ($text === null || $text === '') {
            return new HtmlString('');
        }

        $built = self::processWithBackticks($text);

        return new HtmlString($built);
    }

    private static function processWithBackticks(string $text): string
    {
        $out = '';
        $offset = 0;
        while (preg_match(self::BACKTICK_PATTERN, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $matchStart = (int) $m[0][1];
            $matchLen = strlen($m[0][0]);
            $before = substr($text, $offset, $matchStart - $offset);
            $out .= self::processOutsideBackticks($before);
            $out .= self::processBacktickInner((string) $m[1][0]);
            $offset = $matchStart + $matchLen;
        }
        $out .= self::processOutsideBackticks(substr($text, $offset));

        return $out;
    }

    /**
     * نص خارج backticks: تفكيك تاغات HTML خام + تهريب.
     */
    private static function processOutsideBackticks(string $segment): string
    {
        if ($segment === '') {
            return '';
        }

        $segments = preg_split(self::SPLIT_PATTERN, $segment, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if ($segments === false) {
            return htmlspecialchars($segment, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $tagIndices = [];
        foreach ($segments as $index => $part) {
            if (preg_match(self::TAG_PATTERN, $part) === 1) {
                $tagIndices[] = $index;
            }
        }

        $tagCount = count($tagIndices);
        if ($tagCount === 0) {
            return htmlspecialchars($segment, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $wrapWholeAsLtr = preg_match(self::TAG_PATTERN, $segments[0] ?? '') === 1;

        $renderPart = function (string $part, bool $insideSnippet) {
            if (preg_match(self::TAG_PATTERN, $part) === 1) {
                return self::wrapTagChip(htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            }

            $escaped = htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if (! $insideSnippet && self::containsArabic($part)) {
                return self::wrapRtlRun($escaped);
            }

            return $escaped;
        };

        if ($wrapWholeAsLtr) {
            $out = '';
            foreach ($segments as $part) {
                $out .= $renderPart($part, true);
            }

            return self::wrapLtrLine($out);
        }

        $out = '';
        $index = 0;
        $segmentCount = count($segments);

        while ($index < $segmentCount) {
            $part = $segments[$index];

            if (preg_match(self::TAG_PATTERN, $part) !== 1) {
                $out .= $renderPart($part, false);
                $index++;

                continue;
            }

            $snippetEnd = self::findLtrSnippetEnd($segments, $index, false);
            $codeBuffer = '';

            for ($cursor = $index; $cursor <= $snippetEnd; $cursor++) {
                $codeBuffer .= $renderPart($segments[$cursor], true);
            }

            if ($snippetEnd > $index) {
                $out .= self::wrapLtrLine($codeBuffer);
            } else {
                $out .= $codeBuffer;
            }

            $index = $snippetEnd + 1;
        }

        return $out;
    }

    /**
     * يحدد نهاية مقطع LTR المتصل (مثل <h3> HTML </h3>) دون ابتلاع تاغات منفصلة داخل جملة عربية.
     */
    private static function findLtrSnippetEnd(array $segments, int $startIdx, bool $allowArabicInside): int
    {
        if (! preg_match(self::TAG_PATTERN, $segments[$startIdx] ?? '')) {
            return $startIdx;
        }

        $endIdx = $startIdx;

        for ($cursor = $startIdx + 1; $cursor < count($segments); $cursor++) {
            $part = $segments[$cursor];

            if (preg_match(self::TAG_PATTERN, $part) === 1) {
                $endIdx = $cursor;
                if (str_starts_with(ltrim($part), '</')) {
                    return $endIdx;
                }

                continue;
            }

            if (self::containsArabic($part) && ! $allowArabicInside) {
                return $startIdx;
            }

            $endIdx = $cursor;
        }

        return $endIdx;
    }

    private static function containsArabic(string $text): bool
    {
        return preg_match('/\p{Arabic}/u', $text) === 1;
    }

    private static function wrapRtlRun(string $escapedContent): string
    {
        return '<span class="mixed-bidi-rtl-run" dir="rtl" style="unicode-bidi:isolate;">'
            .$escapedContent
            .'</span>';
    }

    private static function wrapLtrLine(string $content): string
    {
        return '<span class="mixed-bidi-ltr-line" dir="ltr" style="unicode-bidi:isolate;">'
            .$content
            .'</span>';
    }

    private static function processBacktickInner(string $inner): string
    {
        $trimmed = trim($inner);
        $escaped = htmlspecialchars($trimmed, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($trimmed !== '' && preg_match(self::TAG_PATTERN, $trimmed) === 1) {
            return self::wrapTagChip($escaped);
        }

        return '<code class="mixed-bidi-inline-code" dir="ltr" style="unicode-bidi:isolate;">'.$escaped.'</code>';
    }

    private static function wrapTagChip(string $escapedContent): string
    {
        return '<span class="mixed-bidi-tag-chip" dir="ltr" translate="no" style="unicode-bidi:isolate;">'
            .$escapedContent
            .'</span>';
    }
}
