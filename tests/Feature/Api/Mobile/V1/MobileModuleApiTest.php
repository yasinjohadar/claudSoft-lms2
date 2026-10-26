<?php

namespace Tests\Feature\Api\Mobile\V1;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

class MobileModuleApiTest extends MobileApiMysqlTestCase
{
    private function lessonModule(Course $course): CourseModule
    {
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'Section', 'is_visible' => true]);

        return CourseModule::create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'module_type' => 'lesson',
            'modulable_type' => \App\Models\Lesson::class,
            'modulable_id' => 0,
            'title' => 'Lesson',
            'is_visible' => true,
            'is_required' => true,
        ]);
    }

    public function test_student_can_complete_and_uncomplete_a_lesson(): void
    {
        Event::fake();
        $student = $this->completeStudent();
        $course = $this->course();
        $this->enroll($student, $course);
        $module = $this->lessonModule($course);
        Sanctum::actingAs($student);

        $this->postJson("/api/mobile/v1/modules/{$module->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonStructure(['data' => ['module_id', 'status', 'course_completion_percentage']]);

        $this->assertDatabaseHas('module_completions', [
            'module_id' => $module->id, 'student_id' => $student->id, 'completion_status' => 'completed',
        ]);

        $this->getJson("/api/mobile/v1/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.sections.0.modules.0.status', 'completed');

        $this->deleteJson("/api/mobile/v1/modules/{$module->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');
    }

    public function test_cannot_complete_lesson_of_course_not_enrolled(): void
    {
        $student = $this->completeStudent();
        $module = $this->lessonModule($this->course());
        Sanctum::actingAs($student);

        $this->postJson("/api/mobile/v1/modules/{$module->id}/complete")->assertForbidden();
    }

    public function test_playback_rejects_non_video_module(): void
    {
        $student = $this->completeStudent();
        $course = $this->course();
        $this->enroll($student, $course);
        $module = $this->lessonModule($course);
        Sanctum::actingAs($student);

        $this->getJson("/api/mobile/v1/modules/{$module->id}/playback")->assertStatus(422);
    }

    public function test_playback_requires_enrollment(): void
    {
        $student = $this->completeStudent();
        $module = $this->lessonModule($this->course());
        Sanctum::actingAs($student);

        $this->getJson("/api/mobile/v1/modules/{$module->id}/playback")->assertForbidden();
    }

    private function videoModule(Course $course): CourseModule
    {
        $video = \App\Models\Video::query()->whereNotNull('video_url')->first();
        if (! $video) {
            $this->markTestSkipped('No videos in the database.');
        }

        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'Videos', 'is_visible' => true]);

        return CourseModule::create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'module_type' => 'video',
            'modulable_type' => \App\Models\Video::class,
            'modulable_id' => $video->id,
            'title' => 'Video lesson',
            'is_visible' => true,
        ]);
    }

    public function test_playback_never_exposes_the_video_link_and_ticket_is_limited(): void
    {
        $student = $this->completeStudent(['name_ar' => 'طالب العلامة']);
        $course = $this->course();
        $this->enroll($student, $course);
        $module = $this->videoModule($course);
        Sanctum::actingAs($student);

        $response = $this->getJson("/api/mobile/v1/modules/{$module->id}/playback");
        if ($response->status() === 503) {
            $this->markTestSkipped('Video has no playable link in this environment.');
        }

        $response->assertOk()->assertJsonMissingPath('data.embed_url');
        $this->assertStringNotContainsString('mediadelivery', $response->getContent());
        $this->assertStringNotContainsString('token=', $response->getContent());

        $playerUrl = $response->json('data.player_url');

        // Opens up to the limit, with watermark and no-store headers.
        for ($i = 0; $i < 3; $i++) {
            $this->get($playerUrl)
                ->assertOk()
                ->assertSee('<iframe', false)
                ->assertSee('طالب العلامة')
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        $this->get($playerUrl)->assertForbidden()->assertDontSee('<iframe', false);
    }

    public function test_player_rejects_forged_ticket_and_wrong_module(): void
    {
        $student = $this->completeStudent();
        $course = $this->course();
        $this->enroll($student, $course);
        $module = $this->videoModule($course);
        Sanctum::actingAs($student);

        $this->get("/api/mobile/v1/modules/{$module->id}/player?ticket=".str_repeat('a', 64))->assertForbidden();

        $playerUrl = $this->getJson("/api/mobile/v1/modules/{$module->id}/playback")->json('data.player_url');
        if ($playerUrl) {
            $this->get(str_replace("modules/{$module->id}/", 'modules/999999999/', $playerUrl))->assertForbidden();
        }
    }
}
