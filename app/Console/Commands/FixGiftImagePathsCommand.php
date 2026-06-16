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
            ->chunkById(100, function ($gifts) use ($storageManager, $resolver, $targets, $dryRun, $diagnose, &$fixed, &$skipped) {
                foreach ($gifts as $gift) {
                    $relativePath = ltrim($gift->image_path, '/');
                    $match = $this->findFixableNestedPath($targets, $relativePath);

                    if ($match === null) {
                        if ($this->pathIsValidFile($targets, $relativePath)) {
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

        $orphans = $this->scanOrphanWebpDirectories($targets);

        if ($orphans !== []) {
            $this->newLine();
            $this->warn('Orphan nested directories under gifts/images/:');
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

        foreach ($storageManager->resolveFailoverStorages(self::LOGICAL_DISK) as $config) {
            $targets->push([
                'label' => $config->name.' ('.$config->driver.')',
                'config' => $config,
                'filesystem' => $storageManager->getFilesystemForConfig($config),
            ]);
        }

        $hasLegacyPublic = $targets->contains(function (array $target) {
            if (! $target['config'] instanceof AppStorageConfig || $target['config']->driver !== 'local') {
                return false;
            }

            $cfg = $target['config']->getDecryptedConfig();

            return ($cfg['path'] ?? 'public') === 'public';
        });

        if ($targets->isEmpty() || ! $hasLegacyPublic) {
            $targets->push([
                'label' => 'Laravel public disk',
                'config' => null,
                'filesystem' => Storage::disk('public'),
            ]);
        }

        return $targets;
    }

    /**
     * @param  Collection<int, array{label: string, config: ?AppStorageConfig, filesystem: Filesystem}>  $targets
     */
    private function pathIsValidFile(Collection $targets, string $relativePath): bool
    {
        foreach ($targets as $target) {
            if ($this->isExistingFile($target['filesystem'], $relativePath)) {
                return true;
            }

            $absolutePath = $this->localAbsolutePath($target['config'], $relativePath);

            if ($absolutePath && is_file($absolutePath)) {
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
        if ($this->isExistingFile($filesystem, $relativePath)) {
            return null;
        }

        $innerPath = $this->innerFileViaFilesystem($filesystem, $relativePath);

        if ($innerPath !== null) {
            return $innerPath;
        }

        $absolutePath = $this->localAbsolutePath($config, $relativePath);

        if ($absolutePath === null || ! is_dir($absolutePath)) {
            return null;
        }

        $innerFiles = File::files($absolutePath);

        if (count($innerFiles) !== 1) {
            return null;
        }

        $parentDir = dirname($relativePath);

        return ($parentDir !== '.' ? $parentDir.'/' : '').basename($innerFiles[0]->getPathname());
    }

    private function innerFileViaFilesystem(Filesystem $filesystem, string $relativePath): ?string
    {
        try {
            if (method_exists($filesystem, 'directoryExists') && ! $filesystem->directoryExists($relativePath)) {
                return null;
            }
        } catch (\Throwable) {
            // Continue with files() probe below.
        }

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
        try {
            if (method_exists($filesystem, 'fileExists')) {
                return $filesystem->fileExists($path);
            }

            if (! $filesystem->exists($path)) {
                return false;
            }

            return $this->innerFileViaFilesystem($filesystem, $path) === null
                && empty($filesystem->directories($path));
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
