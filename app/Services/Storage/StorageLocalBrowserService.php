<?php

namespace App\Services\Storage;

class StorageLocalBrowserService
{
    public function __construct(
        protected StorageCloudBrowserService $pathHelper,
    ) {}

    public function rootPath(): string
    {
        return storage_path('app/public');
    }

    public function rootLabel(): string
    {
        return 'storage/app/public';
    }

    /**
     * @return array<int, array{label: string, disk: string, path: string}>
     */
    public function folderShortcuts(): array
    {
        return $this->pathHelper->folderShortcuts();
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
    public function browse(string $path = ''): array
    {
        $path = $this->pathHelper->normalizePath($path);
        $absoluteDir = $this->absolutePathFor($path);

        if (! is_dir($absoluteDir)) {
            throw new \RuntimeException('المجلد غير موجود على السيرفر المحلي.');
        }

        $directories = [];
        $files = [];

        foreach (scandir($absoluteDir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $absoluteDir.DIRECTORY_SEPARATOR.$entry;
            $relativePath = $path === '' ? $entry : $path.'/'.$entry;

            if (is_dir($fullPath)) {
                $directories[] = [
                    'name' => $entry,
                    'path' => $relativePath,
                ];

                continue;
            }

            if (is_file($fullPath)) {
                $files[] = [
                    'name' => $entry,
                    'path' => $relativePath,
                    'size' => (int) filesize($fullPath),
                    'last_modified' => filemtime($fullPath) ?: null,
                ];
            }
        }

        usort($directories, fn (array $a, array $b) => strnatcasecmp($a['name'], $b['name']));
        usort($files, fn (array $a, array $b) => strnatcasecmp($a['name'], $b['name']));

        return [
            'path' => $path,
            'breadcrumbs' => $this->pathHelper->breadcrumbs($path),
            'directories' => $directories,
            'files' => $files,
            'summary' => [
                'directory_count' => count($directories),
                'file_count' => count($files),
                'total_bytes' => (int) array_sum(array_column($files, 'size')),
            ],
        ];
    }

    protected function absolutePathFor(string $relativePath): string
    {
        $root = rtrim(str_replace('\\', '/', $this->rootPath()), '/');
        $rootReal = realpath($root);

        if ($rootReal === false) {
            if ($relativePath !== '') {
                throw new \RuntimeException('مجلد التخزين المحلي غير موجود.');
            }

            return $root;
        }

        if ($relativePath === '') {
            return $rootReal;
        }

        $target = $rootReal.'/'.str_replace('\\', '/', $relativePath);
        $targetReal = realpath($target);

        if ($targetReal === false) {
            throw new \RuntimeException('المسار غير موجود على السيرفر المحلي.');
        }

        if (! str_starts_with(str_replace('\\', '/', $targetReal), str_replace('\\', '/', $rootReal))) {
            throw new \InvalidArgumentException('مسار غير صالح.');
        }

        return $targetReal;
    }
}
