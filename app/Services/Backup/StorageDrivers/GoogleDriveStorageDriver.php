<?php

namespace App\Services\Backup\StorageDrivers;

use App\Contracts\BackupStorageInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class GoogleDriveStorageDriver implements BackupStorageInterface
{
    use Concerns\StoresFromPath;

    protected array $config;
    protected string $diskName;
    protected $adapter;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->diskName = 'google_drive_custom_' . md5(json_encode($config));
        
        // إعداد disk ديناميكي
        // يستخدم package: yaza/laravel-google-drive-storage
        Config::set("filesystems.disks.{$this->diskName}", [
            'driver' => 'google',
            'clientId' => $config['client_id'] ?? '',
            'clientSecret' => $config['client_secret'] ?? '',
            'refreshToken' => $config['refresh_token'] ?? '',
            'folder' => $config['folder_id'] ?? null, // package يستخدم 'folder' وليس 'folderId'
            'throw' => true, // إجبار رمي exceptions لإظهار رسالة الخطأ الفعلية
        ]);
    }

    public function store(string $path, string $content): bool
    {
        try {
            $storage = \Storage::disk($this->diskName);
            return $storage->put($path, $content) !== false;
        } catch (\Exception $e) {
            Log::error('Google Drive storage store failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function putFromStream(string $remotePath, string $localPath): bool
    {
        $stream = fopen($localPath, 'rb');
        if ($stream === false) {
            Log::error('Google Drive putFromStream: cannot open local file', ['path' => $localPath]);

            return false;
        }

        try {
            $disk = \Storage::disk($this->diskName);
            $size = filesize($localPath) ?: 0;

            Log::info('Google Drive upload starting', [
                'remote' => $remotePath,
                'local' => $localPath,
                'bytes' => $size,
            ]);

            // Flysystem writeStream throws on failure when disk throw=true
            if (method_exists($disk, 'writeStream')) {
                $disk->writeStream($remotePath, $stream);
            } else {
                $ok = $disk->put($remotePath, $stream);
                if ($ok === false) {
                    throw new \RuntimeException('Google Drive put() returned false');
                }
            }

            Log::info('Google Drive upload finished', ['remote' => $remotePath, 'bytes' => $size]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Google Drive putFromStream failed: ' . $e->getMessage(), [
                'remote' => $remotePath,
                'local' => $localPath,
            ]);

            return false;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function retrieve(string $path): string
    {
        try {
            return \Storage::disk($this->diskName)->get($path);
        } catch (\Exception $e) {
            Log::error('Google Drive storage retrieve failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(string $path): bool
    {
        try {
            return \Storage::disk($this->diskName)->delete($path);
        } catch (\Exception $e) {
            Log::error('Google Drive storage delete failed: ' . $e->getMessage());
            return false;
        }
    }

    public function exists(string $path): bool
    {
        try {
            return \Storage::disk($this->diskName)->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function list(string $prefix = ''): array
    {
        try {
            return \Storage::disk($this->diskName)->files($prefix);
        } catch (\Exception $e) {
            Log::error('Google Drive storage list failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getSize(string $path): int
    {
        try {
            return \Storage::disk($this->diskName)->size($path);
        } catch (\Exception $e) {
            Log::error('Google Drive storage getSize failed: ' . $e->getMessage());
            return 0;
        }
    }

    public function testConnection(): bool
    {
        try {
            // التحقق من وجود الإعدادات المطلوبة
            if (empty($this->config['client_id']) || empty($this->config['client_secret']) || empty($this->config['refresh_token'])) {
                Log::error('Google Drive testConnection: Missing required config (client_id, client_secret, or refresh_token)');
                return false;
            }

            $testFile = 'test_' . time() . '.txt';
            $result = \Storage::disk($this->diskName)->put($testFile, 'test');
            if ($result) {
                \Storage::disk($this->diskName)->delete($testFile);
            }
            return $result !== false;
        } catch (\Exception $e) {
            Log::error('Google Drive testConnection failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    public function getAvailableSpace(): ?int
    {
        // Google Drive API يوفر معلومات المساحة المتاحة
        // يحتاج تنفيذ إضافي
        return null;
    }

    public function getMetadata(string $path): array
    {
        try {
            return [
                'size' => \Storage::disk($this->diskName)->size($path),
                'last_modified' => \Storage::disk($this->diskName)->lastModified($path),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}

