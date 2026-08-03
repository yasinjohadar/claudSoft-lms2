<?php

use App\Models\LaravelAiModel;
use App\Services\AiNew\LaravelAiPromptRunner;
use Prism\Prism\Exceptions\PrismRequestTooLargeException;

uses(Tests\TestCase::class);

test('effectiveMaxTokens uses model max_tokens and does not raise above it', function () {
    $runner = new LaravelAiPromptRunner;
    $model = new LaravelAiModel(['max_tokens' => 12000]);

    expect($runner->effectiveMaxTokens($model, 4096, 8192))->toBe(12000);
    expect($runner->effectiveMaxTokens($model, 4096, 20000))->toBe(12000);
});

test('effectiveMaxTokens falls back to preferMinTokens when model limit missing', function () {
    $runner = new LaravelAiPromptRunner;
    $model = new LaravelAiModel(['max_tokens' => 0]);

    expect($runner->effectiveMaxTokens($model, 4096, 8192))->toBe(8192);
});

test('maxTokenAttempts steps down from preferred model limit', function () {
    $runner = new LaravelAiPromptRunner;

    expect($runner->maxTokenAttempts(16000))->toBe([
        16000,
        12288,
        8192,
        6144,
        4096,
        3072,
        2048,
    ]);
});

test('isRetryableTokenOrSizeError detects prism too large', function () {
    $runner = new LaravelAiPromptRunner;

    expect($runner->isRetryableTokenOrSizeError(
        PrismRequestTooLargeException::make('openrouter')
    ))->toBeTrue();

    expect($runner->isRetryableTokenOrSizeError(
        new RuntimeException('context_length_exceeded')
    ))->toBeTrue();

    expect($runner->isRetryableTokenOrSizeError(
        new RuntimeException('network timeout')
    ))->toBeFalse();
});
