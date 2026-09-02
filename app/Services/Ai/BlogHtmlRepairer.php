<?php

namespace App\Services\Ai;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Makes model-written blog HTML safe to store and render.
 *
 * Forked from DocumentationHtmlRepairer: the balancing/code-reflow logic is
 * identical (generic HTML repair), but blog sections must NOT be force-wrapped
 * in <section class="content-section"> — that class does not exist in the blog
 * stylesheet (resources/views/frontend2/pages/blog-show.blade.php only styles
 * plain h2/h3/p/ul/ol/li/blockquote/img/code/pre). A blog section stays as the
 * plain fragment the model wrote, only balanced and given a heading if missing.
 *
 * ext-dom is available; ext-tidy is not. libxml's HTML parser closes open tags
 * for us, which is what makes balancing reliable rather than regex guesswork.
 */
class BlogHtmlRepairer
{
    /** Below this a single-line code block is plausibly a real one-liner. */
    private const ONE_LINE_CODE_THRESHOLD = 120;

    /** Languages whose statements we can safely split onto separate lines. */
    private const C_LIKE = [
        'dart', 'java', 'javascript', 'js', 'typescript', 'ts', 'php', 'c', 'cpp',
        'csharp', 'cs', 'go', 'rust', 'swift', 'kotlin', 'json', 'css', 'scss',
    ];

    private const MARKUP = ['html', 'markup', 'xml', 'svg', 'vue', 'blade'];

    /** Repair one generated section and make sure it has a heading. */
    public function repairSection(string $html, string $heading = ''): string
    {
        $html = $this->stripArtifacts($html);
        if ($html === '') {
            return '';
        }

        $dom = $this->load($html);
        if ($dom === null) {
            return '';
        }

        $this->normalizeSectionShape($dom, $heading);
        $this->reflowCodeBlocks($dom);

        return $this->dump($dom);
    }

    /** Repair a whole assembled article, leaving its structure as-is. */
    public function repairDocument(string $html): string
    {
        $html = $this->stripArtifacts($html);
        if ($html === '') {
            return '';
        }

        $dom = $this->load($html);
        if ($dom === null) {
            return '';
        }

        $this->reflowCodeBlocks($dom);

        return $this->dump($dom);
    }

