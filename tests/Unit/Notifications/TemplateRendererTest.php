<?php

use App\Services\Notifications\TemplateRenderer;
use Tests\TestCase;

uses(TestCase::class);

it('renders placeholders using payload keys', function () {
    $renderer = new TemplateRenderer();

    $output = $renderer->render(
        'مرحبا {{student_name}} أكملت {{lesson_title}}',
        [
            'student_name' => 'Yasin',
            'lesson_title' => 'Laravel Basics',
        ]
    );

    expect($output)->toBe('مرحبا Yasin أكملت Laravel Basics');
});

it('keeps unknown placeholders unchanged', function () {
    $renderer = new TemplateRenderer();
    $output = $renderer->render('Hello {{name}} {{unknown}}', ['name' => 'Ali']);

    expect($output)->toBe('Hello Ali {{unknown}}');
});
