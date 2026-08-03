<?php

use App\Models\DocumentationAiGeneration;
use App\Models\LaravelAiModel;
use App\Models\User;
use App\Services\Ai\AIDocumentationPageService;
use App\Services\Ai\AIModelService;
use App\Services\Ai\DocumentationAiResultNormalizer;
use App\Services\AiNew\DocumentationAiPipelineService;
use App\Services\AiNew\LaravelAiDocumentationService;
use App\Services\AiNew\LaravelAiPromptRunner;
use App\Services\AiNew\LaravelAiProviderManager;
use App\Services\AiNew\LaravelAiRequestLogger;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StructuredAgentResponse;

uses(Tests\TestCase::class);

beforeEach(function () {
    Schema::dropIfExists('documentation_ai_generations');
    Schema::dropIfExists('laravel_ai_models');
    Schema::dropIfExists('activity_log');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->timestamps();
    });

    Schema::create('activity_log', function (Blueprint $table) {
        $table->id();
        $table->string('log_name')->nullable();
        $table->text('description')->nullable();
        $table->nullableMorphs('subject');
        $table->nullableMorphs('causer');
        $table->json('properties')->nullable();
        $table->uuid('batch_uuid')->nullable();
        $table->string('event')->nullable();
        $table->timestamps();
    });

    Schema::create('laravel_ai_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('provider')->nullable();
        $table->string('model')->nullable();
        $table->text('api_key')->nullable();
        $table->string('base_url')->nullable();
        $table->boolean('is_active')->default(true);
        $table->unsignedInteger('priority')->default(0);
        $table->json('capabilities')->nullable();
        $table->unsignedInteger('max_tokens')->default(16000);
        $table->decimal('temperature', 4, 2)->default(0.70);
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamps();
    });

    Schema::create('documentation_ai_generations', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->foreignId('user_id');
        $table->string('operation', 32);
        $table->string('status', 32)->default('queued');
        $table->unsignedTinyInteger('progress')->default(0);
        $table->string('stage', 64)->nullable();
        $table->string('stage_label')->nullable();
        $table->json('payload');
        $table->json('partial_result')->nullable();
        $table->json('result')->nullable();
        $table->text('error_message')->nullable();
        $table->timestamp('started_at')->nullable();
        $table->timestamp('finished_at')->nullable();
        $table->timestamps();
    });
});

function structuredResponse(array $structured): StructuredAgentResponse
{
    return new StructuredAgentResponse(
        'test-invocation',
        $structured,
        json_encode($structured, JSON_UNESCAPED_UNICODE),
        new Usage,
        new Meta('openai', 'gpt-test'),
    );
}

test('long generate with active laravel ai skips legacy one-shot and uses outline pipeline', function () {
    $user = User::query()->create([
        'name' => 'Admin',
        'email' => 'docs-ai-'.uniqid().'@test.local',
        'password' => bcrypt('password'),
    ]);

    $laraModel = LaravelAiModel::query()->create([
        'name' => 'Test Laravel AI',
        'provider' => 'openai',
        'model' => 'gpt-4o',
        'is_active' => true,
        'priority' => 1,
        'max_tokens' => 16000,
        'capabilities' => ['docs.refine'],
    ]);
    $laraModel->setRawApiKeyForTesting('sk-test');
    $laraModel->save();

    $legacyDocs = Mockery::mock(AIDocumentationPageService::class);
    $legacyDocs->shouldNotReceive('generateDocumentationPage');

    $providerManager = Mockery::mock(LaravelAiProviderManager::class);
    $providerManager->shouldReceive('runWithModel')
        ->andReturnUsing(fn ($model, $callback) => $callback());

    $sectionHtml = '<section class="content-section"><h2 class="section-title">مقدمة</h2>'
        .'<div class="text-block">نص قسم كافٍ للاختبار مع مثال Dart.</div>'
        .'<pre><code class="language-dart">for (final x in list) { print(x); }</code></pre></section>';

    $promptRunner = Mockery::mock(LaravelAiPromptRunner::class);
    $promptRunner->shouldReceive('runStructured')
        ->once()
        ->withArgs(function ($model, $agent) {
            return $agent instanceof \App\Ai\Agents\DocumentationOutlineAgent;
        })
        ->andReturn(structuredResponse([
            'title' => 'Loop in List في Dart',
            'slug' => 'loop-in-list-dart',
            'excerpt' => 'مرجع عملي لحلقات القوائم في Dart',
            'sections' => [
                ['heading' => 'مقدمة', 'brief' => 'تعريف'],
                ['heading' => 'أمثلة', 'brief' => 'أمثلة كود'],
            ],
        ]));

    $promptRunner->shouldReceive('runStructured')
        ->twice()
        ->withArgs(function ($model, $agent) {
            return $agent instanceof \App\Ai\Agents\DocumentationSectionAgent;
        })
        ->andReturn(structuredResponse(['html' => $sectionHtml]));

    $logger = Mockery::mock(LaravelAiRequestLogger::class);
    $logger->shouldReceive('logSuccess')->andReturn(new \App\Models\LaravelAiLog);

    $pipeline = new DocumentationAiPipelineService(
        $providerManager,
        $promptRunner,
        $logger,
        Mockery::mock(LaravelAiDocumentationService::class),
        $legacyDocs,
        Mockery::mock(AIModelService::class),
        new DocumentationAiResultNormalizer,
    );

    $generation = DocumentationAiGeneration::query()->create([
        'user_id' => $user->id,
        'operation' => DocumentationAiGeneration::OPERATION_GENERATE,
        'status' => DocumentationAiGeneration::STATUS_QUEUED,
        'progress' => 0,
        'stage' => 'queued',
        'stage_label' => 'في الطابور…',
        'payload' => [
            'topic' => 'Loop in List في لغة Dart مع أمثلة',
            // Intentionally request legacy — pipeline must force laravel_ai for long.
            'docs_engine' => 'legacy',
            'content_length' => 'long',
            'tone' => 'technical',
            'language' => 'ar',
            'laravel_ai_model_id' => $laraModel->id,
            'generate_meta' => true,
        ],
    ]);

    $pipeline->run($generation);
    $generation->refresh();

    expect($generation->status)->toBe(DocumentationAiGeneration::STATUS_COMPLETED);
    expect($generation->result['title'] ?? null)->toBe('Loop in List في Dart');
    expect($generation->result['content'] ?? '')->toContain('content-section');
    expect($generation->stage)->not->toBe('legacy_generate');
});
