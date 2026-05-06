<?php

namespace App\Services\Storage;

use App\Models\AppStorageConfig;
use App\Models\StorageDiskMapping;
use App\Services\Storage\AppStorageFactory;
use App\Services\Storage\AppStorageAnalyticsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

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
        // This ensures profile photos use the same cloud storage as courses/blog
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
        Log::warning("No active cloud storage found for disk {$diskName}, using local storage");
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
                // Fallback: use any active storage config
                $storageConfig = AppStorageConfig::where('is_active', true)
                    ->orderBy('priority', 'desc')
                    ->first();
                
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
                
                // معالجة خاصة لـ S3 والمحركات المشتقة منه - استخدام Pre-signed URL
                if (in_array($storageConfig->driver, ['s3', 'digitalocean', 'wasabi', 'backblaze', 'cloudflare_r2'])) {
                    $storageDisk = $this->getDisk($disk);
                    try {
                        return $storageDisk->temporaryUrl($path, now()->addHours(24));
                    } catch (\Exception $e) {
                        Log::warning("Failed to generate pre-signed URL for disk {$disk}: " . $e->getMessage());
                        // Fallback إلى الرابط المباشر
                        $decryptedConfig = $storageConfig->getDecryptedConfig();
                        $endpoint = $decryptedConfig['endpoint'] ?? null;
                        $bucket = $decryptedConfig['bucket'] ?? '';
                        if ($endpoint && $bucket) {
                            return rtrim($endpoint, '/') . '/' . $bucket . '/' . ltrim($path, '/');
                        }
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

