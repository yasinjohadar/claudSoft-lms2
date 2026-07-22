<?php

namespace App\Services\Backup\StorageDrivers\Concerns;

use Illuminate\Support\Facades\Log;

/**
 * Default storeFromPath via streaming resource (avoids loading whole file into a string).
 * Drivers with a Laravel disk name should override getFilesystemDiskName().
 */
trait StoresFromPath
{
    /**
     * Upload a local file to remote path without loading the entire file into memory.
     */
    public function storeFromPath(string $remotePath, string $localPath): bool
    {
        if (!is_readable($localPath)) {
            Log::error('storeFromPath: local file not readable', ['path' => $localPath]);

            return false;
        }

        $maxFallbackBytes = (int) config('backup.store_from_path_max_fallback_bytes', 64 * 1024 * 1024);
        $size = filesize($localPath);

        if (method_exists($this, 'putFromStream')) {
            return $this->putFromStream($remotePath, $localPath);
        }

        if ($size !== false && $size > $maxFallbackBytes) {
            Log::warning('storeFromPath: file exceeds fallback limit; refusing full-string load', [
                'path' => $localPath,
                'size' => $size,
                'limit' => $maxFallbackBytes,
                'driver' => static::class,
            ]);

            return false;
        }

        // Last resort for drivers without stream support (small files only)
        $content = file_get_contents($localPath);
        if ($content === false) {
            return false;
        }

        return $this->store($remotePath, $content);
    }
}
