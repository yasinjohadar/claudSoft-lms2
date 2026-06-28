<?php

namespace Tests\Unit\Simulator;

use App\Jobs\GenerateLessonSimulatorJob;
use App\Models\AIModel;
use App\Models\LessonSimulator;
use App\Services\Simulator\SimulatorBundleStorage;
use App\Services\Simulator\SimulatorGenerationService;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class GenerateLessonSimulatorJobTest extends TestCase
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
    }

    public function test_job_saves_bundle_and_marks_completed(): void
    {
        $model = AIModel::create([
            'name' => 'Test Model',
            'provider' => 'openai',
            'model_key' => 'gpt-4',
            'is_active' => true,
            'capabilities' => ['simulator_generation'],
        ]);

        $simulator = LessonSimulator::create([
            'title' => 'Draft Sim',
            'slug' => 'draft-sim',
            'topic_key' => 'custom.test',
            'spec_json' => ['meta' => [], 'sections' => []],
            'render_mode' => 'html_bundle',
            'status' => 'draft',
            'ai_generation_meta' => ['status' => 'pending'],
        ]);

        $generationMock = Mockery::mock(SimulatorGenerationService::class);
        $generationMock->shouldReceive('generate')
            ->once()
            ->andReturn([
                'title' => 'Generated Title',
                'bundle' => [
                    'html' => '<html lang="ar" dir="rtl"><head><title>T</title></head><body class="sim-app"></body></html>',
                    'css' => 'body{margin:0}',
                    'js' => 'console.log(1)',
                ],
                'meta' => ['engine' => 'legacy', 'archetype' => 'playground'],
            ]);

        $storageMock = Mockery::mock(SimulatorBundleStorage::class);
        $storageMock->shouldReceive('save')
            ->once()
            ->andReturn('simulators/draft-sim');

        $job = new GenerateLessonSimulatorJob(
            $simulator,
            'custom.test',
            ['topic_description' => 'Test topic'],
            'legacy',
            $model->id,
        );

        $job->handle($generationMock, $storageMock);

        $simulator->refresh();

        $this->assertSame('Generated Title', $simulator->title);
        $this->assertSame('simulators/draft-sim', $simulator->bundle_path);
        $this->assertSame('completed', $simulator->ai_generation_meta['status'] ?? null);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
