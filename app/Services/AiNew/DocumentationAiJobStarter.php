<?php

namespace App\Services\AiNew;

use App\Jobs\ProcessDocumentationAiJob;
use App\Models\DocumentationAiGeneration;
use App\Models\User;

class DocumentationAiJobStarter
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function start(User $user, string $operation, array $payload): DocumentationAiGeneration
    {
        $generation = DocumentationAiGeneration::query()->create([
            'user_id' => $user->id,
            'operation' => $operation,
            'status' => DocumentationAiGeneration::STATUS_QUEUED,
            'progress' => 0,
            'stage' => 'queued',
            'stage_label' => 'في الطابور…',
            'payload' => $payload,
        ]);

        $this->dispatch($generation);

        return $generation;
    }

    public function dispatch(DocumentationAiGeneration $generation): void
    {
        // Local/dev with sync driver: run after HTTP response so the browser is not blocked.
        if (config('queue.default') === 'sync') {
            $id = $generation->id;
            dispatch(function () use ($id) {
                $row = DocumentationAiGeneration::query()->find($id);
                if ($row) {
                    app(DocumentationAiPipelineService::class)->run($row);
                }
            })->afterResponse();

            return;
        }

        ProcessDocumentationAiJob::dispatch($generation->id);
    }
}
