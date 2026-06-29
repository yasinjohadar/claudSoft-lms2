<?php

namespace Tests\Feature\Admin;

use App\Models\ProgrammingLanguage;
use App\Models\QuestionBank;
use App\Models\QuestionType;
use App\Models\User;
use App\Services\Ai\AIQuestionCreationService;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GeneratedQuestionPersistenceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available for persistence tests: '.$e->getMessage());
        }

        if (! Schema::hasTable('question_types') || ! Schema::hasTable('question_bank')) {
            $this->markTestSkipped('Question bank tables are not migrated in the test database.');
        }

        if (QuestionType::count() === 0) {
            $this->seed(QuestionTypeSeeder::class);
        }
    }

    public function test_save_true_false_with_boolean_false_marks_khata_as_correct(): void
    {
        $user = User::factory()->create();
        $trueFalseType = QuestionType::where('name', 'true_false')->firstOrFail();
        $lang = $this->programmingLanguage('html-ai-persist');

        $saved = app(AIQuestionCreationService::class)->saveParsedQuestionsToBank(
            [[
                'type' => 'true_false',
                'question' => 'وسم img لا يحتاج إلى وسم إغلاق.',
                'correct_answer' => false,
                'explanation' => 'لأن img عنصر void في HTML ولا يتطلب وسم إغلاق.',
                'difficulty' => 'easy',
                'points' => 1,
            ]],
            $lang,
            collect([$trueFalseType]),
            ['user' => $user]
        );

        $this->assertCount(1, $saved);

        $question = QuestionBank::with('options')->findOrFail($saved->first()->id);
        $correct = $question->options->where('is_correct', true)->values();

        $this->assertCount(1, $correct);
        $this->assertSame('خطأ', $correct->first()->option_text);
        $this->assertStringContainsString('void', (string) $question->explanation);
    }

    public function test_save_numerical_stores_metadata_correct_answer(): void
    {
        $user = User::factory()->create();
        $numericalType = QuestionType::where('name', 'numerical')->firstOrFail();
        $lang = $this->programmingLanguage('php-ai-persist');

        $saved = app(AIQuestionCreationService::class)->saveParsedQuestionsToBank(
            [[
                'type' => 'numerical',
                'question' => 'كم يساوي 2+2؟',
                'expected_value' => 4,
                'tolerance' => 0,
                'explanation' => 'الجمع البسيط يعطي 4.',
                'difficulty' => 'easy',
                'points' => 1,
            ]],
            $lang,
            collect([$numericalType]),
            ['user' => $user]
        );

        $question = QuestionBank::findOrFail($saved->first()->id);
        $this->assertSame(4.0, $question->metadata['correct_answer']);
        $this->assertSame(0.0, $question->metadata['tolerance']);
    }

    public function test_save_matching_persists_pairs_as_options(): void
    {
        $user = User::factory()->create();
        $matchingType = QuestionType::where('name', 'matching')->firstOrFail();
        $lang = $this->programmingLanguage('css-ai-persist');

        $saved = app(AIQuestionCreationService::class)->saveParsedQuestionsToBank(
            [[
                'type' => 'matching',
                'question' => 'طابق',
                'pairs' => [
                    ['question' => 'HTML', 'answer' => 'هيكل'],
                    ['question' => 'CSS', 'answer' => 'تنسيق'],
                ],
                'explanation' => 'HTML للهيكل وCSS للتنسيق.',
                'difficulty' => 'easy',
                'points' => 2,
            ]],
            $lang,
            collect([$matchingType]),
            ['user' => $user]
        );

        $question = QuestionBank::with('options')->findOrFail($saved->first()->id);
        $this->assertCount(2, $question->options);
        $this->assertSame('هيكل', $question->options->first()->feedback);
    }

    private function programmingLanguage(string $slug): ProgrammingLanguage
    {
        return ProgrammingLanguage::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => strtoupper($slug),
                'display_name' => strtoupper($slug),
                'category' => 'frontend',
                'is_active' => true,
                'sort_order' => 900,
            ]
        );
    }
}
