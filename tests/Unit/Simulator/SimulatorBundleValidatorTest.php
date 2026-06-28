<?php

namespace Tests\Unit\Simulator;

use App\Services\Simulator\SimulatorBundleValidator;
use App\Support\SimulatorKit;
use Tests\TestCase;

class SimulatorBundleValidatorTest extends TestCase
{
    private SimulatorBundleValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new SimulatorBundleValidator;
    }

    private function validBundle(): array
    {
        $kit = SimulatorKit::PLACEHOLDER_KIT;
        $assets = SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS;

        return [
            'html' => <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<link rel="stylesheet" href="{$kit}/css/tokens.css">
<link rel="stylesheet" href="{$assets}/page.css">
<script defer src="{$assets}/simulator.js"></script>
</head>
<body><div class="sim-app">test</div></body>
</html>
HTML,
            'css' => '.sim-app { display: block; }',
            'js' => "document.addEventListener('DOMContentLoaded', function() {});",
        ];
    }

    public function test_valid_bundle_passes(): void
    {
        $result = $this->validator->validate($this->validBundle());
        $this->assertTrue($result['valid'], implode(', ', $result['errors']));
    }

    public function test_validate_manual_accepts_pasted_standalone_html(): void
    {
        $result = $this->validator->validateManual([
            'html' => '<!DOCTYPE html><html lang="ar" dir="rtl"><head><link href="https://fonts.googleapis.com/css2?family=Cairo"></head><body><div>test</div></body></html>',
            'css' => 'body { margin: 0; }',
            'js' => 'console.log(1);',
        ]);
        $this->assertTrue($result['valid'], implode(', ', $result['errors']));
    }

    public function test_rejects_eval_in_js(): void
    {
        $bundle = $this->validBundle();
        $bundle['js'] = 'eval("bad");';
        $result = $this->validator->validate($bundle);
        $this->assertFalse($result['valid']);
    }
}
