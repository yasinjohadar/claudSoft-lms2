<?php

namespace App\Services\Storage;

use App\Models\AppStorageConfig;
use Illuminate\Support\Collection;

class StorageLocationResolver
{
    public const STATUS_CLOUD_ONLY = 'cloud_only';

    public const STATUS_LOCAL_ONLY = 'local_only';

    public const STATUS_BOTH = 'both';

    public const STATUS_MISSING = 'missing';

    public function __construct(
        protected AppStorageManager $storageManager,
    ) {}

    /**
     * @return array{
     *     found: bool,
     *     status: string,
     *     logical_disk: string,
     *     path: string,
     *     locations: array<int, array<string, mixed>>,
     *     storage_config_id: int|null,
     *     storage_name: string|null,
     *     size: int
     * }
     */
    public function resolve(string $logicalDisk, string $path): array
    {
        $path = $this->normalizePath($path);

        if ($path === '') {
            return $this->emptyResult($logicalDisk, $path);
        }

        $hits = [];

        foreach ($this->storagesToProbe($logicalDisk) as $config) {
            if ($this->storageManager->existsOnConfig($config, $path)) {
                $hits[$this->hitKey($config)] = [
                    'storage_config_id' => $config->id,
                    'storage_name' => $config->name,
                    'driver' => $config->driver,
                    'is_cloud' => $this->isCloudDriver($config->driver),
                    'size' => $this->storageManager->getFileSizeOnConfig($config, $path),
                ];
            }
        }

        if ($this->shouldProbeLegacyPublic($logicalDisk) && $this->storageManager->legacyPublicExists($path)) {
            $hits['legacy_public'] = [
                'storage_config_id' => null,
                'storage_name' => 'Laravel public disk',
                'driver' => 'local',
                'is_cloud' => false,
                'size' => $this->storageManager->legacyPublicSize($path),
            ];
        }

        $locations = array_values($hits);
        $cloudHits = array_values(array_filter($locations, fn (array $hit) => $hit['is_cloud']));
        $localHits = array_values(array_filter($locations, fn (array $hit) => ! $hit['is_cloud']));

        $status = match (true) {
            $locations === [] => self::STATUS_MISSING,
            $cloudHits !== [] && $localHits === [] => self::STATUS_CLOUD_ONLY,
            $cloudHits === [] && $localHits !== [] => self::STATUS_LOCAL_ONLY,
            default => self::STATUS_BOTH,
        };

        $primaryHit = $locations[0] ?? null;

        return [
            'found' => $locations !== [],
            'status' => $status,
            'logical_disk' => $logicalDisk,
            'path' => $path,
            'locations' => $locations,
            'storage_config_id' => $primaryHit['storage_config_id'] ?? null,
            'storage_name' => $primaryHit['storage_name'] ?? null,
            'size' => (int) ($primaryHit['size'] ?? 0),
        ];
    }

    public function isCloudDriver(string $driver): bool
    {
        return in_array($driver, config('storage_inventory.cloud_drivers', []), true);
    }

    protected function storagesToProbe(string $logicalDisk): Collection
    {
        $chain = $this->storageManager->resolveFailoverStorages($logicalDisk);

        if ($chain->isNotEmpty()) {
            return $chain->unique('id')->values();
        }

        $cloud = AppStorageConfig::query()
            ->where('is_active', true)
            ->whereIn('driver', config('storage_inventory.cloud_drivers', ['s3']))
            ->orderByDesc('priority')
            ->get();

        $local = AppStorageConfig::query()
            ->where('is_active', true)
            ->where('driver', 'local')
            ->orderByDesc('priority')
            ->get();

        return $cloud->merge($local)->unique('id')->values();
    }

    protected function shouldProbeLegacyPublic(string $logicalDisk): bool
    {
        return in_array($logicalDisk, [
            'public',
            'blog_images',
            'course_images',
            'course_thumbnails',
            'gift_images',
            'images',
            'payment_receipts',
        ], true);
    }

    protected function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsedPath) ? ltrim($parsedPath, '/') : $path;
        }

        $path = ltrim($path, '/');
        $path = preg_replace('#^storage/#', '', $path);

        return $path;
    }

    protected function hitKey(AppStorageConfig $config): string
    {
        return 'config_'.$config->id;
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyResult(string $logicalDisk, string $path): array
    {
        return [
            'found' => false,
            'status' => self::STATUS_MISSING,
            'logical_disk' => $logicalDisk,
            'path' => $path,
            'locations' => [],
            'storage_config_id' => null,
            'storage_name' => null,
            'size' => 0,
        ];
    }
}
