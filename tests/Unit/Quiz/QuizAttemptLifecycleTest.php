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

afterEach(function () {
    Mockery::close();
});
