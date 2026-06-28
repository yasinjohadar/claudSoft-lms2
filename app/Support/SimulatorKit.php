<?php

namespace App\Support;

class SimulatorKit
{
    public const PLACEHOLDER_KIT = '__SIMULATOR_KIT__';

    public const PLACEHOLDER_BUNDLE_ASSETS = '__BUNDLE_ASSETS__';

    public const PLACEHOLDER_GLOBAL = '__GLOBAL_ASSETS__';

    public static function globalAssetsBaseUrl(): string
    {
        return rtrim(asset('simulator-kit/global'), '/');
    }

    public static function globalPageCssUrl(): string
    {
        return self::globalAssetsBaseUrl().'/page.css';
    }

    public static function globalSimulatorJsUrl(): string
    {
        return self::globalAssetsBaseUrl().'/simulator.js';
    }

    public static function sharedAssetUrl(string $path): string
    {
        return asset('simulator-kit/shared/'.ltrim($path, '/'));
    }

    public static function bundleAssetsBaseUrl(string $slug): string
    {
        return url('/simulator/'.$slug.'/assets');
    }

    public static function adminBundleAssetsBaseUrl(int $simulatorId): string
    {
        return url('/admin/lesson-simulators/'.$simulatorId.'/assets');
    }

    public static function sharedCssLinks(): string
    {
        $files = ['css/tokens.css', 'css/base.css', 'css/components.css', 'css/theme-system.css', 'css/utilities.css'];

        return implode("\n", array_map(
            fn ($f) => '<link rel="stylesheet" href="'.self::sharedAssetUrl($f).'">',
            $files
        ));
    }

    public static function themeManagerScript(): string
    {
        return '<script defer src="'.self::sharedAssetUrl('js/theme-manager.js').'"></script>';
    }

    /**
     * Replace kit/asset placeholders and relative paths for server playback.
     */
    public static function resolveHtmlPaths(string $html, string $bundleAssetsBaseUrl): string
    {
        $kit = rtrim(url('simulator-kit/shared'), '/');
        $global = self::globalAssetsBaseUrl();
        $assets = rtrim($bundleAssetsBaseUrl, '/');

        $html = str_replace(
            [self::PLACEHOLDER_KIT, self::PLACEHOLDER_BUNDLE_ASSETS, self::PLACEHOLDER_GLOBAL],
            [$kit, $assets, $global],
            $html
        );

        $html = self::resolveGlobalAssetPaths($html, $global);

        return self::resolveSharedKitPaths($html, $kit, $assets);
    }

    /**
     * @param  array{cssUrl: string, jsUrl: string, cssInline?: string, jsInline?: string}  $assets
     */
    public static function attachStylesAndScripts(string $html, array $assets): string
    {
        $html = self::injectBeforeHeadClose($html, '<style id="sim-kit-viewport">html,body{margin:0;padding:0;width:100%;height:100%;min-height:100vh;overflow-x:hidden;}</style>');

        $cssUrl = $assets['cssUrl'];
        $jsUrl = $assets['jsUrl'];
        $cssInline = $assets['cssInline'] ?? null;
        $jsInline = $assets['jsInline'] ?? null;

        if (! self::htmlReferencesFile($html, 'page.css')) {
            if ($cssInline !== null && trim($cssInline) !== '') {
                $html = self::injectBeforeHeadClose($html, '<style id="bundle-page-css">'."\n".$cssInline."\n".'</style>');
            } elseif ($cssUrl !== '') {
                $html = self::injectBeforeHeadClose($html, '<link rel="stylesheet" href="'.$cssUrl.'">');
            }
        }

        if (! self::htmlReferencesFile($html, 'simulator.js')) {
            if ($jsInline !== null && trim($jsInline) !== '') {
                $html = self::injectBeforeBodyClose($html, '<script id="bundle-simulator-js">'."\n".$jsInline."\n".'</script>');
            } elseif ($jsUrl !== '') {
                $html = self::injectBeforeBodyClose($html, '<script defer src="'.$jsUrl.'"></script>');
            }
        }

        return $html;
    }

