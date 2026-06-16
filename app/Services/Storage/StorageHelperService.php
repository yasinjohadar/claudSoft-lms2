<?php

namespace App\Services\Storage;

use App\Services\Storage\AppStorageManager;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;

class StorageHelperService
{
    protected AppStorageManager $storageManager;

    public function __construct(AppStorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    /**
     * الحصول على disk
     * 
     * @param string $diskName
     * @return Filesystem
     */
    public function getDisk(string $diskName): Filesystem
    {
        return $this->storageManager->getDisk($diskName);
    }

    /**
     * تخزين ملف
     * 
     * @param string $disk
     * @param string $path
     * @param mixed $content
     * @param string|null $fileType
     * @return bool
     */
    public function storeFile(string $disk, string $path, $content, ?string $fileType = null): bool
    {
        return $this->storageManager->store($disk, $path, $content, $fileType);
    }

    /**
     * تخزين ملف مع Auto-failover
     * 
     * @param string $disk
     * @param string $path
     * @param mixed $content
     * @param string|null $fileType
     * @return bool
     */
    public function storeFileWithFailover(string $disk, string $path, $content, ?string $fileType = null): bool
    {
        try {
            return $this->storageManager->storeWithFailover($disk, $path, $content, $fileType);
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to store file with failover", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * رفع ملف مع تجربة S3 ثم التخزين المحلي عند الحاجة.
     *
     * @param  string  $directory  مجلد الوجهة فقط (مثل gifts/images) — بدون اسم ملف
     */
    public function storeUploadedFileWithFailover(string $disk, string $directory, $file, ?string $fileType = null): ?string
    {
        try {
            return $this->storageManager->storeUploadedFileWithFailover($disk, $directory, $file, $fileType);
        } catch (\Exception $e) {
            Log::error('StorageHelperService: Failed to store uploaded file with failover', [
                'disk' => $disk,
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * رفع ملف باسم محدد مع تجربة S3 ثم التخزين المحلي عند الحاجة.
     *
     * @param  string  $directory  مجلد الوجهة فقط (مثل gifts/images) — بدون اسم ملف
     * @param  string  $filename   اسم الملف فقط (مثل uuid.webp)
     */
    public function storeUploadedFileAsWithFailover(
        string $disk,
        string $directory,
        $file,
        string $filename,
        ?string $fileType = null
    ): ?string {
        try {
            return $this->storageManager->storeUploadedFileAsWithFailover(
                $disk,
                $directory,
                $file,
                $filename,
                $fileType
            );
        } catch (\Exception $e) {
            Log::error('StorageHelperService: Failed to store uploaded file-as with failover', [
                'disk' => $disk,
                'directory' => $directory,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{content: string, mime_type: string}|null
     */
    public function retrieveFileWithFailover(string $disk, string $path): ?array
    {
        try {
            return $this->storageManager->retrieveWithFailover($disk, $path);
        } catch (\Exception $e) {
            Log::error('StorageHelperService: Failed to retrieve file with failover', [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * الحصول على URL للملف
     * 
     * @param string $disk
     * @param string $path
     * @return string
     */
    public function getFileUrl(string $disk, string $path): string
    {
        return $this->storageManager->url($disk, $path);
    }

    /**
     * حذف ملف
     * 
     * @param string $disk
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $disk, string $path): bool
    {
        return $this->storageManager->delete($disk, $path);
    }

    /**
     * التحقق من وجود الملف
     * 
     * @param string $disk
     * @param string $path
     * @return bool
     */
    public function fileExists(string $disk, string $path): bool
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->exists($path);
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to check file existence", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * التحقق من وجود الملف عبر سلسلة failover كاملة.
     */
    public function fileExistsWithFailover(string $disk, string $path): bool
    {
        return $this->storageManager->fileExistsWithFailover($disk, $path);
    }

    /**
     * نسخ ملف
     * 
     * @param string $disk
     * @param string $fromPath
     * @param string $toPath
     * @return bool
     */
    public function copyFile(string $disk, string $fromPath, string $toPath): bool
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->copy($fromPath, $toPath);
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to copy file", [
                'disk' => $disk,
                'from' => $fromPath,
                'to' => $toPath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * نقل ملف
     * 
     * @param string $disk
     * @param string $fromPath
     * @param string $toPath
     * @return bool
     */
    public function moveFile(string $disk, string $fromPath, string $toPath): bool
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->move($fromPath, $toPath);
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to move file", [
                'disk' => $disk,
                'from' => $fromPath,
                'to' => $toPath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * الحصول على محتوى الملف
     * 
     * @param string $disk
     * @param string $path
     * @return string
     */
    public function getFileContent(string $disk, string $path): string
    {
        try {
            return $this->storageManager->retrieve($disk, $path);
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to retrieve file", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }

    /**
     * الحصول على حجم الملف
     * 
     * @param string $disk
     * @param string $path
     * @return int
     */
    public function getFileSize(string $disk, string $path): int
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->size($path);
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to get file size", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * الحصول على آخر وقت تعديل
     * 
     * @param string $disk
     * @param string $path
     * @return int|null
     */
    public function getLastModified(string $disk, string $path): ?int
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->lastModified($path);
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to get last modified", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * الحصول على جميع الملفات في مجلد
     * 
     * @param string $disk
     * @param string $path
     * @return array
     */
    public function getFiles(string $disk, string $path = ''): array
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->files($path);
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to get files", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * الحصول على جميع المجلدات
     * 
     * @param string $disk
     * @param string $path
     * @return array
     */
    public function getDirectories(string $disk, string $path = ''): array
    {
        try {
            $storage = $this->getDisk($disk);
            return $storage->directories($path);
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to get directories", [
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Helper method لتخزين ملف من UploadedFile
     *
     * @param  string  $directory  مجلد الوجهة فقط — بدون اسم ملف
     * @return string|false
     */
    public function storeUploadedFile(string $disk, string $directory, $file, ?string $fileType = null)
    {
        try {
            Log::info("StorageHelperService: Starting file upload", [
                'disk' => $disk,
                'directory' => $directory,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $fileType,
            ]);

            $storage = $this->getDisk($disk);

            $storedPath = $storage->putFile($directory, $file);
            
            if ($storedPath) {
                Log::info("StorageHelperService: File uploaded successfully", [
                    'disk' => $disk,
                    'stored_path' => $storedPath,
                    'directory' => $directory,
                ]);

                // تتبع التخزين باستخدام analytics service مباشرة
                try {
                    $mapping = \App\Models\StorageDiskMapping::where('disk_name', $disk)
                        ->where('is_active', true)
                        ->first();
                    
                    if ($mapping && $mapping->primaryStorage) {
                        $fileSize = $file->getSize();
                        $analyticsService = app(\App\Services\Storage\AppStorageAnalyticsService::class);
                        $analyticsService->trackStorageUsage($mapping->primaryStorage, $fileSize, $fileType);
                        $analyticsService->trackBandwidth($mapping->primaryStorage, 'upload', $fileSize, $fileType);
                    }
                } catch (\Exception $trackingException) {
                    // لا نوقف العملية إذا فشل tracking
                    Log::warning("StorageHelperService: Failed to track storage usage", [
                        'error' => $trackingException->getMessage(),
                    ]);
                }
                
                return $storedPath;
            } else {
                Log::error("StorageHelperService: putFile returned false", [
                    'disk' => $disk,
                    'directory' => $directory,
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("StorageHelperService: Failed to store uploaded file", [
                'disk' => $disk,
                'directory' => $directory,
                'file_name' => $file->getClientOriginalName() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Helper method للحصول على disk مباشرة (للاستخدام مع Laravel Storage methods)
     * 
     * @param string $diskName
     * @return Filesystem
     */
    public function disk(string $diskName): Filesystem
    {
        return $this->getDisk($diskName);
    }
}