    /** True when a code block is still one long line we could not split. */
    public function hasUnsplitCodeBlock(string $html): bool
    {
        if (! preg_match_all('/<pre[^>]*>(.*?)<\/pre>/is', $html, $m)) {
            return false;
        }

        foreach ($m[1] as $block) {
            $text = html_entity_decode(strip_tags($block), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (mb_strlen(trim($text)) > 160 && ! str_contains($text, "\n")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove markdown fences, leading prose and the tail of a broken JSON
     * envelope (`"}`, `" }`) that used to be stored as a stray paragraph.
     */
    public function stripArtifacts(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $html = preg_replace('/^```(?:html|json)?[ \t]*\r?\n?/i', '', $html) ?? $html;
        $html = preg_replace('/\r?\n?```\s*$/', '', $html) ?? $html;
        $html = trim($html);

        // Anything before the first tag is commentary the prompt asked for but
        // did not get suppressed ("إليك القسم المطلوب:").
        $firstTag = mb_strpos($html, '<');
        if ($firstTag !== false && $firstTag > 0) {
            $html = mb_substr($html, $firstTag);
        }

        // Tail of a truncated {"html":"…"} envelope, with or without a wrapper tag.
        $html = preg_replace('/<p>\s*"?\s*\}\s*"?\s*<\/p>\s*$/u', '', $html) ?? $html;
        $html = preg_replace('/\s*"\s*\}\s*$/u', '', $html) ?? $html;

        return trim($html);
    }

    /**
     * Parse a fragment. The known wrapper root keeps libxml from inventing
     * <html><body>, and its error recovery closes whatever the model left open.
     */
    private function load(string $html): ?DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $ok = $dom->loadHTML(
            '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
            .'<div id="blog-repair-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $ok ? $dom : null;
    }

    private function root(DOMDocument $dom): ?DOMElement
    {
        $found = (new DOMXPath($dom))->query('//div[@id="blog-repair-root"]');
        $node = $found !== false && $found->length > 0 ? $found->item(0) : null;

        return $node instanceof DOMElement ? $node : null;
    }

    private function dump(DOMDocument $dom): string
    {
        $root = $this->root($dom);
        if ($root === null) {
            return '';
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    /**
     * Unlike documentation sections, a blog section is NOT collapsed into a
     * single <section> wrapper — it stays as the plain fragment the model
     * wrote. Only two things are fixed: a section that wraps nothing but a
     * duplicate of itself (the model echoed a wrapper it was shown), and a
     * missing/duplicated <h2> heading.
     */
    private function normalizeSectionShape(DOMDocument $dom, string $heading): void
    {
        $root = $this->root($dom);
        if ($root === null) {
            return;
        }

        $xpath = new DOMXPath($dom);

        foreach (iterator_to_array($xpath->query('.//section/section', $root)) as $inner) {
            if ($inner instanceof DOMElement
                && $inner->parentNode instanceof DOMElement
                && trim($this->textOf($inner->parentNode)) === trim($this->textOf($inner))) {
                $this->unwrap($inner);
            }
        }

        $this->ensureHeading($dom, $root, $heading);
    }

    private function ensureHeading(DOMDocument $dom, DOMElement $container, string $heading): void
    {
        $headings = (new DOMXPath($dom))->query('.//h2', $container);

        if ($headings === false || $headings->length === 0) {
            if (trim($heading) === '') {
                return;
            }
            $h2 = $dom->createElement('h2');
            $h2->appendChild($dom->createTextNode($heading));
            $container->insertBefore($h2, $container->firstChild);

            return;
        }

        foreach (iterator_to_array($headings) as $index => $h2) {
            if (! $h2 instanceof DOMElement) {
                continue;
            }
            if ($index === 0) {
                continue;
            }
            // Extra h2s inside one section read as separate sections on screen;
            // demote them to h3 instead.
            $this->renameTo($dom, $h2, 'h3');
        }
    }

    private function renameTo(DOMDocument $dom, DOMElement $element, string $tag): void
    {
        $replacement = $dom->createElement($tag);
        while ($element->firstChild !== null) {
            $replacement->appendChild($element->firstChild);
        }
        $element->parentNode?->replaceChild($replacement, $element);
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }
        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
    }

    private function textOf(DOMNode $node): string
    {
        return preg_replace('/\s+/u', ' ', $node->textContent) ?? $node->textContent;
    }

    /** Give every collapsed <pre><code> its line breaks back. */
    private function reflowCodeBlocks(DOMDocument $dom): void
    {
        $xpath = new DOMXPath($dom);
        $blocks = $xpath->query('//pre');
        if ($blocks === false) {
            return;
        }

        foreach ($blocks as $pre) {
            if (! $pre instanceof DOMElement) {
                continue;
            }

            $codeNodes = $xpath->query('./code', $pre);
            $target = $codeNodes !== false && $codeNodes->length > 0 && $codeNodes->item(0) instanceof DOMElement
                ? $codeNodes->item(0)
                : $pre;

            $text = $target->textContent;
            if (str_contains($text, "\n") || mb_strlen(trim($text)) <= self::ONE_LINE_CODE_THRESHOLD) {
                continue;
            }

            $reflowed = $this->reflow($text, $this->languageOf($target, $pre));
            if ($reflowed === null) {
                continue;
            }

            while ($target->firstChild !== null) {
                $target->removeChild($target->firstChild);
            }
            $target->appendChild($dom->createTextNode($reflowed));
        }
    }

    private function languageOf(DOMNode $code, DOMNode $pre): string
    {
        foreach ([$code, $pre] as $node) {
            if ($node instanceof DOMElement && preg_match('/language-([\w-]+)/', $node->getAttribute('class'), $m)) {
                return strtolower($m[1]);
            }
        }

        return '';
    }

    /**
     * Split a collapsed one-liner back into statements.
     *
     * Returns null for languages where line breaks are semantic (Python, YAML) —
     * a wrong guess there would corrupt working code, so the validator asks the
     * model for the section again instead.
     */
    public function reflow(string $code, string $language): ?string
    {
        $code = trim(preg_replace('/[ \t]+/', ' ', $code) ?? $code);
        if ($code === '') {
            return null;
        }

        if (in_array($language, self::MARKUP, true)) {
            return $this->reflowMarkup($code);
        }

        if ($language !== '' && ! in_array($language, self::C_LIKE, true)) {
            return null;
        }

        if ($language === '' && ! preg_match('/[;{}]/', $code)) {
            return null;
        }

        return $this->reflowCLike($code);
    }

    private function reflowCLike(string $code): string
    {
        $lines = [];
        $current = '';
        $depth = 0;
        $inString = null;
        $length = mb_strlen($code);

        $indent = static fn (int $level): string => str_repeat('    ', max(0, $level));

        $push = function (string $line) use (&$lines, &$depth, $indent): void {
            $line = trim($line);
            if ($line === '') {
                return;
            }
            // A closing bracket de-indents its own line, not the one after it.
            $level = str_starts_with($line, '}') || str_starts_with($line, ')') ? $depth - 1 : $depth;
            $lines[] = $indent($level).$line;
        };

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($code, $i, 1);
            $prev = $i > 0 ? mb_substr($code, $i - 1, 1) : '';

            if ($inString !== null) {
                $current .= $char;
                if ($char === $inString && $prev !== '\\') {
                    $inString = null;
                }

                continue;
            }

            if ($char === '"' || $char === "'" || $char === chr(96)) {
                $inString = $char;
                $current .= $char;

                continue;
            }

            // A // comment runs to the end of its line, so it has to start one.
            if ($char === '/' && mb_substr($code, $i + 1, 1) === '/') {
                $push($current);
                $current = '';
                $rest = mb_substr($code, $i);
                // The comment ends where the next statement plausibly begins:
                // a keyword, a call/assignment, or a typed declaration such as
                // `List fixedList =` (missing that last shape used to leave the
                // type inside the comment and the variable bare).
                $boundary = '/^(\/\/.*?)(?=\s+(?:'
                    .'\}'                                   // block close
                    .'|(?:var|let|const|final|static|int|double|num|bool|void|String|for|if|while|do|switch|return|await|new|public|private|function|def)\s'
                    .'|[A-Za-z_$][\w$<>,\[\]?]*\s+[A-Za-z_$][\w$]*\s*='  // typed declaration
                    .'|[A-Za-z_$][\w$]*\s*[\(\.=]'          // call, member access, assignment
                    .'))/u';
                if (preg_match($boundary, $rest, $m)) {
                    $this->pushComment($m[1], $push);
                    $i += mb_strlen($m[1]) - 1;
                } else {
                    $this->pushComment($rest, $push);
                    break;
                }

                continue;
            }

            if ($char === '{') {
                $push($current.'{');
                $current = '';
                $depth++;

                continue;
            }

            if ($char === '}') {
                $push($current);
                $current = '';
                $depth = max(0, $depth - 1);
                $next = mb_substr($code, $i + 1, 1);
                // `};` and `},` belong on the closing line.
                if ($next === ';' || $next === ',') {
                    $lines[] = $indent($depth).'}'.$next;
                    $i++;
                } else {
                    $lines[] = $indent($depth).'}';
                }

                continue;
            }

            if ($char === ';') {
                $push($current.';');
                $current = '';

                continue;
            }

            $current .= $char;
        }

        $push($current);

        return implode("\n", $lines);
    }

    /**
     * Emit a comment run, splitting where two comments were collapsed together
     * ("// أولاً // ثانياً") so each keeps its own line.
     *
     * @param  callable(string): void  $push
     */
    private function pushComment(string $comment, callable $push): void
    {
        $parts = preg_split('/\s+(?=\/\/)/u', trim($comment)) ?: [$comment];
        foreach ($parts as $part) {
            $push($part);
        }
    }

    private function reflowMarkup(string $code): string
    {
        // One block-level tag per line, indented by nesting depth.
        $parts = preg_split('/(<\/?[A-Za-z][^>]*>)/', $code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if ($parts === false) {
            return $code;
        }

        $inline = ['a', 'span', 'strong', 'em', 'b', 'i', 'code', 'small', 'br', 'img', 'input', 'label'];
        $lines = [];
        $depth = 0;
        $buffer = '';

        foreach ($parts as $part) {
            if (! preg_match('/^<\/?([A-Za-z][\w-]*)/', $part, $m)) {
                $buffer .= $part;

                continue;
            }

            $tag = strtolower($m[1]);
            if (in_array($tag, $inline, true)) {
                $buffer .= $part;

                continue;
            }

            $closing = str_starts_with($part, '</');
            $selfClosing = str_ends_with($part, '/>');

            if (trim($buffer) !== '') {
                $lines[] = str_repeat('  ', $depth).trim($buffer);
            }
            $buffer = '';

            if ($closing) {
                $depth = max(0, $depth - 1);
            }

            $lines[] = str_repeat('  ', $depth).trim($part);

            if (! $closing && ! $selfClosing) {
                $depth++;
            }
        }

        if (trim($buffer) !== '') {
            $lines[] = str_repeat('  ', $depth).trim($buffer);
        }

        return implode("\n", $lines);
    }
}
