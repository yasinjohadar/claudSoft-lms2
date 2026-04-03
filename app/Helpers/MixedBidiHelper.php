<?php

use App\Support\MixedBidiText;
use Illuminate\Support\HtmlString;

if (! function_exists('mixed_bidi_html')) {
    /**
     * نص آمن للعرض في Blade مع عزل تاغات HTML ضمن سياق RTL (مثل نصوص الأسئلة).
     */
    function mixed_bidi_html(?string $text): HtmlString
    {
        return MixedBidiText::toHtml($text);
    }
}
