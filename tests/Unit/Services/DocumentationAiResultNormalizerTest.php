<?php

use App\Services\Ai\DocumentationAiResultNormalizer;

test('unwrapPayload promotes wizard json string into separate fields', function () {
    $normalizer = new DocumentationAiResultNormalizer;
    $raw = json_encode([
        'title' => 'دليل CSS Tables الشامل',
        'slug' => 'css-tables-complete-reference',
        'excerpt' => 'مرجع تفصيلي لتنسيق الجداول في CSS',
        'content' => '<section class="content-section"><h2 class="section-title">مقدمة</h2><div class="text-block">نص</div></section>',
    ], JSON_UNESCAPED_UNICODE);

    $out = $normalizer->unwrapPayload($raw);

    expect($out['title'])->toBe('دليل CSS Tables الشامل');
    expect($out['slug'])->toBe('css-tables-complete-reference');
    expect($out['excerpt'])->toBe('مرجع تفصيلي لتنسيق الجداول في CSS');
    expect($out['content'])->toContain('content-section');
    expect($normalizer->looksLikeJsonBlob($out['content']))->toBeFalse();
});

test('assertWizardShape rejects raw json as content and topic-as-title', function () {
    $normalizer = new DocumentationAiResultNormalizer;
    $topic = 'قم بإنشاء مرجع كامل وبالتفصيل عن tables في لغة CSS بالتفصيل مع كل الخصائص وكل طرق الكتابة ليكون مرجع شامل وكامل مع الأمثلة الرائعة';

    expect(fn () => $normalizer->assertWizardShape([
        'title' => $topic,
        'content' => $topic,
    ], $topic))->toThrow(\RuntimeException::class);
});

test('assertWizardShape accepts unwrapped json payload with real html', function () {
    $normalizer = new DocumentationAiResultNormalizer;
    $topic = 'قم بإنشاء مرجع كامل وبالتفصيل عن tables في لغة CSS بالتفصيل مع كل الخصائص وكل طرق الكتابة ليكون مرجع شامل وكامل مع الأمثلة الرائعة';

    $jsonBlob = json_encode([
        'title' => 'دليل CSS Tables الشامل: كل الخصائص والطرق',
        'slug' => 'css-tables-complete-reference',
        'excerpt' => 'مرجع تفصيلي شامل لتنسيق الجداول في CSS',
        'content' => '<section class="content-section"><h2 class="section-title">مقدمة</h2><p>تُعد الجداول من أهم عناصر العرض.</p></section>',
    ], JSON_UNESCAPED_UNICODE);

    // Simulate bad storage: title=topic, content=full JSON string
    $shaped = $normalizer->assertWizardShape(
        $normalizer->unwrapPayload([
            'title' => $topic,
            'excerpt' => $jsonBlob,
            'content' => $jsonBlob,
        ]),
        $topic
    );

    expect($shaped['title'])->toBe('دليل CSS Tables الشامل: كل الخصائص والطرق');
    expect($shaped['title'])->not->toBe($topic);
    expect($shaped['content'])->toContain('<section');
    expect($shaped['excerpt'])->not->toContain('"title"');
    expect($shaped['meta_title'])->not->toBe($topic);
    expect($normalizer->looksLikeJsonBlob($shaped['meta_description']))->toBeFalse();
});

test('extractSectionHtml pulls html from nested wizard json', function () {
    $normalizer = new DocumentationAiResultNormalizer;
    $raw = json_encode([
        'title' => 'X',
        'content' => '<section class="content-section"><h2 class="section-title">قسم</h2><div class="text-block">جسم</div></section>',
    ], JSON_UNESCAPED_UNICODE);

    $html = $normalizer->extractSectionHtml($raw);

    expect($html)->toContain('content-section');
    expect($normalizer->isPlausibleHtml($html))->toBeTrue();
});

test('looksLikeInstructionPrompt detects command-like titles', function () {
    $normalizer = new DocumentationAiResultNormalizer;

    expect($normalizer->looksLikeInstructionPrompt(
        'قم بإنشاء مرجع كامل عن CSS',
        null
    ))->toBeTrue();

    expect($normalizer->looksLikeInstructionPrompt(
        'دليل CSS Tables',
        'قم بإنشاء مرجع كامل وبالتفصيل عن tables في لغة CSS بالتفصيل مع كل الخصائص'
    ))->toBeFalse();
});

test('unwrapPayload recovers truncated json with html and dart code', function () {
    $normalizer = new DocumentationAiResultNormalizer;
    $html = '<section class="content-section"><h2 class="section-title">Loop</h2>'
        .'<pre><code class="language-dart">for (final i in list) { print(i); }</code></pre>'
        .'<div class="text-block">شرح مختصر عن التكرار.</div></section>';

    $full = json_encode([
        'title' => 'Loop in List',
        'slug' => 'loop-in-list',
        'excerpt' => 'مرجع',
        'content' => $html,
    ], JSON_UNESCAPED_UNICODE);

    // Cut mid-string to simulate max_tokens truncation.
    $truncated = mb_substr($full, 0, (int) (mb_strlen($full) * 0.65));

    $out = $normalizer->unwrapPayload($truncated);

    expect($out['title'] ?? null)->toBe('Loop in List');
    expect($out['content'] ?? '')->toContain('content-section');
    expect($out['content'] ?? '')->toContain('language-dart');
});
