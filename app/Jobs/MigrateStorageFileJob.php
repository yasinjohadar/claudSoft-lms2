<?php

namespace App\Jobs;

use App\Services\Storage\StorageBulkMigrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MigrateStorageFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $migrationId,
        public string $logicalDisk,
        public string $path,
        public bool $deleteLocal = false,
    ) {}

    public function handle(StorageBulkMigrationService $migrationService): void
    {
        $outcome = $migrationService->migrateFile($this->logicalDisk, $this->path, $this->deleteLocal);
        $migrationService->recordProgress($this->migrationId, $outcome);
    }
}
