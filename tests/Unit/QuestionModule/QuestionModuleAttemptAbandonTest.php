<?php

use App\Models\QuestionModuleAttempt;
use App\Models\QuestionModuleResponse;
use Tests\TestCase;

uses(TestCase::class);

test('question module attempt hasAnsweredResponses detects saved answers', function () {
    $attempt = new QuestionModuleAttempt();

    $withAnswer = new QuestionModuleResponse(['student_answer' => ['option' => 1]]);
    $empty = new QuestionModuleResponse(['student_answer' => null]);

    $attempt->setRelation('responses', collect([$empty, $withAnswer]));

    expect($attempt->hasAnsweredResponses())->toBeTrue();
});

test('question module attempt abandon sets status without completed_at', function () {
    $attempt = Mockery::mock(QuestionModuleAttempt::class)->makePartial();
    $attempt->shouldReceive('update')->once()->with([
        'status' => 'abandoned',
        'completed_at' => null,
        'is_passed' => false,
        'total_score' => null,
        'percentage' => null,
    ]);

    $attempt->abandon();
});

afterEach(function () {
    Mockery::close();
});
