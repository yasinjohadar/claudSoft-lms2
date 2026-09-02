<?php

namespace App\Services\Ai;

/**
 * The single description of what generated blog-article HTML must look like.
 *
 * Blog posts render through `.article-content` (see resources/views/frontend2/pages/blog-show.blade.php
 * and resources/views/frontend/pages/blog/show.blade.php), which only styles plain
 * semantic tags — h2, h3, p, ul, ol, li, blockquote, img, code, pre>code. There is no
 * `.content-section` / `.section-title` / `.info-box` / `.styled-table` class anywhere
 * in the blog stylesheets (those belong to the documentation pages only), so this guide
 * must not borrow the documentation vocabulary.
 */
class BlogHtmlStyleGuide
{
    /** Markup vocabulary the blog stylesheet actually supports. */
    public static function markup(): string
    {
        return <<<'GUIDE'
- عنوان القسم: <h2>النص</h2> (عنوان واحد فقط لكل قسم)
- عناوين فرعية داخل القسم: <h3>…</h3>
- فقرات: <p>…</p>
- قوائم: <ul><li>…</li></ul> أو <ol><li>…</li></ol>
- اقتباس: <blockquote><p>…</p></blockquote>
- تمييز داخل النص: <strong>…</strong> أو <em>…</em>
- اسم أو مصطلح قصير داخل فقرة: <code>…</code>
GUIDE;
    }

    /** The code-block contract — used only when a real code sample adds value. */
    public static function codeRules(): string
    {
        return <<<'GUIDE'
قواعد الأكواد (فقط عند الحاجة الحقيقية لمثال برمجي):
- الصيغة: <pre><code>…</code></pre>
- اكتب الكود بأسطر حقيقية: كل تعليمة في سطر مستقل، مع المسافات البادئة (indentation).
- ممنوع منعاً باتاً كتابة الكود كله في سطر واحد.
- اهرب رموز HTML داخل الكود: &lt; بدل <، و &gt; بدل >، و &amp; بدل &.
GUIDE;
    }

    /** What must never appear in the output. */
    public static function forbidden(): string
    {
        return <<<'GUIDE'
ممنوع تماماً: علامات markdown أو ``` — سمات style= أو class= — وسوم <html> أو <head> أو <body> أو <section> — أي نص أو شرح خارج وسوم HTML.
GUIDE;
    }

    /** Full guide for a whole-article prompt. */
    public static function block(): string
    {
        return self::markup()."\n\n".self::codeRules()."\n\n".self::forbidden();
    }

    /** How much a single section should cover. */
    public static function sectionBudget(string $contentLength, bool $compact = false): string
    {
        if ($compact) {
            // Even the fallback rung has a floor — a short section still beats a
            // missing one, but a two-sentence section does not.
            return 'اجعل القسم مختصراً ومركّزاً: 80 كلمة على الأقل، فقرة أو فقرتين بدون تكرار.';
        }

        return match ($contentLength) {
            'short' => 'حجم القسم: 120–200 كلمة.',
            'long' => 'حجم القسم: 220–350 كلمة، مع تفصيل عملي وأمثلة عند الحاجة.',
            default => 'حجم القسم: 160–260 كلمة.',
        };
    }

    /** Total-article guidance for the outline planner. */
    public static function pageBudget(string $contentLength): string
    {
        return match ($contentLength) {
            'short' => '500–800 كلمة إجمالاً',
            'long' => '2000–3000 كلمة إجمالاً — مقال شامل يغطي الموضوع من الأساسيات إلى التفاصيل العملية',
            default => '1000–1500 كلمة إجمالاً',
        };
    }
}
