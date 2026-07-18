<?php

namespace Tests\Unit\Storage;

use App\Models\AppStorageConfig;
use App\Services\Storage\AppStorageManager;
use App\Services\Storage\StorageBulkMigrationService;
use App\Services\Storage\StorageLocationResolver;
use Mockery;
use Tests\TestCase;

class StorageCleanupTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cleanup_refuses_when_file_not_on_cloud(): void
    {
        $manager = Mockery::mock(AppStorageManager::class);
        $resolver = Mockery::mock(StorageLocationResolver::class);
        $resolver->shouldReceive('resolve')->once()->andReturn([
            'found' => true,
            'status' => StorageLocationResolver::STATUS_LOCAL_ONLY,
            'path' => 'payments/receipts/only-local.jpg',
            'locations' => [
                [
                    'storage_config_id' => 1,
                    'storage_name' => 'Local',
                    'driver' => 'local',
                    'is_cloud' => false,
                    'size' => 10,
                ],
            ],
            'size' => 10,
        ]);
        $manager->shouldReceive('resolveLocalConfigsForInventory')->never();
        $manager->shouldReceive('deleteLocalFromConfig')->never();

        $service = new StorageBulkMigrationService($manager, $resolver);
        $result = $service->cleanupLocalIfVerified('payment_receipts', 'payments/receipts/only-local.jpg');

        $this->assertFalse($result['success']);
        $this->assertSame('skipped', $result['action']);
        $this->assertStringContainsString('محلي فقط', $result['message']);
    }

    public function test_cleanup_deletes_local_when_both_confirmed(): void
    {
        $local = new AppStorageConfig(['name' => 'Local', 'driver' => 'local']);
        $local->id = 5;

        $manager = Mockery::mock(AppStorageManager::class);
        $resolver = Mockery::mock(StorageLocationResolver::class);

        $both = [
            'found' => true,
            'status' => StorageLocationResolver::STATUS_BOTH,
            'path' => 'payments/receipts/dup.jpg',
            'locations' => [
                [
                    'storage_config_id' => 9,
                    'storage_name' => 'S3',
                    'driver' => 's3',
                    'is_cloud' => true,
                    'size' => 20,
                ],
                [
                    'storage_config_id' => 5,
                    'storage_name' => 'Local',
                    'driver' => 'local',
                    'is_cloud' => false,
                    'size' => 20,
                ],
            ],
            'size' => 20,
        ];

        $after = [
            'found' => true,
            'status' => StorageLocationResolver::STATUS_CLOUD_ONLY,
            'path' => 'payments/receipts/dup.jpg',
            'locations' => [
                [
                    'storage_config_id' => 9,
                    'storage_name' => 'S3',
                    'driver' => 's3',
                    'is_cloud' => true,
                    'size' => 20,
                ],
            ],
            'size' => 20,
        ];

        $resolver->shouldReceive('resolve')->twice()->andReturn($both, $after);
        $resolver->shouldReceive('isCloudDriver')->andReturnUsing(fn (string $driver) => $driver === 's3');
        $manager->shouldReceive('resolveLocalConfigsForInventory')->andReturn(collect([$local]));
        $manager->shouldReceive('existsOnConfig')->andReturn(true);
        $manager->shouldReceive('getLocalConfigRoot')->andReturn(storage_path('app/public'));
        $manager->shouldReceive('deleteLocalFromConfig')->with($local, 'payments/receipts/dup.jpg')->andReturn(true);
        $manager->shouldReceive('legacyPublicExists')->andReturn(false);

        $service = new StorageBulkMigrationService($manager, $resolver);
        $result = $service->cleanupLocalIfVerified('payment_receipts', 'payments/receipts/dup.jpg');

        $this->assertTrue($result['success']);
        $this->assertSame('cleaned', $result['action']);
        $this->assertSame(StorageLocationResolver::STATUS_CLOUD_ONLY, $result['after_status']);
    }

    public function test_both_succeeds_when_local_gone_even_if_post_cloud_probe_is_missing(): void
    {
        $local = new AppStorageConfig(['name' => 'Local', 'driver' => 'local']);
        $local->id = 5;

        $manager = Mockery::mock(AppStorageManager::class);
        $resolver = Mockery::mock(StorageLocationResolver::class);

        $both = [
            'found' => true,
            'status' => StorageLocationResolver::STATUS_BOTH,
            'path' => 'blog/images/a.jpg',
            'locations' => [
                ['storage_config_id' => 9, 'storage_name' => 'S3', 'driver' => 's3', 'is_cloud' => true, 'size' => 20],
                ['storage_config_id' => null, 'storage_name' => 'Laravel public disk', 'driver' => 'local', 'is_cloud' => false, 'size' => 20],
            ],
            'size' => 20,
        ];

        $afterMissing = [
            'found' => false,
            'status' => StorageLocationResolver::STATUS_MISSING,
            'path' => 'blog/images/a.jpg',
            'locations' => [],
            'size' => 0,
        ];

        $resolver->shouldReceive('resolve')->twice()->andReturn($both, $afterMissing);
        $resolver->shouldReceive('isCloudDriver')->andReturnUsing(fn (string $driver) => $driver === 's3');
        $manager->shouldReceive('resolveLocalConfigsForInventory')->andReturn(collect([$local]));
        $manager->shouldReceive('existsOnConfig')->andReturn(false);
        $manager->shouldReceive('getLocalConfigRoot')->andReturn(storage_path('app/public'));
        $manager->shouldReceive('deleteLegacyPublicCopy')->with('blog/images/a.jpg')->andReturn(true);
        $manager->shouldReceive('legacyPublicExists')->andReturn(false);

        $service = new StorageBulkMigrationService($manager, $resolver);
        $result = $service->cleanupLocalIfVerified('blog_images', 'blog/images/a.jpg');

        $this->assertTrue($result['success']);
        $this->assertSame('cleaned', $result['action']);
    }

    public function test_delete_local_only_requires_explicit_allow_flag(): void
    {
        $manager = Mockery::mock(AppStorageManager::class);
        $resolver = Mockery::mock(StorageLocationResolver::class);
        $resolver->shouldReceive('resolve')->once()->andReturn([
            'found' => true,
            'status' => StorageLocationResolver::STATUS_LOCAL_ONLY,
            'path' => 'x.jpg',
            'locations' => [
                ['storage_config_id' => 1, 'storage_name' => 'Local', 'driver' => 'local', 'is_cloud' => false, 'size' => 1],
            ],
            'size' => 1,
        ]);
        $manager->shouldReceive('deleteLocalFromConfig')->never();

        $service = new StorageBulkMigrationService($manager, $resolver);
        $result = $service->deleteLocalCopy('public', 'x.jpg', allowOrphanLocal: false);

        $this->assertFalse($result['success']);
        $this->assertSame('skipped', $result['action']);
    }
}
