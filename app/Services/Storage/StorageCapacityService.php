<?php

namespace App\Services\Storage;

use App\Models\AppStorageConfig;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class StorageCapacityService
{
    public function __construct(
        protected StorageCloudBrowserService $cloudBrowser,
        protected StorageLocalBrowserService $localBrowser,
        protected AppStorageManager $storageManager,
    ) {}

    public function getCachedSummary(bool $refresh = false): array
    {
        $cacheKey = (string) config('storage_inventory.capacity_cache_key', 'storage_capacity_summary');
        $ttl = (int) config('storage_inventory.capacity_cache_ttl', 3600);

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, fn () => $this->calculateSummary());
    }

    /**
     * @return array{
     *     local: array{bytes: int, files: int, root: string, exists: bool, error: string|null},
     *     cloud: array{bytes: int, files: int, configs: array<int, array{id: int, name: string, driver: string, bytes: int, files: int, error: string|null}>, error: string|null},
     *     scanned_at: string
     * }
     */
    public function calculateSummary(): array
    {
        $local = $this->calculateLocalUsage();
        $cloud = $this->calculateCloudUsage();

        return [
            'local' => $local,
            'cloud' => $cloud,
            'scanned_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * @return array{bytes: int, files: int, root: string, exists: bool, error: string|null}
     */
    public function calculateLocalUsage(): array
    {
        $root = $this->localBrowser->rootPath();
        $rootLabel = $this->localBrowser->rootLabel();

        if (! is_dir($root)) {
            return [
                'bytes' => 0,
                'files' => 0,
                'root' => $rootLabel,
                'exists' => false,
                'error' => null,
            ];
        }

        try {
            $bytes = 0;
            $files = 0;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $bytes += (int) $file->getSize();
                $files++;
            }

            return [
                'bytes' => $bytes,
                'files' => $files,
                'root' => $rootLabel,
                'exists' => true,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'bytes' => 0,
                'files' => 0,
                'root' => $rootLabel,
                'exists' => true,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{
     *     bytes: int,
     *     files: int,
     *     configs: array<int, array{id: int, name: string, driver: string, bytes: int, files: int, error: string|null}>,
     *     error: string|null
     * }
     */
    public function calculateCloudUsage(): array
    {
        $configs = $this->cloudBrowser->availableConfigs();
        $configSummaries = [];
        $totalBytes = 0;
        $totalFiles = 0;
        $errors = [];

        foreach ($configs as $config) {
            $usage = $this->calculateConfigUsage($config);
            $configSummaries[] = [
                'id' => (int) $config->id,
                'name' => (string) $config->name,
                'driver' => (string) $config->driver,
                'bytes' => $usage['bytes'],
                'files' => $usage['files'],
                'error' => $usage['error'],
            ];

            if ($usage['error']) {
                $errors[] = $config->name.': '.$usage['error'];
            }

            $totalBytes += $usage['bytes'];
            $totalFiles += $usage['files'];
        }

        return [
            'bytes' => $totalBytes,
            'files' => $totalFiles,
            'configs' => $configSummaries,
            'error' => $errors === [] ? null : implode(' | ', $errors),
        ];
    }

    /**
     * @return array{bytes: int, files: int, error: string|null}
     */
    public function calculateConfigUsage(AppStorageConfig $config): array
    {
        try {
            $filesystem = $this->storageManager->getFilesystemForConfig($config);
            $usage = $this->sumFilesystemBytes($filesystem);

            return [
                'bytes' => $usage['bytes'],
                'files' => $usage['files'],
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'bytes' => 0,
                'files' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{bytes: int, files: int}
     */
    protected function sumFilesystemBytes(Filesystem $filesystem): array
    {
        $bytes = 0;
        $files = 0;

        foreach ($filesystem->allFiles('') as $path) {
            try {
                $bytes += (int) $filesystem->size($path);
                $files++;
            } catch (\Throwable) {
                continue;
            }
        }

        return [
            'bytes' => $bytes,
            'files' => $files,
        ];
    }
}
