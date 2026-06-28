<?php

namespace Tests\Unit\Simulator;

use App\Services\Simulator\SimulatorSpecJsonParser;
use Tests\TestCase;

class SimulatorSpecJsonParserTest extends TestCase
{
    private SimulatorSpecJsonParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SimulatorSpecJsonParser;
    }

    public function test_parses_raw_json_object(): void
    {
        $json = '{"meta":{"topic_key":"x","title":"t","languages":["php"],"level":"beginner"},"sections":[]}';
        $result = $this->parser->parse($json);

        $this->assertSame('x', $result['meta']['topic_key']);
        $this->assertSame([], $result['sections']);
    }

    public function test_parses_markdown_wrapped_json(): void
    {
        $json = <<<'TXT'
Here is the simulator:
```json
{"meta":{"topic_key":"js.arrays","title":"المصفوفات","languages":["javascript"],"level":"beginner"},"sections":[{"type":"hero","title":"Hi","summary":"S"}]}
```
TXT;

        $result = $this->parser->parse($json);

        $this->assertSame('js.arrays', $result['meta']['topic_key']);
        $this->assertCount(1, $result['sections']);
    }

    public function test_parses_json_with_trailing_comma(): void
    {
        $json = '{"meta":{"topic_key":"x","title":"t","languages":["php"],"level":"beginner",},"sections":[],}';
        $result = $this->parser->parse($json);

        $this->assertSame('x', $result['meta']['topic_key']);
    }

    public function test_throws_on_non_json(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->parser->parse('This is only plain text without JSON.');
    }
}