    /**
     * Build HTML for /simulator/{slug}/play.
     * Per-simulator CSS/JS overrides central global files when non-empty.
     */
    public static function buildPlayDocument(
        string $html,
        string $customCss,
        string $customJs,
        string $perSimulatorAssetsBaseUrl,
    ): string {
        $assets = rtrim($perSimulatorAssetsBaseUrl, '/');
        $html = self::resolveHtmlPaths($html, $assets);

        $hasCustomCss = trim($customCss) !== '';
        $hasCustomJs = trim($customJs) !== '';

        if ($hasCustomCss || $hasCustomJs) {
            $html = self::stripBundleAssetReferences($html);
            $html = self::stripGlobalAssetReferences($html);
        }

        $cssUrl = $hasCustomCss ? $assets.'/page.css' : self::globalPageCssUrl();
        $jsUrl = $hasCustomJs ? $assets.'/simulator.js' : self::globalSimulatorJsUrl();

        return self::attachStylesAndScripts($html, [
            'cssUrl' => $cssUrl,
            'jsUrl' => $jsUrl,
        ]);
    }

    /**
     * Build HTML for admin live preview.
     */
    public static function buildInlinePreviewDocument(
        string $html,
        string $customCss,
        string $customJs,
        string $globalCss,
        string $globalJs,
    ): string {
        $kit = rtrim(url('simulator-kit/shared'), '/');
        $global = self::globalAssetsBaseUrl();

        $html = str_replace([self::PLACEHOLDER_KIT, self::PLACEHOLDER_GLOBAL], [$kit, $global], $html);
        $html = self::resolveGlobalAssetPaths($html, $global);
        $html = self::resolveSharedKitPaths($html, $kit, '');
        $html = self::stripBundleAssetReferences($html);

        $css = trim($customCss) !== '' ? $customCss : $globalCss;
        $js = trim($customJs) !== '' ? $customJs : $globalJs;

        $document = self::attachStylesAndScripts($html, [
            'cssUrl' => '',
            'jsUrl' => '',
            'cssInline' => $css,
            'jsInline' => $js,
        ]);

        return self::wrapIsolatedPreviewDocument($document);
    }

    /**
     * Wrap preview fragments in a minimal document so iframe srcdoc cannot resolve
     * relative admin asset URLs against the parent page path.
     */
    public static function wrapIsolatedPreviewDocument(string $html): string
    {
        if (preg_match('/<html\b/i', $html)) {
            return self::ensurePreviewDocumentBase($html);
        }

        return '<!DOCTYPE html>'."\n"
            .'<html lang="ar" dir="rtl">'."\n"
            .'<head>'."\n"
            .'<meta charset="utf-8">'."\n"
            .'<meta name="viewport" content="width=device-width, initial-scale=1">'."\n"
            .'<base href="about:blank">'."\n"
            .'</head>'."\n"
            .'<body style="margin:0;padding:0;">'."\n"
            .$html."\n"
            .'</body>'."\n"
            .'</html>';
    }

    public static function containsAdminLayoutMarkers(string $html): bool
    {
        foreach (['app-header', 'admin-portal', 'bundle-preview-frame', 'simulator-bundle-form'] as $marker) {
            if (str_contains($html, $marker)) {
                return true;
            }
        }

        return false;
    }

    private static function ensurePreviewDocumentBase(string $html): string
    {
        if (preg_match('/<base\b/i', $html)) {
            return $html;
        }

        if (preg_match('/<head\b[^>]*>/i', $html)) {
            return preg_replace('/<head\b[^>]*>/i', '<head>'."\n".'<base href="about:blank">', $html, 1) ?? $html;
        }

        return self::wrapIsolatedPreviewDocument($html);
    }

    public static function resolveGlobalAssetPaths(string $html, ?string $globalBase = null): string
    {
        $global = $globalBase ?? self::globalAssetsBaseUrl();

        $patterns = [
            '#href=(["\'])(?:\.\./)*global/(page\.css)(\?[^"\']*)?\1#i' => 'href=$1'.$global.'/$2$3$1',
            '#src=(["\'])(?:\.\./)*global/(simulator\.js)(\?[^"\']*)?\1#i' => 'src=$1'.$global.'/$2$3$1',
            '#href=(["\'])/simulator-kit/global/(page\.css)(\?[^"\']*)?\1#i' => 'href=$1'.$global.'/$2$3$1',
            '#src=(["\'])/simulator-kit/global/(simulator\.js)(\?[^"\']*)?\1#i' => 'src=$1'.$global.'/$2$3$1',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html) ?? $html;
        }

