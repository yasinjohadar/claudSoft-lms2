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
        $warnings = [];
        $totalBytes = 0;

        foreach ($items as $item) {
            $logicalDisk = (string) ($item['disk'] ?? 'public');
            $path = (string) ($item['path'] ?? '');
            $location = $this->locationResolver->resolve($logicalDisk, $path);

            if ($location['status'] === StorageLocationResolver::STATUS_CLOUD_ONLY) {
                $skipped++;

                continue;
            }

            if ($location['status'] === StorageLocationResolver::STATUS_MISSING) {
                $missing++;

                continue;
            }

            $cloudTarget = $this->storageManager->resolveCloudPrimaryStorages($logicalDisk)->first();

            if (! $cloudTarget instanceof AppStorageConfig) {
                $warnings[] = [
                    'path' => $location['path'],
                    'disk' => $logicalDisk,
                    'message' => 'لا يوجد تخزين سحابي مرتبط بهذا القرص',
                ];

                continue;
            }

            $eligible[] = array_merge($item, [
                'status' => $location['status'],
                'size' => $location['size'],
                'cloud_target' => $cloudTarget->name,
                'locations' => $location['locations'],
            ]);
            $totalBytes += (int) ($location['size'] ?? 0);
        }

        return [
            'eligible_count' => count($eligible),
            'skipped_count' => $skipped,
            'missing_count' => $missing,
            'warning_count' => count($warnings),
            'total_bytes' => $totalBytes,
            'items' => $eligible,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return array{success: bool, action: string, message: string, path?: string, disk?: string}
     */
    public function migrateFile(string $logicalDisk, string $path, bool $deleteLocal = false): array
    {
        $location = $this->locationResolver->resolve($logicalDisk, $path);
        $path = $location['path'];

        if ($path === '') {
            return ['success' => false, 'action' => 'failed', 'message' => 'Empty path', 'path' => $path, 'disk' => $logicalDisk];
        }

        if ($location['status'] === StorageLocationResolver::STATUS_CLOUD_ONLY) {
            return ['success' => true, 'action' => 'skipped', 'message' => 'Already on cloud', 'path' => $path, 'disk' => $logicalDisk];
        }

        if ($location['status'] === StorageLocationResolver::STATUS_MISSING) {
            return ['success' => false, 'action' => 'failed', 'message' => 'File missing', 'path' => $path, 'disk' => $logicalDisk];
        }

        $cloudTarget = $this->storageManager->resolveCloudPrimaryStorages($logicalDisk)->first();

        if (! $cloudTarget instanceof AppStorageConfig) {
            return ['success' => false, 'action' => 'failed', 'message' => 'No cloud storage configured', 'path' => $path, 'disk' => $logicalDisk];
        }

        if ($this->storageManager->existsOnConfig($cloudTarget, $path)) {
            if ($deleteLocal) {
                $this->deleteLocalCopies($logicalDisk, $path);
            }

            return ['success' => true, 'action' => 'skipped', 'message' => 'Already exists on cloud', 'path' => $path, 'disk' => $logicalDisk];
        }

        $content = $this->readLocalContent($logicalDisk, $path, $location);

        if ($content === null) {
            return ['success' => false, 'action' => 'failed', 'message' => 'Could not read local file', 'path' => $path, 'disk' => $logicalDisk];
        }

        if (! $this->storageManager->putToConfig($cloudTarget, $path, $content)) {
            return ['success' => false, 'action' => 'failed', 'message' => 'Cloud upload failed', 'path' => $path, 'disk' => $logicalDisk];
        }

        if (! $this->storageManager->existsOnConfig($cloudTarget, $path)) {
            return ['success' => false, 'action' => 'failed', 'message' => 'Cloud verification failed', 'path' => $path, 'disk' => $logicalDisk];
        }

        $remoteSize = $this->storageManager->getFileSizeOnConfig($cloudTarget, $path);

        if ($remoteSize !== strlen($content)) {
            return ['success' => false, 'action' => 'failed', 'message' => 'Size mismatch after upload', 'path' => $path, 'disk' => $logicalDisk];
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

        return ['success' => true, 'action' => 'migrated', 'message' => 'Migrated to '.$cloudTarget->name, 'path' => $path, 'disk' => $logicalDisk];
    }

    /**
     * Re-check items and confirm cloud presence for both/local candidates.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function verify(array $items): array
    {
        $results = [
            'checked' => 0,
            'cloud_only' => 0,
            'local_only' => 0,
            'both' => 0,
            'missing' => 0,
            'cloud_confirmed' => 0,
            'items' => [],
        ];

        foreach ($items as $item) {
            $logicalDisk = (string) ($item['disk'] ?? 'public');
            $path = (string) ($item['path'] ?? '');
            $location = $this->locationResolver->resolve($logicalDisk, $path);
            $results['checked']++;

            $status = $location['status'];
            if (isset($results[$status])) {
                $results[$status]++;
            }

            $hasCloud = collect($location['locations'])->contains(fn (array $hit) => ! empty($hit['is_cloud']));
            if ($hasCloud) {
                $results['cloud_confirmed']++;
            }

            $results['items'][] = [
                'disk' => $logicalDisk,
                'path' => $location['path'],
                'status' => $status,
                'cloud_confirmed' => $hasCloud,
                'locations' => $location['locations'],
                'size' => $location['size'],
                'source_key' => $item['source_key'] ?? null,
                'entity_id' => $item['entity_id'] ?? null,
            ];
        }

        return $results;
    }

    /**
     * Delete local copies only when a fresh resolve confirms BOTH (cloud + local).
     *
     * @return array{success: bool, action: string, message: string, path?: string, disk?: string, after_status?: string}
     */
    public function cleanupLocalIfVerified(string $logicalDisk, string $path): array
    {
        return $this->deleteLocalCopy($logicalDisk, $path, allowOrphanLocal: false);
    }

    /**
     * Delete local copies only. Never deletes cloud objects.
     *
     * @return array{success: bool, action: string, message: string, path?: string, disk?: string, after_status?: string|null}
     */
    public function deleteLocalCopy(string $logicalDisk, string $path, bool $allowOrphanLocal = false): array
    {
        $location = $this->locationResolver->resolve($logicalDisk, $path);
        $path = $location['path'];

        $hasCloud = collect($location['locations'])->contains(fn (array $hit) => ! empty($hit['is_cloud']));
        $hasLocal = collect($location['locations'])->contains(fn (array $hit) => empty($hit['is_cloud']));

        if (! $hasLocal) {
            return [
                'success' => false,
                'action' => 'skipped',
                'message' => 'لا توجد نسخة محلية للحذف',
                'path' => $path,
                'disk' => $logicalDisk,
                'after_status' => $location['status'],
            ];
        }

        if ($location['status'] === StorageLocationResolver::STATUS_LOCAL_ONLY && ! $allowOrphanLocal) {
            return [
                'success' => false,
                'action' => 'skipped',
                'message' => 'رُفض: محلي فقط بدون سحابة — فعّل «يشمل المحلي فقط» صراحةً',
                'path' => $path,
                'disk' => $logicalDisk,
                'after_status' => $location['status'],
            ];
        }

        if ($location['status'] === StorageLocationResolver::STATUS_BOTH && ! $hasCloud) {
            return [
                'success' => false,
                'action' => 'skipped',
                'message' => 'رُفض: لم تُؤكَّد النسخة السحابية',
                'path' => $path,
                'disk' => $logicalDisk,
                'after_status' => $location['status'],
            ];
        }

        if (! in_array($location['status'], [
            StorageLocationResolver::STATUS_BOTH,
            StorageLocationResolver::STATUS_LOCAL_ONLY,
        ], true)) {
            return [
                'success' => false,
                'action' => 'skipped',
                'message' => 'رُفض: الحالة '.$location['status'].' لا تسمح بحذف محلي',
                'path' => $path,
                'disk' => $logicalDisk,
                'after_status' => $location['status'],
            ];
        }

        $cloudHitsBefore = array_values(array_filter(
            $location['locations'],
            fn (array $hit) => ! empty($hit['is_cloud'])
        ));

        $deleteReport = $this->deleteLocalCopies($logicalDisk, $path, $location);

        $after = $this->locationResolver->resolve($logicalDisk, $path);
        $localStillThere = collect($after['locations'])->contains(fn (array $hit) => empty($hit['is_cloud']));
        $cloudStillThere = collect($after['locations'])->contains(fn (array $hit) => ! empty($hit['is_cloud']));

        // If resolver lost the cloud hit briefly, re-check known cloud backends directly.
        if (! $cloudStillThere && $cloudHitsBefore !== []) {
            $cloudStillThere = $this->cloudHitsStillExist($cloudHitsBefore, $path);
            if ($cloudStillThere && ! $localStillThere) {
                $after['status'] = StorageLocationResolver::STATUS_CLOUD_ONLY;
            }
        }

        if ($localStillThere) {
            $remaining = collect($after['locations'])
                ->filter(fn (array $hit) => empty($hit['is_cloud']))
                ->map(fn (array $hit) => ($hit['storage_name'] ?? 'local').'/'.($hit['driver'] ?? '?'))
                ->implode(', ');

            Log::warning('Storage local delete failed — local still present', [
                'disk' => $logicalDisk,
                'path' => $path,
                'delete_report' => $deleteReport,
                'remaining_local' => $remaining,
            ]);

            return [
                'success' => false,
                'action' => 'failed',
                'message' => 'فشلت إزالة المحلي — ما زال موجوداً في: '.($remaining ?: 'غير معروف')
                    .' | محاولات الحذف: '.($deleteReport['summary'] ?? ''),
                'path' => $path,
                'disk' => $logicalDisk,
                'after_status' => $after['status'],
            ];
        }

        if ($location['status'] === StorageLocationResolver::STATUS_BOTH && ! $cloudStillThere) {
            Log::warning('Storage local deleted but cloud not confirmed after', [
                'disk' => $logicalDisk,
                'path' => $path,
                'delete_report' => $deleteReport,
            ]);

            return [
                'success' => false,
                'action' => 'failed',
                'message' => 'حُذف المحلي لكن تعذّر تأكيد السحابة بعدها — راجع الملف على S3 يدوياً',
                'path' => $path,
                'disk' => $logicalDisk,
                'after_status' => $after['status'],
            ];
        }

        Log::info('Storage local copy cleaned', [
            'disk' => $logicalDisk,
            'path' => $path,
            'after_status' => $after['status'],
            'delete_report' => $deleteReport,
            'allow_orphan' => $allowOrphanLocal,
        ]);

        return [
            'success' => true,
            'action' => 'cleaned',
            'message' => $cloudStillThere
                ? 'حُذفت النسخة المحلية — بقيت السحابة فقط'
                : 'حُذف المحلي فقط (لم تكن هناك نسخة سحابية)',
            'path' => $path,
            'disk' => $logicalDisk,
            'after_status' => $after['status'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $cloudHits
     */
    protected function cloudHitsStillExist(array $cloudHits, string $path): bool
    {
        foreach ($cloudHits as $hit) {
            $configId = $hit['storage_config_id'] ?? null;
            if ($configId === null) {
                continue;
            }

            $config = AppStorageConfig::find($configId);
            if ($config && $this->storageManager->existsOnConfig($config, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function cleanupLocalBatch(array $items, bool $allowOrphanLocal = false): array
    {
        $results = [
            'cleaned' => 0,
            'skipped' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($items as $item) {
            $outcome = $this->deleteLocalCopy(
                (string) ($item['disk'] ?? 'public'),
                (string) ($item['path'] ?? ''),
                $allowOrphanLocal
            );

            match ($outcome['action']) {
                'cleaned' => $results['cleaned']++,
                'skipped' => $results['skipped']++,
                default => $results['failed']++,
            };

            $results['details'][] = $outcome;
        }

        return $results;
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
            'details' => [],
        ];

        foreach ($items as $item) {
            $outcome = $this->migrateFile(
                (string) ($item['disk'] ?? 'public'),
                (string) ($item['path'] ?? ''),
                $deleteLocal
            );

            match ($outcome['action']) {
                'migrated' => $results['migrated']++,
                'skipped' => $results['skipped']++,
                default => $results['failed']++,
            };

            $results['details'][] = $outcome;

            if (! $outcome['success'] && ($outcome['action'] ?? '') === 'failed') {
                $results['errors'][] = [
                    'path' => $item['path'] ?? ($outcome['path'] ?? ''),
                    'disk' => $item['disk'] ?? ($outcome['disk'] ?? ''),
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

    /**
     * Delete every known local hit for a path. Never touches cloud backends.
     *
     * @param  array<string, mixed>|null  $location  Fresh resolve result when available
     * @return array{attempts: array<int, array<string, mixed>>, summary: string}
     */
    protected function deleteLocalCopies(string $logicalDisk, string $path, ?array $location = null): array
    {
        $attempts = [];
        $location ??= $this->locationResolver->resolve($logicalDisk, $path);
        $mappedLocals = $this->storageManager->resolveLocalStorages($logicalDisk)->keyBy('id');
        $seenConfigIds = [];

        foreach ($location['locations'] ?? [] as $hit) {
            if (! empty($hit['is_cloud'])) {
                continue;
            }

            $configId = $hit['storage_config_id'] ?? null;

            if ($configId === null) {
                $ok = $this->storageManager->deleteLegacyPublic($path);
                $physical = $this->deletePhysicalPublicFile($path);
                $attempts[] = [
                    'target' => 'legacy_public',
                    'ok' => $ok || $physical,
                    'flysystem' => $ok,
                    'physical' => $physical,
                ];

                continue;
            }

            $seenConfigIds[$configId] = true;
            $config = $mappedLocals->get($configId) ?? AppStorageConfig::find($configId);

            if (! $config) {
                $attempts[] = ['target' => 'config_'.$configId, 'ok' => false, 'error' => 'config missing'];

                continue;
            }

            if ($this->locationResolver->isCloudDriver($config->driver)) {
                $attempts[] = ['target' => $config->name, 'ok' => false, 'error' => 'skipped cloud config'];

                continue;
            }

            $existed = $this->storageManager->existsOnConfig($config, $path);
            $ok = $existed ? $this->storageManager->deleteFromConfig($config, $path) : true;
            $attempts[] = [
                'target' => $config->name.'#'.$config->id,
                'ok' => $ok,
                'existed' => $existed,
            ];
        }

        foreach ($mappedLocals as $localConfig) {
            if (isset($seenConfigIds[$localConfig->id])) {
                continue;
            }

            if (! $this->storageManager->existsOnConfig($localConfig, $path)) {
                continue;
            }

            $ok = $this->storageManager->deleteFromConfig($localConfig, $path);
            $attempts[] = [
                'target' => 'mapped:'.$localConfig->name.'#'.$localConfig->id,
                'ok' => $ok,
            ];
        }

        if ($this->storageManager->legacyPublicExists($path)) {
            $ok = $this->storageManager->deleteLegacyPublic($path);
            $physical = $this->deletePhysicalPublicFile($path);
            $attempts[] = [
                'target' => 'legacy_public_fallback',
                'ok' => $ok || $physical,
                'flysystem' => $ok,
                'physical' => $physical,
            ];
        } else {
            $physical = $this->deletePhysicalPublicFile($path);
            if ($physical) {
                $attempts[] = ['target' => 'physical_public', 'ok' => true];
            }
        }

        $summary = collect($attempts)
            ->map(fn (array $a) => ($a['target'] ?? '?').':'.(! empty($a['ok']) ? 'ok' : 'fail'))
            ->implode(', ');

        return [
            'attempts' => $attempts,
            'summary' => $summary !== '' ? $summary : 'no-targets',
        ];
    }

    /**
     * Last-resort unlink under storage/app/public (Laravel public disk root).
     */
    protected function deletePhysicalPublicFile(string $path): bool
    {
        $path = ltrim(str_replace(['\\', '..'], ['/', ''], $path), '/');
        if ($path === '') {
            return false;
        }

        $full = storage_path('app/public/'.$path);
        $realBase = realpath(storage_path('app/public'));
        $realFile = realpath($full);

        if ($realBase === false || $realFile === false) {
            if (is_file($full)) {
                return @unlink($full);
            }

            return false;
        }

        if (! str_starts_with($realFile, $realBase)) {
            return false;
        }

        return is_file($realFile) ? @unlink($realFile) : false;
    }

    protected function progressCacheKey(string $migrationId): string
    {
        return config('storage_inventory.migration_progress_cache_key').':'.$migrationId;
    }
}
