<?php

namespace App\Services\AiNew;

use App\Jobs\ProcessBlogAiJob;
use App\Models\BlogAiGeneration;
use App\Models\User;

class BlogAiJobStarter
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function start(User $user, string $operation, array $payload): BlogAiGeneration
    {
        $generation = BlogAiGeneration::query()->create([
            'user_id' => $user->id,
            'operation' => $operation,
            'status' => BlogAiGeneration::STATUS_QUEUED,
            'progress' => 0,
            'stage' => 'queued',
            'stage_label' => 'في الطابور…',
            'payload' => $payload,
        ]);

        $this->dispatch($generation);

        return $generation;
    }

    public function dispatch(BlogAiGeneration $generation): void
    {
        // Local/dev with sync driver: run after HTTP response so the browser is not blocked.
        if (config('queue.default') === 'sync') {
            $id = $generation->id;
            dispatch(function () use ($id) {
                $row = BlogAiGeneration::query()->find($id);
                if ($row) {
                    app(BlogAiPipelineService::class)->run($row);
                }
            })->afterResponse();

            return;
        }

        ProcessBlogAiJob::dispatch($generation->id);
    }
}
