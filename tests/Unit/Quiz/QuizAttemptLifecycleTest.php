<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\Quiz\QuizAttemptLifecycleService;
use App\Services\Quiz\QuizAttemptStartService;
use App\Services\Quiz\QuizRandomSelectionService;
use Tests\TestCase;

uses(TestCase::class);

test('prepareStart rejects quiz with no questions', function () {
    $quiz = Mockery::mock(Quiz::class)->makePartial();
    $quiz->shouldReceive('isRandomPool')->andReturn(false);

    $quizQuestionsRelation = Mockery::mock();
    $quizQuestionsRelation->shouldReceive('whereNotNull')->with('question_id')->andReturnSelf();
    $quizQuestionsRelation->shouldReceive('orderBy')->with('question_order')->andReturnSelf();
    $quizQuestionsRelation->shouldReceive('pluck')->with('question_id')->andReturn(collect());

    $quiz->shouldReceive('quizQuestions')->andReturn($quizQuestionsRelation);
    $quiz->shuffle_questions = false;
    $quiz->max_score = 0;

    $service = new QuizAttemptStartService(new QuizRandomSelectionService());

    $service->prepareStart($quiz, 1);
})->throws(InvalidArgumentException::class, 'لا يمكن بدء الاختبار لعدم وجود أسئلة');

test('resolveExpiredAttempt abandons expired attempt without answers', function () {
    $attempt = Mockery::mock(QuizAttempt::class)->makePartial();
    $attempt->shouldReceive('getAttribute')->with('status')->andReturn('in_progress');
    $attempt->status = 'in_progress';
    $attempt->shouldReceive('isTimeExpired')->andReturn(true);
    $attempt->shouldReceive('loadMissing')->with(['responses', 'quiz'])->andReturnSelf();
    $attempt->shouldReceive('hasAnsweredResponses')->andReturn(false);
    $attempt->shouldReceive('abandon')->once();

    $service = app(QuizAttemptLifecycleService::class);

    expect($service->resolveExpiredAttempt($attempt))->toBe('abandoned');
});

test('resolveExpiredAttempt requests auto submit only when all questions answered', function () {
    $attempt = Mockery::mock(QuizAttempt::class)->makePartial();
    $attempt->status = 'in_progress';
    $attempt->shouldReceive('isTimeExpired')->andReturn(true);
    $attempt->shouldReceive('loadMissing')->with(['responses', 'quiz'])->andReturnSelf();
    $attempt->shouldReceive('hasAnsweredResponses')->andReturn(true);
    $attempt->shouldReceive('isFullyAnswered')->andReturn(true);
    $attempt->shouldReceive('update')->never();

    $service = app(QuizAttemptLifecycleService::class);

    expect($service->resolveExpiredAttempt($attempt))->toBe('auto_submit');
});

test('resolveExpiredAttempt does not auto submit when answers are incomplete', function () {
    $attempt = Mockery::mock(QuizAttempt::class)->makePartial();
    $attempt->status = 'in_progress';
    $attempt->shouldReceive('isTimeExpired')->andReturn(true);
    $attempt->shouldReceive('loadMissing')->with(['responses', 'quiz'])->andReturnSelf();
    $attempt->shouldReceive('hasAnsweredResponses')->andReturn(true);
    $attempt->shouldReceive('isFullyAnswered')->andReturn(false);
    $attempt->shouldReceive('abandon')->never();
    $attempt->shouldReceive('update')->never();

    $service = app(QuizAttemptLifecycleService::class);

    expect($service->resolveExpiredAttempt($attempt))->toBeNull();
});

test('resolveExpiredAttempt returns null for active attempt', function () {
    $attempt = Mockery::mock(QuizAttempt::class)->makePartial();
    $attempt->status = 'in_progress';
    $attempt->shouldReceive('isTimeExpired')->andReturn(false);

    $service = app(QuizAttemptLifecycleService::class);

    expect($service->resolveExpiredAttempt($attempt))->toBeNull();
});

test('prepareResumableAttempt abandons expired empty attempt before resume', function () {
    $quiz = Mockery::mock(Quiz::class)->makePartial();
    $quiz->id = 10;

    $expiredAttempt = Mockery::mock(QuizAttempt::class)->makePartial();
    $expiredAttempt->status = 'in_progress';
    $expiredAttempt->shouldReceive('isTimeExpired')->andReturn(true);
    $expiredAttempt->shouldReceive('loadMissing')->with(['responses', 'quiz'])->andReturnSelf();
    $expiredAttempt->shouldReceive('hasAnsweredResponses')->andReturn(false);
    $expiredAttempt->shouldReceive('abandon')->once();

    $attemptsRelation = Mockery::mock();
    $attemptsRelation->shouldReceive('realAttempts')->andReturnSelf();
    $attemptsRelation->shouldReceive('where')->with('student_id', 5)->andReturnSelf();
    $attemptsRelation->shouldReceive('where')->with('status', 'in_progress')->andReturnSelf();
    $attemptsRelation->shouldReceive('first')->andReturn($expiredAttempt);

    $quiz->shouldReceive('attempts')->andReturn($attemptsRelation);

    $service = Mockery::mock(
        QuizAttemptLifecycleService::class,
        [app(\App\Services\Quiz\QuizAttemptStartService::class)]
    )->makePartial();
    $service->shouldReceive('reconcileForStudent')->with($quiz, 5)->andReturn(0);

    $result = $service->prepareResumableAttempt($quiz, 5);

    expect($result['resolution'])->toBe('abandoned')
        ->and($result['attempt'])->toBeNull();
});

afterEach(function () {
    Mockery::close();
});
