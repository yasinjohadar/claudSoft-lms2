<?php

namespace Tests\Unit\Simulator;

use App\Services\Simulator\SimulatorBundleSanitizer;
use App\Services\Simulator\SimulatorBundleValidator;
use App\Support\SimulatorKit;
use Tests\TestCase;

class SimulatorBundleSanitizerTest extends TestCase
{
    public function test_normalizes_absolute_kit_script_urls_to_placeholders(): void
    {
        $sanitizer = new SimulatorBundleSanitizer;
        $kit = SimulatorKit::PLACEHOLDER_KIT;
        $assets = SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS;

        $bundle = $sanitizer->sanitize([
            'html' => <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<link rel="stylesheet" href="http://127.0.0.1:8000/simulator-kit/shared/css/tokens.css">
<link rel="stylesheet" href="{$assets}/page.css">
<script defer src="http://127.0.0.1:8000/simulator-kit/shared/js/theme-manager.js"></script>
</head>
<body><div class="sim-app">test</div></body>
</html>
HTML,
            'css' => '.sim-app {}',
            'js' => 'console.log(1);',
        ]);

        $this->assertStringContainsString($kit.'/css/tokens.css', $bundle['html']);
        $this->assertStringContainsString($kit.'/js/theme-manager.js', $bundle['html']);
        $this->assertStringNotContainsString('127.0.0.1', $bundle['html']);

        $validator = new SimulatorBundleValidator;
        $result = $validator->validate($bundle);
        $this->assertTrue($result['valid'], implode(', ', $result['errors']));
    }

    public function test_removes_cdn_scripts(): void
    {
        $sanitizer = new SimulatorBundleSanitizer;
        $kit = SimulatorKit::PLACEHOLDER_KIT;
        $assets = SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS;

        $bundle = $sanitizer->sanitize([
            'html' => <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<link rel="stylesheet" href="{$kit}/css/tokens.css">
<link rel="stylesheet" href="{$assets}/page.css">
<script src="https://cdn.jsdelivr.net/npm/prismjs@1/prism.min.js"></script>
<script defer src="{$assets}/simulator.js"></script>
</head>
<body><div class="sim-app">test</div></body>
</html>
HTML,
            'css' => '.sim-app {}',
            'js' => 'console.log(1);',
        ]);

        $this->assertStringNotContainsString('<script src="https://cdn.jsdelivr.net', $bundle['html']);
        $this->assertStringContainsString('simulator.js', $bundle['html']);

        $validator = new SimulatorBundleValidator;
        $result = $validator->validate($bundle);
        $this->assertTrue($result['valid'], implode(', ', $result['errors']));
    }
}
