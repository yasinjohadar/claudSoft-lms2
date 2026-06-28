<?php

namespace Tests\Feature\Simulator;

use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\LessonSimulator;
use App\Services\Simulator\SimulatorCurriculumService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SimulatorCurriculumTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createMinimalSchema();
    }

    private function createMinimalSchema(): void
    {
        Schema::dropAllTables();

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
            $table->softDeletes();
        });

        Schema::create('course_modules', function ($table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('section_id');
            $table->string('module_type');
            $table->unsignedBigInteger('modulable_id')->nullable();
            $table->string('modulable_type')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_graded')->default(false);
            $table->string('completion_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
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

    public function test_attach_published_simulator_to_section_creates_module_and_pivot(): void
    {
        $categoryId = DB::table('course_categories')->insertGetId([
            'name' => 'Cat',
            'slug' => 'cat-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseId = DB::table('courses')->insertGetId([
            'course_category_id' => $categoryId,
            'title' => 'Course',
            'slug' => 'course-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId = DB::table('course_sections')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Section 1',
            'sort_order' => 1,
            'is_visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $simulator = LessonSimulator::create([
            'title' => 'HTML Basics Sim',
            'slug' => 'html-basics-'.uniqid(),
            'topic_key' => 'html.basics',
            'description' => 'Learn HTML',
            'spec_json' => ['meta' => ['title' => 'HTML'], 'sections' => []],
            'status' => 'published',
            'languages' => ['html'],
        ]);

        $section = CourseSection::findOrFail($sectionId);
        $service = new SimulatorCurriculumService;

        $modules = $service->attachToSection($section, [$simulator->id]);

        $this->assertCount(1, $modules);

        $module = CourseModule::query()->first();
        $this->assertSame('simulator', $module->module_type);
        $this->assertSame(LessonSimulator::class, $module->modulable_type);
        $this->assertSame($simulator->id, $module->modulable_id);
        $this->assertSame('HTML Basics Sim', $module->title);
        $this->assertTrue($module->is_visible);

        $this->assertDatabaseHas('course_lesson_simulator', [
            'course_id' => $courseId,
            'lesson_simulator_id' => $simulator->id,
        ]);
    }

    public function test_attach_rejects_draft_simulator(): void
    {
        $categoryId = DB::table('course_categories')->insertGetId([
            'name' => 'Cat',
            'slug' => 'cat-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseId = DB::table('courses')->insertGetId([
            'course_category_id' => $categoryId,
            'title' => 'Course',
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
            'title' => 'Draft Sim',
            'slug' => 'draft-'.uniqid(),
            'topic_key' => 'php.arrays',
            'spec_json' => ['meta' => [], 'sections' => []],
            'status' => 'draft',
            'languages' => ['php'],
        ]);

        $section = CourseSection::findOrFail($sectionId);
        $service = new SimulatorCurriculumService;

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->attachToSection($section, [$simulator->id]);
    }
}
