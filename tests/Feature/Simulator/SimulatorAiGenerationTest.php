<?php

namespace Tests\Feature\Simulator;

use App\Models\AIModel;
use App\Models\User;
use App\Services\Simulator\SimulatorGenerationService;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class SimulatorAiGenerationTest extends TestCase
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

        Schema::create('ai_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('model_key')->nullable();
            $table->text('api_key')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->integer('max_tokens')->default(8000);
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->json('capabilities')->nullable();
            $table->timestamps();
        });

        Schema::create('laravel_ai_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->integer('max_tokens')->default(8000);
            $table->json('capabilities')->nullable();
            $table->timestamps();
        });
    }

    public function test_generate_sync_returns_bundle_json(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@test.local',
            'password' => bcrypt('password'),
        ]);

        AIModel::create([
            'name' => 'Test Legacy',
            'provider' => 'openai',
            'model_key' => 'gpt-4',
            'is_active' => true,
            'capabilities' => ['simulator_generation'],
        ]);

        $mock = Mockery::mock(SimulatorGenerationService::class);
        $mock->shouldReceive('generateHtmlBundle')
            ->once()
            ->andReturn([
                'title' => 'محاكاة الأمن السيبراني',
                'bundle' => [
                    'html' => '<html lang="ar" dir="rtl"><body class="sim-app"><h1>test</h1></body></html>',
                    'css' => '.sim-app { min-height: 100vh; }',
                    'js' => 'document.addEventListener("DOMContentLoaded", function() {});',
                ],
                'meta' => ['engine' => 'legacy', 'archetype' => 'playground'],
            ]);

        $this->app->instance(SimulatorGenerationService::class, $mock);

        $response = $this->withoutMiddleware()
            ->actingAs($admin)
            ->postJson(route('admin.lesson-simulators.generate-bundle'), [
            'topic_description' => 'الأمن السيبراني',
            'primary_language' => 'html',
            'level' => 'beginner',
            'archetype' => 'playground',
            'simulators_engine' => 'legacy',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'محاكاة الأمن السيبراني')
            ->assertJsonStructure(['data' => ['html', 'css', 'js', 'meta']]);
    }

    public function test_generate_sync_requires_topic(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin2-'.uniqid().'@test.local',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withoutMiddleware()
            ->actingAs($admin)
            ->postJson(route('admin.lesson-simulators.generate-bundle'), [
            'primary_language' => 'html',
            'level' => 'beginner',
        ]);

        $response->assertStatus(422);
    }

    public function test_refine_bundle_returns_updated_files(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin3-'.uniqid().'@test.local',
            'password' => bcrypt('password'),
        ]);

        AIModel::create([
            'name' => 'Test Legacy',
            'provider' => 'openai',
            'model_key' => 'gpt-4',
            'is_active' => true,
            'capabilities' => ['simulator_generation'],
        ]);

        $mock = Mockery::mock(SimulatorGenerationService::class);
        $mock->shouldReceive('refineHtmlBundle')
            ->once()
            ->andReturn([
                'title' => 'Flexbox محدّث',
                'bundle' => [
                    'html' => '<html lang="ar" dir="rtl"><body class="sim-app"><h1>updated</h1></body></html>',
                    'css' => '.sim-app { gap: 1rem; }',
                    'js' => 'console.log("updated");',
                ],
                'meta' => ['operation' => 'refine'],
            ]);

        $this->app->instance(SimulatorGenerationService::class, $mock);

        $response = $this->withoutMiddleware()
            ->actingAs($admin)
            ->postJson(route('admin.lesson-simulators.refine-bundle'), [
                'instructions' => 'أضف خاصية gap',
                'bundle_html' => '<html lang="ar" dir="rtl"><body class="sim-app"></body></html>',
                'bundle_css' => '.sim-app {}',
                'bundle_js' => 'console.log(1);',
                'simulators_engine' => 'legacy',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Flexbox محدّث')
            ->assertJsonStructure(['data' => ['html', 'css', 'js']]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
