<?php

use App\Services\Ai\AIBlogPostService;
use App\Services\Ai\BlogHtmlStyleGuide;
use App\Services\AiNew\BlogAiPipelineService;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Guards the "طويل جداً" tier: a long article is produced by planning more
 * sections and giving each one more room, so every knob has to move together.
 * A missing arm here means very_long silently degrades to the medium default.
 */
function blogSectionCount(string $length): int
{
    $class = new ReflectionClass(BlogAiPipelineService::class);
    $method = $class->getMethod('sectionCountForLength');
    $method->setAccessible(true);

    return $method->invoke($class->newInstanceWithoutConstructor(), $length);
}

test('very_long plans more sections than long', function () {
    expect(blogSectionCount('short'))->toBe(3);
    expect(blogSectionCount('medium'))->toBe(5);
    expect(blogSectionCount('long'))->toBe(8);
    expect(blogSectionCount('very_long'))->toBe(13);
});

test('very_long gets its own completion-token ceiling above long', function () {
    $long = BlogAiPipelineService::sectionTokenCeiling('long');
    $veryLong = BlogAiPipelineService::sectionTokenCeiling('very_long');

    expect($long)->toBe(4096);
    expect($veryLong)->toBe(6144);
    // Raising ai.blog.section_max_tokens instead would have widened 'long' too.
    expect($veryLong)->toBeGreaterThan($long);
});

test('the legacy engine mirrors the same ceiling for very_long', function () {
    $model = new App\Models\AIModel(['max_tokens' => 0]);

    $service = app(AIBlogPostService::class);

    expect($service->tokensForStage($model, 'section', false, 'very_long'))->toBe(6144);
    expect($service->tokensForStage($model, 'section', false, 'long'))->toBe(4096);
});

test('very_long carries its own Arabic word budgets', function () {
    expect(BlogHtmlStyleGuide::pageBudget('very_long'))->toContain('4000–6500');
    expect(BlogHtmlStyleGuide::sectionBudget('very_long'))->toContain('320–480');

    // An unknown length must still fall back rather than blow up.
    expect(BlogHtmlStyleGuide::pageBudget('nonsense'))->toContain('1000–1500');
});
