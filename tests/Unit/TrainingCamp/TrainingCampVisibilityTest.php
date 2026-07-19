<?php

namespace Tests\Unit\TrainingCamp;

use App\Models\CourseGroup;
use App\Models\TrainingCamp;
use App\Models\TrainingCampTarget;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TrainingCampVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('training_camp_targets');
        Schema::dropIfExists('course_group_members');
        Schema::dropIfExists('course_group_courses');
        Schema::dropIfExists('training_camps');
        Schema::dropIfExists('course_groups');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('course_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('training_camps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('training_camp_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('training_camp_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('group_id');
            $table->timestamps();
        });

        Schema::create('course_group_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('course_id');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('course_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('student_id');
            $table->string('role')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('training_camp_targets');
        Schema::dropIfExists('course_group_members');
        Schema::dropIfExists('course_group_courses');
        Schema::dropIfExists('training_camps');
        Schema::dropIfExists('course_groups');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    private function makeCamp(): TrainingCamp
    {
        $suffix = uniqid();

        return TrainingCamp::create([
            'name' => 'Camp '.$suffix,
            'slug' => 'camp-'.$suffix,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'price' => 100,
            'is_active' => true,
        ]);
    }

    private function makeCourse(): int
    {
        $suffix = uniqid();

        return (int) DB::table('courses')->insertGetId([
            'title' => 'Course '.$suffix,
            'slug' => 'course-'.$suffix,
            'is_published' => true,
            'is_visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeGroup(string $name = 'Group'): CourseGroup
    {
        return CourseGroup::create([
            'name' => $name.' '.uniqid(),
            'is_active' => true,
        ]);
    }

    private function addMember(CourseGroup $group, int $studentId): void
    {
        DB::table('course_group_members')->insert([
            'group_id' => $group->id,
            'student_id' => $studentId,
            'role' => 'member',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_camp_without_targets_is_hidden_from_everyone(): void
    {
        $camp = $this->makeCamp();
        $studentId = DB::table('users')->insertGetId([
            'name' => 'Student',
            'email' => 's'.uniqid().'@test.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse($camp->isVisibleToStudent($studentId));
        $this->assertFalse(
            TrainingCamp::query()->visibleToStudent($studentId)->whereKey($camp->id)->exists()
        );
    }

    public function test_camp_visible_only_to_targeted_group_members(): void
    {
        $camp = $this->makeCamp();
        $courseId = $this->makeCourse();
        $groupA = $this->makeGroup('A');
        $groupB = $this->makeGroup('B');

        TrainingCampTarget::create([
            'training_camp_id' => $camp->id,
            'course_id' => $courseId,
            'group_id' => $groupA->id,
        ]);

        $memberId = DB::table('users')->insertGetId([
            'name' => 'Member',
            'email' => 'm'.uniqid().'@test.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $outsiderId = DB::table('users')->insertGetId([
            'name' => 'Outsider',
            'email' => 'o'.uniqid().'@test.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addMember($groupA, $memberId);
        $this->addMember($groupB, $outsiderId);

        $camp->load('targets');

        $this->assertTrue($camp->isVisibleToStudent($memberId));
        $this->assertFalse($camp->isVisibleToStudent($outsiderId));

        $this->assertTrue(
            TrainingCamp::query()->visibleToStudent($memberId)->whereKey($camp->id)->exists()
        );
        $this->assertFalse(
            TrainingCamp::query()->visibleToStudent($outsiderId)->whereKey($camp->id)->exists()
        );
    }

    public function test_audience_rows_for_form_groups_by_course(): void
    {
        $camp = $this->makeCamp();
        $courseId = $this->makeCourse();
        $g1 = $this->makeGroup('G1');
        $g2 = $this->makeGroup('G2');

        TrainingCampTarget::create([
            'training_camp_id' => $camp->id,
            'course_id' => $courseId,
            'group_id' => $g1->id,
        ]);
        TrainingCampTarget::create([
            'training_camp_id' => $camp->id,
            'course_id' => $courseId,
            'group_id' => $g2->id,
        ]);

        $rows = $camp->fresh()->audienceRowsForForm();

        $this->assertCount(1, $rows);
        $this->assertSame($courseId, $rows[0]['course_id']);
        $this->assertEqualsCanonicalizing([$g1->id, $g2->id], $rows[0]['group_ids']);
    }
}
