<?php

use App\Models\QuestionModule;
use App\Models\QuestionModuleAttempt;
use App\Services\QuestionModule\QuestionModuleAttemptLifecycleService;
use Tests\TestCase;

uses(TestCase::class);

test('qm resolveExpiredAttempt abandons expired attempt without answers', function () {
    $attempt = Mockery::mock(QuestionModuleAttempt::class)->makePartial();
    $attempt->status = 'in_progress';
    $attempt->shouldReceive('isTimeExpired')->andReturn(true);
    $attempt->shouldReceive('loadMissing')->with(['responses', 'questionModule'])->andReturnSelf();
    $attempt->shouldReceive('hasAnsweredResponses')->andReturn(false);
    $attempt->shouldReceive('abandon')->once();

    $service = new QuestionModuleAttemptLifecycleService();

    expect($service->resolveExpiredAttempt($attempt))->toBe('abandoned');
});

test('qm resolveExpiredAttempt requests auto submit when expired with answers', function () {
    $attempt = Mockery::mock(QuestionModuleAttempt::class)->makePartial();
    $attempt->status = 'in_progress';
    $attempt->shouldReceive('isTimeExpired')->andReturn(true);
    $attempt->shouldReceive('loadMissing')->with(['responses', 'questionModule'])->andReturnSelf();
    $attempt->shouldReceive('hasAnsweredResponses')->andReturn(true);
    $attempt->shouldReceive('abandon')->never();

    $service = new QuestionModuleAttemptLifecycleService();

    expect($service->resolveExpiredAttempt($attempt))->toBe('auto_submit');
});

test('question module canStudentAttempt is false when in progress exists', function () {
    $module = Mockery::mock(QuestionModule::class)->makePartial();
    $module->attempts_allowed = 3;

    $relation = Mockery::mock();
    $module->shouldReceive('studentAttempts')->with(10)->andReturn($relation);
    $relation->shouldReceive('where')->with('status', 'in_progress')->andReturnSelf();
    $relation->shouldReceive('exists')->andReturn(true);

    expect($module->canStudentAttempt(10))->toBeFalse();
});

test('question module getRemainingAttempts excludes abandoned attempts', function () {
    $module = Mockery::mock(QuestionModule::class)->makePartial();
    $module->attempts_allowed = 3;

    $relation = Mockery::mock();
    $module->shouldReceive('studentAttempts')->with(10)->andReturn($relation);
    $relation->shouldReceive('where')->with('status', 'completed')->andReturnSelf();
    $relation->shouldReceive('count')->andReturn(1);

    expect($module->getRemainingAttempts(10))->toBe(2);
});

afterEach(function () {
    Mockery::close();
});
