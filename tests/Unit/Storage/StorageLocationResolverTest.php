<?php

namespace Tests\Unit\Storage;

use App\Models\AppStorageConfig;
use App\Services\Storage\AppStorageManager;
use App\Services\Storage\StorageLocationResolver;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class StorageLocationResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_classifies_cloud_only_when_only_cloud_backend_has_file(): void
    {
        $cloud = new AppStorageConfig(['name' => 'iDrive', 'driver' => 's3', 'is_active' => true]);
        $cloud->id = 10;

        $manager = Mockery::mock(AppStorageManager::class);
        $manager->shouldReceive('resolveFailoverStorages')->with('blog_images')->andReturn(collect([$cloud]));
        $manager->shouldReceive('existsOnConfig')->with($cloud, 'blog/images/a.jpg')->andReturn(true);
        $manager->shouldReceive('getFileSizeOnConfig')->with($cloud, 'blog/images/a.jpg')->andReturn(120);
        $manager->shouldReceive('legacyPublicExists')->andReturn(false);

        $resolver = new StorageLocationResolver($manager);
        $result = $resolver->resolve('blog_images', 'blog/images/a.jpg');

        $this->assertSame(StorageLocationResolver::STATUS_CLOUD_ONLY, $result['status']);
        $this->assertTrue($result['found']);
    }

    public function test_classifies_both_when_cloud_and_local_have_file(): void
    {
        $cloud = new AppStorageConfig(['name' => 'S3', 'driver' => 's3']);
        $cloud->id = 11;
        $local = new AppStorageConfig(['name' => 'Local', 'driver' => 'local']);
        $local->id = 12;

        $manager = Mockery::mock(AppStorageManager::class);
        $manager->shouldReceive('resolveFailoverStorages')->andReturn(collect([$cloud, $local]));
        $manager->shouldReceive('existsOnConfig')
            ->andReturnUsing(function (AppStorageConfig $config) use ($cloud, $local) {
                return in_array($config->id, [$cloud->id, $local->id], true);
            });
        $manager->shouldReceive('getFileSizeOnConfig')->andReturn(50);
        $manager->shouldReceive('legacyPublicExists')->andReturn(false);

        $resolver = new StorageLocationResolver($manager);
        $result = $resolver->resolve('public', 'courses/images/x.jpg');

        $this->assertSame(StorageLocationResolver::STATUS_BOTH, $result['status']);
    }

    public function test_normalizes_storage_prefixed_paths(): void
    {
        $cloud = new AppStorageConfig(['name' => 'Cloud', 'driver' => 's3']);
        $cloud->id = 99;

        $manager = Mockery::mock(AppStorageManager::class);
        $manager->shouldReceive('resolveFailoverStorages')->andReturn(collect([$cloud]));
        $manager->shouldReceive('existsOnConfig')->with($cloud, 'blog/images/normalized.jpg')->andReturn(false);
        $manager->shouldReceive('legacyPublicExists')->andReturn(false);

        $resolver = new StorageLocationResolver($manager);
        $result = $resolver->resolve('blog_images', 'storage/blog/images/normalized.jpg');

        $this->assertSame('blog/images/normalized.jpg', $result['path']);
        $this->assertSame(StorageLocationResolver::STATUS_MISSING, $result['status']);
    }
}
