<?php

namespace App\Services\Storage;

use App\Models\AppStorageConfig;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StorageCloudBrowserService
{
    public function __construct(
        protected AppStorageManager $storageManager,
    ) {}

    /**
     * @return Collection<int, AppStorageConfig>
     */
    public function availableConfigs(): Collection
    {
        $cloudDrivers = config('storage_inventory.cloud_drivers', ['s3']);

        return AppStorageConfig::query()
            ->where('is_active', true)
            ->whereIn('driver', $cloudDrivers)
            ->orderByDesc('priority')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, array{label: string, disk: string, path: string}>
     */
    public function folderShortcuts(): array
    {
        return collect(config('storage_inventory.sources', []))
            ->filter(fn (array $source) => ! empty($source['path_prefix']))
            ->map(fn (array $source) => [
                'label' => $source['label'] ?? ($source['key'] ?? ''),
                'disk' => $source['disk'] ?? '',
                'path' => rtrim((string) ($source['path_prefix'] ?? ''), '/'),
            ])
            ->unique(fn (array $item) => $item['path'])
            ->sortBy('label')
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     path: string,
     *     breadcrumbs: array<int, array{label: string, path: string}>,
     *     directories: array<int, array{name: string, path: string}>,
     *     files: array<int, array{name: string, path: string, size: int, last_modified: int|null}>,
     *     summary: array{directory_count: int, file_count: int, total_bytes: int}
     * }
     */
    public function browse(AppStorageConfig $config, string $path = ''): array
    {
        $path = $this->normalizePath($path);
        $filesystem = $this->storageManager->getFilesystemForConfig($config);

        $directories = collect($filesystem->directories($path))
            ->map(fn (string $dirPath) => [
                'name' => basename($dirPath),
                'path' => $dirPath,
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $files = collect($filesystem->files($path))
            ->map(function (string $filePath) use ($filesystem) {
                return [
                    'name' => basename($filePath),
                    'path' => $filePath,
                    'size' => $this->safeSize($filesystem, $filePath),
                    'last_modified' => $this->safeLastModified($filesystem, $filePath),
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return [
            'path' => $path,
            'breadcrumbs' => $this->breadcrumbs($path),
            'directories' => $directories,
            'files' => $files,
            'summary' => [
                'directory_count' => count($directories),
                'file_count' => count($files),
                'total_bytes' => (int) collect($files)->sum('size'),
            ],
        ];
    }

    public function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = trim($path, '/');

        if ($path === '' || $path === '.') {
            return '';
        }

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                throw new \InvalidArgumentException('مسار غير صالح.');
            }
        }

        return $path;
    }

    /**
     * @return array<int, array{label: string, path: string}>
     */
    public function breadcrumbs(string $path): array
    {
        $path = $this->normalizePath($path);
        $crumbs = [
            ['label' => 'الجذر', 'path' => ''],
        ];

        if ($path === '') {
            return $crumbs;
        }

        $accumulated = '';
        foreach (explode('/', $path) as $segment) {
            $accumulated = $accumulated === '' ? $segment : $accumulated.'/'.$segment;
            $crumbs[] = [
                'label' => $segment,
                'path' => $accumulated,
            ];
        }

        return $crumbs;
    }

    protected function safeSize(Filesystem $filesystem, string $path): int
    {
        try {
            return (int) $filesystem->size($path);
        } catch (\Throwable $e) {
            Log::debug('StorageCloudBrowserService: size failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    protected function safeLastModified(Filesystem $filesystem, string $path): ?int
    {
        try {
            return $filesystem->lastModified($path);
        } catch (\Throwable $e) {
            Log::debug('StorageCloudBrowserService: lastModified failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
