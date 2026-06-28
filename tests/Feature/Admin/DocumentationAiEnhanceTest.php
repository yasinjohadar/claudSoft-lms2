<?php

namespace Tests\Feature\Admin;

use App\Models\AIModel;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\User;
use App\Services\Ai\AIDocumentationPageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentationAiEnhanceTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Documentation AI feature tests require MySQL (SQLite migrations incompatible).');
        }
    }

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createDocumentationPage(): DocumentationPage
    {
        $category = DocumentationCategory::query()->create([
            'name' => 'Dart',
            'slug' => 'dart',
            'is_active' => true,
        ]);

        return DocumentationPage::query()->create([
            'documentation_category_id' => $category->id,
            'title' => 'Constructor in OOP',
            'slug' => 'constructor-in-oop',
            'content' => '<section class="content-section"><h2 class="section-title">Intro</h2><div class="text-block">Original content.</div></section>',
            'status' => 'published',
            'sort_order' => 0,
            'is_indexable' => true,
        ]);
    }

    public function test_admin_can_view_enhance_page(): void
    {
        $user = $this->adminUser();
        $page = $this->createDocumentationPage();

        $response = $this->actingAs($user)->get(route('admin.docs.ai-pages.enhance'));

        $response->assertOk()
            ->assertViewIs('admin.docs.pages.ai-enhance')
            ->assertViewHas('pagesJson');

        $responseWithPage = $this->actingAs($user)->get(route('admin.docs.ai-pages.enhance', [
            'documentation_page_id' => $page->id,
        ]));

        $responseWithPage->assertOk()
            ->assertViewHas('prefillPage', fn ($prefill) => $prefill?->id === $page->id);
    }

    public function test_refine_enhance_mode_requires_user_notes(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->postJson(route('admin.docs.ai-pages.refine'), [
            'source_html' => '<p>Hello</p>',
            'mode' => 'enhance',
            'user_notes' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['user_notes']);
    }

    public function test_refine_enhance_mode_returns_content_with_stats(): void
    {
        $user = $this->adminUser();

        $aiModel = new AIModel([
            'name' => 'Test Model',
            'provider' => 'openai',
            'model_key' => 'gpt-4o-mini',
            'max_tokens' => 2000,
            'temperature' => 0.35,
            'is_active' => true,
            'is_default' => true,
            'priority' => 0,
        ]);
        $aiModel->setRawApiKeyForTesting('sk-test');
        $aiModel->save();

        $this->mock(AIDocumentationPageService::class, function ($mock) {
            $mock->shouldReceive('enhanceDocumentationContent')
                ->once()
                ->andReturn([
                    'content' => '<section class="content-section"><p>Enhanced</p></section>',
                    'stats' => [
                        'old_length' => 20,
                        'new_length' => 45,
                        'old_sections' => 1,
                        'new_sections' => 1,
                    ],
                ]);
            $mock->shouldReceive('refineDocumentationContent')->never();
        });

        $response = $this->actingAs($user)->postJson(route('admin.docs.ai-pages.refine'), [
            'source_html' => '<p>Hello world</p>',
            'mode' => 'enhance',
            'user_notes' => 'أضف قسم أمثلة عملية مع 3 أمثلة كود',
            'docs_engine' => 'legacy',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.content', '<section class="content-section"><p>Enhanced</p></section>')
            ->assertJsonPath('data.stats.old_length', 20)
            ->assertJsonPath('data.stats.new_sections', 1);
    }
}
