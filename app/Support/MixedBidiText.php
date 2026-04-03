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

        $out = '';
        foreach ($segments as $part) {
            if (preg_match(self::TAG_PATTERN, $part) === 1) {
                $escaped = htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $out .= self::wrapTagChip($escaped);
            } else {
                $out .= htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
        }

        return $out;
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
