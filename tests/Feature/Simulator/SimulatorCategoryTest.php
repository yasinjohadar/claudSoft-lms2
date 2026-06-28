<?php

namespace Tests\Feature\Simulator;

use App\Models\LessonSimulator;
use App\Models\SimulatorCategory;
use App\Models\User;
use App\Services\Simulator\SimulatorCategoryTree;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulatorCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_nested_categories_and_simulator_assignment(): void
    {
        $root = SimulatorCategory::create(['name' => 'برمجة', 'slug' => 'programming']);
        $child = SimulatorCategory::create(['name' => 'PHP', 'slug' => 'php', 'parent_id' => $root->id]);
        $grandchild = SimulatorCategory::create(['name' => 'Laravel', 'slug' => 'laravel', 'parent_id' => $child->id]);

        $simulator = LessonSimulator::create([
            'title' => 'Test Sim',
            'slug' => 'test-sim',
            'topic_key' => 'custom.test',
            'simulator_category_id' => $grandchild->id,
            'render_mode' => 'html_bundle',
            'spec_json' => ['meta' => [], 'sections' => []],
            'status' => 'published',
        ]);

        $this->assertSame('برمجة › PHP › Laravel', $simulator->fresh()->category->full_path);

        $ids = SimulatorCategoryTree::selfAndDescendantIds($root->id);
        $this->assertContains($grandchild->id, $ids);

        $options = SimulatorCategoryTree::optionsForSelect();
        $this->assertStringContainsString('—', $options[$child->id]);
        $this->assertStringContainsString('— —', $options[$grandchild->id]);
    }

    public function test_admin_can_manage_categories(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.lesson-simulators.categories.store'), [
                'name' => 'أمن سيبراني',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.lesson-simulators.categories.index'));

        $this->assertDatabaseHas('simulator_categories', [
            'name' => 'أمن سيبراني',
            'slug' => 'amn-sybrany',
        ]);
    }
}
