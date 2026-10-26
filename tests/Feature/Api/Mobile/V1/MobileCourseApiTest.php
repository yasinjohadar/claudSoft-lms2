<?php

namespace Tests\Feature\Api\Mobile\V1;

use Laravel\Sanctum\Sanctum;

class MobileCourseApiTest extends MobileApiMysqlTestCase
{
    public function test_lists_only_active_and_completed_enrollments_of_the_student(): void
    {
        $student = $this->completeStudent();
        $other = $this->completeStudent();

        $active = $this->course();
        $completed = $this->course();
        $pending = $this->course();
        $foreign = $this->course();

        $this->enroll($student, $active);
        $this->enroll($student, $completed, 'completed');
        $this->enroll($student, $pending, 'pending');
        $this->enroll($other, $foreign);

        Sanctum::actingAs($student);

        $ids = collect($this->getJson('/api/mobile/v1/courses')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->json('data'))->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$active->id, $completed->id], $ids);

        $this->getJson('/api/mobile/v1/courses?status=completed')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $completed->id)
            ->assertJsonPath('data.0.enrollment.status', 'completed');
    }

    public function test_show_enrolled_course_details(): void
    {
        $student = $this->completeStudent();
        $course = $this->course();
        $this->enroll($student, $course);
        Sanctum::actingAs($student);

        $this->getJson("/api/mobile/v1/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $course->id)
            ->assertJsonStructure(['data' => ['title', 'enrollment', 'sections', 'modules_count']]);
    }

    public function test_incomplete_profile_is_blocked_from_courses_when_enforced(): void
    {
        if (! \App\Models\SiteSetting::isStudentProfileCompletionForced()) {
            $this->markTestSkipped('Profile completion enforcement is disabled.');
        }

        Sanctum::actingAs($this->student());

        $this->getJson('/api/mobile/v1/courses')
            ->assertForbidden()
            ->assertJsonPath('profile_completion_required', true);

        // البروفايل يبقى متاحاً لإكماله.
        $this->getJson('/api/mobile/v1/profile')->assertOk();
    }

    public function test_show_returns_404_for_course_not_enrolled(): void
    {
        $student = $this->completeStudent();
        $course = $this->course();
        Sanctum::actingAs($student);

        $this->getJson("/api/mobile/v1/courses/{$course->id}")->assertNotFound();
    }
}
