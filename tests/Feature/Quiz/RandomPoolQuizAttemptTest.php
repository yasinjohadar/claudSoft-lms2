<?php

use App\Models\QuestionBank;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Services\Quiz\QuizAttemptStartService;
use App\Services\Quiz\QuizRandomSelectionService;
use Tests\TestCase;

uses(TestCase::class);

function featureMakeQuestion(int $questionId): QuestionBank
{
    $question = new QuestionBank();
    $question->forceFill([
        'id' => $questionId,
        'default_grade' => 1,
        'question_type_id' => 1,
    ]);

    return $question;
}

test('start service selects different questions across two simulated attempts', function () {
    $quizQuestions = collect();
    for ($i = 1; $i <= 8; $i++) {
        $qq = new QuizQuestion(['question_id' => $i, 'question_order' => $i]);
        $qq->setRelation('question', featureMakeQuestion($i));
        $quizQuestions->push($qq);
    }

    $quiz = Mockery::mock(Quiz::class)->makePartial();
    $quiz->shouldReceive('loadMissing')->andReturnSelf();
    $quiz->shouldReceive('isRandomPool')->andReturn(true);
    $quiz->quiz_type = Quiz::TYPE_RANDOM_POOL;
    $quiz->questions_per_attempt = 3;
    $quiz->shuffle_questions = false;
    $quiz->setRelation('quizQuestions', $quizQuestions);

    $attemptsRelation = Mockery::mock();
    $attemptsRelation->shouldReceive('where')->with('student_id', 10)->andReturnSelf();
    $attemptsRelation->shouldReceive('whereIn')->with('status', ['submitted', 'graded', 'reviewing'])->andReturnSelf();
    $attemptsRelation->shouldReceive('realAttempts')->andReturnSelf();
    $attemptsRelation->shouldReceive('get')->with(['questions_order'])->andReturn(collect());

    $quiz->shouldReceive('attempts')->andReturn($attemptsRelation);

    $selection = new QuizRandomSelectionService();
    $service = new QuizAttemptStartService($selection);

    $first = $service->prepareStart($quiz, 10);
    expect($first['question_ids'])->toHaveCount(3);

    $attemptsRelation->shouldReceive('get')->with(['questions_order'])->andReturn(collect([
        new QuizAttempt(['questions_order' => $first['question_ids'], 'status' => 'submitted']),
    ]));

    $second = $service->prepareStart($quiz, 10);
    expect($second['question_ids'])->toHaveCount(3);

    $overlap = array_intersect($first['question_ids'], $second['question_ids']);
    expect(count($overlap))->toBeLessThan(3);
});

afterEach(function () {
    Mockery::close();
});
