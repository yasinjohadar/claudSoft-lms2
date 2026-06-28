<?php

namespace App\Services\Simulator;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SimulatorBundleParser
{
    /**
     * @return array{html: string, css: string, js: string}
     */
    public function parse(string $raw): array
    {
        $response = $this->normalizeEncoding($raw);
        $blocks = $this->extractCodeBlocks($response);

        $html = $blocks['html'] ?? $this->extractByFence($response, ['html', '']);
        $css = $blocks['css'] ?? $this->extractByFence($response, ['css']);
        $js = $blocks['javascript'] ?? $blocks['js'] ?? $this->extractByFence($response, ['javascript', 'js']);

        if ($html === null && preg_match('/<!DOCTYPE\s+html|<html[\s>]/i', $response)) {
            $html = $this->extractHtmlDocument($response);
        }

        if (! $html || ! $css || ! $js) {
            Log::warning('SimulatorBundleParser incomplete', [
                'has_html' => (bool) $html,
                'has_css' => (bool) $css,
                'has_js' => (bool) $js,
                'preview' => Str::limit($response, 500),
            ]);

            throw new \RuntimeException(
                'فشل استخراج ملفات HTML/CSS/JS من استجابة AI. تأكد أن الرد يحتوي ثلاث كتل كود: html, css, javascript.'
            );
        }

        return [
            'html' => trim($html),
            'css' => trim($css),
            'js' => trim($js),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractCodeBlocks(string $text): array
    {
        $found = [];
        if (preg_match_all('/```(\w+)?\s*\n(.*?)```/s', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $lang = strtolower(trim($match[1] ?? ''));
                $content = trim($match[2]);
                if ($lang === '' && str_contains($content, '<html')) {
                    $lang = 'html';
                }
                if ($lang !== '' && ! isset($found[$lang])) {
                    $found[$lang] = $content;
                }
            }
        }

        return $found;
    }

    /**
     * @param  list<string>  $langs
     */
    private function extractByFence(string $text, array $langs): ?string
    {
        foreach ($langs as $lang) {
            $pattern = '/```'.preg_quote($lang, '/').'\s*\n(.*?)```/s';
            if (preg_match($pattern, $text, $m)) {
                return trim($m[1]);
            }
        }

        return null;
    }

    private function extractHtmlDocument(string $text): ?string
    {
        if (preg_match('/(<!DOCTYPE\s+html.*<\/html>)/is', $text, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(<html.*<\/html>)/is', $text, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private function normalizeEncoding(string $response): string
    {
        if (! mb_check_encoding($response, 'UTF-8')) {
            $response = mb_convert_encoding($response, 'UTF-8', 'auto');
        }

        return mb_convert_encoding($response, 'UTF-8', 'UTF-8');
    }

    public function buildRepairPrompt(string $brokenResponse): string
    {
        $excerpt = Str::limit($brokenResponse, 14000);

        return <<<PROMPT
You previously generated an interactive lesson simulator but the output was incomplete or malformed.

Return EXACTLY three markdown code fences in this order (no other text):
1. ```html ... ```  — full index.html
2. ```css ... ```   — page-specific CSS
3. ```javascript ... ``` — simulator logic

Fix any syntax errors and complete truncated files. Keep Arabic UI text (RTL).

Broken output:
{$excerpt}
PROMPT;
    }

    /**
     * @param  array{html: string, css: string, js: string}  $bundle
     * @param  list<string>  $errors
     */
    public function buildValidationRepairPrompt(array $bundle, array $errors): string
    {
        $errorList = implode("\n- ", $errors);
        $kit = \App\Support\SimulatorKit::PLACEHOLDER_KIT;
        $assets = \App\Support\SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS;

        return <<<PROMPT
You generated an interactive lesson simulator but validation failed.

Fix ALL issues and return EXACTLY three markdown code fences (html, css, javascript) — no other text.

Validation errors:
- {$errorList}

CRITICAL RULES:
- Use ONLY these placeholders for local assets (never http:// or https:// URLs for scripts):
  - Shared kit: {$kit}/css/... and {$kit}/js/theme-manager.js
  - Bundle assets: {$assets}/page.css and {$assets}/simulator.js
- Google Fonts: use <link href="https://fonts.googleapis.com/..."> only (NOT script tags)
- NO CDN scripts (no jQuery, Prism, highlight.js, etc.)
- lang="ar" dir="rtl", root class="sim-app"
- No eval(), no document.write()

Current HTML excerpt:
```html
{$this->excerpt($bundle['html'], 6000)}
```

Current CSS excerpt:
```css
{$this->excerpt($bundle['css'], 3000)}
```

Current JS excerpt:
```javascript
{$this->excerpt($bundle['js'], 3000)}
```
PROMPT;
    }

    private function excerpt(string $text, int $limit): string
    {
        return Str::limit($text, $limit);
    }
}
