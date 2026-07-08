<?php

use App\Models\QuestionBank;
use App\Models\QuestionType;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Services\Quiz\QuizAttemptStartService;
use App\Services\Quiz\QuizRandomSelectionService;
use Tests\TestCase;

uses(TestCase::class);

test('resolveQuestionsForAttempt loads soft-deleted questions linked to quiz', function () {
    $question = new QuestionBank();
    $question->forceFill([
        'id' => 595,
        'question_text' => 'Trashed question',
        'default_grade' => 10,
        'question_type_id' => 1,
        'deleted_at' => now(),
    ]);
    $question->setRelation('questionType', new QuestionType(['name' => 'true_false', 'display_name' => 'صح/خطأ']));
    $question->setRelation('options', collect());

    $quizQuestion = new QuizQuestion([
        'question_id' => 595,
        'question_order' => 1,
        'question_grade' => 10,
    ]);
    $quizQuestion->setRelation('question', $question);

    $quiz = Mockery::mock(Quiz::class)->makePartial();
    $quiz->id = 27;
    $quiz->setRelation('quizQuestions', collect([$quizQuestion]));

    $quizQuestionsRelation = Mockery::mock();
    $quizQuestionsRelation->shouldReceive('where')
        ->with('question_id', 595)
        ->andReturnSelf();
    $quizQuestionsRelation->shouldReceive('with')
        ->andReturnSelf();
    $quizQuestionsRelation->shouldReceive('first')
        ->andReturn($quizQuestion);

    $quiz->shouldReceive('quizQuestions')->andReturn($quizQuestionsRelation);
    $quiz->shouldReceive('isRandomPool')->andReturn(false);

    $attempt = new QuizAttempt([
        'id' => 937,
        'quiz_id' => 27,
        'questions_order' => [595],
    ]);
    $attempt->setRelation('quiz', $quiz);

    $service = new QuizAttemptStartService(new QuizRandomSelectionService());
    $questions = $service->resolveQuestionsForAttempt($attempt);

    expect($questions)->toHaveCount(1);
    expect($questions->first()->id)->toBe(595);
});

afterEach(function () {
    Mockery::close();
});
