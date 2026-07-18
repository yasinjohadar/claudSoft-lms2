<?php

namespace Tests\Unit\Storage;

use App\Services\Storage\AppStorageManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyPublicPhysicalPathTest extends TestCase
{
    public function test_legacy_public_exists_uses_physical_disk_not_public_disk_mapping(): void
    {
        $relativePath = 'blog/images/legacy-probe-test.jpg';
        $fullPath = storage_path('app/public/'.$relativePath);
        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($fullPath, 'physical-bytes');

        Config::set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('app/missing-root'),
            'visibility' => 'public',
            'throw' => false,
        ]);

        Storage::forgetDisk('public');

        $manager = app(AppStorageManager::class);

        $this->assertTrue($manager->legacyPublicExists($relativePath));
        $this->assertSame(strlen('physical-bytes'), $manager->legacyPublicSize($relativePath));
        $this->assertSame('physical-bytes', $manager->getLegacyPublicContent($relativePath));

        @unlink($fullPath);
    }

    public function test_legacy_public_exists_is_false_when_only_cloud_disk_has_file(): void
    {
        Config::set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('app/cloud-mapped-root'),
            'visibility' => 'public',
            'throw' => false,
        ]);

        Storage::forgetDisk('public');
        Storage::disk('public')->put('blog/images/cloud-only.jpg', 'cloud');

        $manager = app(AppStorageManager::class);

        $this->assertFalse($manager->legacyPublicExists('blog/images/cloud-only.jpg'));

        Storage::disk('public')->delete('blog/images/cloud-only.jpg');
    }
}
