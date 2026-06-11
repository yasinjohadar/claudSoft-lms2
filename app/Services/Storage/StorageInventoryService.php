<?php

namespace App\Services\Storage;

use Illuminate\Support\Facades\Cache;

class StorageInventoryService
{
    public function __construct(
        protected StorageFileCatalogService $catalog,
        protected StorageLocationResolver $locationResolver,
    ) {}

    /**
     * @return array{items: array<int, array<string, mixed>>, summary: array<string, int|float>, scanned_at: string}
     */
    public function scan(?string $disk = null, ?string $sourceKey = null, ?string $phase = null): array
    {
        if ($phase !== null) {
            $references = $this->catalog->collectForPhase($phase);
        } else {
            $references = $this->catalog->collectReferences($sourceKey, $disk);
        }

        $items = [];

        foreach ($references as $reference) {
            $location = $this->locationResolver->resolve($reference['disk'], $reference['path']);
            $items[] = array_merge($reference, $location);
        }

        $result = [
            'items' => $items,
            'summary' => $this->summarize($items),
            'scanned_at' => now()->toIso8601String(),
        ];

        Cache::put(
            config('storage_inventory.inventory_cache_key'),
            $result,
            config('storage_inventory.inventory_cache_ttl', 600)
        );

        return $result;
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, summary: array<string, int|float>, scanned_at: string|null}
     */
    public function getCachedScan(): array
    {
        return Cache::get(config('storage_inventory.inventory_cache_key'), [
            'items' => [],
            'summary' => $this->summarize([]),
            'scanned_at' => null,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function filterItems(array $items, ?string $disk = null, ?string $sourceKey = null, ?string $status = null): array
    {
        return array_values(array_filter($items, function (array $item) use ($disk, $sourceKey, $status) {
            if ($disk !== null && ($item['disk'] ?? null) !== $disk) {
                return false;
            }

            if ($sourceKey !== null && ($item['source_key'] ?? null) !== $sourceKey) {
                return false;
            }

            if ($status !== null && ($item['status'] ?? null) !== $status) {
                return false;
            }

            return true;
        }));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, int|float>
     */
    public function summarize(array $items): array
    {
        $summary = [
            'total' => count($items),
            'cloud_only' => 0,
            'local_only' => 0,
            'both' => 0,
            'missing' => 0,
            'local_only_bytes' => 0,
            'both_bytes' => 0,
        ];

        foreach ($items as $item) {
            $status = $item['status'] ?? StorageLocationResolver::STATUS_MISSING;

            if (isset($summary[$status])) {
                $summary[$status]++;
            }

            if (in_array($status, [StorageLocationResolver::STATUS_LOCAL_ONLY, StorageLocationResolver::STATUS_BOTH], true)) {
                $summary[$status === StorageLocationResolver::STATUS_LOCAL_ONLY ? 'local_only_bytes' : 'both_bytes'] += (int) ($item['size'] ?? 0);
            }
        }

        return $summary;
    }
}
