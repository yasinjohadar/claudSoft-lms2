<?php

namespace App\Console\Commands;

use App\Models\AppStorageConfig;
use App\Models\StudentGift;
use App\Services\Storage\AppStorageManager;
use App\Services\Storage\StorageLocationResolver;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FixGiftImagePathsCommand extends Command
{
    private const LOGICAL_DISK = 'gift_images';

    protected $signature = 'gifts:fix-image-paths
                            {--dry-run : عرض التغييرات دون تنفيذها}
                            {--diagnose : عرض تفاصيل أماكن البحث عن كل مسار}';

    protected $description = 'إصلاح مسارات صور الهدايا التي أُنشئت كمجلدات .webp بدلاً من ملفات';

    public function handle(AppStorageManager $storageManager, StorageLocationResolver $resolver): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $diagnose = (bool) $this->option('diagnose');
        $targets = $this->storageTargets($storageManager);
        $fixed = 0;
        $skipped = 0;

        if ($targets->isEmpty()) {
            $this->error('No storage targets found for gift_images.');

            return self::FAILURE;
        }

        $this->line('Storage targets: '.$targets->pluck('label')->implode(', '));
        $this->newLine();

        StudentGift::query()
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($gifts) use ($resolver, $targets, $dryRun, $diagnose, &$fixed, &$skipped) {
                foreach ($gifts as $gift) {
                    $relativePath = ltrim($gift->image_path, '/');
                    $match = $this->findFixableNestedPath($targets, $relativePath);

                    if ($match === null) {
                        if ($this->pathIsValidFile($targets, $relativePath)) {
                            if ($diagnose) {
                                $this->line("Gift #{$gift->id}: OK — {$relativePath}");
                                $this->diagnoseGiftPath($resolver, $targets, $gift->id, $relativePath);
                            }

                            $skipped++;

                            continue;
                        }

                        $this->warn("Gift #{$gift->id}: path missing on disk — {$relativePath}");

                        if ($diagnose) {
                            $this->diagnoseGiftPath($resolver, $targets, $gift->id, $relativePath);
                        }

                        $skipped++;

                        continue;
                    }

                    $newRelativePath = $this->flattenedPath($relativePath, $match['inner_path']);

                    if ($dryRun) {
                        $this->line("[dry-run] Gift #{$gift->id}: would move {$match['inner_path']} → {$newRelativePath} ({$match['label']})");
                        $fixed++;

                        continue;
                    }

                    $applied = $this->applyFix(
                        $match['filesystem'],
                        $match['config'],
                        $relativePath,
                        $match['inner_path'],
                        $newRelativePath
                    );

                    if ($applied === null) {
                        $this->error("Gift #{$gift->id}: failed to move file to {$newRelativePath}");
                        $skipped++;

                        continue;
                    }

                    $gift->update(['image_path' => $applied]);
                    $this->info("Gift #{$gift->id}: fixed → {$applied}");
                    $fixed++;
                }
            });

        $localFixed = $this->fixUnlinkedLocalNestedDirectories($dryRun);
        $fixed += $localFixed;

        $orphans = $this->scanOrphanWebpDirectories($targets);

        if ($orphans !== []) {
            $this->newLine();
            $this->warn('Remaining nested directories under gifts/images/:');
            foreach ($orphans as $orphan) {
                $this->line("  - {$orphan}");
            }
        }

        $this->newLine();
        $this->info("Done. Fixed: {$fixed}, skipped: {$skipped}".($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, array{label: string, config: ?AppStorageConfig, filesystem: Filesystem}>
     */
    private function storageTargets(AppStorageManager $storageManager): Collection
    {
        $targets = collect();

        $targets->push([
            'label' => 'Laravel public disk',
            'config' => null,
            'filesystem' => Storage::disk('public'),
        ]);

        $seenLabels = ['Laravel public disk' => true];

        foreach ($storageManager->resolveFailoverStorages(self::LOGICAL_DISK) as $config) {
            $label = $config->name.' ('.$config->driver.')';

            if (isset($seenLabels[$label])) {
                continue;
            }

            $seenLabels[$label] = true;

            $targets->push([
                'label' => $label,
                'config' => $config,
                'filesystem' => $storageManager->getFilesystemForConfig($config),
            ]);
        }

        return $targets;
    }

    /**
     * @param  Collection<int, array{label: string, config: ?AppStorageConfig, filesystem: Filesystem}>  $targets
     */
    private function pathIsValidFile(Collection $targets, string $relativePath): bool
    {
        if ($this->findFixableNestedPath($targets, $relativePath) !== null) {
            return false;
        }

        foreach ($targets as $target) {
            $absolutePath = $this->localAbsolutePath($target['config'], $relativePath);

            if ($absolutePath && is_dir($absolutePath)) {
                return false;
            }

            if ($absolutePath && is_file($absolutePath)) {
                return true;
            }
        }

        foreach ($targets as $target) {
            if ($this->isExistingFile($target['filesystem'], $relativePath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Collection<int, array{label: string, config: ?AppStorageConfig, filesystem: Filesystem}>  $targets
     * @return array{label: string, config: ?AppStorageConfig, filesystem: Filesystem, inner_path: string}|null
     */
    private function findFixableNestedPath(Collection $targets, string $relativePath): ?array
    {
        foreach ($targets as $target) {
            $innerPath = $this->detectNestedInnerFile($target['filesystem'], $target['config'], $relativePath);

            if ($innerPath !== null) {
                return [
                    'label' => $target['label'],
                    'config' => $target['config'],
                    'filesystem' => $target['filesystem'],
                    'inner_path' => $innerPath,
                ];
            }
        }

        return null;
    }

    private function detectNestedInnerFile(Filesystem $filesystem, ?AppStorageConfig $config, string $relativePath): ?string
    {
        $absolutePath = $this->localAbsolutePath($config, $relativePath);

        if ($absolutePath && is_dir($absolutePath)) {
            $innerFiles = File::files($absolutePath);

            if (count($innerFiles) === 1) {
                $parentDir = dirname($relativePath);

                return ($parentDir !== '.' ? $parentDir.'/' : '').basename($innerFiles[0]->getPathname());
            }
        }

        $innerPath = $this->innerFileViaFilesystem($filesystem, $relativePath);

        if ($innerPath !== null && $innerPath !== $relativePath) {
            return $innerPath;
        }

        return null;
    }

    private function innerFileViaFilesystem(Filesystem $filesystem, string $relativePath): ?string
    {
        try {
            $files = array_values(array_filter(
                $filesystem->files($relativePath),
                fn (string $file) => $file !== $relativePath
            ));

            if (count($files) === 1) {
                return $files[0];
            }
        } catch (\Throwable) {
            // Ignore and try allFiles().
        }

        try {
            $allFiles = $filesystem->allFiles($relativePath);

            if (count($allFiles) === 1) {
                return $allFiles[0];
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function isExistingFile(Filesystem $filesystem, string $path): bool
    {
        if ($this->innerFileViaFilesystem($filesystem, $path) !== null) {
            return false;
        }

        try {
            if (method_exists($filesystem, 'fileExists')) {
                return $filesystem->fileExists($path);
            }

            if (! $filesystem->exists($path)) {
                return false;
            }

            return empty($filesystem->directories($path));
        } catch (\Throwable) {
            return false;
        }
    }

    private function flattenedPath(string $relativePath, string $innerPath): string
    {
        $parentDir = dirname($relativePath);

        return ($parentDir !== '.' ? $parentDir.'/' : '').basename($innerPath);
    }

    private function applyFix(
        Filesystem $filesystem,
        ?AppStorageConfig $config,
        string $relativePath,
        string $innerPath,
        string $newRelativePath
    ): ?string {
        $absoluteDir = $this->localAbsolutePath($config, $relativePath);
        $absoluteInner = $this->localAbsolutePath($config, $innerPath);
        $absoluteNew = $this->localAbsolutePath($config, $newRelativePath);

        if ($absoluteDir && $absoluteInner && $absoluteNew && is_dir($absoluteDir) && is_file($absoluteInner)) {
            if (is_file($absoluteNew)) {
                File::delete($absoluteNew);
            }

            File::ensureDirectoryExists(dirname($absoluteNew));

            if (rename($absoluteInner, $absoluteNew)) {
                File::deleteDirectory($absoluteDir);

                return $newRelativePath;
            }
        }

        try {
            $content = $filesystem->get($innerPath);

            if ($content === false || $content === '') {
                return null;
            }

            if ($this->isExistingFile($filesystem, $newRelativePath)) {
                $filesystem->delete($newRelativePath);
            }

            if (! $filesystem->put($newRelativePath, $content)) {
                return null;
            }

            $filesystem->delete($innerPath);

            try {
                $filesystem->deleteDirectory($relativePath);
            } catch (\Throwable) {
                // Directory cleanup is best-effort on cloud drivers.
            }

            return $newRelativePath;
        } catch (\Throwable) {
            return null;
        }
    }

    private function fixUnlinkedLocalNestedDirectories(bool $dryRun): int
    {
        $base = storage_path('app/public/gifts/images');

        if (! is_dir($base)) {
            return 0;
        }

        $fixed = 0;

        foreach (scandir($base) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (! str_ends_with(strtolower($entry), '.webp')) {
                continue;
            }

            $absoluteDir = $base.DIRECTORY_SEPARATOR.$entry;

            if (! is_dir($absoluteDir)) {
                continue;
            }

            $innerFiles = File::files($absoluteDir);

            if (count($innerFiles) !== 1) {
                $this->warn("Local orphan {$entry}: expected one inner file, found ".count($innerFiles));

                continue;
            }

            $relativeDir = 'gifts/images/'.$entry;
            $innerPath = $relativeDir.'/'.basename($innerFiles[0]->getPathname());
            $newRelativePath = $this->flattenedPath($relativeDir, $innerPath);
            $absoluteNew = $base.DIRECTORY_SEPARATOR.basename($newRelativePath);

            if ($dryRun) {
                $this->line("[dry-run] Local orphan: would flatten {$relativeDir} → {$newRelativePath}");
                $fixed++;

                continue;
            }

            if (is_file($absoluteNew)) {
                File::delete($absoluteNew);
            }

            if (! rename($innerFiles[0]->getPathname(), $absoluteNew)) {
                $this->error("Local orphan {$entry}: failed to flatten");

                continue;
            }

            File::deleteDirectory($absoluteDir);
            $this->info("Local orphan flattened → {$newRelativePath}");
            $fixed++;
        }

        return $fixed;
    }

    /**
     * @param  Collection<int, array{label: string, config: ?AppStorageConfig, filesystem: Filesystem}>  $targets
     */
    private function diagnoseGiftPath(
        StorageLocationResolver $resolver,
        Collection $targets,
        int $giftId,
        string $relativePath
    ): void {
        $resolution = $resolver->resolve(self::LOGICAL_DISK, $relativePath);

        $this->line("  Gift #{$giftId} resolver: found=".($resolution['found'] ? 'yes' : 'no').", status={$resolution['status']}");

        foreach ($targets as $target) {
            $filesystem = $target['filesystem'];
            $exists = false;
            $fileExists = false;
            $dirExists = false;
            $innerCount = 0;

            try {
                $exists = $filesystem->exists($relativePath);
            } catch (\Throwable) {
                $exists = false;
            }

            try {
                if (method_exists($filesystem, 'fileExists')) {
                    $fileExists = $filesystem->fileExists($relativePath);
                }
            } catch (\Throwable) {
                $fileExists = false;
            }

            try {
                if (method_exists($filesystem, 'directoryExists')) {
                    $dirExists = $filesystem->directoryExists($relativePath);
                }
            } catch (\Throwable) {
                $dirExists = false;
            }

            try {
                $innerCount = count($filesystem->files($relativePath));
            } catch (\Throwable) {
                $innerCount = 0;
            }

            $absolutePath = $this->localAbsolutePath($target['config'], $relativePath);
            $localState = 'n/a';

            if ($absolutePath !== null) {
                $localState = is_file($absolutePath)
                    ? 'file'
                    : (is_dir($absolutePath) ? 'dir' : 'missing');
            }

            $this->line("  [{$target['label']}] exists=".($exists ? 'yes' : 'no')
                .", file=".($fileExists ? 'yes' : 'no')
                .", dir=".($dirExists ? 'yes' : 'no')
                .", inner_files={$innerCount}"
                .", local={$localState}"
                .($absolutePath ? " ({$absolutePath})" : ''));
        }
    }

    /**
     * @param  Collection<int, array{label: string, config: ?AppStorageConfig, filesystem: Filesystem}>  $targets
     * @return array<int, string>
     */
    private function scanOrphanWebpDirectories(Collection $targets): array
    {
        $orphans = [];

        foreach ($this->scanLocalNestedWebpDirectoryNames() as $directory) {
            $orphans[] = $directory.' [Laravel public disk]';
        }

        foreach ($targets as $target) {
            try {
                $directories = $target['filesystem']->directories('gifts/images');
            } catch (\Throwable) {
                continue;
            }

            foreach ($directories as $directory) {
                if (! str_ends_with(strtolower($directory), '.webp')) {
                    continue;
                }

                $orphans[] = "{$directory} [{$target['label']}]";
            }
        }

        return array_values(array_unique($orphans));
    }

    /**
     * @return array<int, string>
     */
    private function scanLocalNestedWebpDirectoryNames(): array
    {
        $base = storage_path('app/public/gifts/images');

        if (! is_dir($base)) {
            return [];
        }

        $directories = [];

        foreach (scandir($base) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $absolute = $base.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($absolute) && str_ends_with(strtolower($entry), '.webp')) {
                $directories[] = 'gifts/images/'.$entry;
            }
        }

        return $directories;
    }

    private function localAbsolutePath(?AppStorageConfig $config, string $relativePath): ?string
    {
        if ($config instanceof AppStorageConfig) {
            if ($config->driver !== 'local') {
                return null;
            }

            $driverConfig = $config->getDecryptedConfig();
            $root = storage_path('app/'.($driverConfig['path'] ?? 'public'));
        } else {
            $root = storage_path('app/public');
        }

        return $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
}
