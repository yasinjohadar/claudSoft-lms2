<?php

namespace App\Services\Storage;

use App\Models\AppStorageConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StorageBulkMigrationService
{
    public function __construct(
        protected AppStorageManager $storageManager,
        protected StorageLocationResolver $locationResolver,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function dryRun(array $items): array
    {
        $eligible = [];
        $skipped = 0;
        $missing = 0;
        $totalBytes = 0;

        foreach ($items as $item) {
            $location = $this->locationResolver->resolve($item['disk'], $item['path']);

            if ($location['status'] === StorageLocationResolver::STATUS_CLOUD_ONLY) {
                $skipped++;

                continue;
            }

            if ($location['status'] === StorageLocationResolver::STATUS_MISSING) {
                $missing++;

                continue;
            }

            $eligible[] = $item;
            $totalBytes += (int) ($location['size'] ?? 0);
        }

        return [
            'eligible_count' => count($eligible),
            'skipped_count' => $skipped,
            'missing_count' => $missing,
            'total_bytes' => $totalBytes,
            'items' => $eligible,
        ];
    }

    /**
     * @return array{success: bool, action: string, message: string}
     */
    public function migrateFile(string $logicalDisk, string $path, bool $deleteLocal = false): array
    {
        $location = $this->locationResolver->resolve($logicalDisk, $path);
        $path = $location['path'];

        if ($path === '') {
            return ['success' => false, 'action' => 'failed', 'message' => 'Empty path'];
        }

        if ($location['status'] === StorageLocationResolver::STATUS_CLOUD_ONLY) {
            return ['success' => true, 'action' => 'skipped', 'message' => 'Already on cloud'];
        }

        if ($location['status'] === StorageLocationResolver::STATUS_MISSING) {
            return ['success' => false, 'action' => 'failed', 'message' => 'File missing'];
        }

        $cloudTarget = $this->storageManager->resolveCloudPrimaryStorages($logicalDisk)->first();

        if (! $cloudTarget instanceof AppStorageConfig) {
            return ['success' => false, 'action' => 'failed', 'message' => 'No cloud storage configured'];
        }

        if ($this->storageManager->existsOnConfig($cloudTarget, $path)) {
            if ($deleteLocal) {
                $this->deleteLocalCopies($logicalDisk, $path);
            }

            return ['success' => true, 'action' => 'skipped', 'message' => 'Already exists on cloud'];
        }

        $content = $this->readLocalContent($logicalDisk, $path, $location);

        if ($content === null) {
            return ['success' => false, 'action' => 'failed', 'message' => 'Could not read local file'];
        }

        if (! $this->storageManager->putToConfig($cloudTarget, $path, $content)) {
            return ['success' => false, 'action' => 'failed', 'message' => 'Cloud upload failed'];
        }

        if (! $this->storageManager->existsOnConfig($cloudTarget, $path)) {
            return ['success' => false, 'action' => 'failed', 'message' => 'Cloud verification failed'];
        }

        $remoteSize = $this->storageManager->getFileSizeOnConfig($cloudTarget, $path);

        if ($remoteSize !== strlen($content)) {
            return ['success' => false, 'action' => 'failed', 'message' => 'Size mismatch after upload'];
        }

        if ($deleteLocal) {
            $this->deleteLocalCopies($logicalDisk, $path);
        }

        Log::info('Storage file migrated to cloud', [
            'disk' => $logicalDisk,
            'path' => $path,
            'cloud' => $cloudTarget->name,
            'delete_local' => $deleteLocal,
        ]);

        return ['success' => true, 'action' => 'migrated', 'message' => 'Migrated to '.$cloudTarget->name];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function migrateSync(array $items, bool $deleteLocal = false, bool $dryRun = false): array
    {
        if ($dryRun) {
            return $this->dryRun($items);
        }

        $results = [
            'migrated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($items as $item) {
            $outcome = $this->migrateFile($item['disk'], $item['path'], $deleteLocal);

            match ($outcome['action']) {
                'migrated' => $results['migrated']++,
                'skipped' => $results['skipped']++,
                default => $results['failed']++,
            };

            if (! $outcome['success'] && $outcome['action'] === 'failed') {
                $results['errors'][] = [
                    'path' => $item['path'],
                    'message' => $outcome['message'],
                ];
            }
        }

        return $results;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function startQueuedMigration(array $items, bool $deleteLocal = false): string
    {
        $migrationId = (string) Str::uuid();
        $batchSize = (int) config('storage_inventory.migration_batch_size', 50);

        $progress = [
            'id' => $migrationId,
            'total' => count($items),
            'completed' => 0,
            'migrated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'status' => 'running',
            'errors' => [],
        ];

        Cache::put($this->progressCacheKey($migrationId), $progress, now()->addHours(6));
        Cache::put(config('storage_inventory.migration_progress_cache_key'), $migrationId, now()->addHours(6));

        foreach (array_chunk($items, $batchSize) as $chunk) {
            foreach ($chunk as $item) {
                \App\Jobs\MigrateStorageFileJob::dispatch(
                    $migrationId,
                    $item['disk'],
                    $item['path'],
                    $deleteLocal
                );
            }
        }

        return $migrationId;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getProgress(?string $migrationId = null): ?array
    {
        $migrationId ??= Cache::get(config('storage_inventory.migration_progress_cache_key'));

        if ($migrationId === null) {
            return null;
        }

        return Cache::get($this->progressCacheKey($migrationId));
    }

    public function recordProgress(string $migrationId, array $outcome): void
    {
        $key = $this->progressCacheKey($migrationId);

        Cache::lock($key.'_lock', 10)->block(5, function () use ($key, $outcome) {
            $progress = Cache::get($key, [
                'completed' => 0,
                'migrated' => 0,
                'skipped' => 0,
                'failed' => 0,
                'total' => 0,
                'status' => 'running',
                'errors' => [],
            ]);

            $progress['completed'] = (int) ($progress['completed'] ?? 0) + 1;

            match ($outcome['action'] ?? 'failed') {
                'migrated' => $progress['migrated'] = (int) ($progress['migrated'] ?? 0) + 1,
                'skipped' => $progress['skipped'] = (int) ($progress['skipped'] ?? 0) + 1,
                default => $progress['failed'] = (int) ($progress['failed'] ?? 0) + 1,
            };

            if (($outcome['action'] ?? '') === 'failed') {
                $progress['errors'][] = $outcome['message'] ?? 'Unknown error';
            }

            if ($progress['total'] > 0 && $progress['completed'] >= $progress['total']) {
                $progress['status'] = 'completed';
            }

            Cache::put($key, $progress, now()->addHours(6));
        });
    }

    protected function readLocalContent(string $logicalDisk, string $path, array $location): ?string
    {
        foreach ($location['locations'] as $hit) {
            if ($hit['is_cloud']) {
                continue;
            }

            if ($hit['storage_config_id'] === null) {
                $content = $this->storageManager->getLegacyPublicContent($path);
                if ($content !== null) {
                    return $content;
                }

                continue;
            }

            $config = AppStorageConfig::find($hit['storage_config_id']);

            if ($config) {
                $content = $this->storageManager->getFromConfig($config, $path);
                if ($content !== null) {
                    return $content;
                }
            }
        }

        foreach ($this->storageManager->resolveLocalStorages($logicalDisk) as $localConfig) {
            $content = $this->storageManager->getFromConfig($localConfig, $path);
            if ($content !== null) {
                return $content;
            }
        }

        return $this->storageManager->getLegacyPublicContent($path);
    }

    protected function deleteLocalCopies(string $logicalDisk, string $path): void
    {
        foreach ($this->storageManager->resolveLocalStorages($logicalDisk) as $localConfig) {
            if ($this->storageManager->existsOnConfig($localConfig, $path)) {
                $this->storageManager->deleteFromConfig($localConfig, $path);
            }
        }

        if ($this->storageManager->legacyPublicExists($path)) {
            $this->storageManager->deleteLegacyPublic($path);
        }
    }

    protected function progressCacheKey(string $migrationId): string
    {
        return config('storage_inventory.migration_progress_cache_key').':'.$migrationId;
    }
}
