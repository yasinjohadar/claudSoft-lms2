<?php

use App\Models\QuestionBank;
use App\Models\QuestionType;
use App\Services\QuestionBank\Export\QuestionBankExportSerializer;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

test('export serializer maps multiple choice row for excel import', function () {
    $type = new QuestionType([
        'name' => 'multiple_choice_single',
        'display_name' => 'اختيار من متعدد (إجابة واحدة)',
    ]);

    $question = Mockery::mock(QuestionBank::class)->makePartial();
    $question->question_text = '<p>ما عاصمة السعودية؟</p>';
    $question->default_grade = 2;
    $question->difficulty_level = 'easy';
    $question->tags = ['جغرافيا', 'اختبار'];
    $question->metadata = [];
    $question->lesson_name = null;
    $question->explanation = null;
    $question->shouldReceive('loadMissing')->andReturnSelf();
    $question->shouldReceive('getAttribute')->with('questionType')->andReturn($type);
    $question->questionType = $type;
    $question->course = null;
    $question->programmingLanguages = collect();

    $option1 = (object) ['option_text' => 'الرياض', 'is_correct' => true, 'option_order' => 1, 'feedback' => null];
    $option2 = (object) ['option_text' => 'جدة', 'is_correct' => false, 'option_order' => 2, 'feedback' => null];
    $question->options = collect([$option1, $option2]);

    $row = (new QuestionBankExportSerializer)->toImportRow($question);

    expect($row['question_type'])->toBe('اختيار من متعدد (إجابة واحدة)')
        ->and($row['question_text'])->toBe('ما عاصمة السعودية؟')
        ->and($row['option_1'])->toBe('الرياض')
        ->and($row['option_2'])->toBe('جدة')
        ->and($row['correct_answer'])->toBe('1')
        ->and($row['tags'])->toBe('جغرافيا,اختبار');
});

test('export serializer maps structured json for true false', function () {
    $type = new QuestionType([
        'name' => 'true_false',
        'display_name' => 'صح / خطأ',
    ]);

    $question = Mockery::mock(QuestionBank::class)->makePartial();
    $question->question_text = 'الشمس تشرق من الشرق';
    $question->default_grade = 1;
    $question->difficulty_level = 'medium';
    $question->tags = null;
    $question->metadata = [];
    $question->lesson_name = null;
    $question->explanation = null;
    $question->shouldReceive('loadMissing')->andReturnSelf();
    $question->questionType = $type;
    $question->course = null;
    $question->programmingLanguages = collect();

    $option1 = (object) ['option_text' => 'صح', 'is_correct' => true, 'option_order' => 1, 'feedback' => null];
    $option2 = (object) ['option_text' => 'خطأ', 'is_correct' => false, 'option_order' => 2, 'feedback' => null];
    $question->options = collect([$option1, $option2]);

    $structured = (new QuestionBankExportSerializer)->toStructuredQuestion($question);

    expect($structured['correct_answer'])->toBeTrue()
        ->and($structured['default_grade'])->toBe(1.0)
        ->and($structured['difficulty'])->toBe('medium');
});

afterEach(function () {
    Mockery::close();
});
