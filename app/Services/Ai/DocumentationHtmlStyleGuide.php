<?php

namespace App\Services\Ai;

/**
 * The single description of what generated documentation HTML must look like.
 *
 * Every class named here exists in public/docs/css/style.css and every rule is
 * taken from a page that renders correctly today (documentation_pages "الفقرات
 * في HTML"). Three copies of this guide used to live in the generation services
 * and drifted apart; keep it here so the prompts cannot disagree with the CSS.
 */
class DocumentationHtmlStyleGuide
{
    /** Markup vocabulary the docs stylesheet actually supports. */
    public static function markup(): string
    {
        return <<<'GUIDE'
- لفّ كل قسم رئيسي بـ: <section class="content-section"> … </section> — وأغلق الوسم دائماً.
- عنوان القسم: <h2 class="section-title">النص</h2> (عنوان واحد فقط لكل قسم)
- عناوين فرعية: <h3 class="subsection-title">…</h3>
- فقرات توضيحية: <div class="text-block"><p>…</p></div>
- تنبيهات: <div class="info-box info|warning|success|error"><div class="info-box-title">عنوان</div><p>…</p></div>
- جداول: <table class="styled-table"><thead><tr><th>…</th></tr></thead><tbody><tr><td>…</td></tr></tbody></table>
- قوائم: <ul class="styled-list"><li>…</li></ul>
- تمييز داخل النص: <span class="highlight">…</span> (أو highlight-green / highlight-orange / highlight-red / highlight-purple)
- اسم وسم أو دالة داخل فقرة: <code>&lt;p&gt;</code>
GUIDE;
    }

    /**
     * The code-block contract.
     *
     * One-line code blocks are the single worst failure mode: a whole program on
     * one line puts everything after a `//` inside the comment, so the sample no
     * longer runs. Spell the rule out rather than assuming.
     */
    public static function codeRules(): string
    {
        return <<<'GUIDE'
قواعد الأكواد (إلزامية):
- الصيغة: <pre><code class="language-html">…</code></pre> — بدون div بكلاس code-block (يُضاف تلقائياً عند العرض).
- اختر language- المناسبة: language-html, language-css, language-php, language-javascript, language-dart, language-python, language-json, language-bash.
- اكتب الكود بأسطر حقيقية: كل تعليمة في سطر مستقل، مع المسافات البادئة (indentation).
- ممنوع منعاً باتاً كتابة الكود كله في سطر واحد. التعليقات // تُبطل بقية السطر ويصبح المثال غير صالح للتشغيل.
- اهرب رموز HTML داخل الكود: &lt; بدل <، و &gt; بدل >، و &amp; بدل &.
GUIDE;
    }

    /** What must never appear in the output. */
    public static function forbidden(): string
    {
        return <<<'GUIDE'
ممنوع تماماً: علامات markdown أو ``` — سمات style= — وسوم <html> أو <head> أو <body> — أي نص أو شرح خارج وسوم HTML.
GUIDE;
    }

    /** Full guide for a whole-page prompt. */
    public static function block(): string
    {
        return self::markup()."\n\n".self::codeRules()."\n\n".self::forbidden();
    }

    /**
     * How much a single section should cover.
     *
     * Sections used to come back at ~600 characters because the retry ladder kept
     * dropping to its compact rungs; asking for an explicit budget is what makes a
     * "long" page actually comprehensive.
     */
    public static function sectionBudget(string $contentLength, bool $compact = false): string
    {
        if ($compact) {
            // Even the fallback rung has a floor — a short section still beats a
            // missing one, but a two-sentence section does not.
            return 'اجعل القسم مركّزاً: 150 كلمة على الأقل ومثال كود واحد، بدون تكرار.';
        }

        return match ($contentLength) {
            'short' => 'حجم القسم: 150–250 كلمة، ومثال كود واحد على الأقل.',
            'long' => 'حجم القسم: 300–500 كلمة، ومثالا كود على الأقل، مع جدول <table class="styled-table"> أو تنبيه <div class="info-box"> عندما يضيف ذلك قيمة. غطِّ الموضوع بعمق: الشرح، الاستخدام العملي، والحالات الخاصة.',
            default => 'حجم القسم: 250–400 كلمة، ومثالا كود على الأقل.',
        };
    }

    /** Total-page guidance for the outline planner. */
    public static function pageBudget(string $contentLength): string
    {
        return match ($contentLength) {
            'short' => '800–1200 كلمة إجمالاً',
            'long' => '4000–6500 كلمة إجمالاً — صفحة مرجعية شاملة تغطي الموضوع من الأساسيات إلى الحالات المتقدمة والأخطاء الشائعة',
            default => '2000–3000 كلمة إجمالاً',
        };
    }
}
