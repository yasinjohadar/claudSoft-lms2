<?php

namespace Tests\Unit\Simulator;

use App\Support\SimulatorKit;
use Tests\TestCase;

class SimulatorKitLinkingTest extends TestCase
{
    private string $assetsBase = 'https://lms.test/simulator/demo/assets';

    public function test_play_document_resolves_placeholders_and_asset_paths(): void
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<link rel="stylesheet" href="__SIMULATOR_KIT__/css/tokens.css">
<link rel="stylesheet" href="__BUNDLE_ASSETS__/page.css">
</head>
<body><div class="sim-app">Hi</div>
<script defer src="__BUNDLE_ASSETS__/simulator.js"></script>
</body>
</html>
HTML;

        $doc = SimulatorKit::buildPlayDocument($html, '.sim-app{color:red}', 'console.log(1);', $this->assetsBase);

        $this->assertStringContainsString(url('simulator-kit/shared/css/tokens.css'), $doc);
        $this->assertStringContainsString($this->assetsBase.'/page.css', $doc);
        $this->assertStringContainsString($this->assetsBase.'/simulator.js', $doc);
        $this->assertStringNotContainsString('__BUNDLE_ASSETS__', $doc);
    }

    public function test_play_document_resolves_simulation_langs_relative_paths(): void
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<link rel="stylesheet" href="../../shared/css/tokens.css">
<link rel="stylesheet" href="assets/page.css">
</head>
<body><div>Test</div>
<script defer src="assets/simulator.js"></script>
</body>
</html>
HTML;

        $doc = SimulatorKit::buildPlayDocument($html, 'body{margin:0}', 'alert(1);', $this->assetsBase);

        $this->assertStringContainsString(url('simulator-kit/shared/css/tokens.css'), $doc);
        $this->assertStringContainsString($this->assetsBase.'/page.css', $doc);
        $this->assertStringContainsString($this->assetsBase.'/simulator.js', $doc);
    }

    public function test_play_document_auto_injects_global_assets_when_no_custom_files(): void
    {
        $html = '<!DOCTYPE html><html lang="ar" dir="rtl"><head></head><body><div>Test</div></body></html>';

        $doc = SimulatorKit::buildPlayDocument($html, '', '', $this->assetsBase);

        $this->assertStringContainsString(SimulatorKit::globalPageCssUrl(), $doc);
        $this->assertStringContainsString(SimulatorKit::globalSimulatorJsUrl(), $doc);
    }

    public function test_play_document_uses_custom_assets_when_provided(): void
    {
        $html = '<!DOCTYPE html><html lang="ar" dir="rtl"><head></head><body><div>Test</div></body></html>';

        $doc = SimulatorKit::buildPlayDocument($html, 'body{color:red}', 'console.log(1)', $this->assetsBase);

        $this->assertStringContainsString($this->assetsBase.'/page.css', $doc);
        $this->assertStringContainsString($this->assetsBase.'/simulator.js', $doc);
    }

    public function test_inline_preview_uses_global_when_no_custom(): void
    {
        $html = '<!DOCTYPE html><html lang="ar" dir="rtl"><head></head><body><div id="app">Hi</div></body></html>';

        $doc = SimulatorKit::buildInlinePreviewDocument($html, '', '', 'body{background:#eee}', 'document.body.dataset.ok="1";');

        $this->assertStringContainsString('body{background:#eee}', $doc);
        $this->assertStringContainsString('dataset.ok', $doc);
    }

    public function test_play_document_auto_injects_missing_css_and_js_links(): void
    {
        $html = '<!DOCTYPE html><html lang="ar" dir="rtl"><head></head><body><div>Test</div></body></html>';

        $doc = SimulatorKit::buildPlayDocument($html, 'body{background:#fff}', 'console.log("ok");', $this->assetsBase);

        $this->assertStringContainsString($this->assetsBase.'/page.css', $doc);
        $this->assertStringContainsString($this->assetsBase.'/simulator.js', $doc);
    }

    public function test_inline_preview_inlines_css_and_js_from_editors(): void
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<link rel="stylesheet" href="../../shared/css/base.css">
<link rel="stylesheet" href="assets/page.css">
</head>
<body><div id="app">Hello</div>
<script src="simulator.js"></script>
</body>
</html>
HTML;

        $doc = SimulatorKit::buildInlinePreviewDocument(
            $html,
            '#app { color: blue; }',
            'document.getElementById("app").textContent = "OK";',
            '/* global */',
            '/* global js */',
        );

        $this->assertStringContainsString(url('simulator-kit/shared/css/base.css'), $doc);
        $this->assertStringContainsString('<style id="bundle-page-css">', $doc);
        $this->assertStringContainsString('#app { color: blue; }', $doc);
        $this->assertStringContainsString('<script id="bundle-simulator-js">', $doc);
        $this->assertStringContainsString('textContent = "OK"', $doc);
        $this->assertStringNotContainsString('href="assets/page.css"', $doc);
        $this->assertStringNotContainsString('src="simulator.js"', $doc);
        $this->assertStringContainsString('<base href="about:blank">', $doc);
    }

    public function test_inline_preview_wraps_fragments_in_isolated_document(): void
    {
        $doc = SimulatorKit::buildInlinePreviewDocument(
            '<div id="app">Hi</div>',
            '',
            '',
            'body { background: #000; }',
            'console.log("ok");',
        );

        $this->assertStringContainsString('<!DOCTYPE html>', $doc);
        $this->assertStringContainsString('<base href="about:blank">', $doc);
        $this->assertStringContainsString('<div id="app">Hi</div>', $doc);
        $this->assertStringNotContainsString('app-header', $doc);
    }

    public function test_contains_admin_layout_markers(): void
    {
        $this->assertTrue(SimulatorKit::containsAdminLayoutMarkers('<header class="app-header">'));
        $this->assertFalse(SimulatorKit::containsAdminLayoutMarkers('<div class="sim-app"></div>'));
    }
}
