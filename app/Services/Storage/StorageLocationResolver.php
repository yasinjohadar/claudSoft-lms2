<?php

namespace App\Services\Storage;

use App\Models\AppStorageConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StorageLocationResolver
{
    public const STATUS_CLOUD_ONLY = 'cloud_only';

    public const STATUS_LOCAL_ONLY = 'local_only';

    public const STATUS_BOTH = 'both';

    public const STATUS_MISSING = 'missing';

    /** موجود، لكن على مخزن خارج سلسلة القرص المنطقي (بقايا ربط قديم). */
    public const STATUS_ELSEWHERE = 'elsewhere';

    /**
     * سرد مُخزَّن مؤقتاً لكل (config, prefix) — يحوّل فحص كل ملف من طلب شبكة
     * إلى بحث في الذاكرة. يملؤه primeListings() قبل مسح دفعة كبيرة.
     *
     * @var array<int, array<string, array<string, bool>>>
     */
    protected array $listingCache = [];

    /** @var Collection<int, AppStorageConfig>|null */
    protected ?Collection $allActiveStorages = null;

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
        $chainIds = $this->chainStorageIds($logicalDisk);

        foreach ($this->storagesToProbe($logicalDisk) as $config) {
            if ($this->existsOn($config, $path)) {
                $hits[$this->hitKey($config)] = [
                    'storage_config_id' => $config->id,
                    'storage_name' => $config->name,
                    'driver' => $config->driver,
                    'is_cloud' => $this->isCloudDriver($config->driver),
                    'in_chain' => in_array($config->id, $chainIds, true),
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
                'in_chain' => true,
                'size' => $this->storageManager->legacyPublicSize($path),
            ];
        }

        $locations = array_values($hits);
        $cloudHits = array_values(array_filter($locations, fn (array $hit) => $hit['is_cloud']));
        $localHits = array_values(array_filter($locations, fn (array $hit) => ! $hit['is_cloud']));

        $inChain = array_values(array_filter($locations, fn (array $hit) => $hit['in_chain'] ?? true));

        $status = match (true) {
            $locations === [] => self::STATUS_MISSING,
            // وُجد فقط على مخزن لا تشير إليه خريطة هذا القرص — كان يُبلَّغ عنه «مفقود» خطأً
            $inChain === [] => self::STATUS_ELSEWHERE,
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

    /**
     * سلسلة القرص المنطقي أولاً، ثم **كل** مخزن نشط آخر.
     *
     * السلوك السابق كان يتوقف عند السلسلة، وبما أن fallback_storage_ids فارغ في
     * كل صفوف storage_disk_mappings فإن أي ملف رُفع أيام كان القرص مربوطاً بمخزن
     * آخر (R2 أو Google Drive) كان يُصنَّف «مفقود» رغم وجوده.
     */
    protected function storagesToProbe(string $logicalDisk): Collection
    {
        $chain = $this->storageManager->resolveFailoverStorages($logicalDisk)->unique('id')->values();

        return $chain->merge($this->allActiveStorages())->unique('id')->values();
    }

    /**
     * @return Collection<int, AppStorageConfig>
     */
    protected function allActiveStorages(): Collection
    {
        if ($this->allActiveStorages !== null) {
            return $this->allActiveStorages;
        }

        try {
            return $this->allActiveStorages = AppStorageConfig::query()
                ->where('is_active', true)
                ->orderByDesc('priority')
                ->get();
        } catch (\Throwable $e) {
            // قاعدة البيانات غير متاحة (اختبارات وحدة، أو قبل الترحيلات) —
            // نكتفي بسلسلة القرص كما كان السلوك سابقاً.
            Log::debug('allActiveStorages failed: '.$e->getMessage());

            return $this->allActiveStorages = collect();
        }
    }

    /**
     * @return array<int, int>
     */
    protected function chainStorageIds(string $logicalDisk): array
    {
        return $this->storageManager
            ->resolveFailoverStorages($logicalDisk)
            ->pluck('id')
            ->all();
    }

    /**
     * سرد البادئات المطلوبة مرة واحدة لكل مخزن بدل فحص كل ملف على حدة.
     *
     * طلب exists() واحد يكلّف ~0.4 ثانية، بينما سرد بادئة كاملة يكلّف جزءاً من
     * الثانية مهما بلغ عدد ملفاتها — الفرق حاسم عند آلاف الملفات.
     *
     * @param  array<int, string>  $prefixes
     */
    public function primeListings(string $logicalDisk, array $prefixes): void
    {
        $prefixes = array_values(array_unique(array_filter(array_map(
            fn ($p) => trim((string) $p, '/'),
            $prefixes
        ), fn ($p) => $p !== '')));

        if ($prefixes === []) {
            return;
        }

        foreach ($this->storagesToProbe($logicalDisk) as $config) {
            foreach ($prefixes as $prefix) {
                if (isset($this->listingCache[$config->id][$prefix])) {
                    continue;
                }

                try {
                    $files = $this->storageManager
                        ->getFilesystemForConfig($config)
                        ->allFiles($prefix);

                    $this->listingCache[$config->id][$prefix] = array_fill_keys(
                        array_map(fn ($f) => ltrim((string) $f, '/'), $files),
                        true
                    );
                } catch (\Throwable $e) {
                    // بادئة غير موجودة أو مخزن لا يدعم السرد — نترك الفحص للمسار الاحتياطي
                    Log::debug("primeListings failed: {$config->name} / {$prefix} - ".$e->getMessage());
                }
            }
        }
    }

    public function forgetListings(): void
    {
        $this->listingCache = [];
    }

    /**
     * يستعمل السرد المُخزَّن إن كان يغطي هذا المسار، وإلا يسأل الشبكة.
     */
    protected function existsOn(AppStorageConfig $config, string $path): bool
    {
        foreach ($this->listingCache[$config->id] ?? [] as $prefix => $paths) {
            if (str_starts_with($path, $prefix.'/') || $path === $prefix) {
                return isset($paths[$path]);
            }
        }

        return $this->storageManager->existsOnConfig($config, $path);
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
