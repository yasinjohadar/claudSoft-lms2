<?php

use App\Models\QuestionBank;
use Tests\TestCase;

uses(TestCase::class);

test('fill blanks preview maps answers by option order', function () {
    $question = new QuestionBank([
        'question_text' => 'في HTML5، استخدم [[blank]] داخل [[blank]] لتحديد الرابط.',
        'metadata' => [],
    ]);

    $question->setRelation('options', collect([
        (object) ['option_text' => 'a', 'is_correct' => true, 'option_order' => 1, 'id' => 1],
        (object) ['option_text' => 'href', 'is_correct' => true, 'option_order' => 2, 'id' => 2],
    ]));

    $preview = $question->getFillBlanksPreviewData();

    expect($preview['parts'])->toHaveCount(3)
        ->and($preview['answers'])->toBe(['a', 'href']);
});

test('fill blanks preview uses first answer for single blank with multiple options', function () {
    $question = new QuestionBank([
        'question_text' => 'يستخدم الوسم [[blank]] لإنشاء فقرة.',
        'metadata' => [],
    ]);

    $question->setRelation('options', collect([
        (object) ['option_text' => 'p', 'is_correct' => true, 'option_order' => 1, 'id' => 1],
        (object) ['option_text' => '<p>', 'is_correct' => true, 'option_order' => 2, 'id' => 2],
    ]));

    $preview = $question->getFillBlanksPreviewData();

    expect($preview['answers'])->toBe(['p']);
});

test('fill blanks preview falls back to metadata answers', function () {
    $question = new QuestionBank([
        'question_text' => 'العاصمة هي [[blank]].',
        'metadata' => ['correct_answers' => ['الرياض']],
    ]);

    $question->setRelation('options', collect());

    $preview = $question->getFillBlanksPreviewData();

    expect($preview['answers'])->toBe(['الرياض']);
});
