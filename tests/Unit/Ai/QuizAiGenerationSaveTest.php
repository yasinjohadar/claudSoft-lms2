<?php

namespace Tests\Unit\Ai;

use App\Models\AIQuestionGeneration;
use App\Models\Course;
use App\Models\ProgrammingLanguage;
use App\Models\QuestionType;
use App\Models\Quiz;
use App\Models\User;
use App\Services\Ai\AIQuestionGenerationService;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuizAiGenerationSaveTest extends TestCase
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

        if (! Schema::hasTable('ai_question_generations') || ! Schema::hasColumn('ai_question_generations', 'quiz_id')) {
            $this->markTestSkipped('ai_question_generations.quiz_id is not migrated in the test database.');
        }

        if (QuestionType::count() === 0) {
            $this->seed(QuestionTypeSeeder::class);
        }
    }

    public function test_save_generated_questions_persists_to_bank_and_attaches_to_quiz(): void
    {
        $user = User::factory()->create();
        $course = Course::query()->first() ?? Course::create([
            'title' => 'كورس حفظ AI',
            'slug' => 'quiz-ai-save-'.uniqid(),
            'is_published' => true,
            'created_by' => $user->id,
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'title' => 'اختبار حفظ AI',
            'quiz_type' => 'graded',
            'max_score' => 0,
            'created_by' => $user->id,
        ]);

        $trueFalseType = QuestionType::where('name', 'true_false')->firstOrFail();
        $lang = ProgrammingLanguage::firstOrCreate(
            ['slug' => 'quiz-ai-save-integration'],
            [
                'name' => 'TEST',
                'display_name' => 'TEST',
                'category' => 'frontend',
                'is_active' => true,
                'sort_order' => 902,
            ]
        );

        $generation = AIQuestionGeneration::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'quiz_id' => $quiz->id,
            'programming_language_id' => $lang->id,
            'lesson_name' => 'وحدة اختبار',
            'source_type' => 'manual_text',
            'source_content' => 'HTML basics',
            'question_type' => 'mixed',
            'question_type_ids' => [$trueFalseType->id],
            'number_of_questions' => 1,
            'difficulty_level' => 'easy',
            'default_grade' => 2,
            'status' => 'completed',
            'generated_questions' => [[
                'type' => 'true_false',
                'question' => 'وسم img لا يحتاج وسم إغلاق.',
                'correct_answer' => true,
                'difficulty' => 'easy',
                'points' => 2,
            ]],
            'saved_indices' => [],
            'saved_question_ids' => [],
        ]);

        $saved = app(AIQuestionGenerationService::class)->saveGeneratedQuestions($generation);

        $this->assertCount(1, $saved);
        $bankId = $saved->first()->id;

        $this->assertDatabaseHas('question_bank', ['id' => $bankId]);
        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'question_id' => $bankId,
        ]);

        $quiz->refresh();
        $this->assertGreaterThan(0, (float) $quiz->max_score);
        $this->assertTrue($generation->fresh()->isIndexSaved(0));
    }
}
