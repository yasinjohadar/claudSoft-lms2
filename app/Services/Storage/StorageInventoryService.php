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
     * @return array{items: array<int, array<string, mixed>>, summary: array<string, mixed>, scanned_at: string}
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
            $items[] = $this->enrichItem(array_merge($reference, $location));
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
     * @return array{items: array<int, array<string, mixed>>, summary: array<string, mixed>, scanned_at: string|null}
     */
    public function getCachedScan(): array
    {
        $cached = Cache::get(config('storage_inventory.inventory_cache_key'), [
            'items' => [],
            'summary' => $this->summarize([]),
            'scanned_at' => null,
        ]);

        $cached['items'] = array_map(fn (array $item) => $this->enrichItem($item), $cached['items'] ?? []);
        $cached['summary'] = $this->summarize($cached['items']);

        return $cached;
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
     * @return array<string, mixed>
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
            'total_bytes' => 0,
            'by_disk' => [],
            'by_source' => [],
            'by_status' => [],
        ];

        foreach ($items as $item) {
            $status = $item['status'] ?? StorageLocationResolver::STATUS_MISSING;
            $size = (int) ($item['size'] ?? 0);
            $disk = (string) ($item['disk'] ?? 'unknown');
            $sourceKey = (string) ($item['source_key'] ?? 'unknown');
            $sourceLabel = (string) ($item['source_label'] ?? $sourceKey);

            if (isset($summary[$status])) {
                $summary[$status]++;
            }

            $summary['total_bytes'] += $size;

            if ($status === StorageLocationResolver::STATUS_LOCAL_ONLY) {
                $summary['local_only_bytes'] += $size;
            }

            if ($status === StorageLocationResolver::STATUS_BOTH) {
                $summary['both_bytes'] += $size;
            }

            if (! isset($summary['by_disk'][$disk])) {
                $summary['by_disk'][$disk] = ['count' => 0, 'bytes' => 0, 'by_status' => []];
            }
            $summary['by_disk'][$disk]['count']++;
            $summary['by_disk'][$disk]['bytes'] += $size;
            $summary['by_disk'][$disk]['by_status'][$status] = ($summary['by_disk'][$disk]['by_status'][$status] ?? 0) + 1;

            if (! isset($summary['by_source'][$sourceKey])) {
                $summary['by_source'][$sourceKey] = [
                    'label' => $sourceLabel,
                    'count' => 0,
                    'bytes' => 0,
                    'by_status' => [],
                ];
            }
            $summary['by_source'][$sourceKey]['count']++;
            $summary['by_source'][$sourceKey]['bytes'] += $size;
            $summary['by_source'][$sourceKey]['by_status'][$status] = ($summary['by_source'][$sourceKey]['by_status'][$status] ?? 0) + 1;

            if (! isset($summary['by_status'][$status])) {
                $summary['by_status'][$status] = ['count' => 0, 'bytes' => 0];
            }
            $summary['by_status'][$status]['count']++;
            $summary['by_status'][$status]['bytes'] += $size;
        }

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public function enrichItem(array $item): array
    {
        $status = $item['status'] ?? StorageLocationResolver::STATUS_MISSING;
        $item['status'] = $status;
        $item['status_label'] = $this->statusLabel($status);
        $item['size'] = (int) ($item['size'] ?? 0);
        $item['size_human'] = $this->formatBytes($item['size']);
        $item['locations'] = array_map(function (array $loc) {
            $loc['size'] = (int) ($loc['size'] ?? 0);
            $loc['size_human'] = $this->formatBytes($loc['size']);
            $loc['kind_label'] = ! empty($loc['is_cloud']) ? 'سحابة' : 'محلي';

            return $loc;
        }, $item['locations'] ?? []);

        return $item;
    }

    public function statusLabel(string $status): string
    {
        return (string) (config("storage_inventory.status_labels.{$status}") ?? $status);
    }

    /**
     * @return array<string, string>
     */
    public function statusLabels(): array
    {
        return config('storage_inventory.status_labels', [
            StorageLocationResolver::STATUS_CLOUD_ONLY => 'سحابة فقط',
            StorageLocationResolver::STATUS_LOCAL_ONLY => 'محلي فقط',
            StorageLocationResolver::STATUS_BOTH => 'نسختان (محلي + سحابة)',
            StorageLocationResolver::STATUS_MISSING => 'مفقود',
        ]);
    }

    public function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $bytes = max(0, (float) $bytes);

        if ($bytes < 1024) {
            return number_format($bytes, 0).' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units));
        $value = $bytes / (1024 ** $power);

        return number_format($value, $precision).' '.$units[$power - 1];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function exportRows(array $items): array
    {
        return array_map(function (array $item) {
            $locations = collect($item['locations'] ?? [])
                ->map(fn (array $loc) => sprintf(
                    '%s (%s/%s, %s)',
                    $loc['storage_name'] ?? 'unknown',
                    $loc['driver'] ?? '?',
                    ! empty($loc['is_cloud']) ? 'cloud' : 'local',
                    $loc['size_human'] ?? $this->formatBytes((int) ($loc['size'] ?? 0))
                ))
                ->implode(' | ');

            return [
                'source' => $item['source_label'] ?? '',
                'source_key' => $item['source_key'] ?? '',
                'entity_id' => $item['entity_id'] ?? '',
                'disk' => $item['disk'] ?? '',
                'path' => $item['path'] ?? '',
                'status' => $item['status'] ?? '',
                'status_label' => $item['status_label'] ?? $this->statusLabel((string) ($item['status'] ?? '')),
                'storage_name' => $item['storage_name'] ?? '',
                'size' => $item['size'] ?? 0,
                'size_human' => $item['size_human'] ?? $this->formatBytes((int) ($item['size'] ?? 0)),
                'locations' => $locations,
                'entity_url' => $item['entity_url'] ?? '',
            ];
        }, $items);
    }
}
