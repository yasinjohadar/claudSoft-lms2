<?php

namespace Tests\Unit\Storage;

use App\Models\AppStorageConfig;
use App\Services\Storage\AppStorageManager;
use App\Services\Storage\StorageCapacityService;
use App\Services\Storage\StorageCloudBrowserService;
use App\Services\Storage\StorageLocalBrowserService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class StorageCapacityServiceTest extends TestCase
{
    protected string $tempRoot;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->tempRoot = storage_path('app/public/capacity_test_'.uniqid());
        File::makeDirectory($this->tempRoot.'/nested', 0755, true);
        File::put($this->tempRoot.'/a.txt', str_repeat('a', 500));
        File::put($this->tempRoot.'/nested/b.txt', str_repeat('b', 300));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempRoot);
        Mockery::close();
        parent::tearDown();
    }

    public function test_calculate_local_usage_sums_all_files(): void
    {
        $service = $this->makeService(localRoot: $this->tempRoot);
        $usage = $service->calculateLocalUsage();

        $this->assertSame(800, $usage['bytes']);
        $this->assertSame(2, $usage['files']);
        $this->assertTrue($usage['exists']);
    }

    public function test_calculate_cloud_usage_sums_config_files(): void
    {
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('allFiles')->with('')->andReturn(['one.txt', 'two.txt']);
        $filesystem->shouldReceive('size')->with('one.txt')->andReturn(100);
        $filesystem->shouldReceive('size')->with('two.txt')->andReturn(250);

        $manager = Mockery::mock(AppStorageManager::class);
        $manager->shouldReceive('getFilesystemForConfig')->once()->andReturn($filesystem);

        $config = new AppStorageConfig([
            'name' => 'Cloud A',
            'driver' => 's3',
            'is_active' => true,
        ]);
        $config->id = 5;

        $cloudBrowser = Mockery::mock(StorageCloudBrowserService::class);
        $cloudBrowser->shouldReceive('availableConfigs')->once()->andReturn(collect([$config]));

        $service = new StorageCapacityService(
            $cloudBrowser,
            app(StorageLocalBrowserService::class),
            $manager,
        );

        $usage = $service->calculateCloudUsage();

        $this->assertSame(350, $usage['bytes']);
        $this->assertSame(2, $usage['files']);
        $this->assertSame('Cloud A', $usage['configs'][0]['name']);
    }

    public function test_get_cached_summary_is_remembered_until_refresh(): void
    {
        $payload = [
            'local' => ['bytes' => 10, 'files' => 1, 'root' => 'storage/app/public', 'exists' => true, 'error' => null],
            'cloud' => ['bytes' => 20, 'files' => 2, 'configs' => [], 'error' => null],
            'scanned_at' => '2026-01-01 00:00:00',
        ];

        $service = Mockery::mock(StorageCapacityService::class)->makePartial();
        $service->shouldReceive('calculateSummary')->twice()->andReturn($payload);

        $first = $service->getCachedSummary();
        $second = $service->getCachedSummary();

        $this->assertSame($first, $second);
        $this->assertSame($payload, $service->getCachedSummary(refresh: true));
    }

    protected function makeService(?string $localRoot = null): StorageCapacityService
    {
        $localBrowser = new class(app(StorageCloudBrowserService::class), $localRoot ?? $this->tempRoot) extends StorageLocalBrowserService
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

        $cloudBrowser = Mockery::mock(StorageCloudBrowserService::class);
        $cloudBrowser->shouldReceive('availableConfigs')->andReturn(collect());

        return new StorageCapacityService(
            $cloudBrowser,
            $localBrowser,
            Mockery::mock(AppStorageManager::class),
        );
    }
}
