<?php

namespace Tests\Feature\Api\Mobile\V1;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\DocumentationPage;
use Laravel\Sanctum\Sanctum;

class MobileModuleDocumentApiTest extends MobileApiMysqlTestCase
{
    private function documentModule(Course $course): CourseModule
    {
        $page = DocumentationPage::query()->whereNotNull('content')->where('content', '!=', '')->first();
        if (! $page) {
            $this->markTestSkipped('No documentation pages in the database.');
        }

        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'Docs', 'is_visible' => true]);

        return CourseModule::create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'module_type' => 'documentation',
            'modulable_type' => DocumentationPage::class,
            'modulable_id' => $page->id,
            'title' => 'Doc lesson',
            'is_visible' => true,
        ]);
    }

    public function test_returns_signed_link_that_renders_the_document_in_both_themes(): void
    {
        $student = $this->completeStudent();
        $course = $this->course();
        $this->enroll($student, $course);
        $module = $this->documentModule($course);
        Sanctum::actingAs($student);

        $url = $this->getJson("/api/mobile/v1/modules/{$module->id}/document")
            ->assertOk()
            ->assertJsonStructure(['data' => ['module_id', 'title', 'document_url', 'reading_minutes']])
            ->json('data.document_url');

        // The theme is appended by the app, outside the signature.
        $this->get($url.'&theme=light')
            ->assertOk()
            ->assertSee('data-theme', false)
            ->assertSee('docs-content', false)
            ->assertSee('Doc lesson');

        $this->get($url.'&theme=dark')->assertOk();
    }

    public function test_tampered_or_unsigned_link_is_rejected(): void
    {
        $student = $this->completeStudent();
        $course = $this->course();
        $this->enroll($student, $course);
        $module = $this->documentModule($course);
        Sanctum::actingAs($student);

        $url = $this->getJson("/api/mobile/v1/modules/{$module->id}/document")->json('data.document_url');
        $other = $this->completeStudent();

        $this->get(str_replace('user='.$student->id, 'user='.$other->id, $url))->assertForbidden();
        $this->get("/api/mobile/v1/modules/{$module->id}/document/view?user={$student->id}")->assertForbidden();
    }

    public function test_requires_enrollment(): void
    {
        $student = $this->completeStudent();
        $module = $this->documentModule($this->course());
        Sanctum::actingAs($student);

        $this->getJson("/api/mobile/v1/modules/{$module->id}/document")->assertForbidden();
    }
}
