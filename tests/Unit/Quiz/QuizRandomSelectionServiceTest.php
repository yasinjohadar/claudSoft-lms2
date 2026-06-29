<?php

use App\Models\QuestionBank;
use App\Models\QuestionPool;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Services\Quiz\QuizRandomSelectionService;
use Illuminate\Support\Collection;

function makeRandomPoolQuiz(int $perAttempt, Collection $quizQuestions): Quiz
{
    $quiz = Mockery::mock(Quiz::class)->makePartial();
    $quiz->shouldReceive('loadMissing')->andReturnSelf();
    $quiz->shouldReceive('isRandomPool')->andReturn(true);
    $quiz->quiz_type = Quiz::TYPE_RANDOM_POOL;
    $quiz->questions_per_attempt = $perAttempt;
    $quiz->setRelation('quizQuestions', $quizQuestions);

    return $quiz;
}

function makeQuestion(int $questionId, float $grade = 1.0): QuestionBank
{
    $question = new QuestionBank();
    $question->forceFill([
        'id' => $questionId,
        'default_grade' => $grade,
        'question_type_id' => 1,
    ]);

    return $question;
}

function directQuizQuestion(int $questionId, float $grade = 1.0): QuizQuestion
{
    $qq = new QuizQuestion(['question_id' => $questionId, 'question_pool_id' => null]);
    $qq->setRelation('question', makeQuestion($questionId, $grade));

    return $qq;
}

function poolQuizQuestion(array $questionIds, float $grade = 1.0): QuizQuestion
{
    $questions = collect($questionIds)->map(fn (int $id) => makeQuestion($id, $grade));

    $pool = new QuestionPool(['id' => 99]);
    $pool->setRelation('questions', $questions);
    $pool->setRelation('poolItems', collect());

    $qq = new QuizQuestion(['question_id' => null, 'question_pool_id' => 99]);
    $qq->setRelation('questionPool', $pool);

    return $qq;
}

test('buildCandidatePool merges direct questions and pool questions with dedupe', function () {
    $service = new QuizRandomSelectionService();
    $quiz = makeRandomPoolQuiz(2, collect([
        directQuizQuestion(1, 2.0),
        directQuizQuestion(2, 1.0),
        poolQuizQuestion([2, 3, 4], 1.5),
    ]));

    $pool = $service->buildCandidatePool($quiz);

    expect($pool->pluck('question_id')->all())->toBe([1, 2, 3, 4]);
    expect($pool->firstWhere('question_id', 1)['grade'])->toBe(2.0);
});

test('selectForAttempt excludes previously used questions when enough fresh remain', function () {
    $service = new QuizRandomSelectionService();
    $quiz = makeRandomPoolQuiz(2, collect([
        directQuizQuestion(1),
        directQuizQuestion(2),
        directQuizQuestion(3),
        directQuizQuestion(4),
    ]));

    $attempts = collect([
        new QuizAttempt(['questions_order' => [1, 2], 'status' => 'submitted']),
    ]);

    $attemptsRelation = Mockery::mock();
    $attemptsRelation->shouldReceive('where')->with('student_id', 5)->andReturnSelf();
    $attemptsRelation->shouldReceive('whereIn')->with('status', ['submitted', 'graded', 'reviewing'])->andReturnSelf();
    $attemptsRelation->shouldReceive('realAttempts')->andReturnSelf();
    $attemptsRelation->shouldReceive('get')->with(['questions_order'])->andReturn($attempts);

    $quiz->shouldReceive('attempts')->andReturn($attemptsRelation);

    $result = $service->selectForAttempt($quiz, 5, shuffle: false);

    expect($result->questionIds)->toHaveCount(2);
    expect(array_intersect($result->questionIds, [1, 2]))->toBeEmpty();
    expect($result->recycled)->toBeFalse();
});

test('selectForAttempt recycles when fresh pool is smaller than needed', function () {
    $service = new QuizRandomSelectionService();
    $quiz = makeRandomPoolQuiz(2, collect([
        directQuizQuestion(1),
        directQuizQuestion(2),
        directQuizQuestion(3),
    ]));

    $attempts = collect([
        new QuizAttempt(['questions_order' => [1, 2], 'status' => 'submitted']),
    ]);

    $attemptsRelation = Mockery::mock();
    $attemptsRelation->shouldReceive('where')->with('student_id', 7)->andReturnSelf();
    $attemptsRelation->shouldReceive('whereIn')->with('status', ['submitted', 'graded', 'reviewing'])->andReturnSelf();
    $attemptsRelation->shouldReceive('realAttempts')->andReturnSelf();
    $attemptsRelation->shouldReceive('get')->with(['questions_order'])->andReturn($attempts);

    $quiz->shouldReceive('attempts')->andReturn($attemptsRelation);

    $result = $service->selectForAttempt($quiz, 7, shuffle: false);

    expect($result->questionIds)->toHaveCount(2);
    expect($result->recycled)->toBeTrue();
});

test('validateQuizConfiguration detects empty bank and N greater than pool', function () {
    $service = new QuizRandomSelectionService();

    $emptyQuiz = makeRandomPoolQuiz(5, collect());
    expect($service->validateQuizConfiguration($emptyQuiz))->toContain('يجب إضافة أسئلة');

    $smallQuiz = makeRandomPoolQuiz(5, collect([
        directQuizQuestion(1),
        directQuizQuestion(2),
    ]));
    expect($service->validateQuizConfiguration($smallQuiz))->toContain('أكبر من حجم البنك');
});

afterEach(function () {
    Mockery::close();
});
