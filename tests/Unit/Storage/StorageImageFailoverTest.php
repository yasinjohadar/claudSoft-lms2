<?php

namespace Tests\Unit\Storage;

use App\Services\Storage\AppStorageManager;
use Tests\TestCase;

class StorageImageFailoverTest extends TestCase
{
    public function test_resolve_path_candidates_includes_blog_prefix_for_filename_only(): void
    {
        $manager = app(AppStorageManager::class);

        $candidates = $manager->resolvePathCandidates('abc123.jpg');

        $this->assertContains('abc123.jpg', $candidates);
        $this->assertContains('blog/images/abc123.jpg', $candidates);
    }

    public function test_resolve_path_candidates_keeps_full_blog_path(): void
    {
        $manager = app(AppStorageManager::class);

        $candidates = $manager->resolvePathCandidates('blog/images/example.jpg');

        $this->assertSame(['blog/images/example.jpg', 'example.jpg'], $candidates);
    }

    public function test_blog_images_is_marked_cloud_only_in_config(): void
    {
        $manager = app(AppStorageManager::class);

        $this->assertTrue($manager->isCloudOnlyDisk('blog_images'));
        $this->assertContains('blog_images', config('storage_inventory.cloud_only_disks', []));
    }
}
