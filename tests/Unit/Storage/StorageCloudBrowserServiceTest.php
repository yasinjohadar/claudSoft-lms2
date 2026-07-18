<?php

namespace Tests\Unit\Storage;

use App\Models\AppStorageConfig;
use App\Services\Storage\AppStorageManager;
use App\Services\Storage\StorageCloudBrowserService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Mockery;
use Tests\TestCase;

class StorageCloudBrowserServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_normalize_path_rejects_parent_traversal(): void
    {
        $service = app(StorageCloudBrowserService::class);

        $this->expectException(\InvalidArgumentException::class);
        $service->normalizePath('blog/../etc/passwd');
    }

    public function test_browse_lists_directories_and_files(): void
    {
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('directories')->with('blog/images')->andReturn(['blog/images/sub']);
        $filesystem->shouldReceive('files')->with('blog/images')->andReturn(['blog/images/a.webp']);
        $filesystem->shouldReceive('size')->with('blog/images/a.webp')->andReturn(2048);
        $filesystem->shouldReceive('lastModified')->with('blog/images/a.webp')->andReturn(1700000000);

        $manager = Mockery::mock(AppStorageManager::class);
        $manager->shouldReceive('getFilesystemForConfig')->once()->andReturn($filesystem);

        $config = new AppStorageConfig([
            'name' => 'Test S3',
            'driver' => 's3',
            'is_active' => true,
        ]);
        $config->id = 1;

        $service = new StorageCloudBrowserService($manager);
        $result = $service->browse($config, 'blog/images');

        $this->assertSame('blog/images', $result['path']);
        $this->assertCount(1, $result['directories']);
        $this->assertSame('sub', $result['directories'][0]['name']);
        $this->assertCount(1, $result['files']);
        $this->assertSame('a.webp', $result['files'][0]['name']);
        $this->assertSame(2048, $result['files'][0]['size']);
        $this->assertSame(1, $result['summary']['file_count']);
        $this->assertSame(2048, $result['summary']['total_bytes']);
    }

    public function test_breadcrumbs_build_nested_path(): void
    {
        $service = app(StorageCloudBrowserService::class);
        $crumbs = $service->breadcrumbs('blog/images/thumbs');

        $this->assertSame([
            ['label' => 'الجذر', 'path' => ''],
            ['label' => 'blog', 'path' => 'blog'],
            ['label' => 'images', 'path' => 'blog/images'],
            ['label' => 'thumbs', 'path' => 'blog/images/thumbs'],
        ], $crumbs);
    }
}
