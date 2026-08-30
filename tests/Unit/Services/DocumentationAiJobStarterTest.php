<?php

use App\Models\DocumentationAiGeneration;
use App\Models\User;
use App\Services\AiNew\DocumentationAiPipelineService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Schema::dropIfExists('documentation_ai_sections');
    Schema::dropIfExists('documentation_ai_generations');
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

    Schema::create('documentation_ai_generations', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
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
        $table->timestamp('heartbeat_at')->nullable();
        $table->timestamps();
    });

    Schema::create('documentation_ai_sections', function (Blueprint $table) {
        $table->id();
        $table->foreignId('generation_id')->constrained('documentation_ai_generations')->cascadeOnDelete();
        $table->unsignedSmallInteger('position');
        $table->string('heading');
        $table->text('brief')->nullable();
        $table->string('status', 16)->default('pending');
        $table->longText('html')->nullable();
        $table->unsignedTinyInteger('attempts')->default(0);
        $table->text('last_error')->nullable();
        $table->timestamps();
        $table->unique(['generation_id', 'position']);
        $table->index(['generation_id', 'status']);
    });
});

function makeJobStarterUser(): User
{
    return User::query()->create([
        'name' => 'Tester',
        'email' => 'docs-ai-jobstarter-'.uniqid().'@test.local',
        'password' => bcrypt('password'),
    ]);
}

test('documentation ai generation status payload hides result until completed', function () {
    $generation = DocumentationAiGeneration::query()->create([
        'uuid' => '11111111-1111-1111-1111-111111111111',
        'user_id' => makeJobStarterUser()->id,
        'operation' => DocumentationAiGeneration::OPERATION_GENERATE,
        'status' => DocumentationAiGeneration::STATUS_RUNNING,
        'progress' => 40,
        'stage' => 'section_2',
        'stage_label' => 'كتابة القسم 2',
        'payload' => ['topic' => 'x'],
        'result' => ['title' => 'should-hide'],
        'error_message' => null,
    ]);

    $payload = $generation->toStatusPayload();

    expect($payload['uuid'])->toBe('11111111-1111-1111-1111-111111111111');
    expect($payload['progress'])->toBe(40);
    expect($payload['result'])->toBeNull();
});

test('documentation ai generation status payload returns result when completed', function () {
    $generation = DocumentationAiGeneration::query()->create([
        'uuid' => '22222222-2222-2222-2222-222222222222',
        'user_id' => makeJobStarterUser()->id,
        'operation' => DocumentationAiGeneration::OPERATION_REFINE,
        'status' => DocumentationAiGeneration::STATUS_COMPLETED,
        'progress' => 100,
        'stage' => 'completed',
        'stage_label' => 'اكتمل',
        'payload' => [],
        'result' => [
            'content' => '<section class="content-section">ok</section>',
        ],
    ]);

    $payload = $generation->toStatusPayload();

    expect($payload['result']['content'])->toContain('content-section');
    expect($payload['status'])->toBe('completed');
});

test('section count mapping for content length targets long pages via more sections', function () {
    $service = new ReflectionClass(DocumentationAiPipelineService::class);
    $method = $service->getMethod('sectionCountForLength');
    $method->setAccessible(true);

    $instance = $service->newInstanceWithoutConstructor();

    expect($method->invoke($instance, 'short'))->toBe(4);
    expect($method->invoke($instance, 'medium'))->toBe(7);
    expect($method->invoke($instance, 'long'))->toBe(13);
});
