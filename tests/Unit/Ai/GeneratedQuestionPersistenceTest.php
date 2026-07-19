<?php

use App\Services\Ai\GeneratedQuestionValidator;

test('validate preserves type-specific fields', function () {
    $input = [[
        'type' => 'matching',
        'question' => 'طابق',
        'pairs' => [['question' => 'A', 'answer' => '1']],
        'correct_answers' => ['ignored'],
        'expected_value' => 99,
    ], [
        'type' => 'fill_blanks',
        'question' => 'املأ [[blank]]',
        'dropdown_options' => ['جواب', 'مشتت'],
        'blank_answers' => ['جواب'],
    ], [
        'type' => 'numerical',
        'question' => 'كم؟',
        'expected_value' => 42,
        'tolerance' => 0.5,
    ]];

    $validated = GeneratedQuestionValidator::validate($input);

    expect($validated)->toHaveCount(3);
    expect($validated[0]['pairs'])->toHaveCount(1);
    expect($validated[1]['blank_answers'])->toBe(['جواب']);
    expect($validated[1]['correct_answers'])->toBe(['جواب']);
    expect($validated[1]['dropdown_options'])->toBe(['جواب', 'مشتت']);
    expect($validated[2]['expected_value'])->toBe(42);
    expect($validated[2]['tolerance'])->toBe(0.5);
});

test('normalizeTrueFalseAnswer handles boolean and arabic variants', function () {
    expect(GeneratedQuestionValidator::normalizeTrueFalseAnswer(true))->toBe('صح');
    expect(GeneratedQuestionValidator::normalizeTrueFalseAnswer(false))->toBe('خطأ');
    expect(GeneratedQuestionValidator::normalizeTrueFalseAnswer('true'))->toBe('صح');
    expect(GeneratedQuestionValidator::normalizeTrueFalseAnswer('false'))->toBe('خطأ');
    expect(GeneratedQuestionValidator::normalizeTrueFalseAnswer('صحيح'))->toBe('صح');
    expect(GeneratedQuestionValidator::normalizeTrueFalseAnswer('خاطئ'))->toBe('خطأ');
});

test('normalize converts fill blank placeholders to [[blank]]', function () {
    $normalized = GeneratedQuestionValidator::normalize([
        'type' => 'fill_blanks',
        'question' => 'الكلمة [___] هنا و ___ أيضاً',
    ]);

    expect($normalized['question'])->toContain('[[blank]]');
    expect(substr_count($normalized['question'], '[[blank]]'))->toBe(2);
});

test('normalize prepares true_false for consistent save', function () {
    $data = GeneratedQuestionValidator::normalize([
        'type' => 'true_false',
        'correct_answer' => false,
        'options' => ['True', 'False'],
    ]);

    expect($data['correct_answer'])->toBe('خطأ');
    expect($data['options'])->toBe(['صح', 'خطأ']);
});

test('validate and normalize pipeline keeps fill blank dropdown answers', function () {
    $raw = [[
        'type' => 'fill_blanks',
        'question' => 'الإجابة [___] هنا',
        'correct_answers' => ['صحيحة'],
    ]];

    $validated = GeneratedQuestionValidator::validate($raw);
    $normalized = GeneratedQuestionValidator::normalize($validated[0]);

    expect($validated[0]['blank_answers'])->toBe(['صحيحة']);
    expect($validated[0]['correct_answers'])->toBe(['صحيحة']);
    expect($normalized['question'])->toBe('الإجابة [[blank]] هنا');
    expect($normalized['dropdown_options'])->toContain('صحيحة');
    expect($normalized['blank_answers'])->toBe(['صحيحة']);
});

test('normalize merges blank answers into dropdown options', function () {
    $data = GeneratedQuestionValidator::normalize([
        'type' => 'fill_blanks',
        'question' => 'استخدم [[blank]] داخل [[blank]]',
        'dropdown_options' => ['src', 'img'],
        'blank_answers' => ['href', 'a'],
    ]);

    expect($data['blank_answers'])->toBe(['href', 'a']);
    expect($data['dropdown_options'])->toEqualCanonicalizing(['src', 'img', 'href', 'a']);
});

test('numerical normalize maps correct_answer to expected_value', function () {
    $data = GeneratedQuestionValidator::normalize([
        'type' => 'numerical',
        'correct_answer' => 7.5,
    ]);

    expect($data['expected_value'])->toBe(7.5);
});

test('sanitizeExplanation rejects stub explanations', function () {
    expect(GeneratedQuestionValidator::sanitizeExplanation('صح وخطأ'))->toBeNull();
    expect(GeneratedQuestionValidator::sanitizeExplanation('true/false'))->toBeNull();
    expect(GeneratedQuestionValidator::sanitizeExplanation('لأن وسم img عنصر void ولا يحتاج وسم إغلاق في HTML.'))
        ->toBe('لأن وسم img عنصر void ولا يحتاج وسم إغلاق في HTML.');
});
