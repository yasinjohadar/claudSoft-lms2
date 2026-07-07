<?php

use App\Models\Quiz;
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

afterEach(function () {
    Mockery::close();
});
