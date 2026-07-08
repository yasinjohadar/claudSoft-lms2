<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);

test('getRemainingSeconds returns full limit for fresh attempt', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-08 12:00:00', 'UTC'));

    $quiz = new Quiz(['time_limit' => 30]);
    $attempt = new QuizAttempt([
        'started_at' => Carbon::parse('2026-07-08 12:00:00', 'UTC'),
    ]);
    $attempt->setRelation('quiz', $quiz);

    expect($attempt->getRemainingSeconds())->toBe(30 * 60)
        ->and($attempt->isTimeExpired())->toBeFalse();

    Carbon::setTestNow();
});

test('getRemainingSeconds decreases with elapsed wall time', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-08 12:10:00', 'UTC'));

    $quiz = new Quiz(['time_limit' => 30]);
    $attempt = new QuizAttempt([
        'started_at' => Carbon::parse('2026-07-08 12:00:00', 'UTC'),
    ]);
    $attempt->setRelation('quiz', $quiz);

    expect($attempt->getRemainingSeconds())->toBe(20 * 60)
        ->and($attempt->isTimeExpired())->toBeFalse();

    Carbon::setTestNow();
});

test('getRemainingSeconds and isTimeExpired detect expired attempt', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-08 13:00:00', 'UTC'));

    $quiz = new Quiz(['time_limit' => 30]);
    $attempt = new QuizAttempt([
        'started_at' => Carbon::parse('2026-07-08 12:00:00', 'UTC'),
    ]);
    $attempt->setRelation('quiz', $quiz);

    expect($attempt->getRemainingSeconds())->toBe(0)
        ->and($attempt->isTimeExpired())->toBeTrue();

    Carbon::setTestNow();
});

test('getRemainingSeconds is null when quiz has no time limit', function () {
    $quiz = new Quiz(['time_limit' => null]);
    $attempt = new QuizAttempt([
        'started_at' => now(),
    ]);
    $attempt->setRelation('quiz', $quiz);

    expect($attempt->getRemainingSeconds())->toBeNull()
        ->and($attempt->isTimeExpired())->toBeFalse();
});

test('getQuizEndsAtMs matches started_at plus time_limit', function () {
    $started = Carbon::parse('2026-07-08 12:00:00', 'UTC');
    $quiz = new Quiz(['time_limit' => 15]);
    $attempt = new QuizAttempt(['started_at' => $started]);
    $attempt->setRelation('quiz', $quiz);

    expect($attempt->getQuizEndsAtMs())->toBe(
        $started->copy()->addMinutes(15)->getTimestamp() * 1000
    );
});
