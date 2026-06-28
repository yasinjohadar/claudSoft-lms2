<?php

namespace Tests\Feature\Simulator;

use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\LessonSimulator;
use App\Models\User;
use App\Services\Simulator\SimulatorAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SimulatorAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createMinimalSchema();
    }

    private function createMinimalSchema(): void
    {
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
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
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

    private function studentUser(): User
    {
        return User::create([
            'name' => 'Student',
            'email' => 'student-'.uniqid().'@test.local',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * @return array{courseId: int, sectionId: int, simulator: LessonSimulator}
     */
    private function publishedSimulatorLinkedToCourse(?User $enrolledStudent = null): array
    {
        $categoryId = DB::table('course_categories')->insertGetId([
            'name' => 'Cat '.uniqid(),
            'slug' => 'cat-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseId = DB::table('courses')->insertGetId([
            'course_category_id' => $categoryId,
            'title' => 'Course '.uniqid(),
            'slug' => 'course-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId = DB::table('course_sections')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Section',
            'sort_order' => 1,
            'is_visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $simulator = LessonSimulator::create([
            'title' => 'PHP Arrays Sim',
            'slug' => 'php-arrays-'.uniqid(),
            'topic_key' => 'php.arrays',
            'spec_json' => [
                'meta' => [
                    'topic_key' => 'php.arrays',
                    'title' => 'PHP Arrays',
                    'languages' => ['php'],
                    'level' => 'beginner',
                ],
                'sections' => [
                    ['type' => 'hero', 'title' => 'Test', 'summary' => 'Summary'],
                ],
            ],
            'status' => 'published',
            'languages' => ['php'],
        ]);

        DB::table('course_lesson_simulator')->insert([
            'course_id' => $courseId,
            'lesson_simulator_id' => $simulator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($enrolledStudent) {
            CourseEnrollment::create([
                'course_id' => $courseId,
                'student_id' => $enrolledStudent->id,
                'enrollment_status' => 'active',
                'enrollment_date' => now(),
            ]);
        }

        return ['courseId' => $courseId, 'sectionId' => $sectionId, 'simulator' => $simulator];
    }

    public function test_unenrolled_student_denied_by_access_service(): void
    {
        $student = $this->studentUser();
        $data = $this->publishedSimulatorLinkedToCourse();
        $service = new SimulatorAccessService;

        $result = $service->canAccess($student, $data['simulator']);

        $this->assertFalse($result['allowed']);
    }

    public function test_enrolled_student_allowed_by_access_service(): void
    {
        $student = $this->studentUser();
        $data = $this->publishedSimulatorLinkedToCourse($student);
        $service = new SimulatorAccessService;

        $result = $service->canAccess($student, $data['simulator']);

        $this->assertTrue($result['allowed']);
    }

    public function test_hidden_module_blocks_access(): void
    {
        $student = $this->studentUser();
        $data = $this->publishedSimulatorLinkedToCourse($student);

        $module = CourseModule::create([
            'course_id' => $data['courseId'],
            'section_id' => $data['sectionId'],
            'module_type' => 'simulator',
            'modulable_type' => LessonSimulator::class,
            'modulable_id' => $data['simulator']->id,
            'title' => 'Sim Module',
            'sort_order' => 1,
            'is_visible' => false,
        ]);

        $service = new SimulatorAccessService;
        $result = $service->canAccess($student, $data['simulator'], $module);

        $this->assertFalse($result['allowed']);
    }

    public function test_draft_simulator_denied(): void
    {
        $student = $this->studentUser();
        $data = $this->publishedSimulatorLinkedToCourse($student);
        $data['simulator']->update(['status' => 'draft']);

        $service = new SimulatorAccessService;
        $result = $service->canAccess($student, $data['simulator']);

        $this->assertFalse($result['allowed']);
    }
}
