<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Tests\TestCase;

uses(TestCase::class);

test('quiz attempt grade abandons attempt without answers', function () {
    $attempt = Mockery::mock(QuizAttempt::class)->makePartial();
    $attempt->shouldReceive('hasAnsweredResponses')->andReturn(false);
    $attempt->shouldReceive('abandon')->once();
    $attempt->shouldReceive('responses')->never();

    $attempt->grade();
});

afterEach(function () {
    Mockery::close();
});
