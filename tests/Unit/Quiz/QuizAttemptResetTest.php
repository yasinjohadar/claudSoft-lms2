<?php

use App\Services\Quiz\QuizAttemptLifecycleService;
use Tests\TestCase;

uses(TestCase::class);

test('reconcileProblematicAttemptsForQuiz aggregates lifecycle counts', function () {
    $service = Mockery::mock(QuizAttemptLifecycleService::class)->makePartial();
    $service->shouldReceive('reconcileEmptyInProgressAttempts')->with(49)->andReturn(2);
    $service->shouldReceive('reconcileExpiredInProgressAttempts')->with(49)->andReturn(1);
    $service->shouldReceive('reconcileStaleInProgressAttempts')->with(24, 49)->andReturn(3);
    $service->shouldReceive('reclassifyEmptyFinishedAttempts')->with(49)->andReturn(4);

    expect($service->reconcileProblematicAttemptsForQuiz(49))->toBe([
        'empty_in_progress' => 2,
        'expired_in_progress' => 1,
        'stale_in_progress' => 3,
        'empty_finished' => 4,
        'total' => 10,
    ]);
});

afterEach(function () {
    Mockery::close();
});
