<?php

namespace Tests\Unit\Simulator;

use App\Services\Simulator\SimulatorBundleParser;
use Tests\TestCase;

class SimulatorBundleParserTest extends TestCase
{
    private SimulatorBundleParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SimulatorBundleParser;
    }

    public function test_parses_three_markdown_blocks(): void
    {
        $raw = <<<'TXT'
```html
<!DOCTYPE html>
<html lang="ar" dir="rtl"><body><div class="sim-app">Hi</div></body></html>
```
```css
.sim-app { padding: 1rem; }
```
```javascript
document.addEventListener('DOMContentLoaded', () => {});
```
TXT;

        $result = $this->parser->parse($raw);

        $this->assertStringContainsString('sim-app', $result['html']);
        $this->assertStringContainsString('.sim-app', $result['css']);
        $this->assertStringContainsString('DOMContentLoaded', $result['js']);
    }

    public function test_throws_when_blocks_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->parser->parse('plain text only');
    }
}
