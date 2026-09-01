<?php

namespace App\Jobs;

use App\Models\SimulatorAiGeneration;
use App\Models\SimulatorAiPhase;
use App\Services\Simulator\SimulatorAiPipelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessSimulatorAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Safe to retry: the staged pipeline resumes from persisted phases instead
     * of regenerating the whole bundle.
     */
    public int $tries = 3;

    /** Must stay below the database queue retry_after (3700s) to avoid double-running. */
    public int $timeout = 1200;

    /** @var list<int> */
    public array $backoff = [30, 120];

    public function __construct(public int $generationId) {}

    public function handle(SimulatorAiPipelineService $pipeline): void
    {
        $generation = SimulatorAiGeneration::query()->find($this->generationId);
        if (! $generation) {
            return;
        }

        $pipeline->run($generation);
    }

    public function failed(?Throwable $exception): void
    {
        $generation = SimulatorAiGeneration::query()->find($this->generationId);
        if (! $generation || in_array($generation->status, [
            SimulatorAiGeneration::STATUS_COMPLETED,
            SimulatorAiGeneration::STATUS_PAUSED,
        ], true)) {
            return;
        }

        $message = $exception?->getMessage() ?: 'فشل غير معروف في طابور التوليد.';
        Log::error('ProcessSimulatorAiJob failed', [
            'generation_id' => $this->generationId,
            'message' => $message,
        ]);

        $hasWork = $generation->phases()
            ->where('status', SimulatorAiPhase::STATUS_DONE)
            ->exists();

        if ($hasWork) {
            $generation->markPaused(
                'توقّفت المهمة في الطابور ('.$message.'). الملفات المكتملة محفوظة — اضغط «متابعة التوليد».'
            );

            return;
        }

        $generation->markFailed($message);
    }
}