        return $html;
    }

    public static function resolveSharedKitPaths(string $html, ?string $kitUrl = null, ?string $assetsUrl = null): string
    {
        $kit = $kitUrl ?? rtrim(url('simulator-kit/shared'), '/');
        $assets = $assetsUrl !== null && $assetsUrl !== '' ? rtrim($assetsUrl, '/') : null;

        $kitPatterns = [
            '#href=(["\'])(?:\.\./)+shared/#i' => 'href=$1'.$kit.'/',
            '#src=(["\'])(?:\.\./)+shared/#i' => 'src=$1'.$kit.'/',
            '#href=(["\'])\./shared/#i' => 'href=$1'.$kit.'/',
            '#src=(["\'])\./shared/#i' => 'src=$1'.$kit.'/',
            '#href=(["\'])shared/#i' => 'href=$1'.$kit.'/',
            '#src=(["\'])shared/#i' => 'src=$1'.$kit.'/',
            '#href=(["\'])/simulator-kit/shared/#i' => 'href=$1'.$kit.'/',
            '#src=(["\'])/simulator-kit/shared/#i' => 'src=$1'.$kit.'/',
        ];

        foreach ($kitPatterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html) ?? $html;
        }

        if ($assets !== null) {
            $html = self::resolveBundleAssetPaths($html, $assets);
        }

        return $html;
    }

    public static function resolveBundleAssetPaths(string $html, string $assetsBase): string
    {
        $assets = rtrim($assetsBase, '/');

        $patterns = [
            '#href=(["\'])(?:\.\./)*assets/(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'href=$1'.$assets.'/$2$3$1',
            '#src=(["\'])(?:\.\./)*assets/(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'src=$1'.$assets.'/$2$3$1',
            '#href=(["\'])\./assets/(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'href=$1'.$assets.'/$2$3$1',
            '#src=(["\'])\./assets/(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'src=$1'.$assets.'/$2$3$1',
            '#href=(["\'])assets/(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'href=$1'.$assets.'/$2$3$1',
            '#src=(["\'])assets/(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'src=$1'.$assets.'/$2$3$1',
            '#href=(["\'])\./(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'href=$1'.$assets.'/$2$3$1',
            '#src=(["\'])\./(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'src=$1'.$assets.'/$2$3$1',
            '#href=(["\'])(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'href=$1'.$assets.'/$2$3$1',
            '#src=(["\'])(page\.css|simulator\.js)(\?[^"\']*)?\1#i' => 'src=$1'.$assets.'/$2$3$1',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html) ?? $html;
        }

        return $html;
    }

    public static function stripBundleAssetReferences(string $html): string
    {
        $patterns = [
            '#<link[^>]+href=["\'][^"\']*(?:page\.css|__BUNDLE_ASSETS__[^"\']*)["\'][^>]*>#i',
            '#<script[^>]+src=["\'][^"\']*(?:simulator\.js|__BUNDLE_ASSETS__[^"\']*)["\'][^>]*>\s*</script>#i',
        ];

        foreach ($patterns as $pattern) {
            $html = preg_replace($pattern, '', $html) ?? $html;
        }

        return $html;
    }

    public static function stripGlobalAssetReferences(string $html): string
    {
        $patterns = [
            '#<link[^>]+href=["\'][^"\']*simulator-kit/global/page\.css[^"\']*["\'][^>]*>#i',
            '#<link[^>]+href=["\'][^"\']*global/page\.css[^"\']*["\'][^>]*>#i',
            '#<script[^>]+src=["\'][^"\']*simulator-kit/global/simulator\.js[^"\']*["\'][^>]*>\s*</script>#i',
            '#<script[^>]+src=["\'][^"\']*global/simulator\.js[^"\']*["\'][^>]*>\s*</script>#i',
        ];

        foreach ($patterns as $pattern) {
            $html = preg_replace($pattern, '', $html) ?? $html;
        }

        return $html;
    }

    public static function htmlReferencesFile(string $html, string $filename): bool
    {
        return (bool) preg_match(
            '/(?:href|src)\s*=\s*["\'][^"\']*'.preg_quote($filename, '/').'(?:\?[^"\']*)?["\']/i',
            $html
        );
    }

    private static function injectBeforeHeadClose(string $html, string $snippet): string
    {
        if (preg_match('/<\/head>/i', $html)) {
            return preg_replace('/<\/head>/i', $snippet."\n</head>", $html, 1) ?? $html.$snippet;
        }

        return $snippet."\n".$html;
    }

    private static function injectBeforeBodyClose(string $html, string $snippet): string
    {
        if (preg_match('/<\/body>/i', $html)) {
            return preg_replace('/<\/body>/i', $snippet."\n</body>", $html, 1) ?? $html.$snippet;
        }

        return $html."\n".$snippet;
    }
}
