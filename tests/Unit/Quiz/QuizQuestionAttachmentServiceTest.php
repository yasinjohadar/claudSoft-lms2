<?php

namespace Tests\Unit\Quiz;

use App\Models\Course;
use App\Models\QuestionBank;
use App\Models\QuestionType;
use App\Models\Quiz;
use App\Models\User;
use App\Services\Quiz\QuizQuestionAttachmentService;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuizQuestionAttachmentServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available: '.$e->getMessage());
        }

        if (! Schema::hasTable('quizzes') || ! Schema::hasTable('quiz_questions') || ! Schema::hasTable('question_bank')) {
            $this->markTestSkipped('Quiz tables are not migrated in the test database.');
        }

        if (QuestionType::count() === 0) {
            $this->seed(QuestionTypeSeeder::class);
        }
    }

    public function test_attach_adds_new_questions_and_updates_max_score(): void
    {
        $user = User::factory()->create();
        $course = $this->resolveCourse();
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'اختبار ربط AI',
            'quiz_type' => 'graded',
            'max_score' => 0,
            'created_by' => $user->id,
        ]);

        $questionType = QuestionType::where('name', 'true_false')->firstOrFail();
        $q1 = $this->createQuestion($user, $course, $questionType, 2.0);
        $q2 = $this->createQuestion($user, $course, $questionType, 3.0);

        $service = app(QuizQuestionAttachmentService::class);
        $added = $service->attachQuestionBankItems($quiz, collect([$q1, $q2]));

        $this->assertSame(2, $added);
        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'question_id' => $q1->id,
            'question_grade' => 2.0,
        ]);
        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'question_id' => $q2->id,
        ]);

        $quiz->refresh();
        $this->assertSame(5.0, (float) $quiz->max_score);
    }

    public function test_attach_skips_questions_already_in_quiz(): void
    {
        $user = User::factory()->create();
        $course = $this->resolveCourse();
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'اختبار تخطي مكرر',
            'quiz_type' => 'graded',
            'max_score' => 1,
            'created_by' => $user->id,
        ]);

        $questionType = QuestionType::where('name', 'true_false')->firstOrFail();
        $existing = $this->createQuestion($user, $course, $questionType, 1.0);
        $newQuestion = $this->createQuestion($user, $course, $questionType, 4.0);

        $quiz->questions()->attach($existing->id, [
            'question_order' => 1,
            'question_grade' => 1.0,
            'is_required' => false,
        ]);

        $service = app(QuizQuestionAttachmentService::class);
        $added = $service->attachQuestionBankItems($quiz, collect([$existing, $newQuestion]));

        $this->assertSame(1, $added);
        $this->assertSame(2, DB::table('quiz_questions')->where('quiz_id', $quiz->id)->count());
    }

    private function resolveCourse(): Course
    {
        $existing = Course::query()->first();
        if ($existing) {
            return $existing;
        }

        return Course::create([
            'title' => 'كورس اختبار AI',
            'slug' => 'ai-quiz-attach-test-'.uniqid(),
            'is_published' => true,
            'created_by' => User::factory()->create()->id,
        ]);
    }

    private function createQuestion(User $user, Course $course, QuestionType $type, float $grade): QuestionBank
    {
        return QuestionBank::create([
            'course_id' => $course->id,
            'question_type_id' => $type->id,
            'question_text' => 'سؤال اختبار '.uniqid(),
            'default_grade' => $grade,
            'difficulty_level' => 'medium',
            'is_active' => true,
            'created_by' => $user->id,
        ]);
    }
}
