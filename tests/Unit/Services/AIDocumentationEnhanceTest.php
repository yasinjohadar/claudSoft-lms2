<?php

namespace Tests\Unit\Services;

use App\Models\AIModel;
use App\Services\Ai\AIDocumentationPageService;
use InvalidArgumentException;
use Tests\TestCase;

class AIDocumentationEnhanceTest extends TestCase
{
    public function test_compute_enhance_stats_counts_sections_and_length(): void
    {
        $old = '<section class="content-section"><p>One</p></section>';
        $new = $old.'<section class="content-section"><p>Two</p></section>';

        $stats = AIDocumentationPageService::computeEnhanceStats($old, $new);

        $this->assertSame(mb_strlen($old), $stats['old_length']);
        $this->assertSame(mb_strlen($new), $stats['new_length']);
        $this->assertSame(1, $stats['old_sections']);
        $this->assertSame(2, $stats['new_sections']);
    }

    public function test_enhance_throws_when_user_notes_too_short(): void
    {
        $service = new AIDocumentationPageService;
        $model = new AIModel;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('10 أحرف');

        $service->enhanceDocumentationContent('<p>content</p>', $model, [
            'user_notes' => 'short',
        ]);
    }

    public function test_enhance_throws_when_user_notes_empty(): void
    {
        $service = new AIDocumentationPageService;
        $model = new AIModel;

        $this->expectException(InvalidArgumentException::class);

        $service->enhanceDocumentationContent('<p>content</p>', $model, [
            'user_notes' => '',
        ]);
    }
}
