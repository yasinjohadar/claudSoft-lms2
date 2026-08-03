<?php

use App\Models\AIModel;
use App\Models\DocumentationAiGeneration;
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

uses(Tests\TestCase::class);

beforeEach(function () {
    Schema::dropIfExists('documentation_ai_generations');
    Schema::dropIfExists('ai_models');
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

    Schema::create('ai_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('provider')->nullable();
        $table->string('model_key')->nullable();
        $table->boolean('is_active')->default(true);
        $table->boolean('is_default')->default(false);
        $table->integer('priority')->default(0);
        $table->integer('max_tokens')->default(8000);
        $table->decimal('temperature', 3, 2)->default(0.7);
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

test('long generate with legacy engine uses staged outline and skips one-shot', function () {
    $user = User::query()->create([
        'name' => 'Admin',
        'email' => 'docs-ai-'.uniqid().'@test.local',
        'password' => bcrypt('password'),
    ]);

    $legacyModel = AIModel::query()->create([
        'name' => 'Legacy GPT',
        'provider' => 'openai',
        'model_key' => 'gpt-3.5-turbo',
        'is_active' => true,
        'is_default' => true,
        'priority' => 1,
        'max_tokens' => 8000,
    ]);

    $sectionHtml = '<section class="content-section"><h2 class="section-title">مقدمة</h2>'
        .'<div class="text-block">نص قسم كافٍ للاختبار مع مثال Dart.</div>'
        .'<pre><code class="language-dart">for (final x in list) { print(x); }</code></pre></section>';

    $legacyDocs = Mockery::mock(AIDocumentationPageService::class);
    $legacyDocs->shouldNotReceive('generateDocumentationPage');
    $legacyDocs->shouldReceive('generateDocumentationOutline')
        ->once()
        ->andReturn([
            'title' => 'Loop in List في Dart',
            'slug' => 'loop-in-list-dart',
            'excerpt' => 'مرجع عملي لحلقات القوائم في Dart',
            'sections' => [
                ['heading' => 'مقدمة', 'brief' => 'تعريف'],
                ['heading' => 'أمثلة', 'brief' => 'أمثلة كود'],
            ],
        ]);
    $legacyDocs->shouldReceive('generateDocumentationSectionHtml')
        ->twice()
        ->andReturn($sectionHtml);

    $legacyModelService = Mockery::mock(AIModelService::class);
    $legacyModelService->shouldReceive('getDefaultModel')->andReturn($legacyModel);

    $pipeline = new DocumentationAiPipelineService(
        Mockery::mock(LaravelAiProviderManager::class),
        Mockery::mock(LaravelAiPromptRunner::class),
        Mockery::mock(LaravelAiRequestLogger::class),
        Mockery::mock(LaravelAiDocumentationService::class),
        $legacyDocs,
        $legacyModelService,
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
            'docs_engine' => 'legacy',
            'content_length' => 'long',
            'tone' => 'technical',
            'language' => 'ar',
            'ai_model_id' => $legacyModel->id,
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
