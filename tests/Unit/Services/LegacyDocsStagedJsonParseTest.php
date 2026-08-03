<?php

use App\Services\Ai\Concerns\ParsesAiJsonResponse;
use App\Services\Ai\DocumentationAiResultNormalizer;
use Illuminate\Support\Facades\Log;

uses(Tests\TestCase::class);

beforeEach(function () {
    Log::shouldReceive('info')->byDefault();
    Log::shouldReceive('warning')->byDefault();
});

test('legacy outline and section json shapes parse for staged docs generation', function () {
    $parser = new class
    {
        use ParsesAiJsonResponse;

        public function parse(string $response): array
        {
            return $this->parseJSONResponse($response);
        }
    };

    $outlineRaw = json_encode([
        'title' => 'Loop in List',
        'slug' => 'loop-in-list',
        'excerpt' => 'مرجع',
        'sections' => [
            ['heading' => 'مقدمة', 'brief' => 'تعريف'],
            ['heading' => 'أمثلة', 'brief' => 'كود'],
        ],
    ], JSON_UNESCAPED_UNICODE);

    $outline = $parser->parse($outlineRaw);
    expect($outline['sections'])->toHaveCount(2);

    $sectionRaw = json_encode([
        'html' => '<section class="content-section"><h2 class="section-title">مقدمة</h2>'
            .'<div class="text-block">شرح كافٍ مع مثال Dart.</div></section>',
    ], JSON_UNESCAPED_UNICODE);

    $section = $parser->parse($sectionRaw);
    $html = (new DocumentationAiResultNormalizer)->extractSectionHtml((string) ($section['html'] ?? ''));

    expect($html)->toContain('content-section')
        ->and($html)->toContain('مقدمة');
});
