<?php

use App\Support\MixedBidiText;
use Tests\TestCase;

uses(TestCase::class);

test('html option with arabic text is wrapped as ltr line', function () {
    $html = MixedBidiText::toHtml('<h2>مرحبا</h2>')->toHtml();

    expect($html)
        ->toContain('mixed-bidi-ltr-line')
        ->toContain('mixed-bidi-tag-chip')
        ->toContain('&lt;h2&gt;')
        ->toContain('مرحبا')
        ->toContain('&lt;/h2&gt;');

    expect(strpos($html, '&lt;h2&gt;'))->toBeLessThan(strpos($html, '&lt;/h2&gt;'));
});

test('arabic sentence with embedded tag keeps rtl runs around text', function () {
    $html = MixedBidiText::toHtml('استخدم العنصر <title> في الصفحة')->toHtml();

    expect($html)
        ->toContain('mixed-bidi-rtl-run')
        ->toContain('mixed-bidi-tag-chip')
        ->toContain('&lt;title&gt;')
        ->not->toContain('mixed-bidi-ltr-line');
});

test('arabic sentence with embedded html snippet wraps only snippet as ltr', function () {
    $html = MixedBidiText::toHtml('ما الناتج الصحيح لتنفيذ الكود التالي : <h3> HTML </h3> ؟')->toHtml();

    expect($html)
        ->toContain('mixed-bidi-rtl-run')
        ->toContain('mixed-bidi-ltr-line')
        ->toContain('&lt;h3&gt;')
        ->toContain('&lt;/h3&gt;')
        ->toContain('HTML');

    $ltrStart = strpos($html, 'mixed-bidi-ltr-line');
    $h3Start = strpos($html, '&lt;h3&gt;');
    $h3End = strpos($html, '&lt;/h3&gt;');
    $rtlStart = strpos($html, 'mixed-bidi-rtl-run');

    expect($rtlStart)->toBeLessThan($ltrStart)
        ->and($h3Start)->toBeLessThan($h3End);
});

test('arabic prose with multiple separate tags keeps each tag in place', function () {
    $html = MixedBidiText::toHtml('ما الفرق الأساسي بين الوسم <strong> والوسم <b> ؟')->toHtml();

    $strongPos = strpos($html, '&lt;strong&gt;');
    $bPos = strpos($html, '&lt;b&gt;');

    expect($html)
        ->toContain('mixed-bidi-rtl-run')
        ->toContain('mixed-bidi-tag-chip')
        ->and($strongPos)->not->toBeFalse()
        ->and($bPos)->not->toBeFalse()
        ->and($strongPos)->toBeLessThan($bPos);

    expect(substr_count($html, 'mixed-bidi-ltr-line'))->toBe(0);
});

test('arabic option with two tags does not merge them into one ltr block', function () {
    $html = MixedBidiText::toHtml('بينما <b> يجعل النص غامقاً فقط لتغيير الشكل يستخدم للتأكيد الدلالي <strong>')->toHtml();

    $bPos = strpos($html, '&lt;b&gt;');
    $strongPos = strpos($html, '&lt;strong&gt;');

    expect($bPos)->not->toBeFalse()
        ->and($strongPos)->not->toBeFalse()
        ->and($bPos)->toBeLessThan($strongPos);

    expect(substr_count($html, 'mixed-bidi-ltr-line'))->toBe(0);
});

test('backtick code still renders as inline code', function () {
    $html = MixedBidiText::toHtml('الخاصية `href` هي المسؤولة')->toHtml();

    expect($html)
        ->toContain('mixed-bidi-inline-code')
        ->toContain('href');
});
