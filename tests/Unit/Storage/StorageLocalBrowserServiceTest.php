<?php

namespace Tests\Unit\Storage;

use App\Services\Storage\StorageCloudBrowserService;
use App\Services\Storage\StorageLocalBrowserService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StorageLocalBrowserServiceTest extends TestCase
{
    protected string $tempRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempRoot = storage_path('app/public/local_browser_test_'.uniqid());
        File::makeDirectory($this->tempRoot.'/blog/images', 0755, true);
        File::put($this->tempRoot.'/blog/images/a.webp', str_repeat('x', 1024));
        File::put($this->tempRoot.'/readme.txt', 'hello');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempRoot);
        parent::tearDown();
    }

    public function test_browse_lists_local_directories_and_files(): void
    {
        $service = new class($this->app->make(StorageCloudBrowserService::class), $this->tempRoot) extends StorageLocalBrowserService
        {
            public function __construct(StorageCloudBrowserService $pathHelper, private string $testRoot)
            {
                parent::__construct($pathHelper);
            }

            public function rootPath(): string
            {
                return $this->testRoot;
            }
        };

        $rootListing = $service->browse('');
        $this->assertSame(1, $rootListing['summary']['directory_count']);
        $this->assertSame(1, $rootListing['summary']['file_count']);

        $blogListing = $service->browse('blog/images');
        $this->assertSame('blog/images', $blogListing['path']);
        $this->assertSame('a.webp', $blogListing['files'][0]['name']);
        $this->assertSame(1024, $blogListing['files'][0]['size']);
    }

    public function test_browse_rejects_paths_outside_root(): void
    {
        $service = new class($this->app->make(StorageCloudBrowserService::class), $this->tempRoot) extends StorageLocalBrowserService
        {
            public function __construct(StorageCloudBrowserService $pathHelper, private string $testRoot)
            {
                parent::__construct($pathHelper);
            }

            public function rootPath(): string
            {
                return $this->testRoot;
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $service->browse('../..');
    }
}
