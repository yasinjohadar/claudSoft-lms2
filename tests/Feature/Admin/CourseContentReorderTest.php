<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseContentReorderTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available: '.$e->getMessage());
        }

        if (! Schema::hasTable('course_sections') || ! Schema::hasTable('course_modules')) {
            $this->markTestSkipped('Course tables are not migrated in the test database.');
        }
    }

    public function test_reorder_sections_updates_sort_order_and_order_index(): void
    {
        $admin = $this->adminUser();
        $course = $this->createCourse($admin);

        $sectionA = $this->createSection($course, 'A', 1, $admin);
        $sectionB = $this->createSection($course, 'B', 2, $admin);
        $sectionC = $this->createSection($course, 'C', 3, $admin);

        $response = $this->actingAs($admin)->postJson(
            route('courses.sections.reorder', $course),
            ['sections' => [$sectionC->id, $sectionA->id, $sectionB->id]]
        );

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertSame(2, (int) CourseSection::find($sectionA->id)->sort_order);
        $this->assertSame(2, (int) CourseSection::find($sectionA->id)->order_index);
        $this->assertSame(1, (int) CourseSection::find($sectionC->id)->sort_order);
        $this->assertSame(3, (int) CourseSection::find($sectionB->id)->sort_order);
    }

    public function test_reorder_modules_within_section_updates_sort_order(): void
    {
        $admin = $this->adminUser();
        $course = $this->createCourse($admin);
        $section = $this->createSection($course, 'Main', 1, $admin);

        $moduleA = $this->createModule($course, $section, 'Quiz A', 1);
        $moduleB = $this->createModule($course, $section, 'Quiz B', 2);

        $response = $this->actingAs($admin)->postJson(
            route('sections.modules.reorder', $section),
            ['module_ids' => [$moduleB->id, $moduleA->id]]
        );

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertSame(2, (int) CourseModule::find($moduleA->id)->sort_order);
        $this->assertSame(1, (int) CourseModule::find($moduleB->id)->sort_order);
    }

    public function test_reorder_modules_rejects_ids_from_another_section(): void
    {
        $admin = $this->adminUser();
        $course = $this->createCourse($admin);
        $sectionOne = $this->createSection($course, 'One', 1, $admin);
        $sectionTwo = $this->createSection($course, 'Two', 2, $admin);

        $foreignModule = $this->createModule($course, $sectionTwo, 'Foreign', 1);

        $response = $this->actingAs($admin)->postJson(
            route('sections.modules.reorder', $sectionOne),
            ['module_ids' => [$foreignModule->id]]
        );

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createCourse(User $admin): Course
    {
        $categoryId = DB::table('course_categories')->insertGetId([
            'name' => 'Reorder Cat',
            'slug' => 'reorder-cat-'.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Course::query()->create([
            'course_category_id' => $categoryId,
            'title' => 'Reorder Course',
            'slug' => 'reorder-course-'.uniqid(),
            'created_by' => $admin->id,
            'is_published' => true,
        ]);
    }

    private function createSection(Course $course, string $title, int $order, User $admin): CourseSection
    {
        return CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => $title,
            'sort_order' => $order,
            'order_index' => $order,
            'is_visible' => true,
            'created_by' => $admin->id,
        ]);
    }

    private function createModule(Course $course, CourseSection $section, string $title, int $order): CourseModule
    {
        return CourseModule::query()->create([
            'course_id' => $course->id,
            'section_id' => $section->id,
            'module_type' => 'quiz',
            'modulable_id' => null,
            'modulable_type' => CourseModule::class,
            'title' => $title,
            'sort_order' => $order,
            'is_visible' => true,
        ]);
    }
}
