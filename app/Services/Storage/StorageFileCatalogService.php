<?php

namespace App\Services\Storage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class StorageFileCatalogService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function collectReferences(?string $sourceKey = null, ?string $disk = null): array
    {
        $references = [];

        foreach ($this->sources() as $source) {
            if ($sourceKey !== null && $source['key'] !== $sourceKey) {
                continue;
            }

            if ($disk !== null && $source['disk'] !== $disk) {
                continue;
            }

            $references = array_merge($references, $this->collectFromSource($source));
        }

        return $references;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function collectForPhase(string $phase): array
    {
        $sourceKeys = config("storage_inventory.phases.{$phase}", []);

        if ($sourceKeys === []) {
            return [];
        }

        $references = [];

        foreach ($sourceKeys as $sourceKey) {
            $references = array_merge($references, $this->collectReferences($sourceKey));
        }

        return $references;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function collectFromSource(array $source): array
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $source['model'];
        $column = $source['column'];
        $pathFilter = $source['path_filter'] ?? null;

        $query = $modelClass::query()
            ->whereNotNull($column)
            ->where($column, '!=', '');

        if ($pathFilter !== null) {
            $query->where($column, 'like', $pathFilter.'%');
        }

        $items = [];

        $query->select(['id', $column])
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$items, $source, $column) {
                foreach ($rows as $row) {
                    $path = (string) $row->{$column};

                    if ($path === '') {
                        continue;
                    }

                    $items[] = [
                        'source_key' => $source['key'],
                        'source_label' => $source['label'],
                        'entity_id' => $row->id,
                        'entity_label' => $source['label'].' #'.$row->id,
                        'entity_url' => $this->buildEntityUrl($source, $row->id),
                        'disk' => $source['disk'],
                        'path' => $path,
                        'path_prefix' => $source['path_prefix'],
                    ];
                }
            });

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sources(): array
    {
        return config('storage_inventory.sources', []);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function phases(): array
    {
        return config('storage_inventory.phases', []);
    }

    protected function buildEntityUrl(array $source, int $entityId): ?string
    {
        $routeName = $source['route_name'] ?? null;
        $routeParam = $source['route_param'] ?? null;

        if ($routeName === null || $routeParam === null) {
            return null;
        }

        if (! Route::has($routeName)) {
            return null;
        }

        return route($routeName, [$routeParam => $entityId]);
    }
}
