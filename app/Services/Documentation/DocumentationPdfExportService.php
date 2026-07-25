<?php

namespace App\Services\Documentation;

use App\Models\DocumentationPage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DocumentationPdfExportService
{
    public function export(DocumentationPage $page, bool $allowDraft = false): string
    {
        $page->loadMissing('category');

        if (! $allowDraft) {
            if (! $page->isPublished() || ! $page->category?->is_active) {
                throw new \RuntimeException('لا يمكن تصدير صفحة غير منشورة.');
            }
        } elseif (! $page->category) {
            throw new \RuntimeException('لا يمكن تصدير صفحة بدون قسم.');
        }

        try {
            return $this->exportViaBrowser($page, $allowDraft);
        } catch (ProcessFailedException $e) {
            $detail = trim($e->getProcess()?->getErrorOutput() ?: $e->getMessage());

            throw new \RuntimeException(
                'تعذّر تصدير PDF. تأكد من تثبيت Node.js وChrome/Chromium ووجود حزمة puppeteer. '.Str::limit($detail, 300),
                0,
                $e
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'تعذّر تصدير PDF: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    public function filename(DocumentationPage $page): string
    {
        $page->loadMissing('category');

        $categorySlug = Str::slug($page->category?->slug ?? 'docs') ?: 'docs';
        $pageSlug = Str::slug($page->slug ?: 'page') ?: ('page-'.$page->id);

        return "{$categorySlug}-{$pageSlug}.pdf";
    }

    protected function exportViaBrowser(DocumentationPage $page, bool $allowDraft): string
    {
        if (config('browsershot.pdf_browser_use_signed_url')) {
            return $this->exportViaSignedUrl($page, $allowDraft);
        }

        $html = View::make('frontend.docs.show', [
            'category' => $page->category,
            'page' => $page,
            'pdfExport' => true,
            'forcedTheme' => 'light',
        ])->render();

        $html = $this->prepareHtmlForBrowser($html);

        $shot = Browsershot::html($html);

        if ($base = rtrim((string) config('app.url'), '/')) {
            $shot->setContentUrl($base);
        }

        return $this->renderContinuousPdf($this->configureBrowsershot($shot));
    }

    protected function exportViaSignedUrl(DocumentationPage $page, bool $allowDraft): string
    {
        $context = $allowDraft ? 'admin' : 'public';
        $url = URL::temporarySignedRoute(
            'frontend.docs.export-render',
            now()->addMinutes((int) config('browsershot.pdf_signed_url_ttl_minutes', 5)),
            [
                'documentation_page' => $page->id,
                'context' => $context,
            ]
        );

        if ($internalBase = config('browsershot.pdf_internal_url')) {
            $url = $this->replaceUrlBase($url, rtrim((string) $internalBase, '/'));
        }

        return $this->renderContinuousPdf(
            $this->configureBrowsershot(Browsershot::url($url))
        );
    }

    /**
     * Measure rendered content height and emit a single continuous PDF page.
     * Falls back to A4 when the page is taller than Chromium can reliably render.
     */
    protected function renderContinuousPdf(Browsershot $shot): string
    {
        $viewportWidth = (int) config('browsershot.pdf_viewport_width', 720);
        $maxContinuous = (int) config('browsershot.pdf_max_continuous_height', 14000);

        try {
            $rawHeight = $shot->evaluate(
                'Math.ceil(Math.max('
                .'document.body.scrollHeight,'
                .'document.documentElement.scrollHeight,'
                .'document.body.offsetHeight,'
                .'document.documentElement.offsetHeight'
                .'))'
            );
            $contentHeight = (int) preg_replace('/\D+/', '', (string) $rawHeight);
        } catch (\Throwable) {
            $contentHeight = 0;
        }

        $minHeight = (int) config('browsershot.pdf_viewport_height', 2400);
        $pageHeight = max($contentHeight + 48, $minHeight, 900);

        if ($pageHeight > $maxContinuous) {
            return $shot
                ->format('A4')
                ->margins(0, 0, 0, 0)
                ->showBackground()
                ->pdf();
        }

        // Prefer explicit pixel paper size (one continuous page) over A4 pagination.
        $shot->setOption('format', null);
        $shot->setOption('width', $viewportWidth.'px');
        $shot->setOption('height', $pageHeight.'px');
        $shot->setOption('preferCSSPageSize', false);

        if (method_exists($shot, 'paperSize')) {
            $shot->paperSize($viewportWidth, $pageHeight, 'px');
        }

        return $shot->pdf();
    }

    protected function replaceUrlBase(string $url, string $base): string
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return $url;
        }

        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';

        return $base.$path.$query;
    }

    /**
     * Inline local CSS/images; Prism/fonts load from CDN.
     */
    protected function prepareHtmlForBrowser(string $html): string
    {
        $cssPath = public_path('docs/css/style.css');

        if (is_readable($cssPath)) {
            $css = (string) file_get_contents($cssPath);
            $html = preg_replace(
                '/<link[^>]+href=["\'][^"\']*docs\/css\/style\.css[^"\']*["\'][^>]*>/i',
                '<style>'.$css.'</style>',
                $html,
                1
            ) ?? $html;
        }

        return $this->inlineLocalImages($html);
    }

    protected function inlineLocalImages(string $html): string
    {
        return preg_replace_callback(
            '/\ssrc=(["\'])([^"\']+)\1/i',
            function (array $matches): string {
                $quote = $matches[1];
                $src = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);

                if (str_starts_with($src, 'data:')) {
                    return $matches[0];
                }

                $path = $this->resolveLocalPath($src);

                if ($path === null || ! is_readable($path)) {
                    return $matches[0];
                }

                $mime = @mime_content_type($path) ?: 'application/octet-stream';
                $encoded = base64_encode((string) file_get_contents($path));

                return ' src='.$quote.'data:'.$mime.';base64,'.$encoded.$quote;
            },
            $html
        ) ?? $html;
    }

    protected function resolveLocalPath(string $src): ?string
    {
        $src = trim($src);

        if ($src === '') {
            return null;
        }

        if (str_starts_with($src, '/')) {
            $publicPath = public_path(ltrim($src, '/'));

            if (is_file($publicPath)) {
                return $publicPath;
            }

            if (str_starts_with($src, '/storage/')) {
                $storagePath = storage_path('app/public/'.ltrim(substr($src, strlen('/storage/')), '/'));

                if (is_file($storagePath)) {
                    return $storagePath;
                }
            }

            return null;
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl !== '' && str_starts_with($src, $appUrl)) {
            return $this->resolveLocalPath(parse_url($src, PHP_URL_PATH) ?: '');
        }

        if (preg_match('#^https?://[^/]+(/.*)$#', $src, $matches)) {
            $host = parse_url($src, PHP_URL_HOST);
            $appHost = parse_url($appUrl, PHP_URL_HOST);

            if ($host && $appHost && strcasecmp($host, $appHost) === 0) {
                return $this->resolveLocalPath($matches[1]);
            }
        }

        return null;
    }

    protected function configureBrowsershot(Browsershot $shot): Browsershot
    {
        if ($node = config('browsershot.node_binary')) {
            $shot->setNodeBinary($node);
        }

        if ($npm = config('browsershot.npm_binary')) {
            $shot->setNpmBinary($npm);
        }

        if ($chrome = $this->resolveChromePath()) {
            $shot->setChromePath($chrome);
        }

        if (config('browsershot.no_sandbox')) {
            $shot->noSandbox();
        }

        $nodeModules = base_path('node_modules');

        if (is_dir($nodeModules)) {
            $shot->setNodeModulePath($nodeModules);
        }

        $viewportWidth = (int) config('browsershot.pdf_viewport_width', 720);
        $viewportHeight = (int) config('browsershot.pdf_viewport_height', 2400);

        $shot = $shot
            ->windowSize($viewportWidth, $viewportHeight)
            ->writeOptionsToFile()
            ->showBackground()
            ->hideBrowserHeaderAndFooter()
            ->margins(0, 0, 0, 0)
            ->timeout((int) config('browsershot.timeout', 120))
            ->emulateMedia('print')
            ->setDelay((int) config('browsershot.pdf_browser_delay_ms', 1500));

        if (config('browsershot.pdf_wait_until_network_idle')) {
            $shot->waitUntilNetworkIdle(true);
        }

        return $shot;
    }

    protected function resolveChromePath(): ?string
    {
        $configured = trim((string) config('browsershot.chrome_path', ''));

        if ($configured !== '' && (is_executable($configured) || is_file($configured))) {
            return $configured;
        }

        $candidates = [
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/snap/bin/chromium',
            // Nixpacks / some Coolify images
            '/usr/lib/chromium/chromium',
            '/root/.cache/puppeteer/chrome',
        ];

        foreach ($candidates as $path) {
            if (is_executable($path) || is_file($path)) {
                return $path;
            }
        }

        // Puppeteer-managed Chrome (when PUPPETEER_SKIP_CHROMIUM_DOWNLOAD is not set)
        $puppeteerCache = getenv('HOME') ?: '/root';
        $glob = glob($puppeteerCache.'/.cache/puppeteer/chrome/*/chrome-linux64/chrome');
        if (is_array($glob) && $glob !== []) {
            return $glob[0];
        }

        return null;
    }
}