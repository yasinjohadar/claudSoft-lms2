<?php

namespace App\Services\Simulator;

use App\Support\SimulatorKit;

class SimulatorBundleValidator
{
    private const MAX_HTML_BYTES = 512000;

    private const MAX_CSS_BYTES = 200000;

    private const MAX_JS_BYTES = 200000;

    /**
     * @param  array{html: string, css: string, js: string}  $bundle
     * @return array{valid: bool, errors: list<string>}
     */
    public function validateManual(array $bundle): array
    {
        $errors = [];

        if (trim($bundle['html'] ?? '') === '') {
            $errors[] = 'ملف HTML مطلوب.';
        }

        foreach (['html', 'css', 'js'] as $key) {
            $content = $bundle[$key] ?? '';
            $max = match ($key) {
                'html' => self::MAX_HTML_BYTES,
                'css' => self::MAX_CSS_BYTES,
                default => self::MAX_JS_BYTES,
            };
            if (strlen($content) > $max) {
                $errors[] = "ملف {$key} كبير جداً.";
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * @param  array{html: string, css: string, js: string}  $bundle
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate(array $bundle): array
    {
        $errors = [];

        foreach (['html', 'css', 'js'] as $key) {
            if (empty(trim($bundle[$key] ?? ''))) {
                $errors[] = "ملف {$key} فارغ.";
            }
        }

        if (! empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }

        $html = $bundle['html'];
        $css = $bundle['css'];
        $js = $bundle['js'];

        if (strlen($html) > self::MAX_HTML_BYTES) {
            $errors[] = 'ملف HTML كبير جداً.';
        }
        if (strlen($css) > self::MAX_CSS_BYTES) {
            $errors[] = 'ملف CSS كبير جداً.';
        }
        if (strlen($js) > self::MAX_JS_BYTES) {
            $errors[] = 'ملف JS كبير جداً.';
        }

        if (! preg_match('/<html[\s>]/i', $html)) {
            $errors[] = 'HTML يجب أن يحتوي على عنصر html.';
        }
        if (! preg_match('/lang\s*=\s*["\']ar["\']/i', $html)) {
            $errors[] = 'HTML يجب أن يستخدم lang="ar".';
        }
        if (! str_contains($html, 'dir="rtl"') && ! str_contains($html, "dir='rtl'")) {
            $errors[] = 'HTML يجب أن يستخدم dir="rtl".';
        }
        if (! str_contains($html, 'sim-app') && ! str_contains($html, 'class="sim-')) {
            $errors[] = 'HTML يجب أن يستخدم class sim-app أو sim-* (هيكل المحاكاة).';
        }
        if (! str_contains($html, SimulatorKit::PLACEHOLDER_KIT) && ! str_contains($html, 'simulator-kit/shared')) {
            $errors[] = 'HTML يجب أن يربط shared kit عبر '.SimulatorKit::PLACEHOLDER_KIT;
        }
        if (! str_contains($html, SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS) && ! str_contains($html, 'page.css')) {
            $errors[] = 'HTML يجب أن يربط page.css عبر '.SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS;
        }

        if (preg_match_all('/<script[^>]+src\s*=\s*["\'](https?:\/\/[^"\']+)["\']/i', $html, $scriptMatches)) {
            foreach ($scriptMatches[1] as $src) {
                if ($this->isDisallowedExternalScript($src)) {
                    $errors[] = 'لا يُسمح بـ script خارجي (CDN) في HTML — استخدم __SIMULATOR_KIT__ و __BUNDLE_ASSETS__ فقط.';
                    break;
                }
            }
        }
        if (preg_match('/\beval\s*\(/', $js)) {
            $errors[] = 'JavaScript: eval() غير مسموح.';
        }
        if (preg_match('/document\.write\s*\(/', $js)) {
            $errors[] = 'JavaScript: document.write() غير مسموح.';
        }

        if (preg_match('/^\s*\{\s*"meta"/s', trim($html.$css.$js))) {
            $errors[] = 'الرد يبدو JSON وليس HTML/CSS/JS — أعد التوليد.';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function isDisallowedExternalScript(string $src): bool
    {
        if (str_contains($src, SimulatorKit::PLACEHOLDER_KIT)) {
            return false;
        }
        if (str_contains($src, SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS)) {
            return false;
        }
        if (str_contains($src, 'simulator-kit/shared')) {
            return false;
        }
        if (preg_match('#/assets/simulator\.js$#i', $src)) {
            return false;
        }

        return (bool) preg_match('#^https?://#i', $src);
    }
}
