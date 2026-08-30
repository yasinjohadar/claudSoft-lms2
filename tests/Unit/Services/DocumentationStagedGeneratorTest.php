<?php

use App\Exceptions\Ai\AiProviderException;
use App\Exceptions\Ai\ResumableIncompleteException;
use App\Models\AIModel;
use App\Models\DocumentationAiGeneration;
use App\Models\DocumentationAiSection;
use App\Models\User;
use App\Services\Ai\AIDocumentationPageService;
use App\Services\Ai\AiErrorClassifier;
use App\Services\Ai\DocumentationAiResultNormalizer;
use App\Services\AiNew\DocumentationStagedGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Schema::dropIfExists('documentation_ai_sections');
    Schema::dropIfExists('documentation_ai_generations');
    Schema::dropIfExists('activity_log');
    Schema::dropIfExists('ai_models');
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

    // Backoff sleeps would make these tests slow for no reason — every retry path
    // under test is exercised via error kind, not via real elapsed time.
    config(['ai.docs.retry_backoff' => false]);
});

function stagedGenerator(): DocumentationStagedGenerator
{
    return new DocumentationStagedGenerator(new DocumentationAiResultNormalizer, new AiErrorClassifier);
}

function stagedGeneration(): DocumentationAiGeneration
{
    $user = User::query()->create([
        'name' => 'Tester',
        'email' => 'staged-gen-'.uniqid().'@test.local',
        'password' => bcrypt('password'),
    ]);

    return DocumentationAiGeneration::query()->create([
        'user_id' => $user->id,
        'operation' => DocumentationAiGeneration::OPERATION_GENERATE,
        'status' => DocumentationAiGeneration::STATUS_RUNNING,
        'payload' => ['topic' => 'staged generator test topic'],
        'progress' => 0,
    ]);
}

/**
 * A section body long enough to clear DocumentationSectionValidator, so these
 * tests exercise the retry ladder rather than the quality floor.
 */
function fakeSectionHtml(string $heading, string $marker = 'generated'): string
{
    $paragraph = str_repeat('نص توضيحي كافٍ لتجاوز الحد الأدنى لطول القسم في المدقق. ', 12);

    return '<section class="content-section">'
        .'<h2 class="section-title">'.$heading.'</h2>'
        .'<div class="text-block"><p>'.$marker.' '.$heading.'</p><p>'.$paragraph.'</p></div>'
        .'</section>';
}

/**
 * @return array{title: string, slug: string, excerpt: string, sections: list<array{heading: string, brief: string}>}
 */
function outlineWithSections(int $count): array
{
    $sections = [];
    for ($i = 1; $i <= $count; $i++) {
        $sections[] = ['heading' => 'section_'.$i, 'brief' => 'brief_'.$i];
    }

    return [
        'title' => 'Staged Generator Test Page',
        'slug' => 'staged-generator-test-page',
        'excerpt' => 'ملخص تجريبي',
        'sections' => $sections,
    ];
}

test('resume only regenerates sections that were not already done', function () {
    $generation = stagedGeneration();
    $outline = outlineWithSections(12);

    // Seed the generation as if 5 of 12 sections already finished on a prior run —
    // exactly the scenario a real pause/resume produces.
    $generation->update(['partial_result' => ['outline' => $outline]]);
    foreach ($outline['sections'] as $i => $section) {
        DocumentationAiSection::query()->create([
            'generation_id' => $generation->id,
            'position' => $i,
            'heading' => $section['heading'],
            'brief' => $section['brief'],
            'status' => $i < 5 ? DocumentationAiSection::STATUS_DONE : DocumentationAiSection::STATUS_PENDING,
            'html' => $i < 5 ? fakeSectionHtml($section['heading'], 'pre-existing') : null,
        ]);
    }

    $calls = [];
    $sectionWriter = function ($attempt) use (&$calls) {
        $calls[] = $attempt->heading;

        return fakeSectionHtml($attempt->heading);
    };
    $outlineWriter = fn (int $target, int $tokens) => $outline;

    $result = stagedGenerator()->generate(
        $generation,
        'topic',
        ['language' => 'ar'],
        10,
        2048,
        4096,
        $outlineWriter,
        $sectionWriter,
    );

    expect($calls)->toHaveCount(7)
        ->and($calls)->not->toContain('section_1', 'section_2', 'section_3', 'section_4', 'section_5')
        ->and($result['content'])->toContain('pre-existing')
        ->and($result['content'])->toContain('generated section_6');
});

