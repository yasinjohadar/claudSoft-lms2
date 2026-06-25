<?php

use App\Models\QuestionBank;
use App\Models\QuizResponse;
use Illuminate\Support\Collection;

function orderingQuizResponseWithOptions(array $responseData, array $correctOptionIds): QuizResponse
{
    $response = new QuizResponse([
        'response_data' => $responseData,
        'max_score' => 2.0,
    ]);

    $builder = Mockery::mock();
    $builder->shouldReceive('orderBy')->with('option_order')->andReturnSelf();
    $builder->shouldReceive('pluck')->with('id')->andReturn(collect($correctOptionIds));

    $question = Mockery::mock(QuestionBank::class);
    $question->shouldReceive('options')->andReturn($builder);

    $response->setRelation('question', $question);

    return $response;
}

function invokeGradeOrdering(QuizResponse $response): bool
{
    $method = new ReflectionMethod(QuizResponse::class, 'gradeOrdering');
    $method->setAccessible(true);

    return $method->invoke($response);
}

test('ordering grades correctly when answer is wrapped in response_data answer key', function () {
    $correct = [10, 20, 30, 40];
    $response = orderingQuizResponseWithOptions(['answer' => $correct], $correct);

    expect(invokeGradeOrdering($response))->toBeTrue();
});

test('ordering grades correctly when answer is stored as flat indexed array legacy format', function () {
    $correct = [10, 20, 30, 40];
    $response = orderingQuizResponseWithOptions($correct, $correct);

    expect(invokeGradeOrdering($response))->toBeTrue();
});

test('ordering grades correctly when option ids are strings from javascript', function () {
    $correct = [10, 20, 30, 40];
    $response = orderingQuizResponseWithOptions(['answer' => ['10', '20', '30', '40']], $correct);

    expect(invokeGradeOrdering($response))->toBeTrue();
});

test('ordering fails when sequence is wrong', function () {
    $correct = [10, 20, 30, 40];
    $response = orderingQuizResponseWithOptions(['answer' => [40, 30, 20, 10]], $correct);

    expect(invokeGradeOrdering($response))->toBeFalse();
});

test('ordering supports sequence key alias', function () {
    $correct = [5, 6, 7];
    $response = orderingQuizResponseWithOptions(['sequence' => $correct], $correct);

    expect(invokeGradeOrdering($response))->toBeTrue();
});

afterEach(function () {
    Mockery::close();
});
