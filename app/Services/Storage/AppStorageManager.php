<?php

namespace App\Services\Storage;

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use App\Services\Storage\AppStorageFactory;
use App\Services\Storage\AppStorageAnalyticsService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AppStorageManager
{
    protected AppStorageAnalyticsService $analyticsService;
    protected $auditLogService = null;

    public function __construct(AppStorageAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        
        // محاولة تحميل AuditLogService إذا كان موجوداً
        if (class_exists(\App\Services\AuditLogService::class)) {
            try {
                $this->auditLogService = app(\App\Services\AuditLogService::class);
            } catch (\Exception $e) {
                Log::debug('AuditLogService not available: ' . $e->getMessage());
            }
        }
    }

    /**
     * الحصول على disk
     */
    public function getDisk(string $diskName): Filesystem
    {
        $mapping = StorageDiskMapping::where('disk_name', $diskName)
            ->where('is_active', true)
            ->first();

        if ($mapping && $mapping->primaryStorage) {
            try {
                return AppStorageFactory::create($mapping->primaryStorage);
            } catch (\Exception $e) {
                Log::error("Failed to create disk {$diskName} from mapping: " . $e->getMessage());
            }
        }

        // Fallback: try to find ANY active storage config to use
        $anyActiveStorage = AppStorageConfig::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->first();
        
        if ($anyActiveStorage) {
            try {
                Log::info("Using fallback active storage for disk {$diskName}", [
                    'storage_id' => $anyActiveStorage->id,
                    'storage_name' => $anyActiveStorage->name,
                    'driver' => $anyActiveStorage->driver,
                ]);
                
                return AppStorageFactory::create($anyActiveStorage);
            } catch (\Exception $e) {
                Log::error("Failed to create fallback disk for {$diskName}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        // Last fallback: local storage
        Log::warning("No active storage found for disk {$diskName}, using local storage");
        return Storage::disk('public');
    }

    /**
     * تخزين ملف
     */
    public function store(string $disk, string $path, $content, ?string $fileType = null): bool
    {
        try {
            $storage = $this->getDisk($disk);
            $result = $storage->put($path, $content);
            
            if ($result) {
                $this->trackStorage($disk, $path, $content, $fileType, 'upload');
            }
            
            return $result !== false;
        } catch (\Exception $e) {
            Log::error("Storage store failed for disk {$disk}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * تخزين مع Auto-failover
     */
    public function storeWithFailover(string $disk, string $path, $content, ?string $fileType = null): bool
    {
        $mapping = StorageDiskMapping::where('disk_name', $disk)
            ->where('is_active', true)
            ->first();

        if (!$mapping) {
            return $this->store($disk, $path, $content, $fileType);
        }

        // محاولة التخزين الأساسي
        try {
            $primaryStorage = AppStorageFactory::create($mapping->primaryStorage);
            if ($primaryStorage->put($path, $content)) {
                $this->trackStorage($disk, $path, $content, $fileType, 'upload', $mapping->primaryStorage);
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("Primary storage failed for disk {$disk}: " . $e->getMessage());

            // تسجيل فشل التخزين الأساسي في سجل التدقيق (إذا كان متاحاً)
            if ($this->auditLogService) {
                try {
                    $this->auditLogService->log(
                        null,
                        'storage_primary_failed',
                        'فشل التخزين الأساسي',
                        [
                            'disk' => $disk,
                            'storage' => $mapping->primaryStorage?->name,
                            'error' => $e->getMessage(),
                        ]
                    );
                } catch (\Exception $auditException) {
                    Log::debug('Failed to log to AuditLogService: ' . $auditException->getMessage());
                }
            }
        }

        // محاولة Fallback storages
        $fallbackStorages = $mapping->getFallbackStorages();
        foreach ($fallbackStorages as $fallbackStorage) {
            try {
                $storage = AppStorageFactory::create($fallbackStorage);
                if ($storage->put($path, $content)) {
                    $this->trackStorage($disk, $path, $content, $fileType, 'upload', $fallbackStorage);
                    Log::info("Used fallback storage for disk {$disk}: {$fallbackStorage->name}");

                    // تنبيه في AuditLog عند استخدام تخزين احتياطي (إذا كان متاحاً)
                    if ($this->auditLogService) {
                        try {
                            $this->auditLogService->log(
                                null,
                                'storage_failover_used',
                                'استخدام تخزين احتياطي',
                                [
                                    'disk' => $disk,
                                    'fallback_storage' => $fallbackStorage->name,
                                ]
                            );
                        } catch (\Exception $auditException) {
                            Log::debug('Failed to log to AuditLogService: ' . $auditException->getMessage());
                        }
                    }

                    return true;
                }
            } catch (\Exception $e) {
                Log::warning("Fallback storage failed: {$fallbackStorage->name} - " . $e->getMessage());
                continue;
            }
        }

        throw new \Exception("All storage options failed for disk: {$disk}");
    }

    /**
     * رفع ملف مع Auto-failover (S3 ثم Local لـ payment_receipts أو عند وجود mapping).
     */
    public function storeUploadedFileWithFailover(
        string $disk,
        string $directory,
        UploadedFile $file,
        ?string $fileType = null
    ): ?string {
        $storages = $this->resolveFailoverStorages($disk);

        if ($storages->isEmpty()) {
            try {
                $storage = $this->getDisk($disk);
                $storedPath = $storage->putFile($directory, $file);

                return $storedPath ?: null;
            } catch (\Exception $e) {
                Log::error("Storage uploaded file failed for disk {$disk}: " . $e->getMessage());

                return null;
            }
        }

        foreach ($storages as $storageConfig) {
            try {
                $storage = AppStorageFactory::create($storageConfig);
                $storedPath = $storage->putFile($directory, $file);

                if ($storedPath) {
                    $this->trackUploadedFile($disk, $storedPath, $file->getSize(), $fileType, $storageConfig);
                    Log::info("Uploaded file stored for disk {$disk}", [
                        'storage' => $storageConfig->name,
                        'path' => $storedPath,
                    ]);

                    return $storedPath;
                }
            } catch (\Exception $e) {
                Log::warning("Uploaded file storage failed: {$storageConfig->name} - " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * @return array{content: string, mime_type: string}|null
     */
    public function retrieveWithFailover(string $disk, string $path): ?array
    {
        foreach ($this->resolveFailoverStorages($disk) as $storageConfig) {
            try {
                $storage = AppStorageFactory::create($storageConfig);
                $content = $storage->get($path);

                if ($content !== false && $content !== '') {
                    $this->trackStorage($disk, $path, $content, null, 'download', $storageConfig);

                    return [
                        'content' => $content,
                        'mime_type' => $this->resolveMimeType($storage, $path),
                    ];
                }
            } catch (\Exception $e) {
                Log::debug("Retrieve failed on {$storageConfig->name} for disk {$disk}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * @return Collection<int, AppStorageConfig>
     */
    public function resolveFailoverStorages(string $disk): Collection
    {
        $mapping = StorageDiskMapping::where('disk_name', $disk)
            ->where('is_active', true)
            ->first();

        if ($mapping && $mapping->primaryStorage) {
            return collect([$mapping->primaryStorage])
                ->merge($mapping->getFallbackStorages())
                ->filter();
        }

        if ($disk === 'payment_receipts') {
            $chain = AppStorageConfig::where('is_active', true)
                ->where('driver', 's3')
                ->orderByDesc('priority')
                ->get();

            $local = AppStorageConfig::where('is_active', true)
                ->where('driver', 'local')
                ->orderByDesc('priority')
                ->first();

            if ($local) {
                $chain = $chain->push($local);
            }

            return $chain->filter();
        }

        return collect();
    }

    /**
     * تخزين في أماكن متعددة (Redundancy)
     */
    public function storeToMultiple(string $disk, string $path, $content, ?string $fileType = null): array
    {
        $mapping = StorageDiskMapping::where('disk_name', $disk)
            ->where('is_active', true)
            ->first();

        if (!$mapping) {
            return [];
        }

        $storages = collect([$mapping->primaryStorage])
            ->merge($mapping->getFallbackStorages())
            ->filter(function($storage) {
                return $storage->redundancy && $storage->is_active;
            });

        $successful = [];
        $failed = [];

        foreach ($storages as $storage) {
            try {
                $storageDisk = AppStorageFactory::create($storage);
                if ($storageDisk->put($path, $content)) {
                    $this->trackStorage($disk, $path, $content, $fileType, 'upload', $storage);
                    $successful[] = $storage->name;
                } else {
                    $failed[] = $storage->name;
                }
            } catch (\Exception $e) {
                Log::error("Redundancy storage failed: {$storage->name} - " . $e->getMessage());
                $failed[] = $storage->name;
            }
        }

        return [
            'successful' => $successful,
            'failed' => $failed,
        ];
    }

    /**
     * استرجاع ملف
     */
    public function retrieve(string $disk, string $path): string
    {
        try {
            $storage = $this->getDisk($disk);
            $content = $storage->get($path);
            
            $this->trackStorage($disk, $path, $content, null, 'download');
            
            return $content;
        } catch (\Exception $e) {
            Log::error("Storage retrieve failed for disk {$disk}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * حذف ملف
     */
    public function delete(string $disk, string $path): bool
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->delete($path);
        } catch (\Exception $e) {
            Log::error("Storage delete failed for disk {$disk}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على URL
     */
    public function url(string $disk, string $path): string
    {
        try {
            $mapping = StorageDiskMapping::where('disk_name', $disk)
                ->where('is_active', true)
                ->first();

            $storageConfig = null;
            
            if ($mapping && $mapping->primaryStorage) {
                $storageConfig = $mapping->primaryStorage;
            } else {
                // Fallback: prefer cloud storage over local
                $storageConfig = AppStorageConfig::where('is_active', true)
                    ->where('driver', '!=', 'local')
                    ->orderBy('priority', 'desc')
                    ->first();
                
                // If no cloud storage found, try any active storage
                if (!$storageConfig) {
                    $storageConfig = AppStorageConfig::where('is_active', true)
                        ->orderBy('priority', 'desc')
                        ->first();
                }
                
                if ($storageConfig) {
                    Log::info("Using fallback active storage for URL generation", [
                        'disk' => $disk,
                        'storage_name' => $storageConfig->name,
                    ]);
                }
            }
            
            if ($storageConfig) {
                // معالجة خاصة لـ Bunny Storage - استخدام CDN URL مباشرة
                if ($storageConfig->driver === 'bunny') {
                    $bunnyUrl = $this->getBunnyUrl($storageConfig, $path);
                    if (!empty($bunnyUrl)) {
                        return $bunnyUrl;
                    }
                }
                
                // معالجة خاصة لـ S3 والمحركات المشتقة منه
                if (in_array($storageConfig->driver, ['s3', 'digitalocean', 'wasabi', 'backblaze', 'cloudflare_r2'])) {
                    $delivery = config('filesystems.image_delivery', 'proxy');
                    $storageDisk = $this->getDisk($disk);

                    if ($delivery === 'cdn' && ! empty($storageConfig->cdn_url)) {
                        return rtrim($storageConfig->cdn_url, '/') . '/' . ltrim($path, '/');
                    }

                    if ($delivery !== 'proxy') {
                        try {
                            $signedUrl = $storageDisk->temporaryUrl($path, now()->addDays(7));
                            if (! empty($signedUrl) && filter_var($signedUrl, FILTER_VALIDATE_URL)) {
                                return $signedUrl;
                            }
                        } catch (\Exception $e) {
                            Log::debug("Pre-signed URL failed for disk {$disk}: " . $e->getMessage());
                        }

                        try {
                            $directUrl = $storageDisk->url($path);
                            if (! empty($directUrl) && filter_var($directUrl, FILTER_VALIDATE_URL)) {
                                return $directUrl;
                            }
                        } catch (\Exception $e) {
                            Log::debug("Direct URL failed for disk {$disk}: " . $e->getMessage());
                        }

                        if (! empty($storageConfig->cdn_url)) {
                            return rtrim($storageConfig->cdn_url, '/') . '/' . ltrim($path, '/');
                        }
                    }

                    $decryptedConfig = $storageConfig->getDecryptedConfig();
                    $endpoint = $decryptedConfig['endpoint'] ?? null;
                    $bucket = $decryptedConfig['bucket'] ?? '';
                    if ($endpoint && $bucket) {
                        return rtrim($endpoint, '/') . '/' . $bucket . '/' . ltrim($path, '/');
                    }
                }
                
                // للمحركات الأخرى - استخدام cdn_url إذا موجود
                if (!empty($storageConfig->cdn_url)) {
                    $cdnUrl = rtrim($storageConfig->cdn_url, '/');
                    return $cdnUrl . '/' . ltrim($path, '/');
                }
            }

            // Fallback إلى storage url
            $storage = $this->getDisk($disk);
            $url = $storage->url($path);
            
            // إذا كان URL فارغاً أو غير صالح، حاول مرة أخرى من CDN URL
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                if ($storageConfig && $storageConfig->cdn_url) {
                    $cdnUrl = rtrim($storageConfig->cdn_url, '/');
                    $url = $cdnUrl . '/' . ltrim($path, '/');
                }
            }
            
            return $url;
        } catch (\Exception $e) {
            Log::error("Storage URL failed for disk {$disk}: " . $e->getMessage());
            return '';
        }
    }

    /**
     * بناء URL لـ Bunny Storage
     */
    private function getBunnyUrl(AppStorageConfig $config, string $path): string
    {
        // الأولوية 1: cdn_url من AppStorageConfig
        if (!empty($config->cdn_url)) {
            return rtrim($config->cdn_url, '/') . '/' . ltrim($path, '/');
        }
        
        // الأولوية 2: pull_zone من config المشفر
        $decryptedConfig = $config->getDecryptedConfig();
        if (!empty($decryptedConfig['pull_zone'])) {
            $pullZone = trim($decryptedConfig['pull_zone']);
            // إذا كان pull_zone URL كامل
            if (str_starts_with($pullZone, 'http')) {
                return rtrim($pullZone, '/') . '/' . ltrim($path, '/');
            }
            // إذا كان اسم zone فقط
            return 'https://' . $pullZone . '.b-cdn.net/' . ltrim($path, '/');
        }
        
        // الأولوية 3: بناء URL من storage_zone
        if (!empty($decryptedConfig['storage_zone'])) {
            return 'https://' . trim($decryptedConfig['storage_zone']) . '.b-cdn.net/' . ltrim($path, '/');
        }
        
        return '';
    }

    /**
     * التحقق من وجود الملف
     */
    public function exists(string $disk, string $path): bool
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->exists($path);
        } catch (\Exception $e) {
            Log::error("Storage exists check failed for disk {$disk}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * نسخ ملف
     */
    public function copy(string $disk, string $fromPath, string $toPath): bool
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->copy($fromPath, $toPath);
        } catch (\Exception $e) {
            Log::error("Storage copy failed for disk {$disk}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * نقل ملف
     */
    public function move(string $disk, string $fromPath, string $toPath): bool
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->move($fromPath, $toPath);
        } catch (\Exception $e) {
            Log::error("Storage move failed for disk {$disk}: " . $e->getMessage());
            return false;
        }
    }

    public function getFilesystemForConfig(AppStorageConfig $config): Filesystem
    {
        return AppStorageFactory::create($config);
    }

    public function existsOnConfig(AppStorageConfig $config, string $path): bool
    {
        try {
            return $this->getFilesystemForConfig($config)->exists($path);
        } catch (\Exception $e) {
            Log::debug("existsOnConfig failed: {$config->name} - " . $e->getMessage());

            return false;
        }
    }

    public function fileExistsWithFailover(string $disk, string $path): bool
    {
        foreach ($this->resolveFailoverStorages($disk) as $config) {
            if ($this->existsOnConfig($config, $path)) {
                return true;
            }
        }

        if ($disk === 'public' && Storage::disk('public')->exists($path)) {
            return true;
        }

        return false;
    }

    public function getFromConfig(AppStorageConfig $config, string $path): ?string
    {
        try {
            $content = $this->getFilesystemForConfig($config)->get($path);

            return ($content !== false && $content !== '') ? $content : null;
        } catch (\Exception $e) {
            Log::debug("getFromConfig failed: {$config->name} - " . $e->getMessage());

            return null;
        }
    }

    public function putToConfig(AppStorageConfig $config, string $path, string $content): bool
    {
        try {
            return $this->getFilesystemForConfig($config)->put($path, $content) !== false;
        } catch (\Exception $e) {
            Log::warning("putToConfig failed: {$config->name} - " . $e->getMessage());

            return false;
        }
    }

    public function deleteFromConfig(AppStorageConfig $config, string $path): bool
    {
        try {
            return $this->getFilesystemForConfig($config)->delete($path);
        } catch (\Exception $e) {
            Log::warning("deleteFromConfig failed: {$config->name} - " . $e->getMessage());

            return false;
        }
    }

    public function getFileSizeOnConfig(AppStorageConfig $config, string $path): int
    {
        try {
            return $this->getFilesystemForConfig($config)->size($path);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * @return Collection<int, AppStorageConfig>
     */
    public function resolveCloudPrimaryStorages(string $disk): Collection
    {
        $cloudDrivers = config('storage_inventory.cloud_drivers', ['s3']);

        return $this->resolveFailoverStorages($disk)
            ->filter(fn (AppStorageConfig $config) => in_array($config->driver, $cloudDrivers, true))
            ->values();
    }

    /**
     * @return Collection<int, AppStorageConfig>
     */
    public function resolveLocalStorages(string $disk): Collection
    {
        return $this->resolveFailoverStorages($disk)
            ->filter(fn (AppStorageConfig $config) => $config->driver === 'local')
            ->values();
    }

    public function getLegacyPublicContent(string $path): ?string
    {
        try {
            if (! Storage::disk('public')->exists($path)) {
                return null;
            }

            $content = Storage::disk('public')->get($path);

            return ($content !== false && $content !== '') ? $content : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function deleteLegacyPublic(string $path): bool
    {
        try {
            return Storage::disk('public')->delete($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function legacyPublicExists(string $path): bool
    {
        try {
            return Storage::disk('public')->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function resolveMimeType(Filesystem $storage, string $path): string
    {
        try {
            return $storage->mimeType($path) ?: 'application/octet-stream';
        } catch (\Exception $e) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            return match ($extension) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'jpg', 'jpeg' => 'image/jpeg',
                'pdf' => 'application/pdf',
                default => 'application/octet-stream',
            };
        }
    }

    private function trackUploadedFile(
        string $disk,
        string $path,
        int $bytes,
        ?string $fileType,
        AppStorageConfig $storage
    ): void {
        try {
            $this->analyticsService->trackStorageUsage($storage, $bytes, $fileType);
            $this->analyticsService->trackBandwidth($storage, 'upload', $bytes, $fileType);
        } catch (\Exception $e) {
            Log::warning("Failed to track uploaded file usage for disk {$disk}: " . $e->getMessage());
        }
    }

    /**
     * تتبع التخزين
     */
    private function trackStorage(string $disk, string $path, $content, ?string $fileType, string $operation, ?AppStorageConfig $storage = null): void
    {
        if (!$storage) {
            $mapping = StorageDiskMapping::where('disk_name', $disk)->first();
            if ($mapping) {
                $storage = $mapping->primaryStorage;
            }
        }

        if ($storage) {
            $bytes = is_string($content) ? strlen($content) : (is_resource($content) ? 0 : filesize($content));
            
            if ($operation === 'upload') {
                $this->analyticsService->trackStorageUsage($storage, $bytes, $fileType);
                $this->analyticsService->trackBandwidth($storage, 'upload', $bytes, $fileType);
            } elseif ($operation === 'download') {
                $this->analyticsService->trackBandwidth($storage, 'download', $bytes, $fileType);
            }
        }
    }
}

