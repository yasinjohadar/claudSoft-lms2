<?php

namespace App\Console\Commands;

use App\Services\Storage\AppStorageManager;
use App\Services\Storage\StorageFileCatalogService;
use App\Services\Storage\StorageLocationResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StoragePullFromCloudCommand extends Command
{
    protected $signature = 'storage:pull-from-cloud
                            {--source=blog_posts : Inventory source key}
                            {--disk= : Override logical disk}
                            {--dry-run : Preview without writing local files}';

    protected $description = 'Restore local copies of cloud-backed files (e.g. blog images after local cleanup)';

    public function handle(
        StorageFileCatalogService $catalog,
        StorageLocationResolver $locationResolver,
        AppStorageManager $storageManager,
    ): int {
        $sourceKey = (string) $this->option('source');
        $source = collect(config('storage_inventory.sources', []))
            ->firstWhere('key', $sourceKey);

        if (! $source) {
            $this->error("Unknown source [{$sourceKey}].");

            return self::FAILURE;
        }

        $logicalDisk = (string) ($this->option('disk') ?: ($source['disk'] ?? 'public'));
        $items = $catalog->collectReferences($sourceKey, $logicalDisk);
        $dryRun = (bool) $this->option('dry-run');

        $restored = 0;
        $skipped = 0;
        $missing = 0;
        $failed = 0;

        foreach ($items as $item) {
            $path = (string) ($item['path'] ?? '');

            if ($path === '') {
                $skipped++;

                continue;
            }

            $location = $locationResolver->resolve($logicalDisk, $path);
            $path = $location['path'];
            $hasCloud = collect($location['locations'])->contains(fn (array $hit) => ! empty($hit['is_cloud']));

            if ($storageManager->legacyPublicExists($path)) {
                $skipped++;

                continue;
            }

            if (! $hasCloud) {
                $missing++;

                continue;
            }

            $payload = $storageManager->retrieveWithFailover($logicalDisk, $path);

            if ($payload === null) {
                $failed++;
                $this->warn("Could not read from cloud: {$path}");

                continue;
            }

            if ($dryRun) {
                $this->line("[dry-run] would restore {$path} (".strlen($payload['content']).' bytes)');
                $restored++;

                continue;
            }

            $written = Storage::disk('public')->put($path, $payload['content']);

            if (! $written) {
                $failed++;
                $this->error("Failed to write local copy: {$path}");

                continue;
            }

            $restored++;
            $this->info("Restored {$path}");
        }

        $this->table(
            ['Result', 'Count'],
            [
                ['Restored'.($dryRun ? ' (dry-run)' : ''), $restored],
                ['Skipped (already local)', $skipped],
                ['Missing on cloud', $missing],
                ['Failed', $failed],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
