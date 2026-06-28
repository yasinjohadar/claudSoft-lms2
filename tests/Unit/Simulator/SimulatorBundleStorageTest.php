<?php

namespace Tests\Unit\Simulator;

use App\Services\Simulator\SimulatorBundleStorage;
use App\Support\SimulatorKit;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SimulatorBundleStorageTest extends TestCase
{
    public function test_save_load_and_resolve_play_html(): void
    {
        Storage::fake('local');
        $storage = new SimulatorBundleStorage;

        $kit = SimulatorKit::PLACEHOLDER_KIT;
        $assets = SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS;

        $bundle = [
            'html' => <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<link rel="stylesheet" href="{$kit}/css/tokens.css">
<link rel="stylesheet" href="{$assets}/page.css">
<script defer src="{$assets}/simulator.js"></script>
</head>
<body><div class="sim-app">Hello</div></body>
</html>
HTML,
            'css' => '.sim-app { color: red; }',
            'js' => 'console.log(1);',
        ];

        $path = $storage->save('demo-slug', $bundle);
        $this->assertSame('simulators/demo-slug', $path);
        $this->assertTrue($storage->exists('demo-slug'));

        $loaded = $storage->load('demo-slug');
        $this->assertStringContainsString('sim-app', $loaded['html']);

        $assetsBase = 'https://example.test/simulator/demo-slug/assets/';
        $playHtml = $storage->playHtml('demo-slug', $assetsBase);
        $this->assertStringContainsString(url('simulator-kit/shared/css/tokens.css'), $playHtml);
        $this->assertStringContainsString(rtrim($assetsBase, '/').'/page.css', $playHtml);
    }
}
