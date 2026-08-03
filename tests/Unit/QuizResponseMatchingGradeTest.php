<?php

use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizResponse;

function pairOption(int $id, string $feedback): QuestionOption
{
    $option = new QuestionOption(['feedback' => $feedback]);
    $option->id = $id;

    return $option;
}

function pairResponse(string $method, ?array $submitted, array $requiredOptions, float $maxScore = 30.0): QuizResponse
{
    $responseData = $submitted === null ? null : ['answer' => $submitted];

    $response = new QuizResponse([
        'response_data' => $responseData,
        'max_score' => $maxScore,
    ]);

    if ($submitted !== null) {
        $builder = Mockery::mock();
        $builder->shouldReceive('where')->with('is_correct', true)->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect($requiredOptions));

        $question = Mockery::mock(QuestionBank::class);
        $question->shouldReceive('options')->andReturn($builder);

        $response->setRelation('question', $question);
    }

    return $response;
}

function invokePairGrading(string $method, QuizResponse $response): array
{
    $ref = new ReflectionMethod(QuizResponse::class, $method);
    $ref->setAccessible(true);

    return $ref->invoke($response);
}

dataset('pair grading methods', ['gradeMatching', 'gradeDragDrop']);

test('awards full score when all required pairs are answered correctly', function (string $method) {
    $options = [pairOption(1, 'فقرة'), pairOption(2, 'صورة'), pairOption(3, 'رابط')];

    [$ok, $score] = invokePairGrading($method, pairResponse($method, ['1' => 'فقرة', '2' => 'صورة', '3' => 'رابط'], $options));

    expect($ok)->toBeTrue()->and($score)->toBe(30.0);
})->with('pair grading methods');

test('gives proportional partial credit and is not fully correct when pairs are left unanswered', function (string $method) {
    // Regression: the denominator used to be the number of pairs the student
    // submitted, not the number required by the question — leaving 2 of 3
    // pairs blank while getting the 1 attempted pair right used to score 30/30.
    $options = [pairOption(1, 'فقرة'), pairOption(2, 'صورة'), pairOption(3, 'رابط')];

    [$ok, $score] = invokePairGrading($method, pairResponse($method, ['1' => 'فقرة'], $options));

    expect($ok)->toBeFalse()->and($score)->toBe(10.0);
})->with('pair grading methods');

test('gives zero when every attempted pair is wrong', function (string $method) {
    $options = [pairOption(1, 'فقرة'), pairOption(2, 'صورة')];

    [$ok, $score] = invokePairGrading($method, pairResponse($method, ['1' => 'صورة', '2' => 'فقرة'], $options));

    expect($ok)->toBeFalse()->and($score)->toBe(0.0);
})->with('pair grading methods');

test('returns zero without querying the question when response data is empty', function (string $method) {
    $response = pairResponse($method, null, []);

    [$ok, $score] = invokePairGrading($method, $response);

    expect($ok)->toBeFalse()->and($score)->toBe(0);
})->with('pair grading methods');

afterEach(function () {
    Mockery::close();
});
