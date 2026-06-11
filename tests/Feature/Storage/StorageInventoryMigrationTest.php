<?php

namespace Tests\Feature\Storage;

use App\Services\Storage\StorageBulkMigrationService;
use App\Services\Storage\StorageInventoryService;
use App\Services\Storage\StorageLocationResolver;
use Tests\TestCase;

class StorageInventoryMigrationTest extends TestCase
{
    public function test_inventory_service_summarizes_status_counts(): void
    {
        $service = app(StorageInventoryService::class);

        $summary = $service->summarize([
            ['status' => StorageLocationResolver::STATUS_CLOUD_ONLY, 'size' => 100],
            ['status' => StorageLocationResolver::STATUS_LOCAL_ONLY, 'size' => 200],
            ['status' => StorageLocationResolver::STATUS_BOTH, 'size' => 300],
            ['status' => StorageLocationResolver::STATUS_MISSING, 'size' => 0],
        ]);

        $this->assertSame(4, $summary['total']);
        $this->assertSame(1, $summary['cloud_only']);
        $this->assertSame(1, $summary['local_only']);
        $this->assertSame(1, $summary['both']);
        $this->assertSame(1, $summary['missing']);
        $this->assertSame(200, $summary['local_only_bytes']);
        $this->assertSame(300, $summary['both_bytes']);
    }

    public function test_bulk_migration_dry_run_skips_cloud_only(): void
    {
        $this->mock(StorageLocationResolver::class, function ($mock) {
            $mock->shouldReceive('resolve')
                ->andReturn([
                    'found' => true,
                    'status' => StorageLocationResolver::STATUS_CLOUD_ONLY,
                    'path' => 'blog/images/already.jpg',
                    'locations' => [],
                    'size' => 50,
                ]);
        });

        $migration = app(StorageBulkMigrationService::class);

        $preview = $migration->dryRun([
            ['disk' => 'blog_images', 'path' => 'blog/images/already.jpg'],
        ]);

        $this->assertSame(0, $preview['eligible_count']);
        $this->assertSame(1, $preview['skipped_count']);
    }

    public function test_blog_image_url_helper_returns_proxy_for_blog_path(): void
    {
        $url = blog_image_url('blog/images/example.jpg');

        $this->assertNotEmpty($url);
        $this->assertStringContainsString('blog/images', $url);
    }

    public function test_course_image_url_helper_returns_url_for_course_path(): void
    {
        $url = course_image_url('courses/images/example.jpg');

        $this->assertNotEmpty($url);
        $this->assertStringContainsString('courses/images', $url);
    }
}
