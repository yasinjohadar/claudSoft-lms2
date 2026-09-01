<?php

namespace App\Services\Simulator;

use App\Support\SimulatorKit;

class SimulatorBundleSanitizer
{
    /**
     * @param  array{html: string, css: string, js: string}  $bundle
     * @return array{html: string, css: string, js: string}
     */
    public function sanitize(array $bundle): array
    {
        return [
            'html' => $this->sanitizeHtml($bundle['html'] ?? ''),
            'css' => trim($bundle['css'] ?? ''),
            'js' => trim($bundle['js'] ?? ''),
        ];
    }

    private function sanitizeHtml(string $html): string
    {
        $html = $this->normalizeKitPaths($html);
        $html = $this->normalizeBundleAssetPaths($html);
        $html = $this->removeExternalScripts($html);

        return trim($html);
    }

    private function normalizeKitPaths(string $html): string
    {
        $kit = SimulatorKit::PLACEHOLDER_KIT;

        $patterns = [
            '#https?://[^"\'\s<>]+/simulator-kit/shared#i' => $kit,
            '#(?:/|\./|\.\./)+simulator-kit/shared#i' => $kit,
            '#'.preg_quote($kit, '#').'/shared#i' => $kit,
        ];

        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html) ?? $html;
        }

        return $html;
    }

    private function normalizeBundleAssetPaths(string $html): string
    {
        $assets = SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS;

        $html = preg_replace(
            '#https?://[^"\'\s<>]+/simulator/[^"\'\s<>]+/assets/(page\.css|simulator\.js)#i',
            $assets.'/$1',
            $html
        ) ?? $html;

        $html = preg_replace(
            '#(?:/|\./|\.\./)+assets/(page\.css|simulator\.js)#i',
            $assets.'/$1',
            $html
        ) ?? $html;

        return $html;
    }

    private function removeExternalScripts(string $html): string
    {
        return preg_replace_callback(
            '/<script\b[^>]*\ssrc\s*=\s*(["\'])(https?:\/\/[^"\']+)\1[^>]*>\s*<\/script>/i',
            function (array $match): string {
                $src = $match[2];
                if ($this->isAllowedScriptSrc($src)) {
                    return $match[0];
                }

                return '<!-- removed external script -->';
            },
            $html
        ) ?? $html;
    }

    /** Same trusted hosts the validator allows — keep these two in sync. */
    private const ALLOWED_CDN_HOSTS = ['cdnjs.cloudflare.com', 'cdn.jsdelivr.net'];

    private function isAllowedScriptSrc(string $src): bool
    {
        if (str_contains($src, SimulatorKit::PLACEHOLDER_KIT)) {
            return true;
        }
        if (str_contains($src, SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS)) {
            return true;
        }
        if (str_contains($src, 'simulator-kit/shared')) {
            return true;
        }
        if (preg_match('#/assets/(page\.css|simulator\.js)$#i', $src)) {
            return true;
        }

        $host = parse_url($src, PHP_URL_HOST);
        if ($host !== null && in_array(strtolower($host), self::ALLOWED_CDN_HOSTS, true)) {
            // Require a pinned version segment, matching the validator's rule.
            return (bool) preg_match('#(?:@|/)\d+\.\d+(?:\.\d+)?(?:[/@]|$)#', $src);
        }

        return false;
    }
}
