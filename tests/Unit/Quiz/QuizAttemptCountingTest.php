<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Tests\TestCase;

uses(TestCase::class);

/**
 * @return array{quiz: Quiz, attemptsRelation: \Mockery\MockInterface}
 */
function mockQuizForCounting(int $attemptsAllowed = 1, bool $unlimited = false): array
{
    $quiz = Mockery::mock(Quiz::class)->makePartial();
    $quiz->attempts_allowed = $unlimited ? null : $attemptsAllowed;

    $quiz->shouldReceive('hasUnlimitedAttempts')->andReturn($unlimited);
    $quiz->shouldReceive('isAvailable')->andReturn(true);

    $attemptsRelation = Mockery::mock();

    $quiz->shouldReceive('attempts')->andReturn($attemptsRelation);

    return compact('quiz', 'attemptsRelation');
}

function chainFinishedAttempts($relation, int $studentId, int $count): void
{
    $relation->shouldReceive('realAttempts')->andReturnSelf();
    $relation->shouldReceive('where')->with('student_id', $studentId)->andReturnSelf();
    $relation->shouldReceive('whereIn')->with('status', Quiz::FINISHED_ATTEMPT_STATUSES)->andReturnSelf();
    $relation->shouldReceive('count')->andReturn($count);
}

function chainInProgressExists($relation, int $studentId, bool $exists): void
{
    $relation->shouldReceive('realAttempts')->andReturnSelf();
    $relation->shouldReceive('where')->with('student_id', $studentId)->andReturnSelf();
    $relation->shouldReceive('where')->with('status', 'in_progress')->andReturnSelf();
    $relation->shouldReceive('exists')->andReturn($exists);
}

test('in_progress attempt does not reduce remaining attempts', function () {
    ['quiz' => $quiz, 'attemptsRelation' => $relation] = mockQuizForCounting();

    $relation->shouldReceive('realAttempts')->andReturnSelf();
    $relation->shouldReceive('where')->with('student_id', 10)->andReturnSelf();
    $relation->shouldReceive('whereIn')->with('status', Quiz::FINISHED_ATTEMPT_STATUSES)->andReturnSelf();
    $relation->shouldReceive('count')->andReturn(0);

    $relation->shouldReceive('realAttempts')->andReturnSelf();
    $relation->shouldReceive('where')->with('student_id', 10)->andReturnSelf();
    $relation->shouldReceive('where')->with('status', 'in_progress')->andReturnSelf();
    $relation->shouldReceive('exists')->andReturn(true);

    expect($quiz->getRemainingAttempts(10))->toBe(1)
        ->and($quiz->getFinishedAttemptsCount(10))->toBe(0)
        ->and($quiz->hasInProgressAttempt(10))->toBeTrue()
        ->and($quiz->canAttempt(10))->toBeFalse();
});

test('submitted attempt counts toward attempt limit', function () {
    ['quiz' => $quiz, 'attemptsRelation' => $relation] = mockQuizForCounting();

    $relation->shouldReceive('realAttempts')->andReturnSelf();
    $relation->shouldReceive('where')->with('student_id', 10)->andReturnSelf();
    $relation->shouldReceive('whereIn')->with('status', Quiz::FINISHED_ATTEMPT_STATUSES)->andReturnSelf();
    $relation->shouldReceive('count')->andReturn(1);

    $relation->shouldReceive('realAttempts')->andReturnSelf();
    $relation->shouldReceive('where')->with('student_id', 10)->andReturnSelf();
    $relation->shouldReceive('where')->with('status', 'in_progress')->andReturnSelf();
    $relation->shouldReceive('exists')->andReturn(false);

    expect($quiz->getRemainingAttempts(10))->toBe(0)
        ->and($quiz->getFinishedAttemptsCount(10))->toBe(1)
        ->and($quiz->canAttempt(10))->toBeFalse();
});

test('canAttempt is false when in_progress exists even with remaining slot', function () {
    ['quiz' => $quiz, 'attemptsRelation' => $relation] = mockQuizForCounting(2);

    $relation->shouldReceive('realAttempts')->andReturnSelf();
    $relation->shouldReceive('where')->with('student_id', 10)->andReturnSelf();
    $relation->shouldReceive('where')->with('status', 'in_progress')->andReturnSelf();
    $relation->shouldReceive('exists')->andReturn(true);

    expect($quiz->canAttempt(10))->toBeFalse();
});

test('unlimited attempts returns null remaining', function () {
    ['quiz' => $quiz, 'attemptsRelation' => $relation] = mockQuizForCounting(unlimited: true);

    $relation->shouldReceive('realAttempts')->andReturnSelf();
    $relation->shouldReceive('where')->with('student_id', 10)->andReturnSelf();
    $relation->shouldReceive('where')->with('status', 'in_progress')->andReturnSelf();
    $relation->shouldReceive('exists')->andReturn(false);

    expect($quiz->getRemainingAttempts(10))->toBeNull()
        ->and($quiz->canAttempt(10))->toBeTrue();
});

test('finished attempt statuses constant includes submitted graded reviewing', function () {
    expect(Quiz::FINISHED_ATTEMPT_STATUSES)->toBe(['submitted', 'graded', 'reviewing']);
});

afterEach(function () {
    Mockery::close();
});
