<?php

use App\Services\Ai\DocumentationHtmlRepairer;
use App\Services\Ai\DocumentationSectionValidator;

function repairer(): DocumentationHtmlRepairer
{
    return new DocumentationHtmlRepairer;
}

function sectionTagBalance(string $html): array
{
    $lower = strtolower($html);

    return [substr_count($lower, '<section'), substr_count($lower, '</section>')];
}

test('a section truncated mid-tag comes back balanced', function () {
    // A run that hit the token ceiling used to store this verbatim, and every
    // later section rendered inside it.
    $html = repairer()->repairSection(
        '<section class="content-section"><h2 class="section-title">مقدمة</h2><div class="text-block"><p>نص ناقص',
        'مقدمة'
    );

    expect(sectionTagBalance($html))->toBe([1, 1])
        ->and($html)->toContain('</div>')
        ->and($html)->toContain('نص ناقص');
});

test('a collapsed dart code block gets its line breaks back', function () {
    // Taken from documentation_pages "dart-lists-comprehensive-guide", where 14 of
    // 16 blocks were stored on one line — everything after `//` was commented out.
    $html = repairer()->repairSection(
        '<section class="content-section"><h2 class="section-title">القوائم</h2>'
        .'<pre><code class="language-dart">void main() { // إنشاء قائمة List fixedList = List.filled(3, 0);'
        .' print(fixedList); // Output: [0, 0, 0] List other = List.generate(5, (i) =&gt; i * 2); print(other); }</code></pre>'
        .'</section>',
        'القوائم'
    );

    expect($html)->toContain("\n")
        ->and($html)->toContain('List fixedList = List.filled(3, 0);')
        ->and($html)->toContain('print(fixedList);');

    // Every statement must sit on its own line, not trail a comment.
    preg_match('/<code[^>]*>(.*?)<\/code>/s', $html, $m);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $m[1]))));
    expect($lines)->toContain('print(fixedList);')
        ->and(count($lines))->toBeGreaterThan(5);
});

test('a real one-liner short enough to be intentional is left alone', function () {
    $code = '<pre><code class="language-dart">final total = items.length;</code></pre>';
    $html = repairer()->repairDocument('<section class="content-section"><p>x</p>'.$code.'</section>');

    expect($html)->toContain('final total = items.length;')
        ->and($html)->not->toContain("final total = items.length;\n");
});

test('a truncated json envelope tail is dropped', function () {
    // documentation_pages id 45 ends with exactly this.
    $html = repairer()->repairSection(
        '<section class="content-section"><h2 class="section-title">الفقرات</h2><p>نص</p></section>'."\n".'<p>" }</p>',
        'الفقرات'
    );

    expect($html)->not->toContain('" }')
        ->and($html)->toContain('نص');
});

test('markdown fences and leading commentary are stripped', function () {
    $html = repairer()->repairSection(
        "```html\n<section class=\"content-section\"><h2 class=\"section-title\">أ</h2><p>نص</p></section>\n```",
        'أ'
    );

    expect($html)->not->toContain('```')
        ->and(sectionTagBalance($html))->toBe([1, 1]);
});

test('several sections in one reply collapse into one', function () {
    $html = repairer()->repairSection(
        '<section class="content-section"><h2 class="section-title">أ</h2><p>1</p></section>'
        .'<section class="content-section"><h2 class="section-title">ب</h2><p>2</p></section>',
        'أ'
    );

    expect(sectionTagBalance($html))->toBe([1, 1])
        ->and($html)->toContain('<h2 class="section-title">أ</h2>')
        // The second heading stays visible, demoted so it does not read as a
        // section of its own.
        ->and($html)->toContain('<h3 class="subsection-title">ب</h3>');
});

test('a bare fragment is wrapped and given its heading', function () {
    $html = repairer()->repairSection('<div class="text-block"><p>بدون قسم</p></div>', 'العنوان');

    expect(sectionTagBalance($html))->toBe([1, 1])
        ->and($html)->toContain('<section class="content-section">')
        ->and($html)->toContain('<h2 class="section-title">العنوان</h2>');
});

test('python is never guessed at, it is reported for a retry', function () {
    // Line breaks are semantic in Python, so reflowing it would corrupt working
    // code. The validator asks the model again instead.
    $code = 'def total(items): result = 0 for item in items: result += item return result '
        .'# padding to carry this sample comfortably past the one-hundred-and-sixty character length the checker cares about';
    $html = repairer()->repairDocument(
        '<section class="content-section"><p>x</p><pre><code class="language-python">'.$code.'</code></pre></section>'
    );

    expect($html)->toContain('def total(items): result = 0')
        ->and(repairer()->hasUnsplitCodeBlock($html))->toBeTrue();
});

test('the validator rejects thin sections and still-collapsed code', function () {
    $validator = new DocumentationSectionValidator;

    $thin = '<section class="content-section"><h2 class="section-title">أ</h2><p>قصير</p></section>';
    expect($validator->rejectionReason($thin, 'long', false))->toBe('too_short');

    $noHeading = '<section class="content-section"><p>'.str_repeat('نص طويل كفاية للتجاوز. ', 60).'</p></section>';
    expect($validator->rejectionReason($noHeading, 'long', false))->toBe('missing_heading');

    $body = '<section class="content-section"><h2 class="section-title">أ</h2><p>'
        .str_repeat('نص طويل كفاية لتجاوز الحد الأدنى. ', 60).'</p></section>';
    expect($validator->rejectionReason($body, 'long', false))->toBeNull();

    // A compact rung has a lower floor, so a shorter fallback still counts.
    $short = '<section class="content-section"><h2 class="section-title">أ</h2><p>'
        .str_repeat('نص متوسط الطول هنا. ', 20).'</p></section>';
    expect($validator->rejectionReason($short, 'long', false))->toBe('too_short')
        ->and($validator->rejectionReason($short, 'long', true))->toBeNull();
});
