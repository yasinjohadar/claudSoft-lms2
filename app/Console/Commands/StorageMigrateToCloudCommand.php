<?php

namespace App\Console\Commands;

use App\Services\Storage\StorageBulkMigrationService;
use App\Services\Storage\StorageFileCatalogService;
use App\Services\Storage\StorageInventoryService;
use App\Services\Storage\StorageLocationResolver;
use Illuminate\Console\Command;

class StorageMigrateToCloudCommand extends Command
{
    protected $signature = 'storage:migrate-to-cloud
                            {disk? : Logical disk name}
                            {--phase= : Migrate a configured phase (blog, courses, gifts, profiles)}
                            {--source= : Migrate a single source key}
                            {--status=local_only : Target location status}
                            {--delete-local : Delete local copy after successful upload}
                            {--dry-run : Preview without migrating}
                            {--queue : Dispatch migration jobs to the queue}';

    protected $description = 'Migrate local files to cloud storage in bulk';

    public function handle(
        StorageFileCatalogService $catalog,
        StorageInventoryService $inventoryService,
        StorageBulkMigrationService $migrationService,
    ): int {
        $items = $this->resolveItems($catalog, $inventoryService);

        if ($items === []) {
            $this->warn('No file references found for the given filters.');

            return self::SUCCESS;
        }

        $status = $this->option('status') ?: StorageLocationResolver::STATUS_LOCAL_ONLY;
        $items = $inventoryService->filterItems($items, status: $status);

        if ($items === []) {
            $this->info("No files with status [{$status}] to migrate.");

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $preview = $migrationService->dryRun($items);
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Eligible', $preview['eligible_count']],
                    ['Skipped (cloud only)', $preview['skipped_count']],
                    ['Missing', $preview['missing_count']],
                    ['Bytes to migrate', number_format((float) $preview['total_bytes'])],
                ]
            );

            return self::SUCCESS;
        }

        if ($this->option('queue')) {
            $migrationId = $migrationService->startQueuedMigration($items, (bool) $this->option('delete-local'));
            $this->info("Queued migration {$migrationId} for ".count($items).' files.');

            return self::SUCCESS;
        }

        $results = $migrationService->migrateSync($items, (bool) $this->option('delete-local'));

        $this->table(
            ['Result', 'Count'],
            [
                ['Migrated', $results['migrated']],
                ['Skipped', $results['skipped']],
                ['Failed', $results['failed']],
            ]
        );

        foreach ($results['errors'] as $error) {
            $this->error(($error['path'] ?? 'unknown').': '.($error['message'] ?? ''));
        }

        return ($results['failed'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function resolveItems(StorageFileCatalogService $catalog, StorageInventoryService $inventoryService): array
    {
        if ($this->option('phase')) {
            $scan = $inventoryService->scan(phase: $this->option('phase'));

            return $scan['items'];
        }

        if ($this->option('source')) {
            $scan = $inventoryService->scan(sourceKey: $this->option('source'));

            return $scan['items'];
        }

        $disk = $this->argument('disk');

        if ($disk) {
            $scan = $inventoryService->scan(disk: $disk);

            return $scan['items'];
        }

        $scan = $inventoryService->scan();

        return $scan['items'];
    }
}
