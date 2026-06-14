<?php

namespace Tests\Unit\QuestionBank\TypeImport;

use App\Services\QuestionBank\TypeImport\ImportDefaultsResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Admin\QuestionBankTypeImportMysqlTestCase;

class ImportDefaultsResolverTest extends QuestionBankTypeImportMysqlTestCase
{
    use DatabaseTransactions;

    public function test_applies_default_course_when_row_course_empty(): void
    {
        $courseId = $this->insertPublishedCourse('كورس افتراضي للاختبار');

        $resolver = new ImportDefaultsResolver;
        $result = $resolver->apply(['course' => '', 'language' => ''], $courseId, null);

        $this->assertSame('كورس افتراضي للاختبار', $result['row']['course']);
        $this->assertTrue($result['course_from_default']);
        $this->assertFalse($result['language_from_default']);
    }

    public function test_file_course_is_not_overridden_by_default(): void
    {
        $defaultCourseId = $this->insertPublishedCourse('كورس الواجهة');
        $this->insertPublishedCourse('كورس الملف');

        $resolver = new ImportDefaultsResolver;
        $result = $resolver->apply(['course' => 'كورس الملف'], $defaultCourseId, null);

        $this->assertSame('كورس الملف', $result['row']['course']);
        $this->assertFalse($result['course_from_default']);
    }

    public function test_applies_default_language_when_row_language_empty(): void
    {
        $langId = DB::table('programming_languages')->insertGetId([
            'name' => 'PHP',
            'slug' => 'php-'.uniqid(),
            'display_name' => 'بي إتش بي',
            'category' => 'backend',
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resolver = new ImportDefaultsResolver;
        $result = $resolver->apply(['course' => '', 'language' => ''], null, $langId);

        $this->assertSame('بي إتش بي', $result['row']['language']);
        $this->assertTrue($result['language_from_default']);
    }

    private function insertPublishedCourse(string $title): int
    {
        $courseCategoryId = DB::table('course_categories')->insertGetId([
            'name' => 'فئة '.uniqid(),
            'slug' => 'cat-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('courses')->insertGetId([
            'course_category_id' => $courseCategoryId,
            'title' => $title,
            'slug' => 'slug-'.uniqid(),
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
