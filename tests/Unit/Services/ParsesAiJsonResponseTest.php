<?php

use App\Services\Ai\Concerns\ParsesAiJsonResponse;
use Illuminate\Support\Facades\Log;

uses(Tests\TestCase::class);

beforeEach(function () {
    Log::shouldReceive('info')->byDefault();
    Log::shouldReceive('warning')->byDefault();
});

function makeJsonParser(): object
{
    return new class
    {
        use ParsesAiJsonResponse;

        public function parse(string $response): array
        {
            return $this->parseJSONResponse($response);
        }
    };
}

test('parseJSONResponse repairs truncated json with dart html content', function () {
    $html = '<section class="content-section"><h2 class="section-title">Loop in List</h2>'
        .'<pre><code class="language-dart">void main() {\n  for (final item in items) {\n    print(item);\n  }\n}</code></pre>'
        .'<div class="text-block">شرح الحلقة على القائمة.</div></section>';

    $full = json_encode([
        'title' => 'Loop in List في Dart',
        'slug' => 'loop-in-list-dart',
        'excerpt' => 'مرجع',
        'content' => $html,
    ], JSON_UNESCAPED_UNICODE);

    $truncated = mb_substr($full, 0, (int) (mb_strlen($full) * 0.7));

    $parser = makeJsonParser();
    $out = $parser->parse($truncated);

    expect($out['title'] ?? null)->toBe('Loop in List في Dart');
    expect($out['content'] ?? '')->toContain('content-section');
    expect($out['content'] ?? '')->toContain('language-dart');
});

test('parseJSONResponse extracts fields heuristically when repair cannot decode', function () {
    $raw = 'noise before {"title":"عنوان تجريبي","content":"<section class="content-section">'
        .'<h2 class="section-title">مقدمة</h2><div class="text-block">نص كافٍ ليكون HTML صالحاً.</div></section>';

    $parser = makeJsonParser();
    $out = $parser->parse($raw);

    expect($out['title'] ?? null)->toBe('عنوان تجريبي');
    expect($out['content'] ?? '')->toContain('<section');
});
