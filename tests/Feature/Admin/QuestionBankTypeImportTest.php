<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\User;
use App\Services\QuestionBank\TypeImport\Excel\TypeExcelTemplateGenerator;
use Database\Seeders\QuestionTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class QuestionBankTypeImportTest extends QuestionBankTypeImportMysqlTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (QuestionType::count() === 0) {
            $this->seed(QuestionTypeSeeder::class);
        }

        if (! Schema::hasColumn('question_bank', 'lesson_name')) {
            $this->markTestSkipped('Run migrations: question_bank.lesson_name column is required.');
        }
    }

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * @return array{course: Course, courseTitle: string}
     */
    private function createPublishedCourse(): array
    {
        $courseCategoryId = DB::table('course_categories')->insertGetId([
            'name' => 'فئة اختبار '.uniqid(),
            'slug' => 'test-cat-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $title = 'كورس اختبار الاستيراد '.uniqid();
        $courseId = DB::table('courses')->insertGetId([
            'course_category_id' => $courseCategoryId,
            'title' => $title,
            'slug' => 'import-test-'.uniqid(),
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'course' => Course::findOrFail($courseId),
            'courseTitle' => $title,
        ];
    }

    public function test_select_type_page_loads_for_excel(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('question-bank.import.type.select', 'excel'));

        $response->assertOk();
        $response->assertSee('اختر نوع السؤال');
        $response->assertSee('أنواع الأسئلة المتاحة');
    }

    public function test_json_preview_rejects_mismatched_question_type(): void
    {
        $admin = $this->adminUser();
        ['courseTitle' => $courseTitle] = $this->createPublishedCourse();

        $payload = [
            'version' => '1.0',
            'question_type' => 'matching',
            'questions' => [
                [
                    'question_text' => 'سؤال اختبار',
                    'course' => $courseTitle,
                    'default_grade' => 1,
                    'difficulty' => 'easy',
                    'options' => [
                        ['text' => 'أ', 'is_correct' => true],
                        ['text' => 'ب', 'is_correct' => false],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent(
            'questions.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        $response = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.preview', ['format' => 'json', 'type' => 'multiple_choice_single']),
            ['import_file' => $file]
        );

        $response->assertStatus(500);
        $response->assertJsonPath('success', false);
    }

    public function test_json_preview_and_process_multiple_choice_single(): void
    {
        $admin = $this->adminUser();
        ['courseTitle' => $courseTitle] = $this->createPublishedCourse();

        $payload = [
            'version' => '1.0',
            'question_type' => 'multiple_choice_single',
            'questions' => [
                [
                    'question_text' => 'ما عاصمة السعودية؟',
                    'course' => $courseTitle,
                    'default_grade' => 2,
                    'difficulty' => 'easy',
                    'options' => [
                        ['text' => 'الرياض', 'is_correct' => true],
                        ['text' => 'جدة', 'is_correct' => false],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent(
            'questions.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        $preview = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.preview', ['format' => 'json', 'type' => 'multiple_choice_single']),
            ['import_file' => $file]
        );

        $preview->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('valid_rows', 1);

        $questionsData = $preview->json('data');

        $process = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.process', ['format' => 'json', 'type' => 'multiple_choice_single']),
            ['questions_data' => json_encode($questionsData)]
        );

        $process->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('imported', 1);

        $this->assertDatabaseHas('question_bank', [
            'question_text' => 'ما عاصمة السعودية؟',
            'default_grade' => 2,
        ]);

        $question = QuestionBank::where('question_text', 'ما عاصمة السعودية؟')->first();
        $this->assertNotNull($question);
        $this->assertEquals(2, QuestionOption::where('question_id', $question->id)->count());
    }

    public function test_json_preview_fails_when_course_missing(): void
    {
        $admin = $this->adminUser();

        $payload = [
            'questions' => [
                [
                    'question_text' => 'سؤال بدون كورس',
                    'default_grade' => 1,
                    'difficulty' => 'easy',
                    'matching_pairs' => [
                        ['question' => 'أ', 'answer' => '1'],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent(
            'matching.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        $preview = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.preview', ['format' => 'json', 'type' => 'matching']),
            ['import_file' => $file]
        );

        $preview->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('valid_rows', 0);
        $this->assertCount(1, $preview->json('errors'));
    }

    public function test_json_preview_uses_default_course_when_missing_in_file(): void
    {
        $admin = $this->adminUser();
        ['course' => $course, 'courseTitle' => $courseTitle] = $this->createPublishedCourse();

        $payload = [
            'version' => '1.0',
            'question_type' => 'true_false',
            'questions' => [
                [
                    'question_text' => 'سؤال بدون كورس في الملف',
                    'default_grade' => 1,
                    'difficulty' => 'easy',
                    'correct_answer' => true,
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent(
            'true-false.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        $preview = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.preview', ['format' => 'json', 'type' => 'true_false']),
            [
                'import_file' => $file,
                'default_course_id' => $course->id,
            ]
        );

        $preview->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('valid_rows', 1);

        $row = $preview->json('data.0');
        $this->assertSame($courseTitle, $row['course']);
        $this->assertTrue($row['course_from_default']);
    }

    public function test_json_preview_file_course_overrides_default_course(): void
    {
        $admin = $this->adminUser();
        ['courseTitle' => $fileCourseTitle] = $this->createPublishedCourse();
        ['course' => $uiCourse] = $this->createPublishedCourse();

        $payload = [
            'version' => '1.0',
            'question_type' => 'true_false',
            'questions' => [
                [
                    'question_text' => 'سؤال بكورس في الملف',
                    'course' => $fileCourseTitle,
                    'default_grade' => 1,
                    'difficulty' => 'easy',
                    'correct_answer' => false,
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent(
            'true-false.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        $preview = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.preview', ['format' => 'json', 'type' => 'true_false']),
            [
                'import_file' => $file,
                'default_course_id' => $uiCourse->id,
            ]
        );

        $preview->assertOk()->assertJsonPath('valid_rows', 1);
        $row = $preview->json('data.0');
        $this->assertSame($fileCourseTitle, $row['course']);
        $this->assertFalse($row['course_from_default']);
    }

    public function test_json_process_uses_default_programming_language(): void
    {
        $admin = $this->adminUser();
        ['courseTitle' => $courseTitle] = $this->createPublishedCourse();

        $langId = DB::table('programming_languages')->insertGetId([
            'name' => 'Python',
            'slug' => 'python-'.uniqid(),
            'display_name' => 'بايثون',
            'category' => 'backend',
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'version' => '1.0',
            'question_type' => 'true_false',
            'questions' => [
                [
                    'question_text' => 'سؤال للغة الافتراضية',
                    'course' => $courseTitle,
                    'default_grade' => 1,
                    'difficulty' => 'easy',
                    'correct_answer' => true,
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent(
            'true-false.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        $preview = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.preview', ['format' => 'json', 'type' => 'true_false']),
            ['import_file' => $file]
        );

        $questionsData = $preview->json('data');

        $process = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.process', ['format' => 'json', 'type' => 'true_false']),
            [
                'questions_data' => json_encode($questionsData),
                'default_programming_language_id' => $langId,
            ]
        );

        $process->assertOk()->assertJsonPath('imported', 1);

        $question = QuestionBank::where('question_text', 'سؤال للغة الافتراضية')->first();
        $this->assertNotNull($question);
        $this->assertTrue($question->programmingLanguages()->where('programming_languages.id', $langId)->exists());
    }

    public function test_excel_preview_and_process_matching(): void
    {
        $admin = $this->adminUser();
        ['courseTitle' => $courseTitle] = $this->createPublishedCourse();

        $questionType = QuestionType::where('name', 'matching')->firstOrFail();
        $generator = new TypeExcelTemplateGenerator;
        $tempFile = $generator->generate($questionType);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempFile);
        $sheet = $spreadsheet->getSheetByName('أسئلة') ?? $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A2', 'طابق المصطلحات');
        $sheet->setCellValue('C2', '2');
        $sheet->setCellValue('D2', 'easy');
        $sheet->setCellValue('E2', $courseTitle);
        $sheet->setCellValue('I2', 'متغير||variable;;;دالة||function');

        $uploadPath = sys_get_temp_dir().'/qb_matching_test.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($uploadPath);

        $file = new UploadedFile($uploadPath, 'matching.xlsx', null, null, true);

        $preview = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.preview', ['format' => 'excel', 'type' => 'matching']),
            ['import_file' => $file]
        );

        $preview->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('valid_rows', 1);

        $process = $this->actingAs($admin)->postJson(
            route('question-bank.import.type.process', ['format' => 'excel', 'type' => 'matching']),
            ['questions_data' => json_encode($preview->json('data'))]
        );

        $process->assertOk()->assertJsonPath('imported', 1);

        $question = QuestionBank::where('question_text', 'طابق المصطلحات')->first();
        $this->assertNotNull($question);
        $this->assertGreaterThanOrEqual(2, QuestionOption::where('question_id', $question->id)->count());

        @unlink($uploadPath);
        @unlink($tempFile);
    }

    public function test_download_json_template(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(
            route('question-bank.import.type.template', ['format' => 'json', 'type' => 'true_false'])
        );

        $response->assertOk();
        $this->assertStringContainsString('application/json', (string) $response->headers->get('content-type'));
    }
}
