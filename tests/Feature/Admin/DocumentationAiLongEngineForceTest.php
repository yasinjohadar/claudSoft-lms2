<?php

namespace Tests\Feature\Admin;

use App\Models\DocumentationAiGeneration;
use App\Models\DocumentationCategory;
use App\Models\LaravelAiModel;
use App\Models\User;
use App\Services\AiNew\DocumentationAiJobStarter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentationAiLongEngineForceTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Documentation AI feature tests require MySQL (SQLite migrations incompatible).');
        }
    }

    public function test_generate_respects_legacy_engine_for_long_even_when_laravel_model_exists(): void
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        $category = DocumentationCategory::query()->create([
            'name' => 'Dart',
            'slug' => 'dart-ai-long',
            'is_active' => true,
        ]);

        LaravelAiModel::query()->create([
            'name' => 'Laravel AI Docs',
            'provider' => 'openai',
            'model' => 'gpt-4o',
            'is_active' => true,
            'priority' => 1,
            'max_tokens' => 16000,
            'capabilities' => ['docs.refine'],
        ]);

        $generation = new DocumentationAiGeneration([
            'uuid' => '33333333-3333-3333-3333-333333333333',
            'operation' => DocumentationAiGeneration::OPERATION_GENERATE,
            'status' => DocumentationAiGeneration::STATUS_QUEUED,
            'progress' => 0,
            'stage' => 'queued',
            'stage_label' => 'في الطابور…',
            'payload' => [],
        ]);

        $starter = Mockery::mock(DocumentationAiJobStarter::class);
        $starter->shouldReceive('start')
            ->once()
            ->withArgs(function ($authUser, $operation, $payload) use ($user) {
                return $authUser->is($user)
                    && $operation === DocumentationAiGeneration::OPERATION_GENERATE
                    && ($payload['docs_engine'] ?? null) === 'legacy'
                    && ($payload['content_length'] ?? null) === 'long';
            })
            ->andReturn($generation);

        $this->app->instance(DocumentationAiJobStarter::class, $starter);

        $response = $this->actingAs($user)->postJson(route('admin.docs.ai-pages.generate'), [
            'topic' => 'Loop in List في Dart',
            'docs_engine' => 'legacy',
            'content_length' => 'long',
            'tone' => 'technical',
            'language' => 'ar',
            'documentation_category_id' => $category->id,
            'generate_meta' => true,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
    }
}
