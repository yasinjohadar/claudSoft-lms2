<?php

namespace App\Console\Commands;

use App\Services\Storage\StorageBulkMigrationService;
use App\Services\Storage\StorageInventoryService;
use Illuminate\Console\Command;

class StorageMigratePhaseCommand extends Command
{
    protected $signature = 'storage:migrate-phase
                            {phase : Phase key (blog, courses, gifts, profiles)}
                            {--delete-local : Delete local copies after upload}
                            {--dry-run : Preview only}
                            {--queue : Use queue}';

    protected $description = 'Run a configured migration phase (blog → courses → gifts → profiles)';

    public function handle(
        StorageInventoryService $inventoryService,
        StorageBulkMigrationService $migrationService,
    ): int {
        $phase = $this->argument('phase');
        $phases = config('storage_inventory.phases', []);

        if (! array_key_exists($phase, $phases)) {
            $this->error('Unknown phase. Available: '.implode(', ', array_keys($phases)));

            return self::FAILURE;
        }

        $this->info("Phase [{$phase}]: ".implode(', ', $phases[$phase]));

        $scan = $inventoryService->scan(phase: $phase);
        $items = $inventoryService->filterItems($scan['items'], status: 'local_only');

        if ($items === []) {
            $this->info('No local-only files in this phase.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $preview = $migrationService->dryRun($items);
            $this->table(['Metric', 'Value'], [
                ['Eligible', $preview['eligible_count']],
                ['Bytes', number_format((float) $preview['total_bytes'])],
            ]);

            return self::SUCCESS;
        }

        if ($this->option('queue')) {
            $id = $migrationService->startQueuedMigration($items, $this->option('delete-local'));
            $this->info("Queued migration {$id}");

            return self::SUCCESS;
        }

        $results = $migrationService->migrateSync($items, (bool) $this->option('delete-local'));
        $this->info("Migrated: {$results['migrated']}, skipped: {$results['skipped']}, failed: {$results['failed']}");

        return ($results['failed'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