test('a section exhausting its retry ladder pauses the generation without losing finished work', function () {
    $generation = stagedGeneration();
    $outline = outlineWithSections(5);
    $outlineWriter = fn (int $target, int $tokens) => $outline;

    $failingHeading = 'section_3';
    $sectionWriter = function ($attempt) use ($failingHeading) {
        if ($attempt->heading === $failingHeading) {
            throw new AiProviderException('simulated permanent failure', AiProviderException::KIND_TOO_LARGE);
        }

        return fakeSectionHtml($attempt->heading);
    };

    $caught = null;
    try {
        stagedGenerator()->generate($generation, 'topic', ['language' => 'ar'], 5, 2048, 4096, $outlineWriter, $sectionWriter);
    } catch (ResumableIncompleteException $e) {
        $caught = $e;
    }

    expect($caught)->not->toBeNull()
        ->and($caught->done)->toBe(4)
        ->and($caught->planned)->toBe(5)
        ->and($caught->failedHeadings)->toBe([$failingHeading]);

    $sections = $generation->sections()->get()->keyBy('heading');
    expect($sections['section_1']->status)->toBe(DocumentationAiSection::STATUS_DONE)
        ->and($sections['section_2']->status)->toBe(DocumentationAiSection::STATUS_DONE)
        ->and($sections[$failingHeading]->status)->toBe(DocumentationAiSection::STATUS_FAILED)
        ->and($sections['section_4']->status)->toBe(DocumentationAiSection::STATUS_DONE)
        ->and($sections['section_5']->status)->toBe(DocumentationAiSection::STATUS_DONE);
});

test('a retryable rate limit error is retried instead of failing the section immediately', function () {
    $generation = stagedGeneration();
    $outline = outlineWithSections(2);
    $outlineWriter = fn (int $target, int $tokens) => $outline;

    $attemptsForSection1 = 0;
    $sectionWriter = function ($attempt) use (&$attemptsForSection1) {
        if ($attempt->heading === 'section_1') {
            $attemptsForSection1++;
            if ($attemptsForSection1 === 1) {
                throw new AiProviderException('rate limited', AiProviderException::KIND_RATE_LIMIT, retryAfterSeconds: 1);
            }
        }

        return fakeSectionHtml($attempt->heading);
    };

    $result = stagedGenerator()->generate($generation, 'topic', ['language' => 'ar'], 2, 2048, 4096, $outlineWriter, $sectionWriter);

    expect($attemptsForSection1)->toBeGreaterThan(1)
        ->and($result['content'])->toContain('generated section_1')
        ->and($result['content'])->toContain('generated section_2');
});

test('a fatal auth error aborts the whole generation immediately instead of burning remaining sections', function () {
    $generation = stagedGeneration();
    $outline = outlineWithSections(5);
    $outlineWriter = fn (int $target, int $tokens) => $outline;

    $calls = [];
    $sectionWriter = function ($attempt) use (&$calls) {
        $calls[] = $attempt->heading;
        if ($attempt->heading === 'section_2') {
            throw new AiProviderException('invalid api key', AiProviderException::KIND_AUTH);
        }

        return fakeSectionHtml($attempt->heading);
    };

    expect(fn () => stagedGenerator()->generate($generation, 'topic', ['language' => 'ar'], 5, 2048, 4096, $outlineWriter, $sectionWriter))
        ->toThrow(AiProviderException::class, 'invalid api key');

    // Aborted on the first attempt at section_2 — never retried it, never moved on to 3/4/5.
    expect($calls)->toBe(['section_1', 'section_2']);
});

test('a model with a large configured limit never receives more than the section token cap', function () {
    $model = new AIModel(['max_tokens' => 10000]);
    $service = new AIDocumentationPageService;

    // The section cap is length-aware now: a long page gets the full ceiling,
    // a medium one less, and the model's own limit never wins over either.
    expect($service->tokensForStage($model, 'section', false, 'long'))
        ->toBe((int) config('ai.docs.section_max_tokens', 8192))
        ->toBeLessThan(10000);

    expect($service->tokensForStage($model, 'section', false, 'medium'))
        ->toBeLessThan($service->tokensForStage($model, 'section', false, 'long'));

    expect($service->tokensForStage($model, 'outline'))
        ->toBe((int) config('ai.docs.outline_max_tokens', 3072))
        ->toBeLessThan(10000);
});
