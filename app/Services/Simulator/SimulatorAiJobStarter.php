<?php

namespace App\Services\Simulator;

use App\Jobs\ProcessSimulatorAiJob;
use App\Models\SimulatorAiGeneration;
use App\Models\User;

class SimulatorAiJobStarter
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function start(User $user, string $operation, array $payload, ?int $lessonSimulatorId = null): SimulatorAiGeneration
    {
        $generation = SimulatorAiGeneration::query()->create([
            'user_id' => $user->id,
            'lesson_simulator_id' => $lessonSimulatorId,
            'operation' => $operation,
            'status' => SimulatorAiGeneration::STATUS_QUEUED,
            'progress' => 0,
            'stage' => 'queued',
            'stage_label' => 'في الطابور…',
            'payload' => $payload,
        ]);

        $this->dispatch($generation);

        return $generation;
    }

    public function dispatch(SimulatorAiGeneration $generation): void
    {
        // Local/dev with sync driver: run after HTTP response so the browser is not blocked.
        if (config('queue.default') === 'sync') {
            $id = $generation->id;
            dispatch(function () use ($id) {
                $row = SimulatorAiGeneration::query()->find($id);
                if ($row) {
                    app(SimulatorAiPipelineService::class)->run($row);
                }
            })->afterResponse();

            return;
        }

        ProcessSimulatorAiJob::dispatch($generation->id);
    }
}
