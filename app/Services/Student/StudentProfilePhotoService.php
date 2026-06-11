<?php

namespace App\Services\Student;

use App\Models\User;
use App\Services\Storage\AppStorageManager;
use App\Services\Storage\StorageHelperService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class StudentProfilePhotoService
{
    private const DISK = 'public';

    private const DIRECTORY = 'profile-photos';

    private const FILE_TYPE = 'profile-photo';

    public function __construct(
        protected StorageHelperService $storageHelper,
        protected AppStorageManager $storageManager,
    ) {}

    public function store(User $user, UploadedFile $file): ?string
    {
        $this->deleteForUser($user);

        $storedPath = $this->storageHelper->storeUploadedFileWithFailover(
            self::DISK,
            self::DIRECTORY,
            $file,
            self::FILE_TYPE
        );

        if ($storedPath) {
            Log::info('StudentProfilePhotoService: Photo stored', [
                'user_id' => $user->id,
                'path' => $storedPath,
            ]);
        }

        return $storedPath;
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $path = ltrim($path, '/');
        $path = preg_replace('#^storage/#', '', $path);

        foreach ($this->storageManager->resolveFailoverStorages(self::DISK) as $config) {
            if ($this->storageManager->existsOnConfig($config, $path)) {
                $this->storageManager->deleteFromConfig($config, $path);
            }
        }

        if ($this->storageManager->legacyPublicExists($path)) {
            $this->storageManager->deleteLegacyPublic($path);
        }
    }

    public function deleteForUser(User $user): void
    {
        $paths = array_values(array_unique(array_filter([
            $user->photo,
            $user->avatar,
        ])));

        foreach ($paths as $path) {
            $this->delete($path);
        }
    }
}
