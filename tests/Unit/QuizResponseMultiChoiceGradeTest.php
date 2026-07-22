<?php

use App\Models\QuestionBank;
use App\Models\QuizResponse;

function multiChoiceResponse(array $selectedIds, array $correctIds, float $maxScore = 10.0): QuizResponse
{
    $response = new QuizResponse([
        'selected_option_ids' => $selectedIds,
        'max_score' => $maxScore,
    ]);

    $builder = Mockery::mock();
    $builder->shouldReceive('where')->with('is_correct', true)->andReturnSelf();
    $builder->shouldReceive('pluck')->with('id')->andReturn(collect($correctIds));

    $question = Mockery::mock(QuestionBank::class);
    $question->shouldReceive('options')->andReturn($builder);

    $response->setRelation('question', $question);

    return $response;
}

function invokeGradeMultipleChoiceMultiple(QuizResponse $response): array
{
    $method = new ReflectionMethod(QuizResponse::class, 'gradeMultipleChoiceMultiple');
    $method->setAccessible(true);

    return $method->invoke($response);
}

test('multi choice awards full score only for exact correct set', function () {
    [$ok, $score] = invokeGradeMultipleChoiceMultiple(
        multiChoiceResponse([2, 4], [2, 4])
    );

    expect($ok)->toBeTrue()->and($score)->toBe(10.0);
});

test('multi choice gives zero when wrong extras are selected with all correct', function () {
    // Student selected all: wrong(1,3) + correct(2,4) — old bug awarded 10/10
    [$ok, $score] = invokeGradeMultipleChoiceMultiple(
        multiChoiceResponse([1, 2, 3, 4], [2, 4])
    );

    expect($ok)->toBeFalse()->and($score)->toBe(0.0);
});

test('multi choice gives partial credit for subset of correct without wrong extras', function () {
    [$ok, $score] = invokeGradeMultipleChoiceMultiple(
        multiChoiceResponse([2], [2, 4])
    );

    expect($ok)->toBeFalse()->and($score)->toBe(5.0);
});

test('multi choice normalizes string option ids', function () {
    [$ok, $score] = invokeGradeMultipleChoiceMultiple(
        multiChoiceResponse(['2', '4'], [2, 4])
    );

    expect($ok)->toBeTrue()->and($score)->toBe(10.0);
});

afterEach(function () {
    Mockery::close();
});
