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
        // اللاحقة تُبطل الكاش القديم تلقائياً عند تغيّر شكل الملخّص
        // (أُضيف قسم server) بدل إخفاء القسم حتى تنتهي الصلاحية.
        $cacheKey = (string) config('storage_inventory.capacity_cache_key', 'storage_capacity_summary').':v2';
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
            'server' => $this->calculateServerFootprint(),
            'scanned_at' => now()->toDateTimeString(),
        ];
    }


    /**
     * أكبر مستهلكي المساحة على السيرفر — لا ملفات المستخدمين وحدها.
     *
     * ملفات المحتوى قد لا تتجاوز بضع مئات من الكيلوبايت بينما يلتهم سجل واحد
     * مئات الميجابايت، فترحيل الملفات للسحابة لا يصغّر المشروع ما لم يُعالج هذا.
     *
     * @return array{paths: array<int, array{label: string, path: string, bytes: int, files: int, exists: bool, hint: string|null}>, total_bytes: int, disk_free: int|null, disk_total: int|null}
     */
    public function calculateServerFootprint(): array
    {
        $targets = [
            ['label' => 'السجلات', 'path' => storage_path('logs'), 'hint' => 'استخدم LOG_CHANNEL=daily مع LOG_DAILY_DAYS للتدوير'],
            ['label' => 'كاش الإطار', 'path' => storage_path('framework'), 'hint' => 'آمن للمسح عبر artisan optimize:clear'],
            ['label' => 'نسخ احتياطية محلية', 'path' => storage_path('app/backups'), 'hint' => 'يُنظَّف عبر backup:cleanup-expired'],
            ['label' => 'ملفات مؤقتة', 'path' => storage_path('app/temp'), 'hint' => 'بقايا عمليات لم تكتمل'],
            ['label' => 'ملفات خاصة', 'path' => storage_path('app/private'), 'hint' => null],
            ['label' => 'ملفات عامة (محتوى)', 'path' => storage_path('app/public'), 'hint' => 'هذه وحدها ما يشملها الترحيل للسحابة'],
            ['label' => 'أصول القالب', 'path' => public_path('assets'), 'hint' => 'جزء من الكود ولا يُرحَّل للسحابة'],
        ];

        $paths = [];
        $total = 0;

        foreach ($targets as $target) {
            $usage = $this->measureDirectory($target['path']);
            $total += $usage['bytes'];

            $paths[] = [
                'label' => $target['label'],
                'path' => $target['path'],
                'bytes' => $usage['bytes'],
                'files' => $usage['files'],
                'exists' => $usage['exists'],
                'hint' => $target['hint'],
            ];
        }

        usort($paths, fn (array $a, array $b) => $b['bytes'] <=> $a['bytes']);

        $free = @disk_free_space(base_path());
        $totalSpace = @disk_total_space(base_path());

        return [
            'paths' => $paths,
            'total_bytes' => $total,
            'disk_free' => $free === false ? null : (int) $free,
            'disk_total' => $totalSpace === false ? null : (int) $totalSpace,
        ];
    }

    /**
     * @return array{bytes: int, files: int, exists: bool}
     */
    protected function measureDirectory(string $path): array
    {
        if (! is_dir($path)) {
            return ['bytes' => 0, 'files' => 0, 'exists' => false];
        }

        $bytes = 0;
        $files = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $bytes += $file->getSize();
                    $files++;
                }
            }
        } catch (\Throwable $e) {
            return ['bytes' => $bytes, 'files' => $files, 'exists' => true];
        }

        return ['bytes' => $bytes, 'files' => $files, 'exists' => true];
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
