<?php

namespace App\Jobs;

use App\Models\DocumentationAiGeneration;
use App\Services\AiNew\DocumentationAiPipelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDocumentationAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 1800;

    public function __construct(public int $generationId) {}

    public function handle(DocumentationAiPipelineService $pipeline): void
    {
        $generation = DocumentationAiGeneration::query()->find($this->generationId);
        if (! $generation) {
            return;
        }

        $pipeline->run($generation);
    }

    public function failed(?Throwable $exception): void
    {
        $generation = DocumentationAiGeneration::query()->find($this->generationId);
        if (! $generation || $generation->status === DocumentationAiGeneration::STATUS_COMPLETED) {
            return;
        }

        $message = $exception?->getMessage() ?: 'فشل غير معروف في طابور التوليد.';
        Log::error('ProcessDocumentationAiJob failed', [
            'generation_id' => $this->generationId,
            'message' => $message,
        ]);
        $generation->markFailed($message);
    }
}
