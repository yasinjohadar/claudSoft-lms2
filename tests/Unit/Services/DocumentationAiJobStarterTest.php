<?php

use App\Models\DocumentationAiGeneration;

test('documentation ai generation status payload hides result until completed', function () {
    $generation = new DocumentationAiGeneration([
        'uuid' => '11111111-1111-1111-1111-111111111111',
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
    $generation = new DocumentationAiGeneration([
        'uuid' => '22222222-2222-2222-2222-222222222222',
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
    $service = new ReflectionClass(\App\Services\AiNew\DocumentationAiPipelineService::class);
    $method = $service->getMethod('sectionCountForLength');
    $method->setAccessible(true);

    $instance = $service->newInstanceWithoutConstructor();

    expect($method->invoke($instance, 'short'))->toBe(4);
    expect($method->invoke($instance, 'medium'))->toBe(7);
    expect($method->invoke($instance, 'long'))->toBe(12);
});
