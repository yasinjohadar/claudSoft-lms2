<?php

namespace Tests\Unit\Simulator;

use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\LessonSimulator;
use App\Models\User;
use App\Services\Simulator\SimulatorAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SimulatorAccessServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropAllTables();

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('course_categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('courses', function ($table) {
            $table->id();
            $table->unsignedBigInteger('course_category_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_sections', function ($table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('title');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('course_modules', function ($table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('section_id');
            $table->string('module_type');
            $table->unsignedBigInteger('modulable_id')->nullable();
            $table->string('modulable_type')->nullable();
            $table->string('title');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_enrollments', function ($table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('student_id');
            $table->string('enrollment_status')->default('active');
            $table->timestamp('enrollment_date')->nullable();
            $table->timestamps();
        });

        Schema::create('lesson_simulators', function ($table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('topic_key');
            $table->json('spec_json');
            $table->string('spec_version')->default('1.0');
            $table->string('render_mode')->default('html_bundle');
            $table->string('simulator_archetype')->nullable();
            $table->string('bundle_path')->nullable();
            $table->string('status')->default('draft');
            $table->json('languages')->nullable();
            $table->json('ai_generation_meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_lesson_simulator', function ($table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('lesson_simulator_id');
            $table->timestamps();
        });
    }

    public function test_access_via_pivot_and_module_course_ids(): void
    {
        $service = new SimulatorAccessService;
        $student = User::create([
            'name' => 'Student',
            'email' => 'student-'.uniqid().'@test.local',
            'password' => bcrypt('password'),
        ]);

        $categoryId = DB::table('course_categories')->insertGetId([
            'name' => 'Cat',
            'slug' => 'cat-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseA = DB::table('courses')->insertGetId([
            'course_category_id' => $categoryId,
            'title' => 'A',
            'slug' => 'a-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseB = DB::table('courses')->insertGetId([
            'course_category_id' => $categoryId,
            'title' => 'B',
            'slug' => 'b-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId = DB::table('course_sections')->insertGetId([
            'course_id' => $courseB,
            'title' => 'S',
            'sort_order' => 1,
            'is_visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $simulator = LessonSimulator::create([
            'title' => 'Sim',
            'slug' => 'sim-'.uniqid(),
            'topic_key' => 'php.arrays',
            'spec_json' => [
                'meta' => ['topic_key' => 'php.arrays', 'title' => 'T', 'languages' => ['php'], 'level' => 'beginner'],
                'sections' => [['type' => 'hero', 'title' => 'H', 'summary' => 'S']],
            ],
            'status' => 'published',
        ]);

        DB::table('course_lesson_simulator')->insert([
            'course_id' => $courseA,
            'lesson_simulator_id' => $simulator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        CourseModule::create([
            'course_id' => $courseB,
            'section_id' => $sectionId,
            'module_type' => 'simulator',
            'modulable_type' => LessonSimulator::class,
            'modulable_id' => $simulator->id,
            'title' => 'Mod',
            'sort_order' => 1,
            'is_visible' => true,
        ]);

        CourseEnrollment::create([
            'course_id' => $courseB,
            'student_id' => $student->id,
            'enrollment_status' => 'active',
            'enrollment_date' => now(),
        ]);

        $ids = $service->linkedCourseIds($simulator);
        $this->assertCount(2, $ids);

        $result = $service->canAccess($student, $simulator);
        $this->assertTrue($result['allowed']);
    }
}
