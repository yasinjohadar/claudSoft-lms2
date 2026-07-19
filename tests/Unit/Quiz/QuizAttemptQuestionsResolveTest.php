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

test('resolveQuestionsForAttempt shuffles options stably when quiz shuffle_answers is enabled', function () {
    $options = collect([
        (new \App\Models\QuestionOption())->forceFill(['id' => 11, 'option_text' => 'A', 'option_order' => 1]),
        (new \App\Models\QuestionOption())->forceFill(['id' => 12, 'option_text' => 'B', 'option_order' => 2]),
        (new \App\Models\QuestionOption())->forceFill(['id' => 13, 'option_text' => 'C', 'option_order' => 3]),
        (new \App\Models\QuestionOption())->forceFill(['id' => 14, 'option_text' => 'D', 'option_order' => 4]),
    ]);

    $question = new QuestionBank();
    $question->forceFill([
        'id' => 701,
        'question_text' => 'MCQ',
        'default_grade' => 1,
        'question_type_id' => 1,
    ]);
    $question->setRelation('questionType', new QuestionType(['name' => 'multiple_choice_single', 'display_name' => 'اختيار']));
    $question->setRelation('options', $options);

    $quizQuestion = new QuizQuestion([
        'question_id' => 701,
        'question_order' => 1,
        'question_grade' => 1,
    ]);
    $quizQuestion->setRelation('question', $question);

    $quiz = Mockery::mock(Quiz::class)->makePartial();
    $quiz->id = 40;
    $quiz->shuffle_answers = true;
    $quiz->setRelation('quizQuestions', collect([$quizQuestion]));

    $quizQuestionsRelation = Mockery::mock();
    $quizQuestionsRelation->shouldReceive('where')
        ->with('question_id', 701)
        ->andReturnSelf();
    $quizQuestionsRelation->shouldReceive('with')
        ->andReturnSelf();
    $quizQuestionsRelation->shouldReceive('first')
        ->andReturn($quizQuestion);

    $quiz->shouldReceive('quizQuestions')->andReturn($quizQuestionsRelation);
    $quiz->shouldReceive('isRandomPool')->andReturn(false);

    $attempt = new QuizAttempt([
        'id' => 1201,
        'quiz_id' => 40,
        'questions_order' => [701],
    ]);
    $attempt->setRelation('quiz', $quiz);

    $service = new QuizAttemptStartService(new QuizRandomSelectionService());

    $first = $service->resolveQuestionsForAttempt($attempt)->first()->options->pluck('id')->all();
    $second = $service->resolveQuestionsForAttempt($attempt)->first()->options->pluck('id')->all();

    expect($first)->toBe($second);
    expect($first)->not->toBe([11, 12, 13, 14]);
});

test('resolveQuestionsForAttempt keeps option_order when shuffle_answers is disabled', function () {
    $options = collect([
        (new \App\Models\QuestionOption())->forceFill(['id' => 21, 'option_text' => 'A', 'option_order' => 1]),
        (new \App\Models\QuestionOption())->forceFill(['id' => 22, 'option_text' => 'B', 'option_order' => 2]),
        (new \App\Models\QuestionOption())->forceFill(['id' => 23, 'option_text' => 'C', 'option_order' => 3]),
    ]);

    $question = new QuestionBank();
    $question->forceFill([
        'id' => 702,
        'question_text' => 'MCQ',
        'default_grade' => 1,
        'question_type_id' => 1,
    ]);
    $question->setRelation('questionType', new QuestionType(['name' => 'multiple_choice_single', 'display_name' => 'اختيار']));
    $question->setRelation('options', $options);

    $quizQuestion = new QuizQuestion([
        'question_id' => 702,
        'question_order' => 1,
        'question_grade' => 1,
    ]);
    $quizQuestion->setRelation('question', $question);

    $quiz = Mockery::mock(Quiz::class)->makePartial();
    $quiz->id = 41;
    $quiz->shuffle_answers = false;
    $quiz->setRelation('quizQuestions', collect([$quizQuestion]));

    $quizQuestionsRelation = Mockery::mock();
    $quizQuestionsRelation->shouldReceive('where')
        ->with('question_id', 702)
        ->andReturnSelf();
    $quizQuestionsRelation->shouldReceive('with')
        ->andReturnSelf();
    $quizQuestionsRelation->shouldReceive('first')
        ->andReturn($quizQuestion);

    $quiz->shouldReceive('quizQuestions')->andReturn($quizQuestionsRelation);
    $quiz->shouldReceive('isRandomPool')->andReturn(false);

    $attempt = new QuizAttempt([
        'id' => 1202,
        'quiz_id' => 41,
        'questions_order' => [702],
    ]);
    $attempt->setRelation('quiz', $quiz);

    $service = new QuizAttemptStartService(new QuizRandomSelectionService());
    $ids = $service->resolveQuestionsForAttempt($attempt)->first()->options->pluck('id')->all();

    expect($ids)->toBe([21, 22, 23]);
});

afterEach(function () {
    Mockery::close();
});
