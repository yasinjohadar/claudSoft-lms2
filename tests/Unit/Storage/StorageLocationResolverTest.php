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
        $this->assertCount(2, $result['locations']);
    }

    public function test_classifies_local_only_when_only_local_backend_has_file(): void
    {
        $local = new AppStorageConfig(['name' => 'Local', 'driver' => 'local']);
        $local->id = 21;
        $cloud = new AppStorageConfig(['name' => 'S3', 'driver' => 's3']);
        $cloud->id = 22;

        $manager = Mockery::mock(AppStorageManager::class);
        $manager->shouldReceive('resolveFailoverStorages')->andReturn(collect([$cloud, $local]));
        $manager->shouldReceive('existsOnConfig')
            ->andReturnUsing(fn (AppStorageConfig $config) => $config->id === $local->id);
        $manager->shouldReceive('getFileSizeOnConfig')->andReturn(80);
        $manager->shouldReceive('legacyPublicExists')->andReturn(false);

        $resolver = new StorageLocationResolver($manager);
        $result = $resolver->resolve('payment_receipts', 'payments/receipts/a.jpg');

        $this->assertSame(StorageLocationResolver::STATUS_LOCAL_ONLY, $result['status']);
        $this->assertFalse($result['locations'][0]['is_cloud']);
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

    public function test_classifies_elsewhere_when_file_lives_outside_the_disk_chain(): void
    {
        // القرص مربوط بـ iDrive، لكن الملف ما يزال على R2 من ربط قديم.
        $chained = new AppStorageConfig(['name' => 'iDrive', 'driver' => 's3', 'is_active' => true]);
        $chained->id = 20;
        $other = new AppStorageConfig(['name' => 'R2', 'driver' => 'cloudflare_r2', 'is_active' => true]);
        $other->id = 21;

        $manager = Mockery::mock(AppStorageManager::class);
        $manager->shouldReceive('resolveFailoverStorages')->with('blog_images')->andReturn(collect([$chained]));
        $manager->shouldReceive('existsOnConfig')
            ->andReturnUsing(fn (AppStorageConfig $c) => $c->id === 21);
        $manager->shouldReceive('getFileSizeOnConfig')->andReturn(99);
        $manager->shouldReceive('legacyPublicExists')->andReturn(false);

        $resolver = new class($manager) extends StorageLocationResolver {
            protected function allActiveStorages(): Collection
            {
                $other = new AppStorageConfig(['name' => 'R2', 'driver' => 'cloudflare_r2', 'is_active' => true]);
                $other->id = 21;

                return collect([$other]);
            }
        };

        $result = $resolver->resolve('blog_images', 'blog/images/old.jpg');

        // قبل الإصلاح كان هذا يُبلَّغ عنه "missing"
        $this->assertSame(StorageLocationResolver::STATUS_ELSEWHERE, $result['status']);
        $this->assertTrue($result['found']);
        $this->assertSame('R2', $result['storage_name']);
    }

    public function test_still_reports_missing_when_no_store_has_the_file(): void
    {
        $chained = new AppStorageConfig(['name' => 'iDrive', 'driver' => 's3', 'is_active' => true]);
        $chained->id = 30;

        $manager = Mockery::mock(AppStorageManager::class);
        $manager->shouldReceive('resolveFailoverStorages')->andReturn(collect([$chained]));
        $manager->shouldReceive('existsOnConfig')->andReturn(false);
        $manager->shouldReceive('legacyPublicExists')->andReturn(false);

        $resolver = new StorageLocationResolver($manager);
        $result = $resolver->resolve('blog_images', 'blog/images/gone.jpg');

        $this->assertSame(StorageLocationResolver::STATUS_MISSING, $result['status']);
        $this->assertFalse($result['found']);
    }
}
